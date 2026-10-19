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
    <li class="disabled">⏳️ Nedtelling matpost
        <menu>
          <li><a href="/matpost_nedtelling.php?matpost=0">1. mat</a></li>
          <li><a href="/matpost_nedtelling.php?matpost=1">2. mat</a></li>
        </menu>
    </li>
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
<button type="button" class="accent" onclick="open_manual_dialog()">🕐 Manuell tid</button>
<input id="search" type="number" class="form-control"  onkeyup="update()"  placeholder="Søk etter startnummer">
<br>
</div>
<table id="runners"></table>

<dialog id="manual-dialog">
  <header>
    <h2>Manuell registrering</h2>
  </header>
  <form id="manual-form">
    <label>
      Startnummer
      <input type="number" name="id" min="1" required>
    </label>
    <label>
      Post
      <select name="control" required>
        <option value="1">1. matpost</option>
        <option value="2">2. matpost</option>
        <option value="3">Mål</option>
      </select>
    </label>
    <label>
      Tidspunkt
      <input type="datetime-local" name="time" step="1" required>
    </label>
    <output id="manual-status" class="flash default" style="display: none;"></output>
    <button type="submit" class="success">Lagre passering</button>
  </form>
  <footer>
    <form method="dialog">
      <button type="submit" class="default">Lukk</button>
    </form>
  </footer>
</dialog>

<script>
function get_control() {
    const checked = document.querySelector('input[name="post"]:checked');
    return checked ? checked.value : 0;
}

function register_runner(id) {
    const control = get_control();
    if (control == 0) {
      console.error("Ingen post valgt");
      alert("Velg en post!");
      return 0;
    }
    const formData = new FormData();
    formData.append('password', localStorage.getItem("passord"));
    formData.append('control', control);
    formData.append('id', id);
    formData.append('time', new Date(Date.now()).toISOString().split('.')[0] + "Z");
    fetch("api/passing.php", {
        method: "POST",
        body: formData,
    })
    .then(response => {
      if (response.status == 401) {
        alert("Feil passord!");
      }
      update();
    });
    document.getElementById("search").focus();
};

function update() {
        const table = document.getElementById("runners");
        const control = get_control();
        const filter = document.getElementById("search").value;
        fetch(`api/table.php?type=registrering&control=${control}&filter=${encodeURIComponent(filter)}`)
        .then((response) => response.text())
        .then((text) => {table.innerHTML = text;})
    }

function local_datetime_value(date) {
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
        + `T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

function open_manual_dialog() {
    const dialog = document.getElementById("manual-dialog");
    const time = dialog.querySelector('input[name="time"]');
    if (!time.value) {
        time.value = local_datetime_value(new Date());
    }
    const status = document.getElementById("manual-status");
    status.style.display = "none";
    status.textContent = "";
    dialog.showModal();
}

document.getElementById("manual-form").addEventListener("submit", (event) => {
    event.preventDefault();
    const form = event.target;
    const status = document.getElementById("manual-status");
    const when = new Date(form.elements["time"].value);
    if (isNaN(when.getTime())) {
        status.className = "flash danger";
        status.style.display = "";
        status.textContent = "Ugyldig tidspunkt.";
        return;
    }
    const formData = new FormData();
    formData.append("password", localStorage.getItem("passord"));
    formData.append("control", form.elements["control"].value);
    formData.append("id", form.elements["id"].value);
    formData.append("time", when.toISOString().split(".")[0] + "Z");
    fetch("api/passing.php", { method: "POST", body: formData })
        .then(async (response) => {
            if (response.status === 200) {
                document.getElementById("manual-dialog").close();
                form.reset();
                update();
            } else {
                status.className = "flash danger";
                status.style.display = "";
                status.textContent = response.status === 401
                    ? "Feil passord!"
                    : (await response.text() || "Kunne ikkje lagre passering.");
            }
        });
});


  // Sjekk om brukeren er logga inn. hvis ikke hiv de ut til innloggingskjermen
  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/api/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send("username=" + localStorage.getItem("navn")+"&"+"password=" + localStorage.getItem("passord"));
  if (xmlHttpReq.status != 200){
      window.location.href = "/login.html";
  }

</script>
</body>
</html>