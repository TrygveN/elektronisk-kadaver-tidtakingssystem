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
    <link rel="stylesheet" href="/css/matcha.css">
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
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<div class="settings">
<?php $selected_page = "registration"; include(__DIR__ . "/nav.php"); ?>

<div class="post-bar">
  <div id="post-banner" class="flash danger" role="status">Ingen post valgt. Velg post før du registrerer.</div>
  <label for="search">Søk etter startnummer
    <input id="search" type="number" class="form-control" inputmode="numeric" autocomplete="off"
           enterkeyhint="done" onkeydown="register_from_search(event)"
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
  <button onclick="refresh_runners()">Oppdater tabell</button>
  <button class="accent" onclick="open_manual_dialog()">🕐 Manuell tid</button>
</div>

<table id="runners" aria-label="Løpere"
       hx-get="/api/table.php?type=registrering"
       hx-trigger="load, refreshRunners, input changed delay:200ms from:#search"
       hx-vals='js:{control: get_control(), filter: searchInput.value}'
       hx-swap="innerHTML"
       hx-config='{"cache":"no-store"}'
       hx-sync="this:replace"
       hx-status:4xx="swap:none"
       hx-status:5xx="swap:none"></table>
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
  <form id="manual-form"
        hx-post="/api/passing.php"
        hx-vals='js:{...manual_payload(this)}'
        hx-swap="none"
        hx-disable="find button, find input, find select">
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

<script src="/lib/htmx.min.js"></script>
<script>
const POST_LABELS = {1: "1. matpost", 2: "2. matpost", 3: "Mål"};
const searchInput = document.getElementById("search");
const runnersTable = document.getElementById("runners");
let toastTimer = null;
let pendingSearchId = null;
let tableScrollY = 0;

function is_mobile_layout() {
    return window.matchMedia("(max-width: 700px)").matches;
}

function refresh_runners() {
    htmx.trigger(runnersTable, "refreshRunners");
}

