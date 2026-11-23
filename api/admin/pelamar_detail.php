<?php
// api/admin/pelamar_detail.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$idPelamar = intval($_GET['id'] ?? 0);
if ($idPelamar <= 0) {
    jsonResponse(400, false, 'ID pelamar tidak valid.');
}

$conn = getDbConnection();
$stmt = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
if (!$stmt) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query detail pelamar.');
}

mysqli_stmt_bind_param($stmt, 'i', $idPelamar);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);
mysqli_close($conn);

if (!$data) {
    jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
}

$fotoFile = !empty($data['pas_foto']) ? basename((string) $data['pas_foto']) : '';
$uploadsDir = realpath(__DIR__ . '/../../uploads');
$fotoExists = false;
if ($fotoFile !== '' && $uploadsDir) {
    $fp = realpath(__DIR__ . '/../../uploads/' . $fotoFile);
    if ($fp && strpos($fp, $uploadsDir) === 0 && file_exists($fp)) {
        $fotoExists = true;
    }
}

$data['id_pelamar']  = intval($data['id_pelamar']);
$data['foto_file']   = $fotoFile;
$data['foto_exists'] = $fotoExists;

jsonResponse(200, true, 'Detail pelamar berhasil dimuat.', ['pelamar' => $data]);