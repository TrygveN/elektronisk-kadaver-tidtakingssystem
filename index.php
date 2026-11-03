<?php
require __DIR__ . "/lib/request.php";
require_request_path(["/", "/index.php"]);

$config = is_readable(__DIR__ . "/data/config.ini")
    ? (parse_ini_file(__DIR__ . "/data/config.ini") ?: [])
    : [];
$year = date("Y");
$hide_results = in_array($config["skjul_resultater"] ?? "0", ["1", "true", "on", "yes"], true);
$start_timestamp = strtotime($config["start_date"] ?? "");
if ($start_timestamp !== false) {
    $year = date("Y", $start_timestamp);
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakingssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="css/matcha.css">
    <style>
        @media only screen and (max-width: 650px) {
            body {
                padding-left: .75rem;
                padding-right: .75rem;
            }
            #tabellkonteiner,
            [data-results] {
                overflow-x: auto;
            }
            #tabellkonteiner table,
            [data-results] table {
                font-size: .85rem;
            }
            td, th {
                padding: .25rem .4rem;
            }
        }
        @media (prefers-color-scheme: dark) {
            .invert {
                filter: invert(1);
            }
        }
    </style>
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<div class="flex align-center">
<figure><img src="img/kadaver.png" alt="" width="100px" class="invert"></figure>
<h1>Løpende resultater Kadaverløpet <?= htmlspecialchars($year, ENT_QUOTES, "UTF-8") ?></h1>
</div>
<?php if ($hide_results): ?>
<p>Resultatene er ikke publisert ennå.</p>
<?php else: ?>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<p id="live-status" class="smaller muted" role="status" aria-live="polite">Oppdateres automatisk …</p>

<div id='tabellkonteiner' class='flex' data-results hx-get="/api/table.php?type=liveresultater" hx-swap="innerMorph" hx-trigger="load, every 5s" hx-status:5xx="swap:none"></div>
<h2>Minikadaver'n</h2>
<div class='flex' data-results hx-get="/api/table.php?type=minikadavern" hx-swap="innerMorph" hx-trigger="load, every 15s" hx-status:5xx="swap:none"></div>
<?php endif; ?>

<footer>
<p><a href="paameldte.php">Se alle påmeldte</a></p>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
<script src="/lib/htmx.min.js"></script>
<script>
function mark_updated() {
    const status = document.getElementById("live-status");
    status.textContent =
        "Oppdatert kl. " + new Date().toLocaleTimeString("no-NO", { hour12: false });
    status.className = "smaller muted";
}

function mark_update_failed() {
    const status = document.getElementById("live-status");
    status.textContent = "Får ikke kontakt med serveren. Viser siste kjente resultater.";
    status.className = "smaller danger";
}

// htmx 4 bruker namespace-aktige hendingsnamn, og detaljane ligg i
// event.detail.ctx (ikkje event.detail.elt som i htmx 2).
document.body.addEventListener("htmx:after:swap", (event) => {
    const ctx = event.detail?.ctx;
    const element = ctx?.target ?? event.target;
    if (element && (element.id === "tabellkonteiner" || element.hasAttribute("data-results"))) {
        if ((ctx?.response?.status ?? 200) < 400) {
            mark_updated();
        } else {
            mark_update_failed();
        }
    }
});
document.body.addEventListener("htmx:response:error", mark_update_failed);
document.body.addEventListener("htmx:error", mark_update_failed);
</script>
</body>
</html>
