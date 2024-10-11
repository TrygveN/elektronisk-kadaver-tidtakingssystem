<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="https://matcha.mizu.sh/matcha.css">
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
<h1>Løpende resultater kadaverløpet 2024</h1>
</div>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<table>
<tbody>
<?php
include("get_table.php")
?>
</tbody>
<footer>
</table>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
</body>
<script>
    function update() {
        const table = document.querySelector("table");
        const myRequest = new Request(`get_table.php`);
        fetch(myRequest)
        .then((response) => response.text())
        .then((text) => {
        table.innerHTML = text;
        });
    }
    setInterval(update, 5*1000)
</script>
</html>
