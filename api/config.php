<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
function write_ini_file($assoc_array, $path) {
    $content = "";
    foreach ($assoc_array as $section => $values) {
        if (is_array($values)) {
            $content .= "[$section]\n";
            foreach ($values as $key => $val) {
                $content .= "$key = \"$val\"\n";
            }
        } else {
            $content .= "$section = \"$values\"\n";
        }
        $content .= "\n";
    }
    file_put_contents($path, data: $content);
}

$config = parse_ini_file("$documentRoot/data/config.ini");

$hash = file_get_contents("$documentRoot/data/hash.txt");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "POST") {
    $start_time = $_POST['start_time'];

    $password = $_POST['password'];
    if (!password_verify($password, $hash)) {
        http_response_code(response_code: 401);
    }
    else {
        $file = "$documentRoot/data/config.ini";
        $config["start_date"] = $start_time . "+01";
        write_ini_file($config, $file);
        header("HX-Replace-Url: false");
        echo("Starttida er oppdatert til: $start_time");
    }
}
elseif ($method == "GET"){
    print($config["start_date"]);
}


