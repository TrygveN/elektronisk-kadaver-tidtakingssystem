<?php
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
class Runner
{
    public int $id;
    public string $name;
    public string $club;
    public string $course;
    public string $email;
    public string $phone;
    public string $is_student;
    public int $line_in_csv;
    public array $splits;

    function __construct($id, $name, $club, $course, $email, $phone, $is_student, $line_in_csv)
    {
        if ($id == null) {
            $id = 0;
            $name = "";
        }
        $this->id = $id;
        $this->name = $name;
        $this->club = $club;
        $this->course = $course;
        $this->email = $email;
        $this->phone = $phone;
        $this->is_student = $is_student;
        $this->line_in_csv = $line_in_csv;
        $this->splits = [false, false, false];
    }

    function set_split($control, $timestamp)
    {
        $this->splits[$control] = $timestamp;
    }

    function get_control(){
        if ($this->splits[2] != false) {
            return 2;
        } elseif ($this->splits[1] != false) {
            return 1;
        }
        elseif ($this->splits[0] != false) {
            return 0;
        }
        else {
            return -1;
        }
    }
}

// Escape verdier før dei blir skrivne ut som HTML.
function esc($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Formaterer eit felt trygt for den ;-delte db.csv-fila.
function csv_field($value)
{
    $value = str_replace(["\r", "\n"], " ", (string) $value);
    if (strpbrk($value, ";\"") !== false) {
        return '"' . str_replace('"', '""', $value) . '"';
    }
    return $value;
}

// Legg ei linje trygt til ei fil, også når fila manglar avsluttande newline.
function append_line($path, $line)
{
    $handle = fopen($path, "c+");
    if ($handle === false) {
        return false;
    }
    flock($handle, LOCK_EX);
    fseek($handle, 0, SEEK_END);
    $needs_newline = false;
    if (ftell($handle) > 0) {
        fseek($handle, -1, SEEK_END);
        $needs_newline = fread($handle, 1) !== "\n";
    }
    fseek($handle, 0, SEEK_END);
    $result = fwrite($handle, ($needs_newline ? "\n" : "") . $line) !== false;
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
    return $result;
}

// Byggjer ei manuelt registrert løparline i same kolonneformat som Nettskjema.
function csv_runner_line($id, $name, $email, $phone, $club, $course, $student)
{
    return csv_field($id) . ";;" . csv_field($name) . ";" . csv_field($email) . ";"
        . csv_field($phone) . ";" . csv_field($club) . ";" . csv_field($course) . ";"
        . csv_field($student) . ";\n";
}

//find runner by id in list of Runner objects
function get_runner($runnner_list, int $id)
{
    for ($i = 0; $i < count($runnner_list); $i++) {
        if ($runnner_list[$i]->id == $id) {
            return $runnner_list[$i];
        }
    }
    return false;
}

function search_for_runner($runner_list, $search_term) {
    $search_term = strtolower((string) $search_term);
    if ($search_term === "") {
        return [];
    }
    $filtered_runners = [];
    for ($i = 0; $i < count($runner_list); $i++) {
        $runner = $runner_list[$i];
        if (str_contains(strtolower($runner->name), $search_term)){
            array_push($filtered_runners, $runner);
        }
        elseif (str_contains((string) $runner->id, $search_term)){
            array_push($filtered_runners, $runner);
        }
        elseif (str_contains(strtolower($runner->club), $search_term)){
            array_push($filtered_runners, $runner);
        }
        
    }
    return $filtered_runners;
}


function cmp(Runner $a, Runner $b) {
    $a_control = $a->get_control();
    $b_control = $b->get_control();
    if ($a_control != $b_control) {
        // Høgaste kontroll (kome lengst) først.
        return $b_control <=> $a_control;
    }
    if ($a_control == -1) {
        // Ingen passeringar på nokon av dei, likt.
        return 0;
    }
    return $a->splits[$a_control]->getTimestamp() <=> $b->splits[$b_control]->getTimestamp();
}
function cmp_course(Runner $a, Runner $b) {
    return strcmp($a->course, $b->course);
}

function filter_runners(Runner $runner, $id) {
    if ($runner->id == $id) {
        return True;
    }
    else {
        return False;
    }
}

function time_diff(DateTime $date_1, DateTime $date_2) {
    return $date_2->getTimestamp() - $date_1->getTimestamp();
}

function read_runners_from_csv(){
    $documentRoot = $_SERVER['DOCUMENT_ROOT'];
    $runners = [];
    $db_path = "$documentRoot/data/db.csv";
    $csv_runners = is_readable($db_path) ? str_getcsv(file_get_contents($db_path), "\n") : [];
    for ($i = 1; $i < count($csv_runners); $i++) {
        if (trim($csv_runners[$i]) === "") {
            continue;
        }
        // Nettskjema-API-et HTML-enkoder tekstfelta (t.d. Minikadaver&#39;n),
        // så vi dekoder før verdiane blir brukt.
        $line = array_map(
            fn($value) => html_entity_decode($value, ENT_QUOTES | ENT_HTML5, "UTF-8"),
            str_getcsv($csv_runners[$i], ";")
        );
        if (count($line) < 8) {
            continue;
        }
        array_push($runners, new Runner($line[0], $line[2], $line[5], $line[6], $line[3], $line[4], $line[7], $i));
    }


    $timings_path = "$documentRoot/data/passering.csv";
    $timings = is_readable($timings_path) ? str_getcsv(file_get_contents($timings_path), "\n") : [];
    for ($i = 0; $i < count($timings); $i++) {
        if (trim($timings[$i]) === "") {
            continue;
        }
        $line = str_getcsv($timings[$i]);
        if (count($line) < 3) {
            continue;
        }

        $time = DateTime::createFromFormat("Y-m-d\TH:i:sp", $line[2]);
        if (!$time) {
            //error
            continue;
        }

        $runner = get_runner($runners, (int) $line[1]);
        if (!$runner) {
            //error
            continue;
        }

        $control = (int) $line[0];
        if ($control < 1 || $control > count($runner->splits)) {
            // Ukjent post, hopp over.
            continue;
        }
        $runner->set_split($control - 1, $time);
    }
    return $runners;
}