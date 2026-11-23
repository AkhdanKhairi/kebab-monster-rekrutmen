<?php
// Nama File: api/career.php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error(),
    ]);
    exit();
}
mysqli_set_charset($conn, 'utf8mb4');

$keyword = trim($_GET['q'] ?? '');
$divisi  = trim($_GET['divisi'] ?? '');

$sql = "SELECT * FROM `lowongan` WHERE `view` = 'aktif'";
$types  = '';
$params = [];

if ($divisi !== '') {
    $sql .= " AND `divisi` = ?";
    $types .= 's';
    $params[] = $divisi;
}

if ($keyword !== '') {
    $sql .= " AND (`posisi_pekerjaan` LIKE ? OR `divisi` LIKE ? OR `lokasi_pekerjaan` LIKE ?)";
    $like = '%' . $keyword . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY `tgl_dibuat` DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$lowongan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['formatted_date'] = !empty($row['tgl_dibuat'])
        ? date('d-M-Y', strtotime($row['tgl_dibuat']))
        : '-';
    $lowongan[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode([
    'success'  => true,
    'total'    => count($lowongan),
    'lowongan' => $lowongan,
]);