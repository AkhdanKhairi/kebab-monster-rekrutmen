<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_penerimaan_karyawan"; // Sesuaikan dengan nama database Anda di phpMyAdmin

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>