<?php
require __DIR__ . "/../lib/request.php";
require_request_path(["/instruks", "/instruks/", "/instruks/index.php"]);

$path = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);

if ($path === "/instruks") {
    header("Location: /instruks/", true, 301);
    exit;
}

header("Content-Type: text/html; charset=UTF-8");
readfile(__DIR__ . "/index.html");
