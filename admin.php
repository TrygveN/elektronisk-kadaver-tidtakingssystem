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
    <li><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
    <li><a href="/matpost_nedtelling.php?matpost=0">⏳ Nedtelling 1. mat</a></li>
    <li><a href="/matpost_nedtelling.php?matpost=1">⏳ Nedtelling 2. mat</a></li>
  </menu>
</nav>
  <button class="danger" onclick="log_out()">Logg ut</button>
  <button class="accent" onclick="export_results()">📄 Eksporter resultater (HTML)</button>
<div class='flex wrap' hx-get="/api/statistics.php" hx-trigger="every 5s">
<?php
include("api/statistics.php");
?>
</div>
<h1>Søk opp løper</h1>
<form action="/api/runner.php" method="GET" hx-boost="true" hx-target="#runner" hx-swap="show:none" hx-headers='js:{"Password": localStorage.getItem("passord")}'>
  <label>
    Navn, startnummer eller klubb
    <input type="text" name="search" id="search" autocomplete="off" autofocus>
  </label>
  <button type="submit">Søk</button>
</form>
<div id="runner" class="profile-card"></div>

<button hx-get="/api/email.php" hx-target="#emails" hx-headers='js:{"Password": localStorage.getItem("passord")}'>Hent alle e-poster</button>
<button hx-get="/api/email.php?course=Kadaverløpet" hx-target="#emails" hx-headers='js:{"Password": localStorage.getItem("passord")}'>Hent e-poster for Kadaverløpet</button>
<button hx-get="/api/email.php?course=Minikadaver'n" hx-target="#emails" hx-headers='js:{"Password": localStorage.getItem("passord")}'>Hent e-poster for Minikadaver'n</button>
<div class="flex wrap align-center">
  <button class="default" onclick="copy_emails()">📋 Kopier e-postliste</button>
  <span id="email-count" class="muted smaller"></span>
</div>
<div class="flash default">
<output id="emails" aria-live="polite"></output>
</div>
<h2>Synkroniser løperbase</h2>
<button id="sync-button" hx-get="/api/nettskjema_sync.php" hx-target="#sync-status" hx-swap="show:none"
        hx-headers='js:{"Password": localStorage.getItem("passord")}'>🔄 Synk fra Nettskjema</button>
<div class="flash default">
<output id="sync-status" aria-live="polite"></output>
</div>
<h1>Løpende resultater</h1>
<h2>Kadaverløpet</h2>
<div class='flex' hx-get="api/table.php?type=liveresultater" hx-trigger="load, every 5s"></div>
<h2>Minikadaver'n</h2>
<div class='flex' hx-get="api/table.php?type=minikadavern" hx-trigger="load, every 15s"></div>
<script>
  function log_out(){
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/login.html";
  }

  function export_results(){
    fetch("/api/export.php", { headers: { Password: localStorage.getItem("passord") } })
      .then(response => {
        if (response.status === 401) {
          alert("Feil passord!");
          return null;
        }
        return response.blob();
      })
      .then(blob => {
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = "resultater-kadaver.html";
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      });
  }

  function email_list() {
    const text = document.getElementById("emails").textContent.trim();
    return text ? text.split(",").map((email) => email.trim()).filter(Boolean) : [];
  }

  function update_email_count() {
    const emails = email_list();
    document.getElementById("email-count").textContent = emails.length
      ? `${emails.length} e-postadresser`
      : "";
  }

  function fallback_copy(text) {
    const field = document.createElement("textarea");
    field.value = text;
    document.body.appendChild(field);
    field.select();
    const copied = document.execCommand("copy");
    field.remove();
    return copied;
  }

  async function copy_emails() {
    const emails = email_list();
    if (emails.length === 0) {
      document.getElementById("email-count").textContent = "Ingen e-poster å kopiere";
      return;
    }
    const text = emails.join(", ");
    try {
      await navigator.clipboard.writeText(text);
    } catch (error) {
      fallback_copy(text);
    }
    document.getElementById("email-count").textContent =
      `${emails.length} e-postadresser kopiert`;
    setTimeout(update_email_count, 3000);
  }

  // htmx 4 bruker namespace-aktige hendingsnamn.
  document.body.addEventListener("htmx:after:swap", (event) => {
    const element = event.detail && event.detail.elt ? event.detail.elt : event.target;
    if (element && element.id === "emails") {
      update_email_count();
    }
  });

  // Hindre dobbel synkronisering medan førespurnaden går.
  document.body.addEventListener("htmx:before:request", (event) => {
    const element = event.detail && event.detail.elt ? event.detail.elt : event.target;
    if (element && element.id === "sync-button") {
      element.disabled = true;
    }
  });
  document.body.addEventListener("htmx:after:request", (event) => {
    const element = event.detail && event.detail.elt ? event.detail.elt : event.target;
    if (element && element.id === "sync-button") {
      element.disabled = false;
    }
  });

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