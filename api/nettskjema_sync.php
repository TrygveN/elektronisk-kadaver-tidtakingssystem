<?php
// Synkroniserer løperbasen (data/db.csv) fra Nettskjema sitt csv-report-endepunkt.
// Synken legger kun til nye løpere nederst i db.csv. Eksisterende rader endres aldri.
// Manuelle løpere (tom $created-kolonne) matches på navn, og Nettskjema-rader med samme navn hoppes over.
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

function nettskjema_runner_key($fields)
{
    return trim($fields[1] ?? "") . "|" . trim($fields[8] ?? "");
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

$password = rawurldecode($_SERVER['HTTP_PASSWORD'] ?? "");
if (!password_verify($password, $hash)) {
    http_response_code(401);
    echo("<span class='bg-danger'>Feil passord.</span>");
    exit;
}

$app_config = parse_ini_file("$documentRoot/data/config.ini") ?: [];
if (in_array($app_config["deaktiver_nettskjema_synk"] ?? "0", ["1", "true", "on", "yes"], true)) {
    nettskjema_error("synkronisering fra Nettskjema er deaktivert i data/config.ini.", 403);
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

// Eksisterende rader i db.csv skrives aldri over. Nye løpere legges kun til nederst.
// Nettskjema-rader identifiseres med $created og $answer_time_ms, manuelle rader (tom $created) med navn.
$db_path = "$documentRoot/data/db.csv";
$db_text = is_readable($db_path) ? file_get_contents($db_path) : "";
$existing_rows = nettskjema_split_rows($db_text);
$existing_keys = [];
$manual_names = [];
foreach (array_slice($existing_rows, 1) as $raw) {
    $fields = str_getcsv($raw, ";");
    if (trim($fields[1] ?? "") === "") {
        $manual_names[nettskjema_normalize_name($fields[2] ?? "")] = true;
        continue;
    }
    $existing_keys[nettskjema_runner_key($fields)] = true;
}

$report_header = array_shift($report_rows);
if (count($existing_rows) < 1) {
    $content = $report_header . ";Startet;Betalt\n";
}
else {
    $content = rtrim($db_text, "\r\n") . "\n";
}

$new_runners = [];
foreach ($report_rows as $raw) {
    $fields = str_getcsv($raw, ";");
    $key = nettskjema_runner_key($fields);
    $name_key = nettskjema_normalize_name($fields[2] ?? "");
    if (isset($existing_keys[$key]) || isset($manual_names[$name_key])) {
        continue;
    }
    $existing_keys[$key] = true;
    $content .= $raw . ";false;false\n";
    $new_runners[] = [
        "id" => $fields[0] ?? "",
        "name" => html_entity_decode($fields[2] ?? "", ENT_QUOTES | ENT_HTML5, "UTF-8"),
    ];
}

$new_count = count($new_runners);

$tmp_path = "$documentRoot/data/db.csv.tmp";
if (file_put_contents($tmp_path, $content) === false) {
    nettskjema_error("kunne ikke skrive til data/db.csv.", 500);
}
if (!rename($tmp_path, $db_path)) {
    @unlink($tmp_path);
    nettskjema_error("kunne ikke oppdatere data/db.csv.", 500);
}

header("HX-Replace-Url: false");
echo("<span class='bg-success'>Synk fullført: $new_count nye løpere lagt til"
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
    echo("<div class='flash accent'><b>Ingen nye løpere siden sist synkronisering</b></div>");
}