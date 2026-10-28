<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakingssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="css/matcha.css">
    <style>
      html {
        scrollbar-gutter: stable;
      }
      body {
        padding: 0;
        margin: 0;
        width: 100%;
        max-width: 100%;
      }
      .settings {
        padding: 0 1.5rem;
        margin: 0 auto;
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
      /* Post og søkefelt ligg fast øvst medan lista kan bli lang. */
      .post-bar {
        position: sticky;
        top: 0;
        z-index: 20;
        padding: .25rem 0 .5rem;
        border-bottom: 1px solid var(--bd-muted);
        background: var(--bg-default);
      }
      .post-bar .flash {
        padding: .5rem .75rem;
        margin: .25rem 0 .5rem;
      }
      .post-bar label {
        margin-bottom: .5rem;
      }
      .post-bar input {
        min-height: 2.75rem;
        font-size: 1.25rem;
      }
      .post-bar-exit {
        display: none;
        margin-left: auto;
      }
      #refresh-status {
        margin: 0;
      }
      #runners {
        width: 100%;
        overflow-anchor: none;
      }
      #runners .register-button {
        min-width: 7.5rem;
        padding: .5rem .75rem;
      }
      .status-cell {
        white-space: nowrap;
      }
      /* På mobil vert kvar rad eit kompakt kort i staden for ein brei tabell. */
      .runner-meta {
        display: none;
      }
      .toast {
        position: fixed;
        bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
        left: 50%;
        z-index: 1000;
        display: none;
        width: min(28rem, calc(100vw - 2rem));
        align-items: flex-start;
        gap: .65rem;
        padding: .75rem .9rem;
        border: 1px solid var(--bd-muted);
        border-left: .3rem solid var(--success);
        border-radius: var(--bd-radius);
        background: var(--bg-default);
        box-shadow: var(--shadow);
        transform: translateX(-50%);
        pointer-events: none;
      }
      .toast.show {
        display: flex;
        animation: toast-in .18s ease-out;
      }
      .toast.danger {
        border-left-color: var(--danger);
      }
      .toast.attention {
        border-left-color: var(--attention);
      }
      .toast-icon {
        display: flex;
        width: 1.75rem;
        height: 1.75rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--bg-success);
        color: var(--success);
        font-weight: 700;
      }
      .toast.danger .toast-icon {
        background: var(--bg-danger);
        color: var(--danger);
      }
      .toast.attention .toast-icon {
        background: var(--bg-attention);
        color: var(--attention);
      }
      .toast-body {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        line-height: 1.35;
      }
      .toast-title {
        color: var(--muted);
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
      }
      .toast-name {
        font-size: 1.05rem;
        font-weight: 600;
        overflow-wrap: anywhere;
      }
      .toast-detail {
        color: var(--muted);
        font-size: .9rem;
      }
      .toast-close {
        display: flex;
        width: 2rem;
        height: 2rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: 0;
        margin: 0;
        background: transparent;
        color: var(--muted);
        font-size: 1.4rem;
        line-height: 1;
        pointer-events: auto;
      }
      .toast-close:not(:disabled):hover {
        border-color: transparent;
        background: transparent;
        color: var(--default);
      }
      @keyframes toast-in {
        from {
          opacity: 0;
          transform: translate(-50%, .5rem);
        }
        to {
          opacity: 1;
          transform: translate(-50%, 0);
        }
      }
      @media (max-width: 700px) {
        .settings {
          padding: 0 .75rem;
        }
        /* Når tastaturet er oppe, skjul alt utanom post, søk og treff. */
        body.searching nav,
        body.searching fieldset,
        body.searching .actions,
        body.searching #refresh-status {
          display: none;
        }
        body.searching .post-bar-exit {
          display: inline-block;
        }
        #runners {
          overflow: visible;
        }
        #runners thead {
          display: none;
        }
        #runners tbody {
          display: block;
        }
        #runners tr {
          display: grid;
          grid-template-columns: auto minmax(0, 1fr) auto;
          align-items: center;
          gap: 0 .6rem;
          padding: .55rem .25rem;
          border-bottom: 1px solid var(--bd-muted);
        }
        #runners td {
          display: block;
          padding: 0;
          border: 0;
        }
        #runners tr[data-empty] {
          display: block;
        }
        #runners tr[data-empty] td {
          padding: .25rem 0;
        }
        #runners .id-cell {
          font-size: 1.1rem;
          font-weight: 700;
          white-space: nowrap;
        }
        #runners .name-cell {
          min-width: 0;
          overflow-wrap: anywhere;
        }
        #runners .runner-meta {
          display: block;
          color: var(--muted);
          font-size: .85rem;
          line-height: 1.3;
        }
        #runners .time-cell,
        #runners .status-cell {
          display: none;
        }
        #runners .register-button {
          min-width: 0;
          padding: .5rem .6rem;
          white-space: nowrap;
        }
      }
    </style>
