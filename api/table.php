<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

$config = parse_ini_file("$documentRoot/data/config.ini");
date_default_timezone_set('UTC');
$GLOBALS['start_time'] = DateTime::createFromFormat(DateTime::ISO8601, $config["start_date"]);
$GLOBALS['start_time_mini'] = DateTime::createFromFormat(DateTime::ISO8601, $config["start_date_mini"]);
$GLOBALS['number_of_controls'] = 3;
/*
// Caching
header("Last-Modified: " . date("F d Y H:i:s.", filemtime("data/passering.csv")));
$etag = '"' . md5_file("data/passering.csv"). '"';
header(header: 'ETag: ' . $etag );

if(isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	// If HTTP_IF_NONE_MATCH is same as the generated ETag => content is the same as browser cache
	// So send a 304 Not Modified response header and exit
	if($_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
		http_response_code(304);
		exit();
	}
}
*/

require_once("$documentRoot/import_runners.php");

function registration_table($runners) {
    parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
    $matpost = (int) ($query["control"] ?? 0);
    $filter = $query["filter"] ?? "";
    $runners_filtered = [];
    if ($filter !== "") {
        for ($i = 0; $i < count($runners); $i++) {
            if (filter_runners($runners[$i], $filter)) {
                array_push($runners_filtered, $runners[$i]);
            }
        }
        $runners = $runners_filtered;
    }
    if (count($runners) == 0) {
        $runner_id = (int) $filter;
        $response = "<div class=\"flash default\">Fant ingen løper med dette startnummeret. Registrer likevell? <button onclick=\"register_runner($runner_id)\">Registrer startnummer $runner_id ✅️</button></div>";
        echo($response);
    } else {

        $split_index = $matpost - 1;

        echo("  <thead><tr>
        <th>#</th>
        <th>Navn</th>
        <th>Tid</th>
        <th></th>
        </tr></thead>
        <tbody>");
        for ($i = 0; $i < count($runners); $i++) {
            $runner = $runners[$i];
            $id = (int) $runner->id;
            $name = esc($runner->name);

            // Klokkeslett for denne posten
            $tid_passering = "";
            if (isset($runner->splits[$split_index]) && $runner->splits[$split_index] != false) {
                $tid_passering = $GLOBALS['start_time']->diff($runner->splits[$split_index])->format('%H:%I:%S');
            }

            if ($runner->get_control() == $split_index) {
                // Løperen har vært på denne matposten og vi farger raden grønn
                $button = "<button onclick=\"register_runner($id)\">☑️</button>";
                $cssclass = "class=\"bg-success\"";
            }
            elseif ($runner->get_control() > $split_index) {
                // Løperen har passert ein seinare post, farger raden lilla
                $button = "<button onclick=\"register_runner($id)\">☑️</button>";
                $cssclass = "class=\"bg-active\"";
            }
            else {
                $button = "<button onclick=\"register_runner($id)\">✅️</button>";
                $cssclass = "";
            }
            echo ("<tr $cssclass><td>$id</td><td>$name</td><td>$tid_passering</td><td>$button</td></tr>\n");
        }
        echo("</tbody>");
    }
}

function participants_table($runners) {
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

    // vis_startnummer = false/0/"" skjuler startnummer-kolonna.
    $vis_startnummer = !in_array($GLOBALS['config']['vis_startnummer'] ?? "1", ["", "0", "false", "off", "no"], true);

    echo("<table><thead>
        <tr>");
    if ($vis_startnummer) {
        echo("<th class=\"col-start\">S.nr</th>");
    }
    echo("<th class=\"col-name\">Navn</th>
        <th class=\"col-club\">Klubb/Forening</th>
        <th class=\"col-course\">Variant</th>
    </tr></thead>
    <tbody>");
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        echo("<tr>");
        if ($vis_startnummer) {
            echo("<td class=\"col-start\">" . (int) $runner->id . "</td>");
        }
        echo("<td class=\"col-name\">" . esc($runner->name) . "</td><td class=\"col-club\">" . esc($runner->club) . "</td><td class=\"col-course\">" . esc($runner->course) . "</td></tr>\n");
    }
    echo("</tbody></table>");
}
function liveresult_table($runners) {
    usort($runners, "cmp");
    usort($runners, "cmp_course");
    $kadaver_table = "<table id='kadavertabell'><thead>
        <tr>
        <th>#</th>
	<th>Navn</th>
        <th>1. matpost</th>
        <th>2. matpost</th>
        <th>Mål</th>
        <th>Etter</th>
        <th>Sprekkindeks</th>
    </tr></thead>
    <tbody>";
       	$kadaver_num = 0;
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        $tid_maal = "";
        $sprekk = "<td></td>";
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

            //tid etter vinner
            $tid_etter = "";
            if ($runner->splits[2] != false && $kadaver_num > 0) {
                $tid_etter = $runners[0]->splits[2]->diff($runner->splits[2])->format('%H:%I:%S');
            }
            //sprekkindekss
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
        if ($runner->course == "Kadaverløpet") {
            $runner_number = $kadaver_num .".";
            if ($runner->get_control() == -1){$runner_number = "";};
            $kadaver_table .= "<tr id='" . (int) $runner->id . "'><td>". $runner_number ."</td><td>" . esc($runner->name) . "</td>$matposter<td><b>$tid_maal</b></td><td>$tid_etter</td>$sprekk</tr>\n";
        }
    }
    $kadaver_table .= "</tbody></table>\n";
    echo($kadaver_table);
    
}

function minikadadvern_table($runners) {
    usort($runners, "cmp");
    $minikadaver_table = "<table><thead>
    <tr>
    <th>#</th>
    <th>Navn</th>
    <th>Mål</th>
    </tr></thead>
    <tbody>";
    $mini_num = 0;	
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        $tid_maal = "";
        if ($runner->splits[2] != false) {
            $tid_maal = $GLOBALS['start_time_mini']->diff($runner->splits[2])->format('%H:%I:%S');
        }
	    if ($runner->course == "Minikadaver'n") {
		    $mini_num++;
            $runner_number = $mini_num . ".";
            if ($runner->get_control() != 2){$runner_number = "";};
            $minikadaver_table .= "<tr><td>". "$runner_number" ."</td><td>" . esc($runner->name) . "</td><td>$tid_maal</td></tr>\n";
        }
    }
    $minikadaver_table .= "</tbody></table>";
    echo($minikadaver_table);
    
}

$runners = read_runners_from_csv();

if (!isset($query)){
    parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
    $type = $query["type"] ?? "";
    if ($type == "registrering"){
        registration_table($runners);
    }
    elseif ($type == "paameldte") {
        participants_table($runners);
    }
    elseif ($type == "liveresultater") {
        liveresult_table($runners);
    }
    elseif ($type == "minikadavern") {
        minikadadvern_table($runners);
    }
}
