<?php
// api/lowongan/detail.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

$idLowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idLowongan <= 0) {
    jsonResponse(400, false, 'Parameter id lowongan tidak valid.');
}

$conn = getDbConnection();

$stmt = mysqli_prepare($conn, "SELECT * FROM `lowongan` WHERE `id_lowongan` = ? LIMIT 1");
if (!$stmt) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query detail lowongan.');
}

mysqli_stmt_bind_param($stmt, 'i', $idLowongan);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil detail lowongan.');
}

$res = mysqli_stmt_get_result($stmt);
$job = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$job) {
    jsonResponse(404, false, 'Lowongan tidak ditemukan.');
}

$rawStatus = strtolower(trim((string) ($job['status_aktif'] ?? 'Aktif')));
$isAktif   = !in_array($rawStatus, [
    '0', 'nonaktif', 'tidak aktif', 'inactive', 'non-aktif', 'tutup', 'closed', 'false', 'tidak'
], true);

jsonResponse(200, true, 'Detail lowongan berhasil diambil.', [
    'lowongan' => $job,
    'is_aktif' => $isAktif,
]);