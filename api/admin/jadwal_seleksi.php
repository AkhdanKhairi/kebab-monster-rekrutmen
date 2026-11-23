<?php
// File: api/jadwal_seleksi.php
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
$input  = ($method === 'POST' || $method === 'DELETE') ? (json_decode(file_get_contents('php://input'), true) ?: $_POST) : $_GET;

if ($method === 'DELETE' || ($method === 'POST' && ($input['action'] ?? '') === 'delete')) {
    $id_pel_del = intval($input['id_pelamar'] ?? ($_GET['id_pelamar'] ?? 0));
    if ($id_pel_del <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id_pelamar tidak valid.']);
        mysqli_close($conn);
        exit();
    }
    $stmt_d = mysqli_prepare($conn, "DELETE FROM `jadwal_seleksi` WHERE `id_pelamar` = ?");
    mysqli_stmt_bind_param($stmt_d, "i", $id_pel_del);
    $ok = mysqli_stmt_execute($stmt_d);
    mysqli_stmt_close($stmt_d);
    echo json_encode(['success' => (bool)$ok, 'message' => $ok ? 'Jadwal seleksi pelamar berhasil dihapus.' : 'Gagal menghapus jadwal.']);
    mysqli_close($conn);
    exit();
}

if ($method === 'POST') {
    $pelamar_ids    = $input['id_pelamar'] ?? [];
    $jenis_tes      = trim((string)($input['jenis_tes'] ?? 'Psikotes'));
    $tanggal_jadwal = trim((string)($input['tanggal_jadwal'] ?? ''));
    $waktu_jadwal   = trim((string)($input['waktu_jadwal'] ?? ''));
    $metode         = trim((string)($input['metode'] ?? 'Offline / Datang Langsung'));
    $lokasi_link    = trim((string)($input['lokasi_atau_link'] ?? ''));
    $catatan_hr     = trim((string)($input['catatan_hr'] ?? ''));
    $status_email   = in_array($input['status_email'] ?? '', ['Belum Dikirim', 'Terkirim', 'Gagal'], true)
        ? $input['status_email']
        : 'Belum Dikirim';

    if (!in_array($jenis_tes, ['Psikotes', 'Interview'], true)) {
        $jenis_tes = 'Psikotes';
    }
    if (!in_array($metode, ['Offline / Datang Langsung', 'Online / Zoom / Meet'], true)) {
        $metode = 'Offline / Datang Langsung';
    }
    if (!is_array($pelamar_ids) || empty($pelamar_ids) || $tanggal_jadwal === '' || $waktu_jadwal === '' || $lokasi_link === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Lengkapi id_pelamar, tanggal_jadwal, waktu_jadwal, dan lokasi_atau_link.']);
        mysqli_close($conn);
        exit();
    }

    $berhasil = 0;
    $stmt_ins = mysqli_prepare(
        $conn,
        "INSERT INTO `jadwal_seleksi`
        (`id_pelamar`, `jenis_tes`, `tanggal_jadwal`, `waktu_jadwal`, `metode`, `lokasi_atau_link`, `catatan_hr`, `status_kehadiran`, `status_email`)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Dijadwalkan', ?)"
    );
    foreach ($pelamar_ids as $raw_id) {
        $id_pel = intval($raw_id);
        if ($id_pel <= 0) continue;
        mysqli_stmt_bind_param($stmt_ins, "isssssss", $id_pel, $jenis_tes, $tanggal_jadwal, $waktu_jadwal, $metode, $lokasi_link, $catatan_hr, $status_email);
        if (mysqli_stmt_execute($stmt_ins)) {
            $berhasil++;
        }
    }
    if ($stmt_ins) mysqli_stmt_close($stmt_ins);

    echo json_encode([
        'success'          => true,
        'message'          => "Berhasil menjadwalkan {$berhasil} pelamar.",
        'jumlah_terjadwal' => $berhasil,
    ]);
    mysqli_close($conn);
    exit();
}

// GET: Ambil daftar pelamar belum & sudah diundang
$cari = trim((string)($input['cari'] ?? ''));
$where_sql = '';
if ($cari !== '') {
    $esc = mysqli_real_escape_string($conn, $cari);
    $where_sql = "WHERE (p.nama_lengkap LIKE '%$esc%' OR p.email LIKE '%$esc%' OR p.no_telepon LIKE '%$esc%')";
}

$belum = [];
$q_belum = mysqli_query($conn, "
    SELECT p.id_pelamar, p.nama_lengkap, p.email, p.no_telepon, COUNT(j.id_jadwal) AS jml_undangan
    FROM `pelamar` p
    LEFT JOIN `jadwal_seleksi` j ON p.id_pelamar = j.id_pelamar
    $where_sql
    GROUP BY p.id_pelamar
    HAVING jml_undangan = 0
    ORDER BY p.nama_lengkap ASC
");
if ($q_belum) while ($r = mysqli_fetch_assoc($q_belum)) $belum[] = $r;

$sudah = [];
$q_sudah = mysqli_query($conn, "
    SELECT p.id_pelamar, p.nama_lengkap, p.email, p.no_telepon,
           COUNT(j.id_jadwal) AS jml_undangan,
           MAX(j.jenis_tes) AS tahap_terakhir,
           MAX(CONCAT(j.tanggal_jadwal, ' ', j.waktu_jadwal)) AS jadwal_terdekat,
           MAX(j.status_email) AS status_email_terakhir
    FROM `pelamar` p
    INNER JOIN `jadwal_seleksi` j ON p.id_pelamar = j.id_pelamar
    $where_sql
    GROUP BY p.id_pelamar
    HAVING jml_undangan > 0
    ORDER BY p.nama_lengkap ASC
");
if ($q_sudah) while ($r = mysqli_fetch_assoc($q_sudah)) $sudah[] = $r;

echo json_encode([
    'success'       => true,
    'belum_diundang'=> $belum,
    'sudah_diundang'=> $sudah,
]);
mysqli_close($conn);