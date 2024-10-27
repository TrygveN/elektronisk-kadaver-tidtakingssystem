<?php
date_default_timezone_set('UTC');
$GLOBALS['start_time'] = DateTime::createFromFormat(DateTime::ISO8601, "2024-10-08T08:07:32+01");
$GLOBALS['number_of_controls'] = 3;

//declare(strict_types=1);
class Runner
{
    public int $id;
    public string $name;
    public string $club;
    public string $course;
    public array $splits;

    function __construct($id, $name, $club, $course)
    {
        //echo($id);
        //echo($name);
        if ($id == null) {
            $id = 0;
            $name = "";
        }
        $this->id = $id;
        $this->name = $name;
        $this->club = $club;
        $this->course = $course;
        for ($i = 0; $i < $GLOBALS['number_of_controls']; $i++) {
            $this->splits[$i] = false;
        }
    }

    function set_split($control, $timestamp)
    {
        $this->splits[$control] = $timestamp;
    }

    function get_control()
    {
        // Returns wich control the runner last passed
        for ($i = 0; $i < count($this->splits); $i++) {
            if (!is_object($this->splits[$i])) {
                return $i-1;
            }
        }
        return count($this->splits)-1;
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

function cmp(Runner $a, Runner $b) {
    $a_control = $a->get_control();
    $b_control = $b->get_control();
    if ($a_control > $b_control){
        return -1;
    }
    if ($a_control < $b_control){
        return 1;
    }
    if ($a_control == -1) {
        return 1;
    }
    if ($b_control == -1) {
        return -1;
    }
    if ($a->splits[$a_control]->getTimestamp() < $b->splits[$b_control]->getTimestamp()){
        return -1;
    }
    if ($a->splits[$a_control]->getTimestamp() > $b->splits[$b_control]->getTimestamp()){
        return 1;
    }
    return 0;
}
function cmp_course(Runner $a, Runner $b) {
    return strcmp($a->course, $b->course);
}


$runners = [];
$csv_runners = file_get_contents("db.csv");
$csv_runners = str_getcsv($csv_runners, "\n");
//print_r($csv_runners);
for ($i = 1; $i < count($csv_runners); $i++) {
    $line = str_getcsv($csv_runners[$i]);
    array_push($runners, new Runner($line[0], $line[1], $line[2], $line[3]));
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


if (!isset($query)){
    $query = explode(",",$_SERVER['QUERY_STRING']);
}

if ($query[0] == "registrering"){
    $matpost = $query[1];
    echo("  <thead><tr>
    <th>#</th>
    <th>Navn</th>
    <th>Tid</th>
    <th></th>
    </tr></thead>
    <tbody>");
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        
        // Klokkeslett for denne posten
        if ($runner->splits[$matpost-1] != false) {
            $tid_passering = $GLOBALS['start_time']->diff($runner->splits[$matpost-1])->format('%H:%I:%S');
        }
        else {
            $tid_passering = "";
        }
        
        if ($runner->get_control() == $matpost-1) {
            // Løperen har vært på denne matposten og vi farger raden grønn
            $button = "<button onclick=\"register_runner($runner->id)\">✓</button>";
            $cssclass = "class=\"bg-success\"\"";
        }
        elseif ($runner->get_control() > $matpost-1) {
            // Løperen har vært på denne matposten og vi farger raden grønn
            $button = "<button onclick=\"register_runner($runner->id)\">✓</button>";
            $cssclass = "class=\"bg-active\"\"";
        }
        else {
            $button = "<button onclick=\"register_runner($runner->id)\">✓</button>";
            $cssclass = "";
        }
        echo ("<tr $cssclass><td>$runner->id</td><td>$runner->name</td><td>$tid_passering</td><td>$button</td></tr>\n");
    }
    echo("</tbody>");
}
elseif ($query[0] == "paameldte") {
    usort($runners, "cmp_course");

    $kadaverløpere = 0;
    $minikadaverløpere = 0;
    for ($i = 0; $i < count($runners); $i++) {
        if ($runners[$i]->course == "Kadaverløpet") {
            $kadaverløpere++;
        }
        elseif ($runners[$i]->course == "Minikadaver'n") {
            $minikadaverløpere++;
        }
    }

    echo("<div class=\"flex space-evenly\">
    <div class=\"flash accent\">$kadaverløpere påmeldt Kadaverløpet</div><div class=\"flash accent\">$minikadaverløpere påmeldte i Minikadaver'n</div>
    </div>");

    echo("<table><tbody>
        <tr>
        <th>Navn</th>
        <th>Klubb/Forening</th>
        <th>Variant</th>
    </tr>");
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        echo ("<tr><td>$runner->name</td><td>$runner->club</td><td>$runner->course</td></tr>\n");
    }
    echo("<table><tbody>");
}
else {
    usort($runners, "cmp");
    echo("<table><tbody>
        <tr>
        <th>#</th>
        <th>Startnummer</th>
        <th>Navn</th>
        <th>1. matpost</th>
        <th>2. matpost</th>
        <th>Mål</th>
    </tr>");
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        $tid_1_mat = "";
        if ($runner->splits[0] != false) {
            // https://www.php.net/manual/en/class.dateinterval.php
            $tid_1_mat = $GLOBALS['start_time']->diff($runner->splits[0])->format('%H:%I:%S');
        }
        $tid_2_mat = "";
        if ($runner->splits[1] != false) {
            $tid_2_mat = $GLOBALS['start_time']->diff($runner->splits[1])->format('%H:%I:%S');
        }
        $tid_maal = "";
        if ($runner->splits[2] != false) {
            $tid_maal = $GLOBALS['start_time']->diff($runner->splits[2])->format('%H:%I:%S');
        }
        echo ("<tr><td>". $i+1 .".</td><td>$runner->id</td><td>$runner->name</td><td>$tid_1_mat</td><td>$tid_2_mat</td><td>$tid_maal</td></tr>\n");
    }
    echo("</tbody></table>");
}