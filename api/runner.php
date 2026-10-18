<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");


include("$documentRoot/data/hash.php");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "POST") {
    $runner_id = $_POST['id'] ?? "";
    $name = $_POST['name'] ?? "";
    $club = $_POST['club'] ?? "";
    $course = $_POST['course'] ?? "";

    $password = $_POST['password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    elseif (!ctype_digit($runner_id)){
        http_response_code(response_code: 400);
        echo("<span class='bg-danger'>Startnummer må vere eit tal.</span>");
    }
    elseif ($course != "Kadaverløpet" && $course != "Minikadaver'n") {
        http_response_code(response_code: 400);
        echo("<span class='bg-danger'>Ukjent klasse.</span>");
    }
    else {
        $runners = read_runners_from_csv();
        if (get_runner($runners, (int) $runner_id) !== false) {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Startnummer er allerede i bruk!</span>");
        } else {
            $line = csv_runner_line($runner_id, $name, "", "", $club, $course, "");
            $file = "$documentRoot/data/db.csv";
            append_line($file, $line);
            header("HX-Replace-Url: false");
            echo("Løper lagt til: " . esc($line));
        }


    }
}
if ($method == "PATCH") {
    $password = getallheaders()['Password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    } else {
        parse_str(file_get_contents('php://input'), $_PATCH);
        $csv_line = $_PATCH["line_in_csv"] ?? "";
        $id = $_PATCH["id"] ?? "";
        $name = $_PATCH["name"] ?? "";
        $email = $_PATCH["email"] ?? "";
        $phone = $_PATCH["phone"] ?? "";
        $club = $_PATCH["club"] ?? "";
        $course = $_PATCH["course"] ?? "";
        $student = $_PATCH["student"] ?? "";

        $all_lines = file("$documentRoot/data/db.csv");

        if (!ctype_digit($csv_line) || !isset($all_lines[(int) $csv_line])) {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Ugyldig linjenummer.</span>");
        } elseif (!ctype_digit($id)) {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Startnummer må vere eit tal.</span>");
        } else {
            $old_id = str_getcsv($all_lines[(int) $csv_line], ";")[0];
            $runners = read_runners_from_csv();

            if (get_runner($runners, (int) $id) !== false && $id != $old_id) {
                http_response_code(response_code: 400);
                echo("<span class='bg-danger'>Startnummer er allerede i bruk!</span>");
            } else {
                $line = csv_runner_line($id, $name, $email, $phone, $club, $course, $student);
                $all_lines[(int) $csv_line] = $line;
                file_put_contents("$documentRoot/data/db.csv", implode("", $all_lines), LOCK_EX);
                echo("<span class='bg-success'>Endret: " . esc($line) . "</span>");
            }
        }
}
}

if ($method == "GET") {
    $password = getallheaders()['Password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    } else {
        parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
        $response = "";

        $runners = read_runners_from_csv();
        if (isset($query['get'])) {
            $found = get_runner($runners, (int) $query['get']);
            $filtered = $found === false ? [] : [$found];
        } else {
            $filtered = search_for_runner($runners, $query['search'] ?? "");
        }
        $edit = ($query['edit'] ?? "") == "true" ? "true" : "";
        if (count($filtered) == 1 && $edit == "true") {
            $r = $filtered[0];
            $kadaver_checked = $r->course == "Kadaverløpet" ? "checked='checked'" : "";
            $mini_checked = $r->course == "Minikadaver'n" ? "checked='checked'" : "";
            $response .= "
            <h2>Endre " . (int) $r->id . " " . esc($r->name) . "</h2>
            <form hx-patch='/api/runner.php' hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>
                <label>Startnummer<input type='number' id='id' name='id' required value='" . (int) $r->id . "'></label>
                <label>Navn<input type='text' id='name' name='name' required value='" . esc($r->name) . "'></label>
                <label>Forening<input type='text' id='club' name='club' required value='" . esc($r->club) . "'></label>
                <label>
                <fieldset>
                <input type='radio' name='course' value='Kadaverløpet' $kadaver_checked>
                Kadaverløpet
                </label>
                    <label>
                <input type='radio' name='course' value=\"Minikadaver'n\" $mini_checked>
                Minikadaver'n
                </label>
                </fieldset>
                <label>epost<input type='text' id='email' name='email' value='" . esc($r->email) . "'></label>
                <label>telefon<input type='text' id='phone' name='phone' value='" . esc($r->phone) . "'></label>
                <label><input type='hidden' id='student' name='student' value='" . esc($r->is_student) . "'></label>
                <label><input type='hidden' id='line_in_csv' name='line_in_csv' value='" . (int) $r->line_in_csv . "'></label>
                <button type='submit'>Endre løper</button>
            </form>
            ";
            header("HX-Replace-Url: false");
            echo($response);
        } elseif (count($filtered) == 1){
            $r = $filtered[0];
            $response .= "
            <h2> " . esc($r->name) . "</h2>
            <p> <b>Klubb:</b> " . esc($r->club) . "</p>
            <p> <b>Løype:</b> " . esc($r->course) . "</p>
            <p> <b>Epost:</b> <a href=\"mailto:" . esc($r->email) . "\">" . esc($r->email) . "</a></p>
            <p> <b>Mobilnummer:</b> <a href=\"tel:" . esc($r->phone) . "\">" . esc($r->phone) . "</a></p>
            <p> <b>Student?</b> " . esc($r->is_student) . "</p>
            ";
            header("HX-Replace-Url: false");
            echo($response);
        }
        elseif (count($filtered) > 1){
            for ($i = 0; $i < count($filtered); $i++) {
                $runner = $filtered[$i];
                if ($edit == "true") {
                    $url = "/api/runner.php?get=" . (int) $runner->id . "&edit=true";
                } else {
                    $url = "/api/runner.php?get=" . (int) $runner->id;
                }
                $response .= "<button class=\"default\" hx-get=\"$url\" hx-target=\"#runner\" hx-swap=\"show:none\" hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>" . (int) $runner->id . " " . esc($runner->name) . "</button>";
                header("HX-Replace-Url: false");
            }
            echo($response);
        }
        else{
            echo("Ingen resultater...");
        }
    }
}