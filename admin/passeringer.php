<?php
require_once(__DIR__ . "/../import_runners.php");

$documentRoot = $_SERVER['DOCUMENT_ROOT'];
$passering_file = "$documentRoot/data/passering.csv";

function passering_esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function passering_post_label($control)
{
    $labels = [1 => "1. mat", 2 => "2. mat", 3 => "Mål"];
    return $labels[(int) $control] ?? (string) $control;
}

function passering_time_label($timestamp)
{
    $time = DateTime::createFromFormat("Y-m-d\TH:i:sp", (string) $timestamp);
    if (!$time) {
        return (string) $timestamp;
    }
    return $time->setTimezone(new DateTimeZone("UTC"))->format("Y-m-d H:i:s");
}

$passeringer = [];
if (is_readable($passering_file)) {
    $handle = fopen($passering_file, "r");
    if ($handle !== false) {
        while (($fields = fgetcsv($handle, null, ",", '"', "")) !== false) {
            if ($fields === [null] || (count($fields) === 1 && trim((string) $fields[0]) === "")) {
                continue;
            }
            $passeringer[] = $fields;
        }
        fclose($handle);
    }
}

$runners = read_runners_from_csv();
$runner_names = [];
foreach ($runners as $runner) {
    $runner_names[(int) $runner->id] = $runner->name;
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Passeringer – EKT admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakingssystem" />
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
      .table-wrap {
        overflow-x: auto;
      }
      .table-search {
        max-width: 32rem;
      }
      .passering-table {
        width: 100%;
      }
      .passering-table .time-cell {
        white-space: nowrap;
      }
      .passering-table .number-cell {
        text-align: right;
        white-space: nowrap;
      }
      .passering-table .action-cell {
        width: 1%;
        white-space: nowrap;
        text-align: right;
      }
      .passering-table .action-cell button {
        margin: 0;
        padding: .25rem .75rem;
      }
    </style>
    <script data-goatcounter="https://teller.ekt.trygve.net/count"
        async src="//teller.ekt.trygve.net/count.js"></script>
</head>
<body>
<?php $selected_page = "passeringer"; include(__DIR__ . "/nav.php"); ?>

  <header class="page-header">
    <h1>Passeringer</h1>
    <div class="flex wrap">
      <button class="default" onclick="location.reload()">Oppdater</button>
      <button class="default" onclick="log_out()">Logg ut</button>
    </div>
  </header>

  <p class="muted">
    <?= count($passeringer) ?> linjer frå <code>data/passering.csv</code> i filrekkefølge.
    Nyaste passering ligg nederst. Løpernavn er henta frå <code>data/db.csv</code>.
  </p>

  <label class="table-search">
    Filtrer passeringer
    <input type="search" id="passering-filter" placeholder="Navn, startnummer eller post" autocomplete="off" autofocus>
  </label>

  <div class="table-wrap">
    <table class="passering-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Post</th>
          <th>Startnummer</th>
          <th>Navn</th>
          <th>Tid (UTC)</th>
          <th>Registrert av</th>
          <th class="action-cell"></th>
        </tr>
      </thead>
      <tbody>
      <?php if (count($passeringer) === 0): ?>
        <tr><td colspan="7" class="muted">Ingen passeringer registrert.</td></tr>
      <?php else: ?>
        <?php foreach ($passeringer as $index => $fields): ?>
          <?php
            $control = $fields[0] ?? "";
            $runner_id = $fields[1] ?? "";
            $timestamp = $fields[2] ?? "";
            $navn = trim((string) ($fields[3] ?? ""));
            $runner_name = $runner_names[(int) $runner_id] ?? "";
          ?>
          <tr data-passering-row>
            <td class="number-cell muted"><?= $index + 1 ?></td>
            <td><?= passering_esc(passering_post_label($control)) ?></td>
            <td class="number-cell"><?= passering_esc($runner_id) ?></td>
            <td><?= $runner_name !== "" ? passering_esc($runner_name) : '<span class="muted">–</span>' ?></td>
            <td class="time-cell"><time datetime="<?= passering_esc($timestamp) ?>"><?= passering_esc(passering_time_label($timestamp)) ?></time></td>
            <td><?= $navn !== "" ? passering_esc($navn) : '<span class="muted">–</span>' ?></td>
            <td class="action-cell"><button type="button" class="default" data-delete-passering="<?= $index ?>">Slett</button></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
  <p id="no-results" class="muted" hidden>Ingen passeringer passer til søket.</p>

<script>
  function log_out() {
    localStorage.removeItem("navn");
    localStorage.removeItem("passord");
    window.location.href = "/admin/login.html";
  }

  document.querySelectorAll("[data-delete-passering]").forEach((button) => {
    button.addEventListener("click", async () => {
      if (!confirm("Slette denne passeringa?")) return;
      button.disabled = true;
      const response = await fetch("/api/delete_passing.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
        body: new URLSearchParams({
          index: button.dataset.deletePassering,
          password: localStorage.getItem("passord") ?? "",
        }),
      });
      if (response.ok) {
        location.reload();
      } else {
        button.disabled = false;
        alert(await response.text() || "Kunne ikke slette passeringa.");
      }
    });
  });

  document.getElementById("passering-filter").addEventListener("input", (event) => {
    const search = event.target.value.trim().toLocaleLowerCase("no-NO");
    let visible = 0;
    document.querySelectorAll("[data-passering-row]").forEach((row) => {
      const matches = row.textContent.toLocaleLowerCase("no-NO").includes(search);
      row.hidden = !matches;
      if (matches) visible++;
    });
    document.getElementById("no-results").hidden = visible !== 0;
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
</script>
</body>
</html>
