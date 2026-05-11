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


require __DIR__ . '/../vendor/autoload.php';

// Load .env file agar getenv() berfungsi
if (class_exists('Dotenv\\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    $dotenv->required(['CLOUDINARY_CLOUD_NAME', 'CLOUDINARY_API_KEY', 'CLOUDINARY_API_SECRET']);
}

require_once __DIR__ . "/koneksi.php";

use Cloudinary\Cloudinary;

$cloudName = getenv('CLOUDINARY_CLOUD_NAME') ?: ($_ENV['CLOUDINARY_CLOUD_NAME'] ?? null);
$apiKey    = getenv('CLOUDINARY_API_KEY') ?: ($_ENV['CLOUDINARY_API_KEY'] ?? null);
$apiSecret = getenv('CLOUDINARY_API_SECRET') ?: ($_ENV['CLOUDINARY_API_SECRET'] ?? null);

if (!$cloudName || !$apiKey || !$apiSecret) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Cloudinary configuration missing'
    ]);
    exit;
}

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => $cloudName,
        'api_key'    => $apiKey,
        'api_secret' => $apiSecret,
    ],
    'url' => [
        'secure' => true
    ]
]);

$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";

if ($title === "") {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Title wajib diisi"
    ]);
    exit;
}

// UPLOAD THUMBNAIL
if (!isset($_FILES["thumbnail"]) || $_FILES["thumbnail"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Thumbnail wajib diupload"
    ]);
    exit;
}
$thumbnailExt = strtolower(pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION));
$allowedImage = ["jpg", "jpeg", "png", "webp"];
if (!in_array($thumbnailExt, $allowedImage, true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format thumbnail tidak didukung"
    ]);
    exit;
}
$thumbnailUpload = $cloudinary->uploadApi()->upload(
    $_FILES["thumbnail"]["tmp_name"],
    [ 'folder' => 'thumbnail' ]
);
$thumbnailUrl = $thumbnailUpload['secure_url'];

// UPLOAD VIDEO
if (!isset($_FILES["video"]) || $_FILES["video"]["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Video wajib diupload"
    ]);
    exit;
}
$videoExt = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));
$allowedVideo = ["mp4", "mov", "avi", "mkv"];
if (!in_array($videoExt, $allowedVideo, true)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Format video tidak didukung"
    ]);
    exit;
}
$videoUpload = $cloudinary->uploadApi()->upload(
    $_FILES["video"]["tmp_name"],
    [ 'resource_type' => 'video', 'folder' => 'video' ]
);
$videoUrl = $videoUpload['secure_url'];

// INSERT DATABASE
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO mhs_232175 (title, thumbnail, video) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sss", $title, $thumbnailUrl, $videoUrl);
mysqli_stmt_execute($stmt);
$newId = mysqli_insert_id($koneksi);

if ($newId > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Data berhasil ditambah",
        "data" => [
            "id" => $newId,
            "title" => $title,
            "thumbnail" => $thumbnailUrl,
            "video" => $videoUrl
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal menambah data"
    ]);
}
