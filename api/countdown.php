<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");

function format($seconds) {
    // Ensure it's an integer and non-negative
    $seconds = max(0, (int)$seconds);

    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;

    // Format with leading zeros (e.g., 02:05)
    return sprintf('%02d:%02d', $minutes, $remainingSeconds);
}

$method = $_SERVER['REQUEST_METHOD'];

$time_limit = 240;

if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $control = $query['matpost'];
    
    $runners = read_runners_from_csv();
    usort($runners, "cmp");
    $runners = array_reverse($runners);
    $hourMin = date('H:i:s');
    $waiting = "$hourMin
    <h1>Tid igjen på matpost:<h1><div class='flex wrap center'>";
    $finished = "<h1>Kan løpe videre:<h1><div class='flex wrap center'>";
    for ($i = 0; $i < count($runners); $i++) {
        $runner = $runners[$i];
        if ($runner->get_control() == $control){
            $time_since_punch = time() - ($runner->splits[$control])->getTimestamp();

            //Løperen har ikke venta 4 minutt:
            if ($time_since_punch < $time_limit) {
                $time = format($time_limit - $time_since_punch);
                $waiting .= "<div class=\"flash attention\">$runner->id $runner->name <span class=\"time\">-$time</span></div>";
            } elseif ($time_since_punch >= $time_limit && $time_since_punch <900){ //Løperen har venta ferdig 
                $time = format(time()-$runner->splits[$control]->getTimestamp() - $time_limit) ;
                $finished .= "<div class=\"flash success\">$runner->id $runner->name <span class=\"time\">+$time</span></div>";
            }
            

            
        }
    }
    echo($waiting . "</div>". $finished . "</div>");
}