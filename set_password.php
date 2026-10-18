<?php
// Setter nytt admin-passord. Kjøres fra kommandolinjen:
//   php set_password.php [nytt-passord]
// Uten argument blir du spurt om passordet (skjult inntasting).
// Passordet kan også gis via miljøvariabelen EKT_PASSWORD.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Kun tilgjengelig fra kommandolinjen.\n");
}

function read_password($prompt)
{
    if (function_exists('readline')) {
        $stty = shell_exec('stty -g 2>/dev/null');
        if ($stty !== null) {
            shell_exec('stty -echo');
        }
        $value = readline($prompt);
        if ($stty !== null) {
            shell_exec("stty " . escapeshellarg(trim($stty)));
        }
        fwrite(STDOUT, "\n");
        return $value;
    }

    fwrite(STDOUT, $prompt);
    $value = rtrim(fgets(STDIN), "\r\n");
    fwrite(STDOUT, "\n");
    return $value;
}

$root = __DIR__;
$hash_file = "$root/data/hash.php";

$password = $argv[1] ?? getenv('EKT_PASSWORD') ?: null;

if ($password === null || $password === '') {
    $password = read_password("Nytt passord: ");
    $confirm = read_password("Bekreft passord: ");

    if ($password !== $confirm) {
        fwrite(STDERR, "Passordene er ikke like.\n");
        exit(1);
    }
}

if (strlen($password) < 4) {
    fwrite(STDERR, "Passordet er for kort (minst 4 tegn).\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_ARGON2ID, [
    'memory_cost' => 4096,
    'time_cost' => 3,
    'threads' => 1,
]);

if ($hash === false || !password_verify($password, $hash)) {
    fwrite(STDERR, "Kunne ikke generere passordhash.\n");
    exit(1);
}

if (@file_put_contents($hash_file, "<?php\n\$hash = '" . $hash . "';\n") === false) {
    fwrite(STDERR, "Kunne ikke skrive til $hash_file\n");
    exit(1);
}

fwrite(STDOUT, "Nytt passord lagret i data/hash.php\n");
