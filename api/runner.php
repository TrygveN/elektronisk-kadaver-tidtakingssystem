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
        echo("<span class='bg-danger'>Feil passord.</span>");
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
            if (!append_line($file, $line)) {
                http_response_code(response_code: 500);
                echo("<span class='bg-danger'>Kunne ikke lagre løperen.</span>");
            } else {
                header("HX-Replace-Url: false");
                echo("Løper lagt til: " . esc($line));
            }
        }


    }
}
if ($method == "PATCH") {
    $password = getallheaders()['Password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
        echo("<span class='bg-danger'>Feil passord.</span>");
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
        } elseif ($course != "Kadaverløpet" && $course != "Minikadaver'n") {
            http_response_code(response_code: 400);
            echo("<span class='bg-danger'>Ukjent klasse.</span>");
        } else {
            $old_fields = str_getcsv($all_lines[(int) $csv_line], ";");
            $old_id = $old_fields[0];
            $runners = read_runners_from_csv();

            if (get_runner($runners, (int) $id) !== false && $id != $old_id) {
                http_response_code(response_code: 400);
                echo("<span class='bg-danger'>Startnummer er allerede i bruk!</span>");
            } else {
                $line = csv_runner_line(
                    $id, $name, $email, $phone, $club, $course, $student,
                    $old_fields[9] ?? false, $old_fields[10] ?? false
                );
                $all_lines[(int) $csv_line] = $line;
                if (file_put_contents("$documentRoot/data/db.csv", implode("", $all_lines), LOCK_EX) === false) {
                    http_response_code(response_code: 500);
                    echo("<span class='bg-danger'>Kunne ikke lagre endringene.</span>");
                } else {
                    echo("<span class='bg-success'>Endret: " . esc($line) . "</span>");
                }
            }
        }
}
}

if ($method == "GET") {
    $password = getallheaders()['Password'] ?? "";
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
        echo("<span class='bg-danger'>Feil passord.</span>");
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
            $student_ja = $r->is_student == "Ja" ? "selected" : "";
            $student_nei = $r->is_student == "Nei" ? "selected" : "";
            $student_blank = $r->is_student == "" ? "selected" : "";
            $response .= "
            <h2>Endre " . (int) $r->id . " " . esc($r->name) . "</h2>
            <form class='form-grid' hx-patch='/api/runner.php' hx-target='#runner' hx-swap='innerHTML show:none' hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>
                <label>Startnummer<input type='number' name='id' min='0' required value='" . (int) $r->id . "'></label>
                <label>Navn<input type='text' name='name' required value='" . esc($r->name) . "'></label>
                <label>Klubb/forening<input type='text' name='club' required value='" . esc($r->club) . "'></label>
                <fieldset>
                    <legend>Klasse</legend>
                    <label><input type='radio' name='course' value='Kadaverløpet' $kadaver_checked required> Kadaverløpet</label>
                    <label><input type='radio' name='course' value=\"Minikadaver'n\" $mini_checked required> Minikadaver'n</label>
                </fieldset>
                <label>E-post<input type='email' name='email' value='" . esc($r->email) . "'></label>
                <label>Telefon<input type='tel' name='phone' value='" . esc($r->phone) . "'></label>
                <label>Student?
                    <select name='student'>
                        <option value='' $student_blank>Ikke oppgitt</option>
                        <option value='Ja' $student_ja>Ja</option>
                        <option value='Nei' $student_nei>Nei</option>
                    </select>
                </label>
                <input type='hidden' name='line_in_csv' value='" . (int) $r->line_in_csv . "'>
                <div class='form-actions'>
                    <button type='submit'>Lagre endringer</button>
                    <button type='button' class='default' hx-get='/api/runner.php?get=" . (int) $r->id . "'
                        hx-target='#runner' hx-swap='innerHTML show:none'
                        hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>Avbryt</button>
                </div>
            </form>
            ";
            header("HX-Replace-Url: false");
            echo($response);
        } elseif (count($filtered) == 1){
            $r = $filtered[0];
            $response .= "
            <h2>" . esc($r->name) . "</h2>
            <p> <b>Startnummer:</b> " . (int) $r->id . "</p>
            <p> <b>Klubb:</b> " . esc($r->club) . "</p>
            <p> <b>Løype:</b> " . esc($r->course) . "</p>
            <p> <b>Epost:</b> <a href=\"mailto:" . esc($r->email) . "\">" . esc($r->email) . "</a></p>
            <p> <b>Mobilnummer:</b> <a href=\"tel:" . esc($r->phone) . "\">" . esc($r->phone) . "</a></p>
            <p> <b>Student?</b> " . esc($r->is_student) . "</p>
            <button type='button' hx-get='/api/runner.php?get=" . (int) $r->id . "&amp;edit=true'
                hx-target='#runner' hx-swap='innerHTML show:none'
                hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>✏️ Endre løper</button>
            ";
            header("HX-Replace-Url: false");
            echo($response);
        }
        elseif (count($filtered) > 1){
            $response .= "<div class='runner-list'>";
            for ($i = 0; $i < count($filtered); $i++) {
                $runner = $filtered[$i];
                if ($edit == "true") {
                    $url = "/api/runner.php?get=" . (int) $runner->id . "&amp;edit=true";
                } else {
                    $url = "/api/runner.php?get=" . (int) $runner->id;
                }
                $response .= "<button class=\"default\" hx-get=\"$url\" hx-target=\"#runner\" hx-swap=\"innerHTML show:none\" hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>" . (int) $runner->id . " " . esc($runner->name) . "</button>";
                header("HX-Replace-Url: false");
            }
            $response .= "</div>";
            echo($response);
        }
        else{
            echo("Ingen resultater...");
        }
    }
}

if (!in_array($method, ["GET", "POST", "PATCH"], true)) {
    http_response_code(response_code: 405);
    header("Allow: GET, POST, PATCH");
    echo("<span class='bg-danger'>Method not allowed</span>");
}