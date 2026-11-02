<?php
$selected_page = $selected_page ?? "";
$nav_class = $nav_class ?? "";
?>
<nav<?= $nav_class !== "" ? ' class="' . htmlspecialchars($nav_class, ENT_QUOTES, "UTF-8") . '"' : "" ?>>
  <menu>
    <li<?= $selected_page === "dashboard" ? ' class="selected"' : "" ?>><a href="/admin/">📊 Dashbord</a></li>
    <li<?= $selected_page === "registration" ? ' class="selected"' : "" ?>><a href="/admin/registrering.php">⏱️ Registrer passering på matpost/mål</a></li>
    <li<?= $selected_page === "runners" ? ' class="selected"' : "" ?>><a href="/admin/db_editor.php">👥 Løperbase</a></li>
    <li<?= $selected_page === "status" ? ' class="selected"' : "" ?>><a href="/admin/start_og_betaling.php">💰📋 Start og betaling</a></li>
    <li<?= $selected_page === "config" ? ' class="selected"' : "" ?>><a href="/admin/config_editor.php">⚙️ Konfigurasjon</a></li>
    <li<?= $selected_page === "countdown-0" ? ' class="selected"' : "" ?>><a href="/admin/matpost_nedtelling.php?matpost=0">⏳️ Nedtelling 1. mat</a></li>
    <li<?= $selected_page === "countdown-1" ? ' class="selected"' : "" ?>><a href="/admin/matpost_nedtelling.php?matpost=1">⏳️ Nedtelling 2. mat</a></li>
  </menu>
</nav>
