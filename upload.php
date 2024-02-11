<?php
$runner_id = $_POST['id'];
print_r($_POST);
$file = 'passering.csv';
$current = file_get_contents($file);
$current .= $runner_id . "\n";
file_put_contents($file, $current);