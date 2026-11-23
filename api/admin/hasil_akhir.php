<?php
// File: api/hasil_akhir.php
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

$method = $_SERVER['REQUEST_METHOD'];
$input  = ($method === 'POST') ? (json_decode(file_get_contents('php://input'), true) ?: $_POST) : $_GET;

if ($method === 'POST') {
    $ids_terpilih  = $input['id_pelamar'] ?? [];
    $status_massal = trim((string)($input['status_massal'] ?? ''));
    $pesan_massal  = trim((string)($input['pesan_massal'] ?? ''));
    $timpa_pesan   = !empty($input['timpa_pesan']) ? 1 : 0;

    if (!is_array($ids_terpilih) || empty($ids_terpilih)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pilih minimal 1 pelamar.']);
        mysqli_close($conn);
        exit();
    }
    if (!in_array($status_massal, ['Diterima', 'Tidak Diterima'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status massal harus Diterima atau Tidak Diterima.']);
        mysqli_close($conn);
        exit();
    }

    $clean_ids = array_values(array_unique(array_filter(array_map('intval', $ids_terpilih), function ($v) {
        return $v > 0;
    })));
    if (empty($clean_ids)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID pelamar tidak valid.']);
        mysqli_close($conn);
        exit();
    }

    $placeholders = implode(',', array_fill(0, count($clean_ids), '?'));
    $types_ids    = str_repeat('i', count($clean_ids));

    if ($pesan_massal !== '') {
        if ($timpa_pesan === 1) {
            $sql_upd  = "UPDATE `pelamar` SET `hasil_akhir` = ?, `pesan_hasil_akhir` = ? WHERE `id_pelamar` IN ($placeholders)";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            $params   = array_merge([$status_massal, $pesan_massal], $clean_ids);
            mysqli_stmt_bind_param($stmt_upd, "ss" . $types_ids, ...$params);
        } else {
            $sql_upd  = "UPDATE `pelamar`
                         SET `hasil_akhir` = ?,
                             `pesan_hasil_akhir` = CASE
                                 WHEN `pesan_hasil_akhir` IS NULL OR TRIM(`pesan_hasil_akhir`) = '' THEN ?
                                 ELSE `pesan_hasil_akhir`
                             END
                         WHERE `id_pelamar` IN ($placeholders)";
            $stmt_upd = mysqli_prepare($conn, $sql_upd);
            $params   = array_merge([$status_massal, $pesan_massal], $clean_ids);
            mysqli_stmt_bind_param($stmt_upd, "ss" . $types_ids, ...$params);
        }
    } else {
        $sql_upd  = "UPDATE `pelamar` SET `hasil_akhir` = ? WHERE `id_pelamar` IN ($placeholders)";
        $stmt_upd = mysqli_prepare($conn, $sql_upd);
        $params   = array_merge([$status_massal], $clean_ids);
        mysqli_stmt_bind_param($stmt_upd, "s" . $types_ids, ...$params);
    }

    if ($stmt_upd && mysqli_stmt_execute($stmt_upd)) {
        $affected = mysqli_stmt_affected_rows($stmt_upd);
        mysqli_stmt_close($stmt_upd);
        echo json_encode([
            'success'       => true,
            'message'       => 'Status hasil akhir berhasil diperbarui.',
            'jumlah_dipilih'=> count($clean_ids),
            'baris_diubah'  => $affected,
            'status_massal' => $status_massal,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status hasil akhir: ' . mysqli_error($conn)]);
    }
    mysqli_close($conn);
    exit();
}

// GET: Statistik & daftar pelamar
$stat_total = 0; $stat_diterima = 0; $stat_ditolak = 0; $stat_menunggu = 0;
$q_stat = mysqli_query($conn, "SELECT hasil_akhir FROM `pelamar`");
if ($q_stat) {
    while ($rs = mysqli_fetch_assoc($q_stat)) {
        $stat_total++;
        $st = trim((string)($rs['hasil_akhir'] ?? ''));
        if ($st === 'Diterima') $stat_diterima++;
        elseif ($st === 'Tidak Diterima') $stat_ditolak++;
        else $stat_menunggu++;
    }
}

$cari          = trim((string)($input['cari'] ?? ''));
$filter_status = trim((string)($input['filter_status'] ?? ''));
$sort_by       = trim((string)($input['sort_by'] ?? 'nama'));
$sort_dir      = (strtolower((string)($input['sort_dir'] ?? 'asc')) === 'desc') ? 'DESC' : 'ASC';

$where = [];
if ($cari !== '') {
    $esc = mysqli_real_escape_string($conn, $cari);
    $where[] = "(nama_lengkap LIKE '%$esc%' OR email LIKE '%$esc%')";
}
if ($filter_status === 'Diterima' || $filter_status === 'Tidak Diterima') {
    $esc_s = mysqli_real_escape_string($conn, $filter_status);
    $where[] = "hasil_akhir = '$esc_s'";
} elseif ($filter_status === 'Belum Ditentukan') {
    $where[] = "(hasil_akhir IS NULL OR TRIM(hasil_akhir) = '' OR hasil_akhir NOT IN ('Diterima', 'Tidak Diterima'))";
}
$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

switch ($sort_by) {
    case 'email':
        $order_sql = "ORDER BY COALESCE(email, '') $sort_dir, nama_lengkap ASC";
        break;
    case 'status':
        $order_sql = "ORDER BY CASE WHEN hasil_akhir = 'Diterima' THEN 1 WHEN hasil_akhir = 'Tidak Diterima' THEN 2 ELSE 3 END $sort_dir, nama_lengkap ASC";
        break;
    case 'nama':
    default:
        $order_sql = "ORDER BY nama_lengkap $sort_dir";
        break;
}

$data = [];
$q_list = mysqli_query($conn, "SELECT id_pelamar, nama_lengkap, email, hasil_akhir, pesan_hasil_akhir FROM `pelamar` $where_sql $order_sql");
if ($q_list) {
    while ($r = mysqli_fetch_assoc($q_list)) {
        $r['id_pelamar'] = intval($r['id_pelamar']);
        $data[] = $r;
    }
}

echo json_encode([
    'success' => true,
    'stats'   => [
        'total'    => $stat_total,
        'diterima' => $stat_diterima,
        'ditolak'  => $stat_ditolak,
        'menunggu' => $stat_menunggu,
    ],
    'data'    => $data,
]);
mysqli_close($conn);