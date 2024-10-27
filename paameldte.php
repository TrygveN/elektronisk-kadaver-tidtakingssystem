<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Påmeldte kadaverløpet</title>
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
<h1>Påmeldte kadaverløpet 2024</h1>
</div>
<h2>Oppdatert 13.10.24</h2>
<?php
$query = ["paameldte"];
include("table.php");
?>
<footer>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
</body>
</html>
