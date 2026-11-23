<?php
// Nama File: api/detail_pelamar.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$id_admin  = intval($_SESSION['user_id'] ?? 0);

if ($id_admin <= 0 || !in_array($role_user, ['admin', 'hr', 'hrd'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai HR/Admin.']);
    exit();
}

$id_pelamar = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
if ($id_pelamar <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Pelamar tidak valid.']);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
    exit();
}
mysqli_set_charset($conn, 'utf8mb4');

$stmt = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id_pelamar);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$data = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$data) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Data pelamar tidak ditemukan.']);
    mysqli_close($conn);
    exit();
}

$riwayat_lamaran = [];
$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
if ($q_tables) {
    while ($t = mysqli_fetch_row($q_tables)) {
        $all_tables[] = $t[0];
    }
}

$tbl_lamaran  = "";
$cols_lamaran = [];
foreach ($all_tables as $tbl) {
    if ($tbl === 'pelamar' || $tbl === 'admin') {
        continue;
    }
    $cols = [];
    $q_c = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
    if ($q_c) {
        while ($c = mysqli_fetch_assoc($q_c)) {
            $cols[] = $c['Field'];
        }
    }
    if (in_array('tgl_melamar', $cols, true) || (in_array('id_pelamar', $cols, true) && count($cols) <= 12)) {
        $tbl_lamaran  = $tbl;
        $cols_lamaran = $cols;
        if (in_array('tgl_melamar', $cols, true)) {
            break;
        }
    }
}

if (!empty($tbl_lamaran)) {
    $fk_pelamar = in_array('id_pelamar', $cols_lamaran, true) ? 'id_pelamar' : 'pelamar_id';
    $tgl_col    = in_array('tgl_melamar', $cols_lamaran, true) ? 'tgl_melamar' : (in_array('tgl_daftar', $cols_lamaran, true) ? 'tgl_daftar' : '');
    $status_col = in_array('status', $cols_lamaran, true) ? 'status' : (in_array('status_lamaran', $cols_lamaran, true) ? 'status_lamaran' : '');

    $direct_pos = "";
    foreach ($cols_lamaran as $cl) {
        if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $cl) && !preg_match('/^id_/i', $cl)) {
            $direct_pos = $cl;
            break;
        }
    }

    if (!empty($direct_pos)) {
        $stmt_lam = mysqli_prepare($conn, "SELECT * FROM `$tbl_lamaran` WHERE `$fk_pelamar` = ?");
        mysqli_stmt_bind_param($stmt_lam, "i", $id_pelamar);
        mysqli_stmt_execute($stmt_lam);
        $q_lam = mysqli_stmt_get_result($stmt_lam);
        while ($rl = mysqli_fetch_assoc($q_lam)) {
            $riwayat_lamaran[] = [
                'posisi'  => $rl[$direct_pos] ?? '-',
                'tanggal' => !empty($tgl_col) ? ($rl[$tgl_col] ?? '-') : '-',
                'status'  => !empty($status_col) ? ($rl[$status_col] ?? 'Diproses') : 'Diproses',
            ];
        }
        mysqli_stmt_close($stmt_lam);
    } else {
        $fk_low = "";
        foreach ($cols_lamaran as $cl) {
            if ($cl !== $fk_pelamar && preg_match('/(lowongan|loker|posisi)/i', $cl)) {
                $fk_low = $cl;
                break;
            }
        }
        if (!empty($fk_low)) {
            foreach ($all_tables as $tbl) {
                if ($tbl === 'pelamar' || $tbl === 'admin' || $tbl === $tbl_lamaran) {
                    continue;
                }
                $cols_low = [];
                $q_clow = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
                if ($q_clow) {
                    while ($c = mysqli_fetch_assoc($q_clow)) {
                        $cols_low[] = $c['Field'];
                    }
                }
                $pk_low = $cols_low[0] ?? '';
                if ($pk_low === $fk_low || in_array($fk_low, $cols_low, true)) {
                    $nama_col = "";
                    foreach ($cols_low as $cn) {
                        if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $cn) && !preg_match('/^id_/i', $cn)) {
                            $nama_col = $cn;
                            break;
                        }
                    }
                    if (!empty($nama_col)) {
                        $sql_rl = "SELECT l.*, low.`$nama_col` AS nama_posisi_low
                                   FROM `$tbl_lamaran` l
                                   LEFT JOIN `$tbl` low ON l.`$fk_low` = low.`$pk_low`
                                   WHERE l.`$fk_pelamar` = ?";
                        $stmt_rl = mysqli_prepare($conn, $sql_rl);
                        mysqli_stmt_bind_param($stmt_rl, "i", $id_pelamar);
                        mysqli_stmt_execute($stmt_rl);
                        $q_lam = mysqli_stmt_get_result($stmt_rl);
                        while ($rl = mysqli_fetch_assoc($q_lam)) {
                            $riwayat_lamaran[] = [
                                'posisi'  => $rl['nama_posisi_low'] ?? '-',
                                'tanggal' => !empty($tgl_col) ? ($rl[$tgl_col] ?? '-') : '-',
                                'status'  => !empty($status_col) ? ($rl[$status_col] ?? 'Diproses') : 'Diproses',
                            ];
                        }
                        mysqli_stmt_close($stmt_rl);
                        break;
                    }
                }
            }
        }
    }
}

$dokumen_list = [
    'pas_foto' => 'Pas Foto',
    'ktp'      => 'Kartu Tanda Penduduk (KTP)',
    'ijazah'   => 'Ijazah Terakhir',
    'skck'     => 'SKCK',
    'kk'       => 'Kartu Keluarga (KK)',
];

$dokumen_status = [];
foreach ($dokumen_list as $kolom => $judul_dok) {
    $nama_file = $data[$kolom] ?? '';
    $path_file = '../uploads/' . $nama_file;
    $ada_file  = (!empty($nama_file) && file_exists(__DIR__ . '/../uploads/' . $nama_file));
    $ext       = $ada_file ? strtolower(pathinfo($nama_file, PATHINFO_EXTENSION)) : '';
    $dokumen_status[$kolom] = [
        'judul'     => $judul_dok,
        'nama_file' => $nama_file,
        'url'       => $ada_file ? $path_file : '',
        'ada_file'  => $ada_file,
        'ext'       => $ext,
    ];
}

echo json_encode([
    'success'         => true,
    'pelamar'         => $data,
    'riwayat_lamaran' => $riwayat_lamaran,
    'dokumen'         => $dokumen_status,
]);

mysqli_close($conn);