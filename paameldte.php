<?php
// År og oppdateringsdato hentast frå når løperbasen (db.csv) sist vart endra.
$db_file = $_SERVER['DOCUMENT_ROOT'] . "/data/db.csv";
$db_updated = is_readable($db_file) ? filemtime($db_file) : time();
$year = date("Y", $db_updated);
$updated = date("d.m.y", $db_updated);
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Påmeldte Kadaverløpet</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="css/matcha.css">
    <style>
        @media (prefers-color-scheme: dark) {
            .invert {
                filter: invert(1);
            }
        }
        @media (max-width: 544px) {
            :root {
                --page-pad: 0.5rem;
            }
            body {
                padding-inline: var(--page-pad);
                font-size: 16px;
            }
            /* matcha forskyver footer med -1.5rem, hald han i takt med sidepadingen. */
            body > footer:last-child {
                margin-inline: calc(-1 * var(--page-pad));
                padding-inline: var(--page-pad);
            }
            figure {
                margin: 0;
                flex-shrink: 0;
            }
            h1 {
                font-size: 1.375rem;
                hyphens: manual;
            }
            table {
                display: table;
                table-layout: fixed;
                width: 100%;
                font-size: clamp(0.7rem, 3.5vw, 1rem);
            }
            td, th {
                padding: 0.25rem;
                overflow-wrap: anywhere;
            }
            .col-start { width: 17%; }
            .col-name { width: 41.5%; }
            .col-club { width: 41.5%; }
            /* Startnummer er kort og skal ikkje brytast over fleire linjer. */
            .col-start {
                font-size: 0.85em;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
<div class="flex align-center">
<figure><img src="img/kadaver.png" alt="" width="100px" class="invert"></figure>
<div>
<h1>Påmeldte Kadaverløpet <?= htmlspecialchars($year) ?></h1>
<strong>Oppdatert <?= htmlspecialchars($updated) ?></strong>
<br><a href="https://nettskjema.no/a/kadavern26">👉️Meld deg på her!👈️</a>
<br><a href="https://kadavern.oj-oj.net/">Les mer om løpet</a>

</div>
</div>

<?php
$query = ["type"=>"paameldte"];
include("api/table.php");
participants_table($runners);
?>
<footer>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
</body>
</html>