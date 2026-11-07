<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/data/hash.php");

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== "POST") {
    http_response_code(response_code: 405);
}
else {
    $index = $_POST['index'] ?? "";
    $password = $_POST['password'] ?? "";

    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
        echo("Feil passord.");
    }
    elseif (!ctype_digit($index)) {
        http_response_code(response_code: 400);
        echo("Ugyldig linjenummer.");
    }
    else {
        $file = "$documentRoot/data/passering.csv";
        $handle = fopen($file, "c+");
        if ($handle === false) {
            http_response_code(response_code: 500);
            echo("Kunne ikke åpne passeringer.");
        }
        else {
            flock($handle, LOCK_EX);
            $kept = [];
            $seen = 0;
            $removed = false;
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === "") {
                    $kept[] = $line;
                    continue;
                }
                if (!$removed && $seen === (int) $index) {
                    $removed = true;
                }
                else {
                    $kept[] = $line;
                }
                $seen++;
            }

            if (!$removed) {
                http_response_code(response_code: 404);
                echo("Fant ikkje passeringa.");
            }
            elseif (rewind($handle) === false || ftruncate($handle, 0) === false
                || fwrite($handle, implode("", $kept)) === false) {
                http_response_code(response_code: 500);
                echo("Kunne ikke slette passeringa.");
            }
            else {
                echo("Passering slett.");
            }
            fflush($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
