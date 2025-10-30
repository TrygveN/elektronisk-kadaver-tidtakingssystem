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
      .ui {
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
        .time {
            font-size: 1.5em;
            font-weight: bold;
        }
        .flash {
            margin: 0.2em;
        }
    </style>
</head>
<body hx-ext="morph">
  <nav class="ui">
  <menu>
    <li class="disabled"><a href="/admin.php">📊 Dashbord</a></li>
    <li class="disabled"><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li class="disabled"><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li class="disabled"><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
    <li class="selected">⏳️ Nedtelling matpost
        <menu>
          <li><a href="http://localhost:8000/matpost_nedtelling.php?matpost=0">1. mat</a></li>
          <li><a href="http://localhost:8000/matpost_nedtelling.php?matpost=1">2. mat</a></li>
        </menu>
    </li>
  </menu>
</nav>
<?php
parse_str($_SERVER['QUERY_STRING'], $query);
$control = $query['matpost'];
echo("<div hx-get='/api/countdown.php?matpost=$control' hx-trigger='load, every 1s' hx-swap='morph:innerHTML'></div>");
?>
<script src="/lib/htmx.min.js"></script>
<script src="https://unpkg.com/idiomorph@0.7.4/dist/idiomorph-ext.min.js" integrity="sha384-SsScJKzATF/w6suEEdLbgYGsYFLzeKfOA6PY+/C5ZPxOSuA+ARquqtz/BZz9JWU8" crossorigin="anonymous"></script>
</body>
</html>