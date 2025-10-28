<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Påmeldte Kadaverløpet</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="matcha.css">
    <style>
        @media (prefers-color-scheme: dark) {
            .invert {
                filter: invert(1);
            }
        }
    </style>
</head>
<body>
<div class="flex align-center">
<figure><img src="img/kadaver.png" alt="" width="100px" class="invert"></figure>
<h1>Påmeldte Kadaverløpet 2025</h1>
</div>
<h2>Oppdatert 24.10.25</h2>
<div class="flash attention">Startnummer blir tildelt nærmere arrangementet. Prøver å oppdatere lista jevnlig. TBN</div>
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