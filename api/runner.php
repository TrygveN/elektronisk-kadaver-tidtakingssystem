<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");


$hash = file_get_contents("$documentRoot/data/hash.txt");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "POST") {
    $runner_id = $_POST['id'];
    $name = $_POST['name'];
    $club = $_POST['club'];
    $course = $_POST['course'];
    
    $line = $runner_id . ";;" . $name . ";;;" . $club . ";" . $course . "\n;;";

    $password = $_POST['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    elseif (!ctype_digit($runner_id)){
        http_response_code(response_code: 400);
    }
    else {
        $file = "$documentRoot/data/db.csv";
        file_put_contents($file, $line, FILE_APPEND);
    }
}
if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'], $query);
    
    $runners = read_runners_from_csv();
    $filtered = search_for_runner($runners, $query['search']);
    if (count($filtered) == 1){
        $r = $filtered[0];
        $response .= "
        <h2> $r->name</h2>
        <p> <b>Klubb:</b> $r->club</p>
        <p> <b>Løype:</b> $r->course</p>
        <p> <b>Epost:</b> <a href=\"mailto:$r->email\">$r->email</a></p>
        <p> <b>Mobilnummer:</b> <a href=\"tel:$r->phone\">$r->phone</a></p>
        <p> <b>Student?</b> $r->is_student</p>
        ";
        header("HX-Replace-Url: false");
        echo($response);
    }
    elseif (count($filtered) > 1){
        $response = "";
        
        for ($i = 0; $i < count($filtered); $i++) {
            $runner = $filtered[$i];
            $response .= "<button class=\"default\" hx-get=\"/api/runner.php?search=$runner->id\" hx-target=\"#runner_info\" hx-swap=\"show:none\">$runner->id $runner->name</button>";
            header("HX-Replace-Url: false");
        }
        echo($response);
    }
    else{
        echo("Ingen resultater...");
    }

}