function now_iso() {
    return new Date().toISOString().split(".")[0] + "Z";
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
    refresh_runners();
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

function runner_row_for_id(id) {
    return Array.from(document.querySelectorAll("#runners tbody tr")).find(
        (tr) => Number(tr.querySelector(".id-cell")?.textContent.trim()) === Number(id)
    );
}

function register_from_search(event) {
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

    // Vent på at tabellen viser treffet før vi trykker på registrer-knappen.
    pendingSearchId = Number(value);
    refresh_runners();
}

function activate_pending_search_result() {
    if (pendingSearchId === null) {
        return;
    }
    const id = pendingSearchId;
    pendingSearchId = null;
    if (Number(searchInput.value) !== id) {
        return;
    }
    const row = runner_row_for_id(id);
    if (!row) {
        show_toast({
            kind: "attention",
            title: "Ingen treff",
            detail: `Fant ingen løper med startnummer ${id}.`,
        });
        return;
    }
    row.querySelector("button.register-button")?.click();
}

function handle_table_response(ctx) {
    if (ctx.response.status >= 400) {
        pendingSearchId = null;
        set_refresh_status("Kunne ikke oppdatere listen.", true);
        return;
    }
    set_refresh_status("Sist oppdatert: " + new Date().toLocaleTimeString("no-NO", { hour12: false }));
}

function table_response_is_current(ctx) {
    const params = new URL(ctx.request.action, window.location.href).searchParams;
    return params.get("filter") === searchInput.value
        && params.get("control") === String(get_control());
}

function handle_registration_response(ctx, button) {
    const status = ctx.response.status;
    const id = ctx.request.body.get("id");
    const control = ctx.request.body.get("control");
    const name = runner_name_from_button(button);

    if (status === 200) {
        const when = new Date(ctx.request.body.get("time"));
        const clock = isNaN(when.getTime())
            ? new Date().toLocaleTimeString("no-NO", { hour12: false })
            : when.toLocaleTimeString("no-NO", { hour12: false });
        show_toast({
            kind: "success",
            title: "Passering lagret",
            name: runner_label(id, name),
            detail: `${POST_LABELS[control]} · kl. ${clock}`,
        });
        searchInput.value = "";
        refresh_runners();
        searchInput.focus();
        return;
    }

    if (status === 401) {
        show_toast({
            kind: "danger",
            title: "Feil passord",
            detail: "Logg inn på nytt.",
        });
        searchInput.focus();
        return;
    }

    show_toast({
        kind: "danger",
        title: "Ikke lagret",
        name: runner_label(id, name),
        detail: ctx.text.trim() || `Kunne ikke lagre passering (HTTP ${status}).`,
    });
    searchInput.focus();
}

function manual_payload(form) {
    const when = new Date(form.elements["time"].value);
    return {
        password: localStorage.getItem("passord"),
        control: form.elements["control"].value,
        id: form.elements["id"].value,
        time: isNaN(when.getTime()) ? "" : when.toISOString().split(".")[0] + "Z",
        navn: localStorage.getItem("navn") ?? "",
    };
}

function handle_manual_response(ctx, form) {
    const status = document.getElementById("manual-status");
    if (ctx.response.status === 200) {
        const id = form.elements["id"].value;
        const control = form.elements["control"].value;
        const when = new Date(form.elements["time"].value);
        const name = runner_name_for_id(id);
        document.getElementById("manual-dialog").close();
        form.reset();
        status.className = "flash danger hidden";
        status.textContent = "";
        show_toast({
            kind: "success",
            title: "Manuell passering lagret",
            name: runner_label(id, name),
            detail: `${POST_LABELS[control]} · kl. ${when.toLocaleTimeString("no-NO", { hour12: false })}`,
        });
        refresh_runners();
        return;
    }

    status.className = "flash danger";
    status.textContent = ctx.response.status === 401
        ? "Feil passord!"
        : (ctx.text.trim() || "Kunne ikke lagre passering.");
}

document.body.addEventListener("htmx:before:request", (event) => {
    const ctx = event.detail?.ctx;
    if (ctx?.sourceElement === runnersTable) {
        tableScrollY = window.scrollY;
    }
    if (ctx?.sourceElement?.matches?.("#manual-form")) {
        const status = document.getElementById("manual-status");
        status.className = "flash danger hidden";
        status.textContent = "";
    }
});

document.body.addEventListener("htmx:after:request", (event) => {
    const ctx = event.detail?.ctx;
    if (!ctx) {
        return;
    }
    const source = ctx.sourceElement;
    if (source === runnersTable) {
        if (!table_response_is_current(ctx)) {
            event.preventDefault();
            return;
        }
        handle_table_response(ctx);
    } else if (source?.classList?.contains("register-button")) {
        handle_registration_response(ctx, source);
    } else if (source?.matches?.("#manual-form")) {
        handle_manual_response(ctx, source);
    }
});

document.body.addEventListener("htmx:after:swap", (event) => {
    const ctx = event.detail?.ctx;
    if (ctx?.sourceElement !== runnersTable) {
        return;
    }
    if (window.scrollY !== tableScrollY) {
        window.scrollTo(0, tableScrollY);
    }
    activate_pending_search_result();
    if (searchInput.value !== "") {
        requestAnimationFrame(() => ensure_result_visible(runnersTable));
    }
});

document.body.addEventListener("htmx:error", (event) => {
    // Ein nyare tabellførespurnad avbryt den førre. Det er ikkje ein nettverksfeil.
    if (event.detail?.error?.name === "AbortError") {
        return;
    }
    const ctx = event.detail?.ctx;
    const source = ctx?.sourceElement;
    if (source === runnersTable) {
        pendingSearchId = null;
        set_refresh_status("Får ikke kontakt med serveren. Viser siste kjente liste.", true);
        return;
    }
    if (source?.classList?.contains("register-button")) {
        const id = ctx.request?.body?.get?.("id");
        const name = runner_name_from_button(source);
        // Behald nummeret slik at det er enkelt å prøve igjen.
        if (id) {
            searchInput.value = id;
        }
        show_toast({
            kind: "danger",
            title: "Ikke lagret",
            name: runner_label(id, name),
            detail: "Fikk ikke kontakt med serveren. Prøv igjen.",
        });
        searchInput.focus();
        return;
    }
    if (source?.matches?.("#manual-form")) {
        const status = document.getElementById("manual-status");
        status.className = "flash danger";
        status.textContent = "Fikk ikke kontakt med serveren. Prøv igjen.";
    }
});

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

// Sjekk om brukeren er logga inn. hvis ikke hiv de ut til innloggingskjermen
let xmlHttpReq = new XMLHttpRequest();
xmlHttpReq.open("POST", "/api/is_authorized.php", false);
xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
xmlHttpReq.send(new URLSearchParams({
    username: localStorage.getItem("navn") ?? "",
    password: localStorage.getItem("passord") ?? "",
}).toString());
if (xmlHttpReq.status != 200) {
    window.location.href = "/admin/login.html";
}

searchInput.addEventListener("focus", update_search_mode);
searchInput.addEventListener("input", () => {
    if (pendingSearchId !== null && Number(searchInput.value) !== pendingSearchId) {
        pendingSearchId = null;
    }
});

restore_post();
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
        refresh_runners();
    }
}, 5000);
document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
        refresh_runners();
    }
});
</script>
</body>
</html>
