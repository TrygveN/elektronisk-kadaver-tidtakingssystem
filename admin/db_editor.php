<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Løperbase – EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="/css/matcha.css">
    <style>
      .page-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: 1.5rem;
      }
      .page-header h1 {
        margin: 0;
      }
      .panel {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--bd-muted);
        border-radius: var(--bd-radius);
        background: var(--bg-muted);
      }
      .panel > h2:first-child {
        margin-top: 0;
      }
      .panel form {
        padding: 0;
        margin: 0;
        background: transparent;
        overflow: visible;
      }
      .search-form,
      .button-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: .5rem;
      }
      .search-form label {
        flex: 1 1 18rem;
        margin: 0;
      }
      .profile-card {
        width: fit-content;
        max-width: 100%;
        padding: 1rem;
        margin-top: 1rem;
        border-left: .25rem solid var(--active);
        border-radius: var(--bd-radius);
        background: var(--bg-active);
      }
      .profile-card:empty {
        display: none;
      }
      .profile-card > h2:first-child {
        margin-top: 0;
      }
      .runner-list {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: .25rem;
        min-width: min(100%, 24rem);
      }
      .runner-list button {
        margin: 0;
        text-align: left;
      }
      .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 1rem;
      }
      .form-grid fieldset,
      .form-grid .form-actions {
        grid-column: 1 / -1;
      }
      .tools-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
      }
      .tools-grid .panel {
        margin: 0;
      }
      .output-container {
        margin-bottom: 0;
        overflow-wrap: anywhere;
      }
      .output-container:has(output:empty) {
        display: none;
      }
      .output-container output {
        display: block;
        max-height: 16rem;
        overflow: auto;
      }
      @media (max-width: 700px) {
        .form-grid,
        .tools-grid {
          grid-template-columns: 1fr;
        }
      }
    </style>
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<?php $selected_page = "runners"; include(__DIR__ . "/nav.php"); ?>

  <header class="page-header">
    <h1>Løperbase</h1>
    <button class="default" onclick="log_out()">Logg ut</button>
  </header>

  <section class="panel" aria-labelledby="search-heading">
    <h2 id="search-heading">Søk opp løper</h2>
    <form class="search-form" action="/api/runner.php" method="GET" hx-boost="true"
          hx-target="#runner" hx-swap="innerHTML show:none" hx-push-url="false"
          hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>
      <label>
        Navn, startnummer eller klubb
        <input type="search" name="search" id="search" autocomplete="off" autofocus required>
      </label>
      <button type="submit">Søk</button>
    </form>
    <div id="runner" class="profile-card" aria-live="polite"></div>
  </section>

  <section class="panel" aria-labelledby="add-heading">
    <h2 id="add-heading">Legg til løper</h2>
    <form class="form-grid" action="/api/runner.php" method="POST" hx-boost="true"
          hx-target="#updated" hx-swap="innerHTML show:none" hx-push-url="false"
          hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>
      <input type="hidden" id="password" name="password" required>

      <label for="id">Startnummer
        <input type="number" id="id" name="id" min="0" required>
      </label>
      <label for="name">Navn
        <input type="text" id="name" name="name" required>
      </label>
      <label for="club">Klubb/forening
        <input type="text" id="club" name="club">
      </label>
      <fieldset>
        <legend>Klasse</legend>
        <label>
          <input type="radio" name="course" value="Kadaverløpet" checked>
          Kadaverløpet
        </label>
        <label>
          <input type="radio" name="course" value="Minikadaver'n">
          Minikadaver'n
        </label>
      </fieldset>
      <div class="form-actions">
        <button type="submit">Legg til løper</button>
      </div>
    </form>
    <div id="updated" aria-live="polite"></div>
  </section>

  <div class="tools-grid">
    <section class="panel" aria-labelledby="email-heading">
      <h2 id="email-heading">E-postlister</h2>
      <p class="muted">Hent adresser for alle eller én klasse.</p>
      <div class="button-row">
        <button hx-get="/api/email.php" hx-target="#emails"
                hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>Hent alle</button>
        <button hx-get="/api/email.php?course=Kadaverløpet" hx-target="#emails"
                hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>Kadaverløpet</button>
        <button hx-get="/api/email.php?course=Minikadaver'n" hx-target="#emails"
                hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>Minikadaver'n</button>
      </div>
      <div class="button-row align-center">
        <button id="copy-emails" class="default" onclick="copy_emails()">📋 Kopier e-postliste</button>
        <span id="email-count" class="muted smaller" role="status"></span>
      </div>
      <div class="flash default output-container">
        <output id="emails" aria-live="polite"></output>
      </div>
    </section>

    <section class="panel" aria-labelledby="sync-heading">
      <h2 id="sync-heading">Synkroniser løperbase</h2>
      <p class="muted">Hent de nyeste påmeldingene fra Nettskjema.</p>
      <button id="sync-button" hx-get="/api/nettskjema_sync.php" hx-target="#sync-status" hx-swap="innerHTML show:none"
              hx-headers='js:{"password": encodeURIComponent(localStorage.getItem("passord") ?? "")}'>🔄 Synk fra Nettskjema</button>
      <div class="flash default output-container">
        <output id="sync-status" aria-live="polite"></output>
      </div>
    </section>
  </div>

<script>
  function log_out() {
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/admin/login.html";
  }

  function email_list() {
    const text = document.getElementById("emails").textContent.trim();
    return text
      ? text.split(",").map((email) => email.trim()).filter((email) => email.includes("@"))
      : [];
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

  // htmx 4 bruker namespace-aktige hendingsnamn, og detaljane ligg i
  // event.detail.ctx (ikkje event.detail.elt som i htmx 2).
  document.body.addEventListener("htmx:after:swap", (event) => {
    const ctx = event.detail?.ctx;
    const element = ctx?.target ?? event.target;
    if (element && element.id === "emails" && (ctx?.response?.status ?? 200) < 400) {
      update_email_count();
    }
  });

  // Hindre dobbel synkronisering medan førespurnaden går.
  document.body.addEventListener("htmx:before:request", (event) => {
    const element = event.detail?.ctx?.sourceElement ?? event.target;
    if (element && element.id === "sync-button") {
      element.disabled = true;
    }
  });
  // finally:request køyrer også når nettverket feilar, slik at knappen
  // ikkje blir ståande deaktivert.
  document.body.addEventListener("htmx:finally:request", (event) => {
    const element = event.detail?.ctx?.sourceElement ?? event.target;
    if (element && element.id === "sync-button") {
      element.disabled = false;
    }
  });

  // htmx:error gjeld nettverks- og byttefeil. HTTP-feil får eige svar frå
  // serveren og blir viste i #sync-status.
  document.body.addEventListener("htmx:error", (event) => {
    const element = event.detail?.ctx?.sourceElement ?? event.target;
    if (element && element.id === "sync-button") {
      const status = document.getElementById("sync-status");
      status.className = "danger";
      status.textContent = "Fikk ikke kontakt med serveren. Prøv igjen.";
    }
  });

  // Sjekk om brukeren er logga inn. Hvis ikke, send til innloggingsskjermen.
  const xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/api/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
  xmlHttpReq.send(new URLSearchParams({
    username: localStorage.getItem("navn") ?? "",
    password: localStorage.getItem("passord") ?? "",
  }).toString());
  if (xmlHttpReq.status !== 200) {
    window.location.href = "/admin/login.html";
  }
  document.getElementById("password").value = localStorage.getItem("passord");
</script>
<script src="/lib/htmx.min.js"></script>
</body>
</html>
