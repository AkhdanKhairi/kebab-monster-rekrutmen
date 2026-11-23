<?php
// api/lowongan/index.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

$conn = getDbConnection();

// Filter opsional via query string:
// ?status_aktif=Aktif  atau  ?view=aktif  atau  ?q=barista
$statusAktif = trim((string) ($_GET['status_aktif'] ?? ''));
$viewFilter  = trim((string) ($_GET['view'] ?? ''));
$search      = trim((string) ($_GET['q'] ?? ''));

$sql    = "SELECT * FROM lowongan WHERE 1=1";
$types  = '';
$params = [];

if ($statusAktif !== '') {
    $sql .= " AND status_aktif = ?";
    $types .= 's';
    $params[] = $statusAktif;
}

if ($viewFilter !== '') {
    $sql .= " AND `view` = ?";
    $types .= 's';
    $params[] = $viewFilter;
}

if ($search !== '') {
    $sql .= " AND (posisi_pekerjaan LIKE ? OR divisi LIKE ? OR lokasi_pekerjaan LIKE ?)";
    $like = '%' . $search . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY id_lowongan DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query data lowongan.');
}

if ($types !== '') {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil data lowongan.');
}

$result = mysqli_stmt_get_result($stmt);
$items  = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

jsonResponse(200, true, 'Data lowongan berhasil diambil.', [
    'lowongan' => $items,
    'total'    => count($items),
]);