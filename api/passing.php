<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

$control = $_POST['control'];
$runner_id = $_POST['id'];
$time = $_POST['time'];
$password = $_POST['password'];

$hash = file_get_contents("$documentRoot/data/hash.txt");

if (!password_verify($password, $hash)) {
    http_response_code(response_code: 401);
}
else {
    $file = "$documentRoot/data/passering.csv";
    $current = file_get_contents($file);
    $current .= $control . "," . $runner_id . "," . $time . "\n";
    file_put_contents($file, $current);
}