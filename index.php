<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="https://matcha.mizu.sh/matcha.css">
</head>
<body>
<h1>Løpende resultater kadaverløpet 2024</h1>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<table>
<tbody>
<?php
include("get_table.php")
?>
</tbody>
</table>
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
