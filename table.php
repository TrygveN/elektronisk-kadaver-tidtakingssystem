<?php
date_default_timezone_set('UTC');
$GLOBALS['start_time'] = DateTime::createFromFormat(DateTime::ISO8601, "2024-11-02T08:53:00+01");
$GLOBALS['number_of_controls'] = 3;

// Caching
header("Last-Modified: " . date("F d Y H:i:s.", filemtime("passering.csv")));
$etag = '"' . md5_file("passering.csv"). '"';
header(header: 'ETag: ' . $etag );

if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	// If HTTP_IF_NONE_MATCH is same as the generated ETag => content is the same as browser cache
	// So send a 304 Not Modified response header and exit
	if($_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		header('HTTP/1.1 304 Not Modified', true, 304);
		exit();
	}
}

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
    {        for ($i = 0; $i < count($this->splits); $i++) {

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

function filter_runners(Runner $runner, $id) {
    if ($runner->id == $id) {
        return True;
    }
    else {
        return False;
    }
}

function time_diff(DateTime $date_1, DateTime $date_2) {
    return $date_2->getTimestamp() - $date_1->getTimestamp();
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
    parse_str($_SERVER['QUERY_STRING'], $query);
}

if ($query["type"] == "registrering"){
    $matpost = $query["control"];
    $runners_filtered = [];
    if ($query["filter"]) {
        for ($i = 0; $i < count($runners); $i++) {
            if (filter_runners($runners[$i], $query["filter"])) {
                array_push($runners_filtered, $runners[$i]);
            }
        }
        $runners = $runners_filtered;
    }

    

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
            $cssclass = "class=\"bg-success\"";
        }
        elseif ($runner->get_control() > $matpost-1) {
            // Løperen har vært på denne matposten og vi farger raden grønn
            $button = "<button onclick=\"register_runner($runner->id)\">✓</button>";
            $cssclass = "class=\"bg-active\"";
        }
        else {
            $button = "<button onclick=\"register_runner($runner->id)\">✓</button>";
            $cssclass = "";
        }
        echo ("<tr $cssclass><td>$runner->id</td><td>$runner->name</td><td>$tid_passering</td><td>$button</td></tr>\n");
    }
    echo("</tbody>");
}
elseif ($query["type"] == "paameldte") {
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

    echo("<table><thead>
        <tr>
        <th>S.nr</th>
        <th>Navn</th>
        <th>Klubb/Forening</th>
        <th>Variant</th>
    </tr></thead>
    <tbody>");
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        echo ("<tr><td>$runner->id</td><td>$runner->name</td><td>$runner->club</td><td>$runner->course</td></tr>\n");
    }
    echo("<table><tbody>");
}
else {
    usort($runners, "cmp");
    usort($runners, "cmp_course");
    $kadaver_table = "<table><thead>
        <tr>
        <th>#</th>
	<th>Navn</th>
        <th>1. matpost</th>
        <th>2. matpost</th>
        <th>Mål</th>
        <th>Sprekkindeks</th>
    </tr></thead>
    <tbody>";
    $minikadaver_table = "<table><thead>
    <tr>
    <th>#</th>
    <th>Navn</th>
    <th>Mål</th>
    </tr></thead>
    <tbody>";
       	$kadaver_num = 0;
       	$mini_num = 0;	
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        $tid_maal = "";
        if ($runner->splits[2] != false) {
            $tid_maal = $GLOBALS['start_time']->diff($runner->splits[2])->format('%H:%I:%S');
        }
	    if ($runner->course == "Kadaverløpet") {
		$kadaver_num++;
            $tid_1_mat = "";
            if ($runner->splits[0] != false) {
                // https://www.php.net/manual/en/class.dateinterval.php
                $tid_1_mat = $GLOBALS['start_time']->diff($runner->splits[0])->format('%H:%I:%S');
            }
            $tid_2_mat = "";
            if ($runner->splits[1] != false) {
                $tid_2_mat = $GLOBALS['start_time']->diff($runner->splits[1])->format('%H:%I:%S');
                try {
                    $sprekk = "<td>" . number_format(100*(time_diff($GLOBALS['start_time'],$runner->splits[2]) - time_diff($GLOBALS['start_time'],$runner->splits[1])) / time_diff($GLOBALS['start_time'],$runner->splits[2]), 0) . "%</td>";
                }
                catch (DivisionByZeroError $e){
                    $sprekk = "<td></td>";
                }
                catch (TypeError $e) {
                    $sprekk = "<td></td>";
                }
            }
            $matposter = "<td>$tid_1_mat</td><td>$tid_2_mat</td>";
	}
	else {
		$mini_num++;
	}

        if ($runner->course == "Kadaverløpet") {
            $kadaver_table .= "<tr><td>". $kadaver_num .".</td><td>$runner->name</td>$matposter<td>$tid_maal</td>$sprekk</tr>\n";
        }
        elseif ($runner->course == "Minikadaver'n") {
            $minikadaver_table .= "<tr><td>". "" .".</td><td>$runner->name</td><td>$tid_maal</td></tr>\n";
        }
    }
    $kadaver_table .= "</tbody></table>";
    $minikadaver_table .= "</tbody></table>";
    echo($kadaver_table);
    
}
