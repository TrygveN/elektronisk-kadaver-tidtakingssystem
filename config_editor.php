<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="css/matcha.css">
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<?php $selected_page = "config"; include(__DIR__ . "/nav.php"); ?>

  <h1>Konfigurasjon</h1>
<div hx-get="/api/config.php" hx-swap="outerHTML" hx-trigger="load"></div>
<script>
  function log_out(){
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/login.html";
  }

  // Sjekk om brukeren er logga inn. hvis ikke hiv de ut til innloggingskjermen
  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/api/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send(new URLSearchParams({
    username: localStorage.getItem("navn") ?? "",
    password: localStorage.getItem("passord") ?? "",
  }).toString());
  if (xmlHttpReq.status != 200){
      window.location.href = "/login.html";
  }

</script>
<script src="/lib/htmx.min.js"></script>
</body>
</html>