</head>
<body>
<div class="settings">
<nav>
  <menu>
    <li><a href="/admin.php">📊 Dashbord</a></li>
    <li class="selected"><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
    <li><a href="/matpost_nedtelling.php?matpost=0">⏳ Nedtelling 1. mat</a></li>
    <li><a href="/matpost_nedtelling.php?matpost=1">⏳ Nedtelling 2. mat</a></li>
  </menu>
</nav>

<div class="post-bar">
  <div id="post-banner" class="flash danger" role="status">Ingen post valgt. Velg post før du registrerer.</div>
  <label for="search">Søk etter startnummer
    <input id="search" type="number" class="form-control" inputmode="numeric" autocomplete="off"
           enterkeyhint="done" oninput="schedule_update()" onkeydown="register_from_search(event)"
           placeholder="Søk etter startnummer">
  </label>
  <div class="flex align-center space-between">
    <p id="refresh-status" class="smaller muted">Sist oppdatert: –</p>
    <button class="default post-bar-exit" onclick="exit_search_mode()">☰ Meny</button>
  </div>
</div>

<fieldset>
    <legend>Velg post</legend>
    <label>
      <input type="radio" name="post" value="1" onchange="set_post(this.value)">
      1. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="2" onchange="set_post(this.value)">
      2. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="3" onchange="set_post(this.value)">
      Mål
    </label>
  </fieldset>

<div class="actions flex wrap">
  <button onclick="update()">Oppdater tabell</button>
  <button class="accent" onclick="open_manual_dialog()">🕐 Manuell tid</button>
</div>

<table id="runners" aria-label="Løpere"></table>
</div>

<div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true">
  <span id="toast-icon" class="toast-icon" aria-hidden="true"></span>
  <div class="toast-body">
    <strong id="toast-title" class="toast-title"></strong>
    <span id="toast-name" class="toast-name"></span>
    <span id="toast-detail" class="toast-detail"></span>
  </div>
  <button class="toast-close" aria-label="Lukk varsel" onclick="hide_toast()">×</button>
</div>

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
    <output id="manual-status" class="flash danger hidden" role="status" aria-live="polite"></output>
    <button type="submit" class="success">Lagre passering</button>
  </form>
  <footer>
    <form method="dialog">
      <button type="submit" class="default">Lukk</button>
    </form>
  </footer>
</dialog>

<script>
const POST_LABELS = {1: "1. matpost", 2: "2. matpost", 3: "Mål"};
const searchInput = document.getElementById("search");
let toastTimer = null;
let searchTimer = null;
let updateSeq = 0;
let registering = false;

function is_mobile_layout() {
    return window.matchMedia("(max-width: 700px)").matches;
}

function schedule_update() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        searchTimer = null;
        update();
    }, 200);
}

function ensure_result_visible(table) {
    if (!is_mobile_layout()) {
        return;
    }
    const row = table.querySelector("tbody tr:not([data-empty])");
    const bar = document.querySelector(".post-bar");
    if (!row || !bar) {
        return;
    }
    const viewport = window.visualViewport;
    const visible_top = Math.max(
        bar.getBoundingClientRect().bottom,
        viewport ? viewport.offsetTop : 0
    );
    const visible_bottom = viewport
        ? viewport.offsetTop + viewport.height
        : window.innerHeight;
    const row_rect = row.getBoundingClientRect();
    if (row_rect.top >= visible_top && row_rect.bottom <= visible_bottom) {
        return;
    }
    scroll_to_post_banner();
}

