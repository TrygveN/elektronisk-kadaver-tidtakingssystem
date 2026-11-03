<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
function write_ini_file($assoc_array, $path) {
    $content = "";
    foreach ($assoc_array as $section => $values) {
        if (is_array($values)) {
            $content .= "[$section]\n";
            foreach ($values as $key => $val) {
                $val = str_replace(['"', "\n", "\r"], "", (string) $val);
                $content .= "$key = \"$val\"\n";
            }
        } else {
            $values = str_replace(['"', "\n", "\r"], "", (string) $values);
            $content .= "$section = \"$values\"\n";
        }
        $content .= "\n";
    }
    return file_put_contents($path, $content) !== false;
}

$config = parse_ini_file("$documentRoot/data/config.ini") ?: [];

include("$documentRoot/data/hash.php");
$method = $_SERVER['REQUEST_METHOD'];
if ($method === "POST") {
    $start_time = $_POST['start_time'] ?? "";
    $start_time_mini = $_POST['mini_time'] ?? "";

    $password = getallheaders()['Password'] ?? "";

    $new_time = DateTime::createFromFormat(DateTime::ISO8601, $start_time. "+01");
    $new_time_mini = DateTime::createFromFormat(DateTime::ISO8601, $start_time_mini. "+01");
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
        echo("Feil passord.");
    } elseif (!$new_time || !$new_time_mini) {
        http_response_code(response_code: 400);
        echo("Ugyldig datoformat. Bruk formatet ÅÅÅÅ-MM-DD TT:MM:SS.");
    } else {

        $file = "$documentRoot/data/config.ini";
        $config["start_date"] = $start_time . "+01";
        $config["start_date_mini"] = $start_time_mini . "+01";
        $config["vis_startnummer"] = isset($_POST["vis_startnummer"]) ? "true" : "false";
        $config["skjul_resultater"] = isset($_POST["skjul_resultater"]) ? "true" : "false";
        $config["skjul_ikke_startet"] = isset($_POST["skjul_ikke_startet"]) ? "true" : "false";
        if (!write_ini_file($config, $file)) {
            http_response_code(response_code: 500);
            echo("Kunne ikke lagre konfigurasjonen.");
        } else {
            header("HX-Replace-Url: false");
            echo("Starttida er oppdatert til: $start_time for Kadaver'n <br> $start_time_mini for Minikadaer'n");
        }
    }
}
elseif ($method === "GET") {
    $start_kadaver = htmlspecialchars(explode("+", $config["start_date"] ?? "")[0], ENT_QUOTES);
    $start_mini = htmlspecialchars(explode("+", $config["start_date_mini"] ?? "")[0], ENT_QUOTES);
    $vis = $config["vis_startnummer"] ?? "1";
    $vis_checked = in_array($vis, ["", "0", "false", "off", "no"], true) ? "" : "checked";
    $skjul = $config["skjul_resultater"] ?? "0";
    $skjul_checked = in_array($skjul, ["1", "true", "on", "yes"], true) ? "checked" : "";
    $skjul_ikke_startet = $config["skjul_ikke_startet"] ?? "0";
    $skjul_ikke_startet_checked = in_array($skjul_ikke_startet, ["1", "true", "on", "yes"], true) ? "checked" : "";
    $response = "
      <form action='/api/config.php' method='POST' hx-boost='true' hx-target='#updated' hx-swap='innerHTML show:none' hx-push-url='false' hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>
    <label>
      Starttid Kadaverløpet
      <input type='datetime-local' id='start_time' name='start_time' step='1' value='$start_kadaver'>
    </label>
        <label>
      Starttid Minikadaver'n
        <input type='datetime-local' id='mini_time' name='mini_time' step='1' value='$start_mini'>
        </label>
        <label>
      <input type='checkbox' id='vis_startnummer' name='vis_startnummer' value='true' $vis_checked>
      Vis startnummer i påmeldtlista
    </label>
    <label>
      <input type='checkbox' id='skjul_resultater' name='skjul_resultater' value='true' $skjul_checked>
      Skjul resultattabellene på forsiden
    </label>
    <label>
      <input type='checkbox' id='skjul_ikke_startet' name='skjul_ikke_startet' value='true' $skjul_ikke_startet_checked>
      Skjul løpere som ikke har startet
    </label>
        <button type='submit'>Oppdater</button>
    </form>
    <div id='updated'></div>
    ";
    echo($response);
}
else {
    http_response_code(405);
    header("Allow: GET, POST");
}


