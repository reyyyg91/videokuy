<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = mysqli_connect("localhost", "root", "", "raihan232175");
    mysqli_set_charset($koneksi, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal"
    ]);
    exit;
}