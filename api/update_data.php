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
require 'vendor/autoload.php';

use Cloudinary\Cloudinary;

/** @var mysqli $koneksi */

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => getenv('CLOUDINARY_API_KEY'),
        'api_secret' => getenv('CLOUDINARY_API_SECRET'),
    ],
    'url' => [
        'secure' => true
    ]
]);

$id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
$title = isset($_POST["title"]) ? trim($_POST["title"]) : "";

if ($id <= 0) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "ID wajib diisi"
    ]);
    exit;
}

if ($title === "") {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Title wajib diisi"
    ]);
    exit;
}

try {

    $checkStmt = mysqli_prepare(
        $koneksi,
        "SELECT thumbnail, video FROM mhs_232175 WHERE id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($checkStmt, "i", $id);
    mysqli_stmt_execute($checkStmt);

    $result = mysqli_stmt_get_result($checkStmt);
    $existingData = mysqli_fetch_assoc($result);

    if (!$existingData) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Data tidak ditemukan"
        ]);
        exit;
    }

    $thumbnailUrl = $existingData["thumbnail"];
    $videoUrl = $existingData["video"];

    // UPDATE THUMBNAIL
    if (isset($_FILES["thumbnail"]) &&
        $_FILES["thumbnail"]["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES["thumbnail"]["error"] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Error upload thumbnail"
            ]);
            exit;
        }

        $thumbnailExt = strtolower(
            pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION)
        );

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
            [
                'folder' => 'thumbnail'
            ]
        );

        $thumbnailUrl = $thumbnailUpload['secure_url'];
    }

    // UPDATE VIDEO
    if (isset($_FILES["video"]) &&
        $_FILES["video"]["error"] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES["video"]["error"] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Error upload video"
            ]);
            exit;
        }

        $videoExt = strtolower(
            pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION)
        );

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
            [
                'resource_type' => 'video',
                'folder' => 'video'
            ]
        );

        $videoUrl = $videoUpload['secure_url'];
    }

    // UPDATE DATABASE
    $updateStmt = mysqli_prepare(
        $koneksi,
        "UPDATE mhs_232175 
         SET title = ?, thumbnail = ?, video = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $updateStmt,
        "sssi",
        $title,
        $thumbnailUrl,
        $videoUrl,
        $id
    );

    mysqli_stmt_execute($updateStmt);

    echo json_encode([
        "success" => true,
        "message" => "Data berhasil diupdate",
        "data" => [
            "id" => $id,
            "title" => $title,
            "thumbnail" => $thumbnailUrl,
            "video" => $videoUrl
        ]
    ]);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Gagal update data",
        "error" => $e->getMessage()
    ]);
}