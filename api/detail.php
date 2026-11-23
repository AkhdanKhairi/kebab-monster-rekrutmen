<?php
// Nama File: api/detail.php
header('Content-Type: application/json; charset=utf-8');

$id_lowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_lowongan <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parameter id lowongan tidak valid.',
    ]);
    exit();
}

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

$stmt = mysqli_prepare($conn, "SELECT * FROM `lowongan` WHERE `id_lowongan` = ? AND `view` = 'aktif' LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_lowongan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);
    $tgl_raw = $row['tgl_dibuat'] ?? '';
    $row['formatted_date'] = (!empty($tgl_raw) && strtotime($tgl_raw) !== false)
        ? date('d-M-Y', strtotime($tgl_raw))
        : '-';

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    echo json_encode([
        'success' => true,
        'data'    => $row,
    ]);
} else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Lowongan tidak ditemukan atau sudah ditutup.',
    ]);
}