function get_control() {
    const checked = document.querySelector('input[name="post"]:checked');
    return checked ? checked.value : 0;
}

function update_post_banner() {
    const control = get_control();
    const banner = document.getElementById("post-banner");
    if (!control) {
        banner.className = "flash danger";
        banner.textContent = "Ingen post valgt. Velg post før du registrerer.";
        return;
    }
    banner.className = "flash accent";
    banner.textContent = "Du registrerer på " + POST_LABELS[control] + ".";
}

function restore_post() {
    const saved = localStorage.getItem("ekt_post");
    const radio = saved
        ? document.querySelector('input[name="post"][value="' + saved + '"]')
        : null;
    if (radio) {
        radio.checked = true;
    }
    update_post_banner();
}

function set_post(value) {
    localStorage.setItem("ekt_post", value);
    update_post_banner();
    update_search_mode();
    update();
}

function update_search_mode() {
    if (!is_mobile_layout()) {
        document.body.classList.remove("searching");
        return;
    }
    const searching = document.activeElement === searchInput && Boolean(get_control());
    document.body.classList.toggle("searching", searching);
}

function exit_search_mode() {
    document.body.classList.remove("searching");
    searchInput.blur();
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function scroll_to_post_banner() {
    if (!is_mobile_layout()) {
        return;
    }
    const banner = document.getElementById("post-banner");
    if (!banner) {
        return;
    }
    const top = banner.getBoundingClientRect().top + window.scrollY;
    window.scrollTo({ top: Math.max(0, top), behavior: "smooth" });
}

function position_toast() {
    const toast = document.getElementById("toast");
    if (!toast || !window.visualViewport) {
        return;
    }
    // Kor mykje av layout-viewporten som er dekt av tastatur/nettlesar-UI.
    const inset = Math.max(
        0,
        window.innerHeight - window.visualViewport.height - window.visualViewport.offsetTop
    );
    toast.style.bottom = `calc(${inset}px + 1rem + env(safe-area-inset-bottom, 0px))`;
}

function hide_toast() {
    clearTimeout(toastTimer);
    document.getElementById("toast").className = "toast";
}

function show_toast({ kind = "success", title = "", name = "", detail = "" }) {
    const toast = document.getElementById("toast");
    const nameEl = document.getElementById("toast-name");
    const detailEl = document.getElementById("toast-detail");
    const icons = { success: "✓", danger: "!", attention: "i" };

    toast.className = "toast " + kind + " show";
    toast.setAttribute("role", kind === "danger" ? "alert" : "status");
    toast.setAttribute("aria-live", kind === "danger" ? "assertive" : "polite");
    document.getElementById("toast-icon").textContent = icons[kind] || "i";
    document.getElementById("toast-title").textContent = title;
    nameEl.textContent = name;
    nameEl.style.display = name ? "" : "none";
    detailEl.textContent = detail;
    detailEl.style.display = detail ? "" : "none";
    position_toast();
    // Tastaturet kan bruke litt tid på å opne seg.
    setTimeout(position_toast, 250);

    clearTimeout(toastTimer);
    if (kind === "success") {
        toastTimer = setTimeout(hide_toast, 6000);
    } else if (kind === "attention") {
        toastTimer = setTimeout(hide_toast, 8000);
    }
}

function show_no_post_toast() {
    show_toast({
        kind: "danger",
        title: "Velg post",
        detail: "Velg en post før du registrerer.",
    });
}

function runner_label(id, name) {
    return name ? `${id} ${name}` : `Startnummer ${id}`;
}

function runner_name_from_button(button) {
    const row = button ? button.closest("tr") : null;
    const nameEl = row ? row.querySelector(".runner-name") : null;
    return nameEl ? nameEl.textContent.trim() : "";
}

function runner_name_for_id(id) {
    const rows = document.querySelectorAll("#runners tbody tr");
    for (const row of rows) {
        const idCell = row.querySelector(".id-cell");
        if (idCell && Number(idCell.textContent.trim()) === Number(id)) {
            const nameEl = row.querySelector(".runner-name");
            return nameEl ? nameEl.textContent.trim() : "";
        }
    }
    return "";
}

function set_refresh_status(message, isError = false) {
    const el = document.getElementById("refresh-status");
    el.textContent = message;
    el.className = "smaller " + (isError ? "danger" : "muted");
}

async function update() {
    const seq = ++updateSeq;
    const table = document.getElementById("runners");
    const control = get_control();
    const filter = searchInput.value;
    try {
        const response = await fetch(
            `api/table.php?type=registrering&control=${control}&filter=${encodeURIComponent(filter)}`,
            { cache: "no-store" }
        );
        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }
        const text = await response.text();
        // Berre den nyaste førespurnaden skal få oppdatere tabellen.
        if (seq !== updateSeq || searchInput.value !== filter) {
            return;
        }
        if (table.innerHTML !== text) {
            const scroll_y = window.scrollY;
            table.innerHTML = text;
            // Nettlesaren kan justere scrollposisjonen når tabellen vert kortare.
            if (window.scrollY !== scroll_y) {
                window.scrollTo(0, scroll_y);
            }
            if (filter !== "") {
                requestAnimationFrame(() => ensure_result_visible(table));
            }
        }
        set_refresh_status("Sist oppdatert: " + new Date().toLocaleTimeString("no-NO", { hour12: false }));
    } catch (error) {
        if (seq === updateSeq) {
            set_refresh_status("Får ikke kontakt med serveren. Viser siste kjente liste.", true);
        }
    }
}

