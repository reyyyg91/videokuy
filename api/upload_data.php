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
        "message" => "Method tidak diizinkan",
    ]);
    exit;
}

require_once __DIR__ . "/koneksi.php";
/** @var mysqli $koneksi */

$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";

if ($title === "") {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Title wajib diisi",
    ]);
    exit;
}

if (!isset($_FILES["thumbnail"], $_FILES["video"])) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Thumbnail dan video wajib diupload",
    ]);
    exit;
}

if ($_FILES["thumbnail"]["error"] !== UPLOAD_ERR_OK || $_FILES["video"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Terjadi error saat upload file",
    ]);
    exit;
}

$thumbnailTmp = $_FILES["thumbnail"]["tmp_name"];
$videoTmp = $_FILES["video"]["tmp_name"];

$thumbnailExt = strtolower(pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION));
$videoExt = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));

$allowedImage = ["jpg", "jpeg", "png", "webp"];
$allowedVideo = ["mp4", "mov", "avi", "mkv"];

if (!in_array($thumbnailExt, $allowedImage, true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format thumbnail tidak didukung",
    ]);
    exit;
}

if (!in_array($videoExt, $allowedVideo, true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format video tidak didukung",
    ]);
    exit;
}

$thumbnailName = uniqid("thumb_", true) . "." . $thumbnailExt;
$videoName = uniqid("video_", true) . "." . $videoExt;

$thumbnailPath = __DIR__ . "/../thumbnail/" . $thumbnailName;
$videoPath = __DIR__ . "/../video/" . $videoName;

if (!move_uploaded_file($thumbnailTmp, $thumbnailPath)) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan file thumbnail",
    ]);
    exit;
}

if (!move_uploaded_file($videoTmp, $videoPath)) {
    @unlink($thumbnailPath);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal menyimpan file video",
    ]);
    exit;
}

try {
    $stmt = mysqli_prepare($koneksi, "INSERT INTO mhs_232175 (title, thumbnail, video) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $title, $thumbnailName, $videoName);
    mysqli_stmt_execute($stmt);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Data berhasil disimpan",
        "data" => [
            "id" => mysqli_insert_id($koneksi),
            "title" => $title,
            "thumbnail" => $thumbnailName,
            "video" => $videoName,
        ],
    ]);
} catch (mysqli_sql_exception $e) {
    @unlink($thumbnailPath);
    @unlink($videoPath);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal simpan data ke database",
    ]);
}
