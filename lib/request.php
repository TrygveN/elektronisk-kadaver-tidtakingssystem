<?php
// Webserverar som FrankenPHP/Caddy og PHP sin innebygde server kan sende
// ukjende stiar til næraste index.php. Utan denne kontrollen ville t.d.
// /admin/api/passing.php fått 200 sjølv om fila ikkje finst.
// Tillat berre stiane som faktisk skal gje denne sida.
function require_request_path(array $allowed_paths): void
{
    $path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
    if (!is_string($path) || $path === "") {
        $path = "/";
    }
    if (!in_array($path, $allowed_paths, true)) {
        http_response_code(404);
        header("Content-Type: text/plain; charset=UTF-8");
        echo "404 Not Found\n";
        exit;
    }
}
