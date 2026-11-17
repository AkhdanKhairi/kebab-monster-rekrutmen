<?php
$host     = 'localhost';
$dbname   = 'db_kebab_monster';
$username = 'root';
$password = '';

// 1. Koneksi standar MySQLi (Digunakan oleh sebagian besar file proyek kita saat ini)
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Koneksi database mysqli gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// 2. Koneksi PDO (Jikalau ada file lain yang membutuhkan PDO)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Abaikan atau matikan jika PDO tidak wajib di halaman tertentu
}
?>