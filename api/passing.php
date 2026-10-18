<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

include("$documentRoot/data/hash.php");
include("$documentRoot/import_runners.php");

$control = $_POST['control'] ?? "";
$runner_id = $_POST['id'] ?? "";
$time = $_POST['time'] ?? "";
$password = $_POST['password'] ?? "";

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    http_response_code(response_code: 405);
}
elseif (!password_verify($password, $hash)) {
    http_response_code(response_code: 401);
}
elseif (!ctype_digit($runner_id) || !in_array($control, ["1", "2", "3"], true)) {
    http_response_code(response_code: 400);
    echo("Ugyldig startnummer eller post.");
}
elseif (!DateTime::createFromFormat("Y-m-d\TH:i:sp", $time)) {
    http_response_code(response_code: 400);
    echo("Ugyldig tidsformat.");
}
else {
    $file = "$documentRoot/data/passering.csv";
    $line = (int) $control . "," . (int) $runner_id . "," . $time . "\n";
    append_line($file, $line);
}