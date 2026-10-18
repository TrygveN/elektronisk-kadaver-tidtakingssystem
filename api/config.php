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
    file_put_contents($path, data: $content);
}

$config = parse_ini_file("$documentRoot/data/config.ini");

include("$documentRoot/data/hash.php");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "POST") {
    $start_time = $_POST['start_time'] ?? "";
    $start_time_mini = $_POST['mini_time'] ?? "";

    $password = getallheaders()['Password'] ?? "";

    $new_time = DateTime::createFromFormat(DateTime::ISO8601, $start_time. "+01");
    $new_time_mini = DateTime::createFromFormat(DateTime::ISO8601, $start_time_mini. "+01");
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    } elseif (!$new_time || !$new_time_mini) {
        http_response_code(response_code: 400);
        echo("Feil datoformat din noldus!!!!!!!11!!! Skriv inn sekund");
    } else {

        $file = "$documentRoot/data/config.ini";
        $config["start_date"] = $start_time . "+01";
        $config["start_date_mini"] = $start_time_mini . "+01";
        write_ini_file($config, $file);
        header("HX-Replace-Url: false");
        echo("Starttida er oppdatert til: $start_time for Kadaver'n <br> $start_time_mini for Minikadaer'n");
    }
}
elseif ($method == "GET"){
    $start_kadaver = htmlspecialchars(explode("+", $config["start_date"] ?? "")[0], ENT_QUOTES);
    $start_mini = htmlspecialchars(explode("+", $config["start_date_mini"] ?? "")[0], ENT_QUOTES);
    $response = "
      <form action='/api/config.php' method='POST' hx-boost='true' hx-target='#updated' hx-swap='show:none' hx-headers='js:{\"Password\": localStorage.getItem(\"passord\")}'>
    <label>
      Starttid Kadaverløpet
      <input type='datetime-local' id='start_time' name='start_time' step='1' value='$start_kadaver'>
    </label>
        <label>
      Starttid Minikadaver'n
        <input type='datetime-local' id='mini_time' name='mini_time' step='1' value='$start_mini'>
        </label>
        <button type='submit'>Oppdater</button>
    </form>
    <div id='updated'></div>
    ";
    echo($response);
}


