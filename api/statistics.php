<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$documentRoot/import_runners.php");
$method = $_SERVER['REQUEST_METHOD'];
if ($method == "GET") {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $runners = read_runners_from_csv();
    $minikadavern = 0;
    $minikadavern_total = 0;
    $mat1 = 0;
    $mat2 = 0;
    $maal = 0;
    $total = 0;
    for ($i = 0; $i < count($runners); $i++) {
            $runner = $runners[$i]; 
            if ($runner->course == "Minikadaver'n") {
                $minikadavern_total++;
                if ($runner->get_control() == 2) {
                    $minikadavern++;
                }
            }
            else {
                $total++;
                if ($runner->get_control() >= 0) {
                    $mat1++;
                }
                if ($runner->get_control() >= 1) {
                    $mat2++;
                }
                if ($runner->get_control() == 2) {
                    $maal++;
                }
            }

        }
    echo("
    <div class='flash accent m-1'><b>$mat1 av $total</b> har passert 1. matpost</div>
    <div class='flash accent m-1'><b>$mat2 av $total</b> har passert 2. matpost</div>
    <div class='flash accent m-1'><b>$maal av $total</b> har fullført Kadaver'n</div>
    <div class='flash accent m-1'><b>$minikadavern av $minikadavern_total</b> har fullført Minikadaver'n</div>");
}