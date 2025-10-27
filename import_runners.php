<?php
class Runner
{
    public int $id;
    public string $name;
    public string $club;
    public string $course;
    public string $email;
    public string $phone;
    public string $is_student;
    public array $splits;

    function __construct($id, $name, $club, $course, $email, $phone, $is_student)
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
        for ($i = 0; $i < $GLOBALS['number_of_controls']; $i++) {
            $this->splits[$i] = false;
        }
    }

    function set_split($control, $timestamp)
    {
        $this->splits[$control] = $timestamp;
    }

    function get_control()
    {        for ($i = 0; $i < count($this->splits); $i++) {

            if (!is_object($this->splits[$i])) {
                return $i-1;
            }
    }
	return count($this->splits)-1;
    }
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
    $filtered_runners = [];
    for ($i = 0; $i < count($runner_list); $i++) {
        $runner = $runner_list[$i];
        if (str_contains(strtolower($runner->name), strtolower($search_term))){
            array_push($filtered_runners, $runner);
        }
        elseif (str_contains($runner->id, $search_term)){
            array_push($filtered_runners, $runner);
        }
        
    }
    return $filtered_runners;
}


function cmp(Runner $a, Runner $b) {
    $a_control = $a->get_control();
    $b_control = $b->get_control();
    if ($a_control > $b_control){
        return -1;
    }
    if ($a_control < $b_control){
        return 1;
    }
    if ($a_control == -1) {
        return 1;
    }
    if ($b_control == -1) {
        return -1;
    }
    if ($a->splits[$a_control]->getTimestamp() < $b->splits[$b_control]->getTimestamp()){
        return -1;
    }
    if ($a->splits[$a_control]->getTimestamp() > $b->splits[$b_control]->getTimestamp()){
        return 1;
    }
    return 0;
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
    $runners = [];
    $csv_runners = file_get_contents("data/db.csv");
    $csv_runners = str_getcsv($csv_runners, "\n");
    for ($i = 1; $i < count($csv_runners); $i++) {
        $line = str_getcsv($csv_runners[$i], ";");
        array_push($runners, new Runner($line[0], $line[2], $line[5], $line[6], $line[3], $line[4], $line[7]));
    }


    $timings = file_get_contents("data/passering.csv");
    $timings = str_getcsv($timings, "\n");
    for ($i = 0; $i < count($timings); $i++) {
        $line = str_getcsv($timings[$i]);

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

        $runner->set_split($line[0]-1, $time);
    }
    return $runners;
}