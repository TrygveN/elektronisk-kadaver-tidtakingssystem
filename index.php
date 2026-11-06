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
    <style>
        /* Berre stilane denne sida treng, henta frå matcha.css. */
        @font-face {
            font-family: "Kadaver Mono";
            font-weight: 700;
            src: url(/fonts/KadaverMono-Bold.woff2) format("woff2");
        }
        @font-face {
            font-family: "Kadaver Mono";
            src: url(/fonts/KadaverMono-Regular.woff2) format("woff2");
        }
        :root {
            --ft: "Kadaver Mono", "Adwaita Mono", "Source Code Pro", -apple-system,
                BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial,
                sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            --ft-size: 14px;
            --bd-radius: 6px;
            --tr-duration: .2s;
            --ct-width: 1024px;
            --default: #1f2328;
            --muted: #656d76;
            --accent: #0969da;
            --danger: #d1242f;
            --bg-default: #ffffff;
            --bg-muted: #f6f8fa;
            --bg-contrast: #24292f;
            --bd-muted: #d0d7deb3;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --default: #e6edf3;
                --muted: #848d97;
                --accent: #4493f8;
                --danger: #f85149;
                --bg-default: #0d1117;
                --bg-muted: #161b22;
                --bg-contrast: #6e7681;
                --bd-muted: #30363db3;
            }
        }
        body {
            margin: 0;
            background: var(--bg-default);
            color: var(--default);
            font-family: var(--ft);
            font-size: var(--ft-size);
            line-height: 1.5;
        }
        /* Innhaldet held seg til lesande breidd; tabellane er unnatekne
           og går heilt ut til skjermkantane. */
        body > :not(#tabellkonteiner, [data-results], footer) {
            box-sizing: border-box;
            max-width: calc(var(--ct-width) + 3rem);
            padding-inline: 1.5rem;
            margin-inline: auto;
        }
        a {
            color: var(--accent);
            text-decoration: inherit;
        }
        a:hover {
            text-decoration: underline;
        }
        h1, h2, h3 {
            margin: 0 0 1rem;
            line-height: 1.25;
            overflow-wrap: break-word;
        }
        h1 { font-size: 1.5rem; }
        h2 { font-size: 1.2rem; }
        h3 { font-size: 1.1rem; }
        :not(:is(dialog, article, body) > header:first-child) > :is(h1, h2) {
        }
        :not(hgroup, blockquote, header) > :is(h1, h2, h3):first-child {
            margin-top: 1.5rem;
        }
        :is(h1, h2, h3):last-child {
            margin-bottom: 0;
        }
        p {
            margin: 0 auto 1rem;
        }
        p:last-child {
            margin-bottom: 0;
        }
        figure {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        img {
            max-width: 100%;
            border-radius: var(--bd-radius);
            margin: auto;
        }
        abbr {
            color: var(--muted);
            text-decoration: underline dotted;
        }
        abbr[title] {
            position: relative;
            color: var(--accent);
            cursor: help;
        }
        footer {
            text-align: right;
        }
        body > footer {
            box-sizing: border-box;
            max-width: var(--ct-width);
            margin-inline: auto;
        }
        /* Tabellområdet går heilt ut til skjermkantane, men tabellen
           veks ikkje meir enn innhaldet krev. På mobil fyller han breidda. */
        #tabellkonteiner,
        [data-results] {
            width: 100%;
            max-width: calc(var(--ct-width) + 3rem);
            margin-inline: auto;
            overflow-x: auto;
        }
        table {
            width: fit-content;
            max-width: 100%;
            margin: 0 auto 1rem;
            border-collapse: collapse;
            border-spacing: 0;
        }
        tbody > tr {
            transition: background-color var(--tr-duration);
        }
        tbody > tr:hover {
            background-color: var(--bg-muted);
        }
        td, th {
            padding: .25rem .8125rem;
            border: 0;
            border-bottom: 1px solid var(--bd-muted);
            text-align: start;
            vertical-align: middle;
        }
        th {
            border-bottom-color: var(--bg-contrast);
            font-weight: 700;
            white-space: nowrap;
        }
        .flex { display: flex; }
        .align-center { align-items: center; }
        .smaller { font-size: .85rem; }
        .muted { color: var(--muted); }
        .danger { color: var(--danger); }

        @media only screen and (max-width: 650px) {
            body > :not(#tabellkonteiner, [data-results], footer) {
                padding-inline: .75rem;
            }
            footer {
                padding-inline: .75rem;
            }
            #tabellkonteiner table,
            [data-results] table {
                width: 100%;
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
<p>Vi tar forbehold om feil. Dette er ikke offisielle resultater</p>
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
