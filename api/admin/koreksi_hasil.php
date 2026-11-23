<?php
// File: api/koreksi_hasil.php
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

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
    exit();
}
mysqli_set_charset($conn, 'utf8mb4');

$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
while ($t = mysqli_fetch_row($q_tables)) {
    $all_tables[] = $t[0];
}

$tbl_hasil = "hasil_psikotes";
foreach ($all_tables as $tbl) {
    if (preg_match('/(hasil_psikotes|hasil_ujian|nilai_psikotes|hasil_tes)/i', $tbl)) {
        $tbl_hasil = $tbl;
        break;
    }
}

$cols_hasil = [];
$q_ch = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_hasil`");
if ($q_ch) while ($c = mysqli_fetch_assoc($q_ch)) $cols_hasil[] = $c['Field'];

$pk_hasil   = $cols_hasil[0] ?? 'id_hasil';
$fk_pelamar = in_array('id_pelamar', $cols_hasil, true) ? 'id_pelamar' : 'pelamar_id';
$col_skor   = in_array('skor_akhir', $cols_hasil, true) ? 'skor_akhir' : (in_array('nilai', $cols_hasil, true) ? 'nilai' : 'skor');
$col_status = in_array('status_lulus', $cols_hasil, true) ? 'status_lulus' : (in_array('status', $cols_hasil, true) ? 'status' : '');
$col_tgl    = in_array('tgl_ujian', $cols_hasil, true) ? 'tgl_ujian' : (in_array('created_at', $cols_hasil, true) ? 'created_at' : '');

$method = $_SERVER['REQUEST_METHOD'];
$input  = ($method === 'POST') ? (json_decode(file_get_contents('php://input'), true) ?: $_POST) : $_GET;

if ($method === 'POST') {
    // Mode massal
    if (!empty($input['publish_massal'])) {
        $ids      = $input['pilih_hasil'] ?? [];
        $aksi_pub = intval($input['nilai_publish'] ?? 1) === 1 ? 1 : 0;
        $clean_ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids), function ($v) {
            return $v > 0;
        })));
        if (empty($clean_ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Pilih minimal 1 data hasil psikotes.']);
            mysqli_close($conn);
            exit();
        }
        $placeholders = implode(',', array_fill(0, count($clean_ids), '?'));
        $types_m      = "i" . str_repeat('i', count($clean_ids));
        $stmt_m       = mysqli_prepare($conn, "UPDATE `$tbl_hasil` SET `is_published` = ? WHERE `$pk_hasil` IN ($placeholders)");
        $bind_m       = array_merge([$aksi_pub], $clean_ids);
        mysqli_stmt_bind_param($stmt_m, $types_m, ...$bind_m);
        $ok = mysqli_stmt_execute($stmt_m);
        mysqli_stmt_close($stmt_m);

        echo json_encode([
            'success'      => (bool)$ok,
            'message'      => $ok ? count($clean_ids) . ' hasil psikotes diperbarui.' : 'Gagal update massal.',
            'is_published' => $aksi_pub,
        ]);
        mysqli_close($conn);
        exit();
    }

    // Mode satuan
    $id_h      = intval($input['id_hasil'] ?? 0);
    $pub_val   = intval($input['is_published'] ?? 0) === 1 ? 1 : 0;
    $catatan   = trim((string)($input['catatan_hr'] ?? ''));
    $st_lulus  = trim((string)($input['status_lulus'] ?? ''));
    $skor_baru = isset($input['skor_akhir']) ? max(0, min(100, floatval($input['skor_akhir']))) : null;

    if ($id_h <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id_hasil tidak valid.']);
        mysqli_close($conn);
        exit();
    }

    $set_sql = ["`is_published` = ?", "`catatan_hr` = ?"];
    $types   = "is";
    $params  = [$pub_val, $catatan];

    if ($skor_baru !== null) {
        $set_sql[] = "`$col_skor` = ?";
        $types    .= "d";
        $params[]  = $skor_baru;
    }
    if ($col_status !== '' && in_array($st_lulus, ['Lulus', 'Tidak Lulus', 'Menunggu Review'], true)) {
        $set_sql[] = "`$col_status` = ?";
        $types    .= "s";
        $params[]  = $st_lulus;
    }
    $types   .= "i";
    $params[] = $id_h;

    $stmt_upd = mysqli_prepare($conn, "UPDATE `$tbl_hasil` SET " . implode(", ", $set_sql) . " WHERE `$pk_hasil` = ?");
    mysqli_stmt_bind_param($stmt_upd, $types, ...$params);
    $ok = mysqli_stmt_execute($stmt_upd);
    mysqli_stmt_close($stmt_upd);

    echo json_encode([
        'success' => (bool)$ok,
        'message' => $ok ? 'Data koreksi & publikasi berhasil disimpan.' : 'Gagal menyimpan koreksi.',
    ]);
    mysqli_close($conn);
    exit();
}

// GET: Daftar hasil psikotes
$cari   = trim((string)($input['cari'] ?? ''));
$status = trim((string)($input['status'] ?? ''));
$pub    = trim((string)($input['pub'] ?? ''));

$where = [];
if ($cari !== '') {
    $esc = mysqli_real_escape_string($conn, $cari);
    $where[] = "(p.nama_lengkap LIKE '%$esc%' OR p.email LIKE '%$esc%')";
}
if ($status !== '' && $col_status !== '') {
    $esc_st = mysqli_real_escape_string($conn, $status);
    $where[] = "h.`$col_status` = '$esc_st'";
}
if ($pub !== '' && in_array($pub, ['0', '1'], true)) {
    $where[] = "h.is_published = " . intval($pub);
}
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
$order_sql = $col_tgl !== '' ? "ORDER BY h.`$col_tgl` DESC" : "ORDER BY h.`$pk_hasil` DESC";

$rows = [];
$q_list = mysqli_query($conn, "
    SELECT h.*, p.nama_lengkap, p.email, p.no_telepon
    FROM `$tbl_hasil` h
    INNER JOIN `pelamar` p ON h.`$fk_pelamar` = p.id_pelamar
    $where_sql
    $order_sql
");
if ($q_list) {
    while ($r = mysqli_fetch_assoc($q_list)) {
        $rows[] = $r;
    }
}

$cnt_pub   = intval((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `$tbl_hasil` WHERE is_published = 1"))['jml'] ?? 0));
$cnt_lulus = $col_status !== '' ? intval((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `$tbl_hasil` WHERE `$col_status` = 'Lulus'"))['jml'] ?? 0)) : 0;

echo json_encode([
    'success' => true,
    'summary' => [
        'total'     => count($rows),
        'lulus'     => $cnt_lulus,
        'published' => $cnt_pub,
    ],
    'data'    => $rows,
]);
mysqli_close($conn);