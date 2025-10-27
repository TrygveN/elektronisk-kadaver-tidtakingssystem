<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="matcha.css">
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
<h1>Søk opp løper</h1>
<form action="/runner.php" method="GET" hx-boost="true" hx-target="#runner_info" hx-swap="show:none">
  <label>
    Navn, startnummer eller klubb
    <input type="text" name="search" id="search">
  </label>
  <button type="submit">Søk</button>
</form>
<div id="runner_info" class="profile-card"></div>
<h1>Løpende resultater</h1>
<?php
include("table.php");
liveresult_table($runners);
?>
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

<script>
  function log_out(){
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/login.html";
  }

  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send("username=" + localStorage.getItem("navn")+"&"+"password=" + localStorage.getItem("passord"));
  if (xmlHttpReq.status != 200){
      window.location.href = "/login.html";
  }

</script>
<script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js" integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz" crossorigin="anonymous"></script>
</body>
</html>