<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="matcha.css">
    <style>
      body {
        padding: 0;
        margin: 0;
        width: 100%;
        max-width: 100%;
      }
      .settings {
        padding: 0 1.5rem;
        padding-top: 0px;
        padding-right: 1.5rem;
        padding-bottom: 0px;
        padding-left: 1.5rem;

        margin: 0 auto;
        margin-top: 0px;
        margin-right: auto;
        margin-bottom: 0px;
        margin-left: auto;
        max-width: var(--ct-width);
      }
      fieldset {
          display: flex;
          flex-wrap: wrap;
          justify-content: space-evenly;
      }
      .bg-success {
        background: var(--bg-success) !important;
      }
      .bg-active {
        background: var(--bg-active) !important;
      }
    </style>
</head>
<body>
<div class="settings">
<nav>
  <menu>
    <li class="disabled"><a href="/admin.php">📊 Dashbord</a></li>
    <li class="selected"><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li class="disabled"><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li class="disabled"><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
  </menu>
</nav>
<fieldset>
    <legend>Velg post</legend>
    <label>
      <input type="radio" name="post" value="1" onclick="update()">
      1. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="2" onclick="update()">
      2. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="3" onclick="update()">
      Mål
    </label>
  </fieldset>

<button onmousedown="update()">Oppdater tabell</button>
<input id="search" type="number" class="form-control"  onkeyup="update()"  placeholder="Søk etter startnummer">
<br>
</div>
<table id="runners"></table>
<script>
// Hvilken matpost vi er på:
var control = location.search[1];

function get_control() {
    try {
      return document.querySelector('input[name="post"]:checked').value;
    }
    catch (error) {
      return 0;
    }
}

function register_runner(id) {
    control = get_control();
    if (control == 0) {
      console.error("Ingen post valgt");
      alert("Velg en post!");
      return 0;
    }
    let formData = new FormData();
    formData.append(name= 'password', localStorage.getItem("passord"))
    formData.append(name= 'control', value=control);
    formData.append(name= 'id', value=id);
    time = new Date(Date.now()).toISOString().split('.')[0]+"Z"
    formData.append('time', time);
    response = fetch("passing.php", {
        method: "POST",
        body: formData,
    })
    .then(response => {
      if (response.status == 401) {
      alert("Feil passord!")
    }})
    .then((response) => update());
    document.getElementById("search").focus();
    
};

function update() {
        const table = document.getElementById("runners");
        control = get_control();
        filter = document.getElementById("search").value;
        let request = new Request(`table.php?type=registrering&control=`+control+`&filter=`+filter);
        fetch(request)
        .then((response) => response.text())
        .then((text) => {table.innerHTML = text;})
    }


  // Sjekk om brukeren er logga inn. hvis ikke hiv de ut til innloggingskjermen
  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send("username=" + localStorage.getItem("navn")+"&"+"password=" + localStorage.getItem("passord"));
  if (xmlHttpReq.status != 200){
      window.location.href = "/login.html";
  }
  document.getElementById('password').value = localStorage.getItem("passord");

</script>
</body>
</html>