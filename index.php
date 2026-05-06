<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$requestUri = parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH);
$path = trim((string) $requestUri, "/");
$scriptName = trim((string) ($_SERVER["SCRIPT_NAME"] ?? ""), "/");
$scriptDir = trim((string) dirname($scriptName), "/");

// Normalisasi path agar tetap bekerja di root, subfolder, dan format /index.php/endpoint.
if ($scriptDir !== "" && $path !== "" && strpos($path, $scriptDir . "/") === 0) {
    $path = substr($path, strlen($scriptDir) + 1);
}

if ($path === trim($scriptName, "/")) {
    $path = "";
} elseif ($scriptName !== "" && strpos($path, trim($scriptName, "/") . "/") === 0) {
    $path = substr($path, strlen(trim($scriptName, "/")) + 1);
}

$segments = $path === "" ? [] : explode("/", $path);
$endpoint = count($segments) > 0 ? $segments[count($segments) - 1] : "";
$endpoint = preg_replace("/\\.php$/", "", (string) $endpoint);

if ($endpoint === "index") {
    $endpoint = "";
}

$routes = [
    "" => null,
    "get_data" => __DIR__ . "/api/get_data.php",
    "upload_data" => __DIR__ . "/api/upload_data.php",
    "update_data" => __DIR__ . "/api/update_data.php",
    "delete_data" => __DIR__ . "/api/delete_data.php",
];

if (!array_key_exists($endpoint, $routes)) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Endpoint tidak ditemukan"
    ]);
    exit;
}

if ($endpoint === "") {
    echo json_encode([
        "success" => true,
        "message" => "API aktif",
        "endpoints" => [
            "/get_data",
            "/get_data.php",
            "/upload_data",
            "/upload_data.php",
            "/update_data",
            "/update_data.php",
            "/delete_data",
            "/delete_data.php"
        ]
    ]);
    exit;
}

require_once $routes[$endpoint];
