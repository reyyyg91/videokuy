<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Railway MySQL plugin: MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
// Alternatif manual di Railway: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT
$host = getenv("MYSQLHOST") ?: getenv("DB_HOST") ?: "localhost";
$user = getenv("MYSQLUSER") ?: getenv("DB_USER") ?: "root";
$pass = getenv("MYSQLPASSWORD") ?: getenv("DB_PASS") ?: "";
$db   = getenv("MYSQLDATABASE") ?: getenv("DB_NAME") ?: "raihan232175";
$port = (int) (getenv("MYSQLPORT") ?: getenv("DB_PORT") ?: 3306);
if ($port <= 0) {
    $port = 3306;
}

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($koneksi, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal",
    ]);
    exit;
}
