<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="css/matcha.css">
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <style>
      .profile-card{
        background-color: var(--bg-active);
        font-size: 1.4em;
        padding: 1rem;
	      border-radius: var(--bd-radius);
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
      }
      .profile-card h2{
        font-size: 2rem;
      }
    </style>
</head>
<body>
  <nav>
  <menu>
    <li class="selected"><a href="/admin.php">📊 Dashbord</a></li>
    <li class="disabled"><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li class="disabled"><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li class="disabled"><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
  </menu>
</nav>
  <button class="danger" onclick="log_out()">Logg ut</button>
<div class='flex wrap' hx-get="/api/statistics.php" hx-trigger="every 5s">
<?php
include("api/statistics.php");
?>
</div>
<h1>Søk opp løper</h1>
<form action="/api/runner.php" method="GET" hx-boost="true" hx-target="#runner_info" hx-swap="show:none">
  <label>
    Navn, startnummer eller klubb
    <input type="text" name="search" id="search">
  </label>
  <button type="submit">Søk</button>
</form>
<div id="runner_info" class="profile-card"></div>

<button hx-get="/api/email.php" hx-target="#emails" hx-headers='js:{"password": localStorage.getItem("passord")}'>hent alle eposter</button>
<button hx-get="/api/email.php?course=Kadaverløpet" hx-target="#emails" hx-headers='js:{"password": localStorage.getItem("passord")}'>Hent bare eposter for fullkadavern</button>
<button hx-get="/api/email.php?course=Minikadaver'n" hx-target="#emails" hx-headers='js:{"password": localStorage.getItem("passord")}'>Hent bare eposter for minikadaver'n</button>
<div class="flash default">
<output id="emails"></output>
</div>
<h1>Løpende resultater</h1>
<h2>Kadaverløpet</h2>
<div class='flex' hx-get="api/table.php?type=liveresultater" hx-trigger="load, every 5s"></div>
<h2>Minikadaver'n</h2>
<div class='flex' hx-get="api/table.php?type=minikadavern" hx-trigger="load, every 15s"></div>
<?php
include("api/table.php");
//liveresult_table($runners);
?>
<script>
  function log_out(){
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/login.html";
  }

  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/api/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send("username=" + localStorage.getItem("navn")+"&"+"password=" + localStorage.getItem("passord"));
  if (xmlHttpReq.status != 200){
      window.location.href = "/login.html";
  }

</script>
<script src="/lib/htmx.min.js"></script>
</body>
</html>