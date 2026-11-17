<?php
session_start();

// 1. Cek apakah user sudah login sebagai pelamar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

// 2. Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 3. Ambil ID Lowongan dari URL
$id_lowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_lowongan == 0) {
    echo "<script>alert('Lowongan tidak valid!'); window.location='karir.php';</script>";
    exit();
}

// 4. Cek apakah pelamar sudah pernah melamar lowongan ini sebelumnya
$cek_sudah_lamar = mysqli_query($conn, "SELECT * FROM lamaran WHERE id_pelamar = $id_pelamar AND id_lowongan = $id_lowongan");
if (mysqli_num_rows($cek_sudah_lamar) > 0) {
    echo "<script>alert('Anda sudah pernah mengirimkan lamaran untuk posisi ini!'); window.location='karir.php';</script>";
    exit();
}

// 5. Masukkan data ke tabel lamaran (Menggunakan data profil pelamar yang sudah ada)
$query_insert = "INSERT INTO lamaran (id_lowongan, id_pelamar, tgl_melamar, status_lamaran) VALUES ($id_lowongan, $id_pelamar, NOW(), 'Pending')";

if (mysqli_query($conn, $query_insert)) {
    echo "<script>alert('Lamaran berhasil dikirim! HRD akan segera meninjau profil dan berkas Anda.'); window.location='karir.php';</script>";
} else {
    echo "<script>alert('Terjadi kesalahan sistem, gagal mengirim lamaran.'); window.location='detail_karir.php?id=$id_lowongan';</script>";
}

mysqli_close($conn);
?>