async function register_runner(id, button) {
    if (registering) {
        return;
    }
    const control = get_control();
    if (!control) {
        show_no_post_toast();
        return;
    }

    const name = runner_name_from_button(button);
    const existing = button && button.dataset ? button.dataset.time : "";
    if (existing && !confirm(
        `${runner_label(id, name)} er allerede registrert på ${POST_LABELS[control]} kl. ${existing}.\n` +
        "Vil du erstatte tiden?"
    )) {
        return;
    }

    const time = new Date(Date.now()).toISOString().split(".")[0] + "Z";
    const formData = new FormData();
    formData.append("password", localStorage.getItem("passord"));
    formData.append("control", control);
    formData.append("id", id);
    formData.append("time", time);

    registering = true;
    if (button) {
        button.disabled = true;
    }

    try {
        const response = await fetch("api/passing.php", {
            method: "POST",
            body: formData,
        });
        if (response.status === 401) {
            show_toast({
                kind: "danger",
                title: "Feil passord",
                detail: "Logg inn på nytt.",
            });
            return;
        }
        if (!response.ok) {
            const text = await response.text();
            show_toast({
                kind: "danger",
                title: "Ikke lagret",
                name: runner_label(id, name),
                detail: text || `Kunne ikke lagre passering (HTTP ${response.status}).`,
            });
            return;
        }
        const clock = new Date().toLocaleTimeString("no-NO", { hour12: false });
        show_toast({
            kind: "success",
            title: "Passering lagret",
            name: runner_label(id, name),
            detail: `${POST_LABELS[control]} · kl. ${clock}`,
        });
        clearTimeout(searchTimer);
        searchTimer = null;
        searchInput.value = "";
        await update();
    } catch (error) {
        // Behald nummeret slik at det er enkelt å prøve igjen.
        searchInput.value = id;
        show_toast({
            kind: "danger",
            title: "Ikke lagret",
            name: runner_label(id, name),
            detail: "Fikk ikke kontakt med serveren. Prøv igjen.",
        });
    } finally {
        registering = false;
        if (button) {
            button.disabled = false;
        }
        searchInput.focus();
    }
}

