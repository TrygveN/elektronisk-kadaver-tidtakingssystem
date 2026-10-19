<?php
// Eksporterer ei ferdig resultatside som HTML-fil (lastast ned).
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");
include("$documentRoot/data/hash.php");

$password = getallheaders()['Password'] ?? ($_GET['password'] ?? "");
if (!password_verify($password, $hash)) {
    http_response_code(401);
    exit("Feil passord.");
}

$config = parse_ini_file("$documentRoot/data/config.ini") ?: [];
date_default_timezone_set('UTC');
$start = DateTime::createFromFormat(DateTime::ISO8601, $config['start_date'] ?? "");
$start_mini = DateTime::createFromFormat(DateTime::ISO8601, $config['start_date_mini'] ?? "");
$year = $start ? $start->format('Y') : date('Y');

$runners = read_runners_from_csv();

// Tid frå start til ei passing, t.d. 01:46:45.
function elapsed(DateTime $start, $split)
{
    if (!$split instanceof DateTime) {
        return "";
    }
    return $start->diff($split)->format('%H:%I:%S');
}

// Klokkeslett for passing i tidssona til starttida.
function clock_time(DateTime $split, DateTimeZone $timezone)
{
    return (clone $split)->setTimezone($timezone)->format('H:i:s');
}

// Sprekkindeks: kor stor del av totaltida som gjekk mellom 2. mat og mål.
function sprekkindeks(DateTime $start, Runner $runner)
{
    if ($runner->splits[1] == false || $runner->splits[2] == false) {
        return "";
    }
    $total = time_diff($start, $runner->splits[2]);
    if ($total <= 0) {
        return "";
    }
    $to_mat2 = time_diff($start, $runner->splits[1]);
    return number_format(100 * ($total - $to_mat2) / $total, 0) . " %";
}

// Sorterer etter måltid og deler ut plassar (delte plassar ved lik tid).
function rank_finishers(array $runners, string $course)
{
    $finishers = [];
    foreach ($runners as $runner) {
        if ($runner->course === $course && $runner->splits[2] != false) {
            $finishers[] = $runner;
        }
    }
    usort($finishers, fn($a, $b) => $a->splits[2] <=> $b->splits[2]);

    $rows = [];
    $place = 0;
    $previous = null;
    foreach ($finishers as $index => $runner) {
        $timestamp = $runner->splits[2]->getTimestamp();
        if ($previous === null || $timestamp !== $previous) {
            $place = $index + 1;
        }
        $rows[] = [$place, $runner];
        $previous = $timestamp;
    }
    return $rows;
}

$kadaver_start = $start ?: new DateTime();
$mini_start = $start_mini ?: new DateTime();

$kadaver_rows = rank_finishers($runners, "Kadaverløpet");
$mini_rows = rank_finishers($runners, "Minikadaver'n");

$startende = 0;
foreach ($runners as $runner) {
    if ($runner->course === "Kadaverløpet") {
        $startende++;
    }
}
$fullforte = count($kadaver_rows);

// Løparar som starta men ikkje fullførte (vist heilt til slutt).
$brutt = [];
foreach ($runners as $runner) {
    if ($runner->course !== "Kadaverløpet" || $runner->splits[2] != false) {
        continue;
    }
    if ($runner->splits[0] != false || $runner->splits[1] != false) {
        $brutt[] = $runner;
    }
}
usort($brutt, fn($a, $b) => strcmp($a->name, $b->name));

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="resultater-kadaver.html"');
?>
<!DOCTYPE html>
<html lang="no">
<head>
<meta charset="UTF-8">
<title>Resultater Kadaver'n <?= esc($year) ?></title>
<style>
    body { max-width: 60rem; margin: 2rem auto; padding: 0 1rem; font-family: system-ui, sans-serif; color: #1f2328; }
    h1 { border-bottom: 2px solid #1f2328; padding-bottom: .25rem; }
    h2 { margin-top: 2rem; }
    .heder p { margin: .15rem 0; }
    table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    caption { text-align: left; font-weight: 600; margin-bottom: .5rem; }
    th, td { border: 1px solid #d0d7de; padding: .35rem .6rem; }
    th { background: #f6f8fa; text-align: left; }
    tbody tr:nth-child(even) { background: #f6f8fa; }
    td.num, th.num { text-align: right; white-space: nowrap; }
    td.pl, th.pl { text-align: right; width: 4rem; }
    td.name { white-space: nowrap; }
    @media print { body { margin: 0; } tbody tr:nth-child(even) { background: transparent; } }
</style>
</head>
<body>
<h1>Resultater Kadaver'n <?= esc($year) ?></h1>
<div class="heder">
    <p>Vinner: </p>
    <p>Beste kvinne: </p>
    <p>Kadaverløpets hederspris: </p>
    <p>Slips/skaut: </p>
    <p>Beste student/ beste kvinnelige student: </p>
    <p>Beste kollektiv/forening: </p>
    <p>Løpsleder: </p>
    <p>Løypelegger: </p>
    <p>Løypelengde i luftlinje:  km, Startende: <?= (int) $startende ?>, Fullførte: <?= (int) $fullforte ?></p>
</div>

<p>Resultater Minikadaver'n finner du lenger nede, under Kadaver-resultatene.</p>

<h2>Kadaverløpet</h2>
<table>
    <thead>
        <tr>
            <th class="pl">Pl.</th>
            <th>Navn</th>
            <th class="num">Tid &gt; 1. mat</th>
            <th class="num">Tid &gt; 2. mat</th>
            <th class="num">Tid &gt; mål</th>
            <th class="num">Sprekkindeks</th>
            <th class="num">Poeng</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($kadaver_rows as [$place, $runner]): ?>
        <tr>
            <td class="pl"><?= (int) $place ?></td>
            <td class="name"><?= esc($runner->name) ?></td>
            <td class="num"><?= esc(elapsed($kadaver_start, $runner->splits[0])) ?></td>
            <td class="num"><?= esc(elapsed($kadaver_start, $runner->splits[1])) ?></td>
            <td class="num"><?= esc(elapsed($kadaver_start, $runner->splits[2])) ?></td>
            <td class="num"><?= esc(sprekkindeks($kadaver_start, $runner)) ?></td>
            <td class="num"><?= $place <= 10 ? (int) (11 - $place) : "" ?></td>
        </tr>
<?php endforeach; ?>
<?php foreach ($brutt as $runner): ?>
        <tr>
            <td class="pl">Brutt</td>
            <td class="name"><?= esc($runner->name) ?></td>
            <td class="num"><?= esc(elapsed($kadaver_start, $runner->splits[0])) ?></td>
            <td class="num"><?= esc(elapsed($kadaver_start, $runner->splits[1])) ?></td>
            <td class="num"></td>
            <td class="num"><?= esc(sprekkindeks($kadaver_start, $runner)) ?></td>
            <td class="num"></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>

<h2>Minikadaver'n</h2>
<table>
    <thead>
        <tr>
            <th class="pl">Pl.</th>
            <th>Navn</th>
            <th class="num">Kl mål</th>
            <th class="num">Tid &gt; mål</th>
        </tr>
    </thead>
    <tbody>
<?php foreach ($mini_rows as [$place, $runner]): ?>
        <tr>
            <td class="pl"><?= (int) $place ?></td>
            <td class="name"><?= esc($runner->name) ?></td>
            <td class="num"><?= esc(clock_time($runner->splits[2], $mini_start->getTimezone())) ?></td>
            <td class="num"><?= esc(elapsed($mini_start, $runner->splits[2])) ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
