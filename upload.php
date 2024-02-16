<?php
$control = $_POST['control'];
$runner_id = $_POST['id'];
$time = $_POST['time'];
$file = 'passering.csv';
$current = file_get_contents($file);
$current .= $control . "," . $runner_id . "," . $time . "\n";
file_put_contents($file, $current);