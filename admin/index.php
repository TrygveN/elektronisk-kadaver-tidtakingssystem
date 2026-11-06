<?php
require __DIR__ . "/../lib/request.php";
require_request_path(["/admin", "/admin/", "/admin/index.php"]);
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="/css/matcha.css">
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<?php $selected_page = "dashboard"; include(__DIR__ . "/nav.php"); ?>
  <button class="danger" onclick="log_out()">Logg ut</button>
  <button class="accent" onclick="export_results()">📄 Eksporter resultater (HTML)</button>
<div class='flex wrap' hx-get="/api/statistics.php" hx-trigger="every 5s" hx-status:5xx="swap:none">
<?php
include(__DIR__ . "/../api/statistics.php");
?>
</div>
<h1>Løpende resultater</h1>
<?php
$only_students = in_array($_GET["student"] ?? "0", ["1", "true", "on", "yes"], true);
$show_club = in_array($_GET["forening"] ?? "0", ["1", "true", "on", "yes"], true);
$filters = array_filter(["student" => $only_students ? "1" : null, "forening" => $show_club ? "1" : null]);
$table_query = $filters ? "&" . htmlspecialchars(http_build_query($filters)) : "";
$href = fn($changes) => "?" . htmlspecialchars(http_build_query(array_merge($filters, $changes)));
?>
<p class="smaller">Vis:
<?php if ($only_students): ?>
    <a href="<?= $href(["student" => null]) ?>">Alle løpere</a> eller <span class="muted">Kun studenter</span>
<?php else: ?>
    <span class="muted">Alle løpere</span> eller <a href="<?= $href(["student" => "1"]) ?>">Kun studenter</a>
<?php endif; ?>
· <?php if ($show_club): ?>
<a href="<?= $href(["forening" => null]) ?>">Skjul forening</a>
<?php else: ?>
<a href="<?= $href(["forening" => "1"]) ?>">Vis forening</a>
<?php endif; ?>
</p>
<h2>Kadaverløpet</h2>
<div class='flex' hx-get="/api/table.php?type=liveresultater<?= $table_query ?>" hx-trigger="load, every 5s" hx-status:5xx="swap:none"></div>
<h2>Minikadaver'n</h2>
<div class='flex' hx-get="/api/table.php?type=minikadavern<?= $table_query ?>" hx-trigger="load, every 15s" hx-status:5xx="swap:none"></div>
<script>
  function log_out(){
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/admin/login.html";
  }

  function export_results(){
    fetch("/api/export.php", { headers: { password: encodeURIComponent(localStorage.getItem("passord") ?? "") } })
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

  let xmlHttpReq = new XMLHttpRequest();
  xmlHttpReq.open("POST", "/api/is_authorized.php", false);
  xmlHttpReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8")
  xmlHttpReq.send(new URLSearchParams({
    username: localStorage.getItem("navn") ?? "",
    password: localStorage.getItem("passord") ?? "",
  }).toString());
  if (xmlHttpReq.status != 200){
      window.location.href = "/admin/login.html";
  }

</script>
<script src="/lib/htmx.min.js"></script>
</body>
</html>