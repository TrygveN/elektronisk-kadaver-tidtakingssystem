<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="icon" type="image/png" href="/img/favicon-96x96.png" sizes="96x96" />
    <link rel="shortcut icon" href="/img/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Kadaver'n" />
    <link rel="manifest" href="/img/site.webmanifest" />
    <link rel="stylesheet" href="css/matcha.css">
    <style>
        @media only screen and (max-width: 650px) {
            body {
                padding-left: 0;
                padding-right: 0;
            }
        }
        @media (prefers-color-scheme: dark) {
            .invert {
                filter: invert(1);
            }
	}
	#tabellkonteiner {
	    min-width: 950px
	}
    </style>
</head>
<body hx-ext="morph">
<div class="flex align-center">
<figure><img src="img/kadaver.png" alt="" width="100px" class="invert"></figure>
<h1>Løpende resultater Kadaverløpet 2025</h1>
</div>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<div id='tabellkonteiner' class='flex' hx-get="api/table.php?type=liveresultater" hx-swap="morph" hx-trigger="load, every 5s"></div>
<h2>Minikadaver'n</h2>
<div class='flex' hx-get="api/table.php?type=minikadavern" hx-swap="morph" hx-trigger="load, every 15s"></div>

<footer>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
<script src="/lib/htmx.min.js"></script>
<script src="https://unpkg.com/idiomorph@0.7.4/dist/idiomorph-ext.min.js" integrity="sha384-SsScJKzATF/w6suEEdLbgYGsYFLzeKfOA6PY+/C5ZPxOSuA+ARquqtz/BZz9JWU8" crossorigin="anonymous"></script>
</body>
</html>
