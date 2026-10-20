<?php
parse_str($_SERVER['QUERY_STRING'] ?? "", $query);
$control = (int) ($query['matpost'] ?? 0);
$control = in_array($control, [0, 1], true) ? $control : 0;
$control_label = $control === 0 ? "1. matpost" : "2. matpost";
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>Nedtelling – EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakingssystem" />
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
        margin: 0 auto;
        max-width: var(--ct-width);
      }
      .time {
        font-size: 1.75em;
        font-weight: bold;
      }
      .flash {
        margin: 0.2em;
      }
      .countdown-card {
        display: flex;
        min-width: 13rem;
        flex-direction: column;
        align-items: center;
      }
      .countdown-name {
        font-weight: 600;
      }
      .ready-label {
        font-weight: 700;
      }
      .ready-card {
        animation: ready-pulse 1.2s ease-in-out 2;
      }
      @keyframes ready-pulse {
        50% { transform: scale(1.04); }
      }
    </style>
</head>
<body>
  <nav class="ui">
  <menu>
    <li><a href="/admin.php">📊 Dashbord</a></li>
    <li><a href="/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li><a href="/db_editor.html">👥 Endre løperbase</a></li>
    <li><a href="/config_editor.html">⚙️ Konfigurasjon</a></li>
    <li<?= $control === 0 ? ' class="selected"' : '' ?>><a href="/matpost_nedtelling.php?matpost=0">⏳ Nedtelling 1. mat</a></li>
    <li<?= $control === 1 ? ' class="selected"' : '' ?>><a href="/matpost_nedtelling.php?matpost=1">⏳ Nedtelling 2. mat</a></li>
  </menu>
</nav>
<div class="ui">
  <div class="flex wrap align-center">
    <h1>Nedtelling – <?= htmlspecialchars($control_label, ENT_QUOTES, "UTF-8") ?></h1>
    <button id="sound-toggle" class="accent" aria-pressed="false">🔔 Slå på lydvarsel</button>
  </div>
  <div hx-get="/api/countdown.php?matpost=<?= $control ?>" hx-trigger="load, every 1s" hx-swap="innerMorph"></div>
</div>
<script src="/lib/htmx.min.js"></script>
<script>
let audioContext = null;
let soundEnabled = false;
let knownReady = new Set();
let firstSwap = true;

function updateSoundButton() {
    const button = document.getElementById("sound-toggle");
    button.textContent = soundEnabled ? "🔕 Slå av lydvarsel" : "🔔 Slå på lydvarsel";
    button.setAttribute("aria-pressed", soundEnabled ? "true" : "false");
}

function beep() {
    if (!soundEnabled || !audioContext) {
        return;
    }
    const now = audioContext.currentTime;
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = "sine";
    oscillator.frequency.value = 880;
    gain.gain.setValueAtTime(0.001, now);
    gain.gain.exponentialRampToValueAtTime(0.25, now + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.7);
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start(now);
    oscillator.stop(now + 0.75);
}

document.getElementById("sound-toggle").addEventListener("click", async () => {
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (!soundEnabled) {
        await audioContext.resume();
        soundEnabled = true;
        beep();
    } else {
        soundEnabled = false;
    }
    updateSoundButton();
});

function update_ready_state() {
    const ready = new Set(
        Array.from(document.querySelectorAll("[data-ready-id]")).map((el) => el.dataset.readyId)
    );
    if (!firstSwap) {
        for (const id of ready) {
            if (!knownReady.has(id)) {
                beep();
                break;
            }
        }
    }
    knownReady = ready;
    firstSwap = false;
    document.title = ready.size > 0
        ? `(${ready.size}) Klar – EKT`
        : "Nedtelling – EKT";
}

// htmx 4 bruker namespace-aktige hendingsnamn.
document.body.addEventListener("htmx:after:swap", update_ready_state);
updateSoundButton();
</script>
</body>
</html>
