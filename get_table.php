<?php
date_default_timezone_set('UTC');
$GLOBALS['start_time'] = DateTime::createFromFormat(DateTime::ISO8601, "2024-10-08T08:07:32+01");
$GLOBALS['number_of_controls'] = 3;

//declare(strict_types=1);
class Runner
{
    public int $id;
    public string $name;
    public array $splits;

    function __construct($id, $name)
    {
        //echo($id);
        //echo($name);
        if ($id == null) {
            $id = 0;
            $name = "";
        }
        $this->id = $id;
        $this->name = $name;
        for ($i = 0; $i < $GLOBALS['number_of_controls']; $i++) {
            $this->splits[$i] = false;
        }
    }

    function set_split($control, $timestamp)
    {
        $this->splits[$control] = $timestamp;
    }
}

//find runner by id in list of Runner objects
function get_runner($runnner_list, int $id)
{
    for ($i = 0; $i < count($runnner_list); $i++) {
        if ($runnner_list[$i]->id == $id) {
            return $runnner_list[$i];
        }
    }
    return false;
}

$runners = [];
$csv_runners = file_get_contents("db.csv");
$csv_runners = str_getcsv($csv_runners, "\n");
//print_r($csv_runners);
for ($i = 1; $i < count($csv_runners); $i++) {
    $line = str_getcsv($csv_runners[$i]);
    array_push($runners, new Runner($line[0], $line[1]));
}


$timings = file_get_contents("passering.csv");
$timings = str_getcsv($timings, "\n");
for ($i = 0; $i < count($timings); $i++) {
    $line = str_getcsv($timings[$i]);

    $time = DateTime::createFromFormat("Y-m-d\TH:i:sp", $line[2]);
    if (!$time) {
        //error
        continue;
    }

    $runner = get_runner($runners, (int) $line[1]);
    if (!$runner) {
        //error
        continue;
    }

    $runner->set_split($line[0]-1, $time);
}

//print_r($runners);
for ($i = 0; $i < count($runners); $i++) {
    $runner = $runners[$i];
    $tid_1_mat = "";
    if ($runner->splits[0] != false) {
        // https://www.php.net/manual/en/class.dateinterval.php
        $tid_1_mat = $GLOBALS['start_time']->diff($runner->splits[0])->format('%H:%I:%S');
    }
    $tid_2_mat = "";
    if ($runner->splits[1] != false) {
        // https://www.php.net/manual/en/class.dateinterval.php
        $tid_2_mat = $GLOBALS['start_time']->diff($runner->splits[1])->format('%H:%I:%S');
    }
    $tid_maal = "";
    if ($runner->splits[2] != false) {
        // https://www.php.net/manual/en/class.dateinterval.php
        $tid_maal = $GLOBALS['start_time']->diff($runner->splits[2])->format('%H:%I:%S');
    }
    echo ("<tr><td>$runner->id</td><td>$runner->name</td><td>$tid_1_mat</td><td>$tid_2_mat</td><td>$tid_maal</td></tr>");
}