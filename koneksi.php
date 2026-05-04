<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = $_ENV['MYSQLHOST'] ?? "localhost";
$user = $_ENV['MYSQLUSER'] ?? "root";
$pass = $_ENV['MYSQLPASSWORD'] ?? "";
$db   = $_ENV['MYSQLDATABASE'] ?? "raihan232175";

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    mysqli_set_charset($koneksi, "utf8mb4");

} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal",
        "error" => $e->getMessage() // debug
    ]);
    exit;
}