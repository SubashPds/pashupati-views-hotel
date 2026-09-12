<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($path, '/build/') || $path === '/favicon.ico') return false;
if (str_starts_with($path, '/storage/')) {
    $file = realpath('/qa/uploads/'.substr($path, 9));
    if (!$file || !str_starts_with($file, '/qa/uploads/') || !is_file($file)) { http_response_code(404); return; }
    header('Content-Type: '.mime_content_type($file)); readfile($file); return;
}
$app = require '/qa/bootstrap.php';
$app->handleRequest(Illuminate\Http\Request::capture());
