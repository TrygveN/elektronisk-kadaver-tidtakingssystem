<?php
//TODO: les fra config istedet:
$start_time = DateTime::createFromFormat(DateTime::ISO8601, "2024-02-12T15:07:32+01");
echo(date_timestamp_get($start_time));

$db = file_get_contents("db.csv");
$db = str_getcsv($db, "\n");

$timings = file_get_contents("passering.csv");
$timings = str_getcsv($timings, "\n");

//add the time diff to each runner
for ($i = 0; $i < count($timings); $i++) {
    $line = str_getcsv($timings[$i]);
    $control = $line[0];
    $id = $line[1];
    $time = DateTime::createFromFormat(DateTime::ISO8601, $line[2]);

    if (!in_array($id, $db)) {
        continue;
        //TODO: Burde logge dette ellerno
    }
    arra

    array_search($id, $db);
}


for ($i=0; $i < count($db); $i++) { 
    $runner = str_getcsv($db[$i]);
    echo("<tr><td>$runner[0]</td><td>$runner[1]</td></tr>");
}