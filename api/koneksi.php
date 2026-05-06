// test update git
<?php

$host = $_ENV['MYSQLHOST'] ?? "trolley.proxy.rlwy.net";
$user = $_ENV['MYSQLUSER'] ?? "root";
$pass = $_ENV['MYSQLPASSWORD'] ?? "mtThLSFsDxhPeZNgwkSOMsvNDuOWCrmO";
$db   = $_ENV['MYSQLDATABASE'] ?? "railway";
$port = $_ENV['MYSQLPORT'] ?? 45888;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>