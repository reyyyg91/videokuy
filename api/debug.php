<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require __DIR__ . '/../vendor/autoload.php';

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

$response = [
    'cloud_name_from_getenv' => getenv('CLOUDINARY_CLOUD_NAME'),
    'cloud_name_from_ENV' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? 'NOT SET',
    'cloud_name_from_SERVER' => $_SERVER['CLOUDINARY_CLOUD_NAME'] ?? 'NOT SET',
    'api_key_length' => strlen(getenv('CLOUDINARY_API_KEY') ?: ''),
    'api_secret_length' => strlen(getenv('CLOUDINARY_API_SECRET') ?: ''),
    'all_cloudinary_vars' => [
        'CLOUDINARY_CLOUD_NAME' => getenv('CLOUDINARY_CLOUD_NAME'),
        'CLOUDINARY_API_KEY' => getenv('CLOUDINARY_API_KEY') ? 'SET (length: ' . strlen(getenv('CLOUDINARY_API_KEY')) . ')' : 'NOT SET',
        'CLOUDINARY_API_SECRET' => getenv('CLOUDINARY_API_SECRET') ? 'SET (length: ' . strlen(getenv('CLOUDINARY_API_SECRET')) . ')' : 'NOT SET',
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT);
