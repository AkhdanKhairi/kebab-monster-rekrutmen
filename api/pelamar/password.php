<?php
// api/pelamar/password.php
require_once __DIR__ . '/../_helper.php';

requireMethod('POST');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.');
}
$idPelamar = intval($_SESSION['user_id']);

$input              = getRequestInput();
$passwordLamaRaw    = (string) ($input['password_lama'] ?? '');
$passwordBaru       = (string) ($input['password_baru'] ?? '');
$konfirmasiPassword = (string) ($input['konfirmasi_password'] ?? '');

if ($passwordLamaRaw === '' || $passwordBaru === '' || $konfirmasiPassword === '') {
    jsonResponse(400, false, 'Semua kolom password wajib diisi.');
}

if ($passwordBaru !== $konfirmasiPassword) {
    jsonResponse(422, false, 'Konfirmasi password baru tidak cocok!');
}

if (strlen($passwordBaru) < 6) {
    jsonResponse(422, false, 'Password baru minimal harus 6 karakter!');
}

$conn = getDbConnection();

$stmtCek = mysqli_prepare($conn, "SELECT `password` FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
if (!$stmtCek) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memeriksa data akun pelamar.');
}
mysqli_stmt_bind_param($stmtCek, 'i', $idPelamar);
mysqli_stmt_execute($stmtCek);
$resCek      = mysqli_stmt_get_result($stmtCek);
$dataPelamar = $resCek ? mysqli_fetch_assoc($resCek) : null;
mysqli_stmt_close($stmtCek);

if (!$dataPelamar) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Akun pelamar tidak ditemukan.');
}

$passwordLamaMd5 = md5($passwordLamaRaw);
if (!hash_equals((string) ($dataPelamar['password'] ?? ''), $passwordLamaMd5)) {
    mysqli_close($conn);
    jsonResponse(400, false, 'Password lama yang Anda masukkan salah!');
}

$passwordBaruMd5 = md5($passwordBaru);
$stmtUpd = mysqli_prepare($conn, "UPDATE `pelamar` SET `password` = ? WHERE `id_pelamar` = ?");
if (!$stmtUpd) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan pembaruan password.');
}

mysqli_stmt_bind_param($stmtUpd, 'si', $passwordBaruMd5, $idPelamar);
if (!mysqli_stmt_execute($stmtUpd)) {
    mysqli_stmt_close($stmtUpd);
    mysqli_close($conn);
    jsonResponse(500, false, 'Terjadi kesalahan sistem, gagal mengubah password.');
}

mysqli_stmt_close($stmtUpd);
mysqli_close($conn);

jsonResponse(200, true, 'Password akun berhasil diperbarui! Silakan gunakan password baru untuk login selanjutnya.');