<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="matcha.css">
    <style>
        @media only screen and (max-width: 650px) {
            body {
                padding-left: 0;
                padding-right: 0;
            }
        }
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
<h1>TESTING Løpende resultater Kadaverløpet 2024</h1>
</div>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<?php
include("table.php");
liveresult_table($runners);
?>
<footer>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
<script>
    function update() {
        const table = document.querySelector("table");
        const request = new XMLHttpRequest();
        request.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            table.innerHTML = this.responseText;
            localStorage.setItem("ETag", this.getResponseHeader("ETag"));
        }};
        request.open("GET", "table.php?type=liveresultater");
        request.setRequestHeader("If-None-Match", localStorage.getItem("ETag"));
        request.send();
    }
    setInterval(update, 5*1000)
</script>
</body>
</html>
