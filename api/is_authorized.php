<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
print_r($documentRoot);
$hash = file_get_contents("$documentRoot/data/hash.txt");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "POST") {
    $password = $_POST['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    else {
        http_response_code(response_code: 200);
    }
}
else {
        http_response_code(response_code: 405);
    }

