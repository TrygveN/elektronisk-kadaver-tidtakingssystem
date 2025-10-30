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
    
    $line = $runner_id . ";;" . $name . ";;;" . $club . ";" . $course . ";;;\n";

    $password = $_POST['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    elseif (!ctype_digit($runner_id)){
        http_response_code(response_code: 400);
    }
    else {
        $runners = read_runners_from_csv();
        $filtered = search_for_runner($runners, $runner_id);
        if ($filtered != []) {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Startnummer er allerede i bruk!</span>");
        } else {
            $file = "$documentRoot/data/db.csv";
            file_put_contents($file, $line, FILE_APPEND);
            header("HX-Replace-Url: false");
            echo("Løper lagt til: $line");
        }


    }
}
if ($method == "PATCH") {
    parse_str(file_get_contents('php://input'), $_PATCH);
    $line = $_PATCH["id"] . ";;" . $_PATCH["name"] . ";" . $_PATCH["email"] . ";" . $_PATCH["phone"] . ";" . $_PATCH["club"] . ";" . $_PATCH["course"] . ";;;\n";

    $all_lines = file("$documentRoot/data/db.csv");
    $csv_line = (int)$_PATCH["line_in_csv"];

    $old_id = str_getcsv($all_lines[$csv_line], ";")[0];

    $runners = read_runners_from_csv();
    $filtered = search_for_runner($runners, $_PATCH["id"]);

    if (!is_int($csv_line)){
        http_response_code(response_code: 400);
    } elseif ($filtered != [] && $_PATCH["id"] != $old_id) {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Startnummer er allerede i bruk!</span>");
    } else {
        $all_lines[$csv_line] =  $line;
        file_put_contents("$documentRoot/data/db.csv",implode("",$all_lines));
        echo("<span class='bg-success'>Endret: $line</span>");
    }
}

if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'], $query);
    
    $runners = read_runners_from_csv();
    $filtered = search_for_runner($runners, $query['search']);
    if (count($filtered) == 1 && $query['edit'] == "true") {
        $r = $filtered[0];
        $response .= "
        <h2>Endre $r->id $r->name</h2>
        <form hx-patch='/api/runner.php'>
            <label>Startnummer<input type='number' id='id' name='id' required value='$r->id'></label>
            <label>Navn<input type='text' id='name' name='name' required value='$r->name'></label>
            <label>Forening<input type='text' id='club' name='club' required value='$r->club'></label>
            <label>
            <fieldset>
            <input type='radio' name='course' value='Kadaverløpet' checked='checked'>
            Kadaverløpet
            </label>
                <label>
            <input type='radio' name='course' value=\"Minikadaver'n\">
            Minikadaver'n
            </label>
            </fieldset>
            <label>epost<input type='text' id='email' name='email' value='$r->email'></label>
            <label>telefon<input type='text' id='phone' name='phone' value='$r->phone'></label>
            <label><input type='hidden' id='student' name='student' required value='$r->is_student'></label>
            <label><input type='hidden' id='line_in_csv' name='line_in_csv' required value='$r->line_in_csv'></label>
            <button type='submit'>Endre løper</button>
        </form>
        ";
        header("HX-Replace-Url: false");
        echo($response);
    } elseif (count($filtered) == 1){
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
            if ($query['edit'] == "true") {
                $url = "/api/runner.php?search=$runner->id&edit=true";
            } else {
                $url = "/api/runner.php?search=$runner->id";
            }
            $response .= "<button class=\"default\" hx-get=\"$url\" hx-target=\"#runner\" hx-swap=\"show:none\">$runner->id $runner->name</button>";
            header("HX-Replace-Url: false");
        }
        echo($response);
    }
    else{
        echo("Ingen resultater...");
    }

}