<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file agar getenv() berfungsi
if (class_exists('Dotenv\\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Railway MySQL plugin: MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
// Alternatif manual: DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT
// Fallback URL: MYSQL_PRIVATE_URL, MYSQL_PUBLIC_URL, MYSQL_URL, DATABASE_URL
function env_value($key)
{
    $value = getenv($key);
    if ($value !== false && $value !== "") {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== "") {
        return $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== "") {
        return $_SERVER[$key];
    }

    return null;
}

$dbUrl = env_value("DATABASE_URL") ?: env_value("MYSQL_URL") ?: env_value("MYSQL_PRIVATE_URL") ?: env_value("MYSQL_PUBLIC_URL");
$dbUrlParts = null;
if ($dbUrl) {
    $parsed = parse_url($dbUrl);
    if (is_array($parsed)) {
        $dbUrlParts = $parsed;
    }
}

$host = env_value("MYSQLHOST")
    ?: env_value("MYSQL_HOST")
    ?: env_value("DB_HOST")
    ?: ($dbUrlParts["host"] ?? "localhost");

$user = env_value("MYSQLUSER")
    ?: env_value("MYSQL_USER")
    ?: env_value("DB_USER")
    ?: ($dbUrlParts["user"] ?? "root");

$pass = env_value("MYSQLPASSWORD")
    ?: env_value("MYSQL_PASSWORD")
    ?: env_value("DB_PASS")
    ?: ($dbUrlParts["pass"] ?? "");

$db = env_value("MYSQLDATABASE")
    ?: env_value("MYSQL_DATABASE")
    ?: env_value("DB_NAME")
    ?: (isset($dbUrlParts["path"]) ? ltrim($dbUrlParts["path"], "/") : "raihan232175");

$port = (int) (
    env_value("MYSQLPORT")
    ?: env_value("MYSQL_PORT")
    ?: env_value("DB_PORT")
    ?: ($dbUrlParts["port"] ?? 3306)
);

if ($port <= 0) {
    $port = 3306;
}

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db, $port);
    mysqli_set_charset($koneksi, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    $response = [
        "success" => false,
        "message" => "Koneksi database gagal",
    ];

    // Aktifkan APP_DEBUG=true di Railway untuk melihat detail penyebab koneksi.
    if (strtolower((string) (env_value("APP_DEBUG") ?? "")) === "true") {
        $response["debug"] = [
            "host" => $host,
            "port" => $port,
            "database" => $db,
            "user" => $user,
            "error" => $e->getMessage(),
        ];
    }

    echo json_encode($response);
    exit;
}
