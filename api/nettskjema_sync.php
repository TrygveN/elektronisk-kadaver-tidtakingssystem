<?php
// Synkroniserer løperbasen (data/db.csv) fra Nettskjema sitt csv-report-endepunkt.
// Manuelle løpere (tom $created-kolonne) beholdes og vinner ved navnekollisjon.
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

include("$documentRoot/data/hash.php");

function nettskjema_error($message, $code = 400)
{
    http_response_code($code);
    echo("<span class='bg-danger'>Synk feilet: $message</span>");
    exit;
}

function nettskjema_http($url, $options = [])
{
    $ch = curl_init($url);
    curl_setopt_array($ch, $options + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        return [0, null, $error];
    }
    return [$status, $body, null];
}

function nettskjema_get_token($client_id, $client_secret, $cache_path)
{
    if (is_readable($cache_path)) {
        $cached = json_decode(file_get_contents($cache_path), true);
        if (is_array($cached) && isset($cached["access_token"], $cached["expires_at"])
            && $cached["expires_at"] - 60 > time()) {
            return $cached["access_token"];
        }
    }

    [$status, $body, $error] = nettskjema_http(
        "https://authorization.nettskjema.no/oauth2/token",
        [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_USERPWD => "$client_id:$client_secret",
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        ]
    );

    if ($error !== null) {
        nettskjema_error("kunne ikke kontakte autorisasjonsserveren ($error)", 502);
    }
    if ($status !== 200) {
        nettskjema_error("autorisasjon feilet (HTTP $status). Sjekk client_id/client_secret.", 502);
    }

    $token = json_decode($body, true);
    if (!is_array($token) || !isset($token["access_token"])) {
        nettskjema_error("ugyldig tokenrespons fra autorisasjonsserveren.", 502);
    }

    $expires_in = $token["expires_in"] ?? 86399;
    file_put_contents($cache_path, json_encode([
        "access_token" => $token["access_token"],
        "expires_at" => time() + (int) $expires_in,
    ]));

    return $token["access_token"];
}

function nettskjema_normalize_name($name)
{
    $name = html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, "UTF-8");
    return mb_strtolower(trim($name));
}

function nettskjema_split_rows($text)
{
    $rows = [];
    foreach (preg_split("/\r\n|\n|\r/", $text) as $line) {
        if (trim($line) === "") {
            continue;
        }
        $rows[] = $line;
    }
    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    exit;
}

$password = getallheaders()['Password'] ?? "";
if (!password_verify($password, $hash)) {
    http_response_code(401);
    exit;
}

$config_path = "$documentRoot/data/nettskjema.ini";
$config = is_readable($config_path) ? parse_ini_file($config_path) : false;
if (!$config || empty($config["client_id"]) || empty($config["client_secret"]) || empty($config["form_id"])) {
    nettskjema_error("mangler konfigurasjon. Fyll ut data/nettskjema.ini.", 500);
}
if ($config["client_id"] === "SETT_INN_CLIENT_ID") {
    nettskjema_error("data/nettskjema.ini er ikke fylt ut ennå.", 500);
}

$token = nettskjema_get_token(
    $config["client_id"],
    $config["client_secret"],
    "$documentRoot/data/nettskjema_token.json"
);

[$status, $report, $error] = nettskjema_http(
    "https://nettskjema.no/api/v3/form/" . urlencode($config["form_id"]) . "/csv-report",
    [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "Accept: text/csv",
        ],
    ]
);

if ($error !== null) {
    nettskjema_error("kunne ikke hente skjemaet ($error)", 502);
}
if ($status !== 200) {
    nettskjema_error("henting av skjema feilet (HTTP $status). Har klienten tilgang til skjemaet?", 502);
}

$report_rows = nettskjema_split_rows($report);
if (count($report_rows) < 1) {
    nettskjema_error("tomt svar fra Nettskjema.", 502);
}

$header = array_shift($report_rows);

// Eksisterende løpere i db.csv, brukt for å finne nye og for manuell-merge.
$db_path = "$documentRoot/data/db.csv";
$existing_rows = [];
$existing_keys = [];
if (is_readable($db_path)) {
    $existing_rows = nettskjema_split_rows(file_get_contents($db_path));
    array_shift($existing_rows);
    foreach ($existing_rows as $raw) {
        $fields = str_getcsv($raw, ";");
        $existing_keys[nettskjema_normalize_name($fields[2] ?? "")] = true;
    }
}

// API-rader indeksert på navn (duplikate innsendinger kollapses, første vinner).
$merged = [];
$new_runners = [];
foreach ($report_rows as $raw) {
    $fields = str_getcsv($raw, ";");
    $name = $fields[2] ?? "";
    $key = nettskjema_normalize_name($name);
    if (!array_key_exists($key, $merged)) {
        $merged[$key] = $raw;
        if (!isset($existing_keys[$key])) {
            $new_runners[] = [
                "id" => $fields[0] ?? "",
                "name" => html_entity_decode($name, ENT_QUOTES | ENT_HTML5, "UTF-8"),
            ];
        }
    }
}
$api_count = count($merged);

// Manuelle rader fra eksisterende db.csv (tom $created-kolonne). De vinner ved kollisjon.
$merge_count = 0;
foreach ($existing_rows as $raw) {
    $fields = str_getcsv($raw, ";");
    if (count($fields) < 2 || trim($fields[1]) !== "") {
        continue;
    }
    $key = nettskjema_normalize_name($fields[2] ?? "");
    if (array_key_exists($key, $merged)) {
        $merge_count++;
    }
    $merged[$key] = $raw;
}

$manual_count = count($merged) - $api_count;
$new_count = count($new_runners);

$content = $header . "\n" . implode("\n", array_values($merged)) . "\n";
$tmp_path = "$documentRoot/data/db.csv.tmp";
if (file_put_contents($tmp_path, $content) === false) {
    nettskjema_error("kunne ikke skrive til data/db.csv.", 500);
}
if (!rename($tmp_path, $db_path)) {
    @unlink($tmp_path);
    nettskjema_error("kunne ikke oppdatere data/db.csv.", 500);
}

header("HX-Replace-Url: false");
echo("<span class='bg-success'>Synk fullført: $api_count løpere fra Nettskjema"
    . ($merge_count > 0 ? ", $merge_count manuelle erstattet" : "")
    . ($manual_count > 0 ? ", $manual_count manuelle beholdt" : "")
    . ($new_count > 0 ? ", $new_count nye" : "")
    . ".</span>");

if ($new_count > 0) {
    echo("<div class='flash accent'><b>$new_count nye løpere:</b><ul>");
    foreach ($new_runners as $runner) {
        echo("<li>" . htmlspecialchars($runner["id"], ENT_QUOTES, "UTF-8") . " "
            . htmlspecialchars($runner["name"], ENT_QUOTES, "UTF-8") . "</li>");
    }
    echo("</ul></div>");
}
else {
    echo("<div class='flash accent'><b>Ingen nye løpere siden sist synkronisering</b><ul>");
}