async function register_from_search(event) {
    if (event.key !== "Enter") {
        return;
    }
    event.preventDefault();
    const value = searchInput.value.trim();
    if (!/^\d+$/.test(value)) {
        return;
    }
    if (!get_control()) {
        show_no_post_toast();
        return;
    }

    // Sørg for at tabellen viser treffet før vi slår opp rada.
    clearTimeout(searchTimer);
    searchTimer = null;
    await update();
    const row = Array.from(document.querySelectorAll("#runners tbody tr")).find(
        (tr) => Number(tr.querySelector(".id-cell")?.textContent.trim()) === Number(value)
    );
    if (!row) {
        show_toast({
            kind: "attention",
            title: "Ingen treff",
            detail: `Fant ingen løper med startnummer ${value}.`,
        });
        return;
    }

    const button = row.querySelector("button.register-button");
    await register_runner(Number(value), button);
}

function local_datetime_value(date) {
    const pad = (n) => String(n).padStart(2, "0");
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
        + `T${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
}

function open_manual_dialog() {
    const dialog = document.getElementById("manual-dialog");
    const time = dialog.querySelector('input[name="time"]');
    time.value = local_datetime_value(new Date());
    const control = dialog.querySelector('select[name="control"]');
    if (get_control()) {
        control.value = get_control();
    }
    const status = document.getElementById("manual-status");
    status.className = "flash danger hidden";
    status.textContent = "";
    dialog.showModal();
    dialog.querySelector('input[name="id"]').focus();
}

document.getElementById("manual-form").addEventListener("submit", async (event) => {
    event.preventDefault();
    const form = event.target;
    const status = document.getElementById("manual-status");
    const when = new Date(form.elements["time"].value);
    status.className = "flash danger hidden";
    status.textContent = "";

    if (isNaN(when.getTime())) {
        status.className = "flash danger";
        status.textContent = "Ugyldig tidspunkt.";
        return;
    }

    const formData = new FormData();
    formData.append("password", localStorage.getItem("passord"));
    formData.append("control", form.elements["control"].value);
    formData.append("id", form.elements["id"].value);
    formData.append("time", when.toISOString().split(".")[0] + "Z");

    try {
        const response = await fetch("api/passing.php", { method: "POST", body: formData });
        if (response.status === 200) {
            const id = form.elements["id"].value;
            const control = form.elements["control"].value;
            const name = runner_name_for_id(id);
            const clock = when.toLocaleTimeString("no-NO", { hour12: false });
            document.getElementById("manual-dialog").close();
            form.reset();
            show_toast({
                kind: "success",
                title: "Manuell passering lagret",
                name: runner_label(id, name),
                detail: `${POST_LABELS[control]} · kl. ${clock}`,
            });
            await update();
        } else {
            status.className = "flash danger";
            status.textContent = response.status === 401
                ? "Feil passord!"
                : (await response.text() || "Kunne ikke lagre passering.");
        }
    } catch (error) {
        status.className = "flash danger";
        status.textContent = "Fikk ikke kontakt med serveren. Prøv igjen.";
    }
});

// Sjekk om brukeren er logga inn. hvis ikke hiv de ut til innloggingskjermen
let xmlHttpReq = new XMLHttpRequest();
xmlHttpReq.open("POST", "/api/is_authorized.php", false);
xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
xmlHttpReq.send(new URLSearchParams({
    username: localStorage.getItem("navn") ?? "",
    password: localStorage.getItem("passord") ?? "",
}).toString());
if (xmlHttpReq.status != 200) {
    window.location.href = "/login.html";
}

searchInput.addEventListener("focus", update_search_mode);

restore_post();
update();
if (get_control()) {
    searchInput.focus();
}
update_search_mode();

// Hald toasten over tastatur og nettlesar-UI på mobil.
if (window.visualViewport) {
    window.visualViewport.addEventListener("resize", position_toast);
    window.visualViewport.addEventListener("scroll", position_toast);
}
window.addEventListener("resize", position_toast);
window.addEventListener("resize", update_search_mode);
window.addEventListener("orientationchange", position_toast);

// Oppdater lista jamleg, men ikkje når fanen er skjult.
setInterval(() => {
    if (document.visibilityState === "visible") {
        update();
    }
}, 5000);
document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        update();
    }
});
</script>
</body>
</html>
