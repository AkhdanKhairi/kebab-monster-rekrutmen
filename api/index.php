<?php
// Nama File: api/index.php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';
$conn = @mysqli_connect($host, $user, $pass, $db);

$total_lowongan = 0;
if ($conn) {
    mysqli_set_charset($conn, 'utf8mb4');
    $q_tables = mysqli_query($conn, "SHOW TABLES");
    if ($q_tables) {
        while ($t = mysqli_fetch_row($q_tables)) {
            if (preg_match('/(lowongan|loker)/i', $t[0])) {
                $tbl = $t[0];
                $res = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `$tbl`");
                if ($res && $row = mysqli_fetch_assoc($res)) {
                    $total_lowongan = intval($row['jml']);
                }
                break;
            }
        }
    }
    mysqli_close($conn);
}

echo json_encode([
    'success' => true,
    'brand' => 'Kebab Monster',
    'total_lowongan_aktif' => $total_lowongan,
    'kota_operasional' => [
        ['kode' => 'JKT', 'nama' => 'Jakarta'],
        ['kode' => 'DPK', 'nama' => 'Depok'],
        ['kode' => 'BGR', 'nama' => 'Bogor'],
        ['kode' => 'TGR', 'nama' => 'Tangerang'],
        ['kode' => 'BKS', 'nama' => 'Bekasi'],
        ['kode' => 'BDG', 'nama' => 'Bandung'],
    ],
]);