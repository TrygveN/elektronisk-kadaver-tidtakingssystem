<?php
$hash = file_get_contents("data/hash.txt");
$method = $_SERVER['REQUEST_METHOD'];
print_r($_POST);
if ($method == "POST") {
    $runner_id = $_POST['id'];
    $name = $_POST['name'];
    $club = $_POST['club'];
    $course = $_POST['course'];
    
    $line = $runner_id . ";;" . $name . ";;;" . $club . ";" . $course . "\n";

    $password = $_POST['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    elseif (!ctype_digit($runner_id)){
        http_response_code(response_code: 400);
    }
    else {
        $file = 'data/db.csv';
        file_put_contents($file, $line, FILE_APPEND);
    }
}


