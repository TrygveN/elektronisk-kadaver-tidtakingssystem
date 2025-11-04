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
<body>
<div class="flex align-center">
<figure><img src="img/kadaver.png" alt="" width="100px" class="invert"></figure>
<h1>Løpende resultater Kadaverløpet 2025</h1>
</div>
<h2>Vi tar forbehold om feil. Dette er ikke offisielle resultater</h2>
<div id='tabellkonteiner' class='flex' hx-get="api/table.php?type=liveresultater" hx-swap="morph" hx-trigger="load, every 5s"></div>
<h2>Minikadaver'n</h2>
<!--
<div class='flex' hx-get="api/table.php?type=minikadavern" hx-swap="morph:innerHTML" hx-trigger="load, every 15s"></div>
-->
<table><thead>
    <tr>
    <th>#</th>
    <th>Navn</th>
    <th>Mål</th>
    </tr></thead>
    <tbody><tr><td>1.</td><td>Mina Sofie Lilleheier</td><td>01:16:27</td></tr>
<tr><td>1.</td><td>Johanne Yri Stokke</td><td>01:16:27</td></tr>
<tr><td>3.</td><td>Magne Nyberg</td><td>01:23:12</td></tr>
<tr><td>4.</td><td>Christian Smed Mikkelsen</td><td>01:23:15</td></tr>
<tr><td>5.</td><td>Thorvald Børresen</td><td>01:23:21</td></tr>
<tr><td>6.</td><td>Ingrid Vollset</td><td>01:26:29</td></tr>
<tr><td>7.</td><td>Ellen-Christine Koren</td><td>01:26:38</td></tr>
<tr><td>8.</td><td>Morten Vollset</td><td>01:26:42</td></tr>
<tr><td>9.</td><td>Martin Gudahl Tufte</td><td>01:44:48</td></tr>
<tr><td>10.</td><td>Håvard Olaisen Hagen</td><td>01:50:12</td></tr>
<tr><td>11.</td><td>Kinga Banfi</td><td>01:52:19</td></tr>
<tr><td>12.</td><td>Lina Pettersen</td><td>01:57:25</td></tr>
<tr><td>13.</td><td>Sigrid Andrine Garvik</td><td>01:57:30</td></tr>
<tr><td>14.</td><td>Hans Petter Stub</td><td>02:06:24</td></tr>
<tr><td>15.</td><td>Pål Are Hagen</td><td>02:06:28</td></tr>
<tr><td>16.</td><td>Bjørnar Bjønness Waage</td><td>02:12:11</td></tr>
<tr><td>17.</td><td>Per-Arne Sæther</td><td>02:13:09</td></tr>
<tr><td>18.</td><td>Oscar Bjønness Waage</td><td>02:19:35</td></tr>
<tr><td>19.</td><td>Oluf Aalde</td><td>02:33:27</td></tr>
<tr><td>20.</td><td>Carina Schytz</td><td>02:33:30</td></tr>
<tr><td>21.</td><td>Helene Røed Eikeland</td><td>02:46:49</td></tr>
<tr><td>22.</td><td>Ruth Malin Dal</td><td>02:46:54</td></tr>
<tr><td>23.</td><td>Kimmy Nguyen</td><td>03:28:34</td></tr>
<tr><td>24.</td><td>Hanna Lye Moum</td><td>03:28:42</td></tr>
<tr><td></td><td>Dalma Banfi</td><td></td></tr>
<tr><td></td><td>Magnus Vollset</td><td></td></tr>
<tr><td></td><td>Lene Marie Wilborn</td><td></td></tr>
<tr><td></td><td>Edvin Søvik</td><td></td></tr>
</tbody></table>

<footer>
<h3>Laget av Trygve. <a href="https://git.willy.club/Trygve/elektronisk-kadaver-tidtakingssystem">Kildekode</a></h3>
<figure><img src="img/NMBUI.webp" alt="" width="200px"></figure>
</footer>
<script src="/lib/htmx.min.js"></script>
<script src="https://unpkg.com/idiomorph@0.7.4/dist/idiomorph-ext.min.js" integrity="sha384-SsScJKzATF/w6suEEdLbgYGsYFLzeKfOA6PY+/C5ZPxOSuA+ARquqtz/BZz9JWU8" crossorigin="anonymous"></script>
</body>
</html>
