<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan"
    ]);
    exit;
}

require_once __DIR__ . "/koneksi.php";
/** @var mysqli $koneksi */

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "ID wajib diisi"
    ]);
    exit;
}

try {
    $stmt = mysqli_prepare($koneksi, "SELECT thumbnail, video FROM mhs_232175 WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Data tidak ditemukan"
        ]);
        exit;
    }

    $deleteStmt = mysqli_prepare($koneksi, "DELETE FROM mhs_232175 WHERE id = ?");
    mysqli_stmt_bind_param($deleteStmt, "i", $id);
    mysqli_stmt_execute($deleteStmt);

    $thumbnailPath = __DIR__ . "/../thumbnail/" . $data["thumbnail"];
    $videoPath = __DIR__ . "/../video/" . $data["video"];

    if (is_file($thumbnailPath)) {
        @unlink($thumbnailPath);
    }
    if (is_file($videoPath)) {
        @unlink($videoPath);
    }

    echo json_encode([
        "success" => true,
        "message" => "Data berhasil dihapus"
    ]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal menghapus data"
    ]);
}
