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
    parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
    $control = (int) ($query['matpost'] ?? -1);

    $runners = read_runners_from_csv();
    $now = time();
    $waiting = [];
    $finished = [];

    foreach ($runners as $runner) {
        if ($runner->get_control() != $control) {
            continue;
        }
        $split = $runner->splits[$control] ?? false;
        if (!($split instanceof DateTime)) {
            continue;
        }

        $since_punch = max(0, $now - $split->getTimestamp());
        if ($since_punch < $time_limit) {
            $waiting[] = ["runner" => $runner, "remaining" => $time_limit - $since_punch];
        } elseif ($since_punch < 900) {
            // Løperen har venta ferdig, men er ikkje meir enn 15 minutt sidan passeringa.
            $finished[] = ["runner" => $runner, "elapsed" => $since_punch - $time_limit];
        }
    }

    // Kortast ventetid først, og dei som nett har blitt klare først.
    usort($waiting, fn($a, $b) => $a["remaining"] <=> $b["remaining"]);
    usort($finished, fn($a, $b) => $a["elapsed"] <=> $b["elapsed"]);

    echo("<p class=\"muted\">Klokka er " . date("H:i:s") . "</p>");
    echo("<h1>Tid igjen på matpost</h1>");
    if (count($waiting) === 0) {
        echo("<p class=\"muted\">Ingen løpere venter akkurat nå.</p>");
    } else {
        echo("<div class=\"flex wrap center\">");
        foreach ($waiting as $item) {
            $runner = $item["runner"];
            echo("<div id=\"waiting-" . (int) $runner->id . "\" class=\"flash attention countdown-card\">"
                . "<span class=\"countdown-name\">" . (int) $runner->id . " " . esc($runner->name) . "</span>"
                . "<span class=\"time\">−" . format($item["remaining"]) . "</span>"
                . "</div>");
        }
        echo("</div>");
    }

    echo("<h1>Klar til å løpe</h1>");
    if (count($finished) === 0) {
        echo("<p class=\"muted\">Ingen har ventet ferdig ennå.</p>");
    } else {
        echo("<div class=\"flex wrap center\">");
        foreach ($finished as $item) {
            $runner = $item["runner"];
            echo("<div id=\"ready-" . (int) $runner->id . "\" class=\"flash success countdown-card ready-card\" data-ready-id=\"" . (int) $runner->id . "\">"
                . "<span class=\"countdown-name\">" . (int) $runner->id . " " . esc($runner->name) . "</span>"
                . "<span class=\"time\">+" . format($item["elapsed"]) . "</span>"
                . "</div>");
        }
        echo("</div>");
    }
}
