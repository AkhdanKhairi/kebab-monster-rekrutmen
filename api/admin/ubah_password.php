<?php
// api/admin/ubah_password.php
require_once __DIR__ . '/../_helper.php';

requireMethod('POST');

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$input = getRequestInput();
$passwordLama       = (string) ($input['password_lama'] ?? '');
$passwordBaru       = (string) ($input['password_baru'] ?? '');
$konfirmasiPassword = (string) ($input['konfirmasi_password'] ?? '');

$conn = getDbConnection();

$stmt = mysqli_prepare($conn, "SELECT `password` FROM `admin` WHERE `id_admin` = ? LIMIT 1");
if (!$stmt) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memverifikasi akun admin.');
}

mysqli_stmt_bind_param($stmt, 'i', $idAdmin);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Akun admin tidak ditemukan.');
}

$passwordDb = (string) ($row['password'] ?? '');
$passwordLamaValid = false;

if (password_verify($passwordLama, $passwordDb)) {
    $passwordLamaValid = true;
} elseif ($passwordDb !== '' && hash_equals($passwordDb, md5($passwordLama))) {
    $passwordLamaValid = true;
} elseif ($passwordDb !== '' && hash_equals($passwordDb, $passwordLama)) {
    $passwordLamaValid = true;
}

if (!$passwordLamaValid) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Password lama yang Anda masukkan salah!');
}

if (strlen($passwordBaru) < 5) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Password baru minimal terdiri dari 5 karakter!');
}

if ($passwordBaru !== $konfirmasiPassword) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Konfirmasi password baru tidak cocok!');
}

$passwordSimpan = password_hash($passwordBaru, PASSWORD_DEFAULT);
$stmtUpd = mysqli_prepare($conn, "UPDATE `admin` SET `password` = ? WHERE `id_admin` = ?");
if (!$stmtUpd) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query pembaruan password.');
}

mysqli_stmt_bind_param($stmtUpd, 'si', $passwordSimpan, $idAdmin);
$ok = mysqli_stmt_execute($stmtUpd);
$err = mysqli_error($conn);
mysqli_stmt_close($stmtUpd);
mysqli_close($conn);

if (!$ok) {
    jsonResponse(500, false, 'Gagal memperbarui password: ' . $err);
}

jsonResponse(200, true, 'Password akun HR/Admin berhasil diperbarui!');