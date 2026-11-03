<?php
require_once(__DIR__ . "/../import_runners.php");
$runners = read_runners_from_csv();
usort($runners, fn($a, $b) => strcasecmp($a->name, $b->name));
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Start og betaling – EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="/css/matcha.css">
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <style>
      .page-header,
      .summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .75rem 1.5rem;
      }
      .page-header {
        justify-content: space-between;
      }
      .page-header h1 {
        margin: 0;
      }
      .summary {
        margin-bottom: 1rem;
      }
      .table-search {
        max-width: 32rem;
      }
      .table-container {
        overflow-x: auto;
      }
      .status-table {
        min-width: 42rem;
      }
      .status-table .check-column {
        width: 6rem;
        text-align: center;
      }
      .status-table input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
      }
      #save-status:empty {
        display: none;
      }
    </style>
</head>
<body>
<?php $selected_page = "status"; include(__DIR__ . "/nav.php"); ?>

<header class="page-header">
  <h1>Start og betaling</h1>
  <button class="default" onclick="log_out()">Logg ut</button>
</header>
<p class="muted">Huk av når en løper har startet eller betalt. Endringene lagres med en gang.</p>

<div class="summary" aria-live="polite">
  <strong id="started-count"></strong>
  <strong id="paid-count"></strong>
</div>
<label class="table-search">
  Filtrer løpere
  <input type="search" id="runner-filter" placeholder="Navn, startnummer eller klubb" autocomplete="off" autofocus>
</label>
<p id="save-status" role="status" aria-live="polite"></p>

<div class="table-container">
<table class="status-table">
  <thead>
    <tr>
      <th>Startnummer</th>
      <th>Navn</th>
      <th>Klubb</th>
      <th class="check-column">Startet</th>
      <th class="check-column">Betalt</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($runners as $runner): ?>
    <tr data-runner-row>
      <td><?= (int) $runner->id ?></td>
      <td><?= esc($runner->name) ?></td>
      <td><?= esc($runner->club) ?></td>
      <?php foreach (["started" => "Startet", "paid" => "Betalt"] as $field => $label): ?>
      <td class="check-column">
        <input type="checkbox" data-status="<?= $field ?>" data-id="<?= (int) $runner->id ?>"
               aria-label="<?= $label ?>: <?= esc($runner->name) ?>" <?= $runner->$field ? "checked" : "" ?>
               hx-patch="/api/runner_status.php" hx-trigger="change"
               hx-vals='js:{id: this.dataset.id, field: this.dataset.status, value: this.checked.toString()}'
               hx-headers='js:{"password": localStorage.getItem("passord")}'
               hx-target="#save-status" hx-swap="innerHTML">
      </td>
      <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<p id="no-results" class="muted" hidden>Ingen løpere passer til søket.</p>

<script>
  function log_out() {
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/admin/login.html";
  }

  function update_summary() {
    const started = document.querySelectorAll('[data-status="started"]:checked').length;
    const paid = document.querySelectorAll('[data-status="paid"]:checked').length;
    document.getElementById("started-count").textContent = `${started} startet`;
    document.getElementById("paid-count").textContent = `${paid} betalt`;
  }

  document.body.addEventListener("htmx:before:request", (event) => {
    const checkbox = event.target;
    if (!checkbox.matches("[data-status]")) return;
    checkbox.dataset.previous = (!checkbox.checked).toString();
    checkbox.disabled = true;
    const status = document.getElementById("save-status");
    status.className = "muted";
    status.textContent = "Lagrer …";
  });

  document.body.addEventListener("htmx:after:request", (event) => {
    const checkbox = event.target;
    if (!checkbox.matches("[data-status]")) return;
    checkbox.disabled = false;
    if (event.detail.ctx.response.status >= 400) {
      checkbox.checked = checkbox.dataset.previous === "true";
    }
    update_summary();
  });

  document.body.addEventListener("htmx:error", (event) => {
    const checkbox = event.target;
    if (!checkbox.matches("[data-status]")) return;
    checkbox.checked = checkbox.dataset.previous === "true";
    checkbox.disabled = false;
    const status = document.getElementById("save-status");
    status.className = "danger";
    status.textContent = "Kunne ikke lagre. Prøv igjen.";
    update_summary();
  });

  document.getElementById("runner-filter").addEventListener("input", (event) => {
    const search = event.target.value.trim().toLocaleLowerCase("no-NO");
    let visible = 0;
    document.querySelectorAll("[data-runner-row]").forEach((row) => {
      const matches = row.textContent.toLocaleLowerCase("no-NO").includes(search);
      row.hidden = !matches;
      if (matches) visible++;
    });
    document.getElementById("no-results").hidden = visible !== 0;
  });

  update_summary();

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
</script>
<script src="/lib/htmx.min.js"></script>
</body>
</html>
