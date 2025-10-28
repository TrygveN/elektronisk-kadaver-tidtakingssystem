<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");
$hash = file_get_contents("$documentRoot/data/hash.txt");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $course = $query['course'];
    $password = getallheaders()['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    $runners = read_runners_from_csv();
    $emails = "";
    for ($i = 0; $i < count($runners); $i++) {
            $email = $runners[$i]->email;
            if (!isset($query['course'])){
                $emails .= "$email, ";
            }
            elseif ($course == $runners[$i]->course) {
                $emails .= "$email, ";
            }
        }
    echo($emails);
}