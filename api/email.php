<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");
include("$documentRoot/data/hash.php");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
    $course = $query['course'] ?? "";
    $password = getallheaders()['Password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    } else {
        $runners = read_runners_from_csv();
        $emails = [];
        for ($i = 0; $i < count($runners); $i++) {
                $email = trim($runners[$i]->email);
                if ($email === "") {
                    continue;
                }
                if (!isset($query['course']) || $course == $runners[$i]->course) {
                    $emails[] = $email;
                }
            }
        echo(implode(", ", $emails));
    }
}