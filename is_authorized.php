<?php
$hash = file_get_contents("data/hash.txt");
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

