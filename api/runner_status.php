<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$documentRoot/import_runners.php");
include("$documentRoot/data/hash.php");

header("Content-Type: text/html; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] !== "PATCH") {
    http_response_code(405);
    header("Allow: PATCH");
    echo("<span class='danger'>Method not allowed</span>");
    exit;
}

$password = $_SERVER['HTTP_PASSWORD'] ?? "";
if (!password_verify($password, $hash)) {
    http_response_code(401);
    echo("<span class='danger'>Ikke autorisert</span>");
    exit;
}

parse_str(file_get_contents("php://input"), $_PATCH);
$id = $_PATCH["id"] ?? "";
$field = $_PATCH["field"] ?? "";
$value = ($_PATCH["value"] ?? "") === "true";
if (!ctype_digit($id) || !in_array($field, ["started", "paid"], true)) {
    http_response_code(400);
    echo("<span class='danger'>Ugyldig forespørsel</span>");
    exit;
}

$path = "$documentRoot/data/db.csv";
$handle = fopen($path, "c+");
if ($handle === false || !flock($handle, LOCK_EX)) {
    http_response_code(500);
    echo("<span class='danger'>Kunne ikke åpne løperbasen</span>");
    exit;
}

$lines = file($path, FILE_IGNORE_NEW_LINES);
$found = false;
if ($lines !== false && isset($lines[0])) {
    $header_fields = array_pad(str_getcsv($lines[0], ";"), 11, "");
    $header_fields[9] = "Startet";
    $header_fields[10] = "Betalt";
    $lines[0] = implode(";", array_map("csv_field", $header_fields));

    $column = $field === "started" ? 9 : 10;
    for ($i = 1; $i < count($lines); $i++) {
        if (trim($lines[$i]) === "") {
            continue;
        }
        $fields = str_getcsv($lines[$i], ";");
        if ((int) ($fields[0] ?? -1) !== (int) $id) {
            continue;
        }
        $fields = array_pad($fields, 11, "false");
        $fields[$column] = $value ? "true" : "false";
        $lines[$i] = implode(";", array_map("csv_field", $fields));
        $found = true;
        break;
    }
}

if (!$found) {
    flock($handle, LOCK_UN);
    fclose($handle);
    http_response_code(404);
    echo("<span class='danger'>Fant ikke løperen</span>");
    exit;
}

$content = implode("\n", $lines) . "\n";
rewind($handle);
$written = ftruncate($handle, 0) && fwrite($handle, $content) !== false;
fflush($handle);
flock($handle, LOCK_UN);
fclose($handle);

if (!$written) {
    http_response_code(500);
    echo("<span class='danger'>Kunne ikke lagre endringen</span>");
    exit;
}

echo("<span class='success'>Endring lagret</span>");
