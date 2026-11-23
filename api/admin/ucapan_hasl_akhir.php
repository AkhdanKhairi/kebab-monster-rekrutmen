<?php
// File: api/ucapan_hasil_akhir.php
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
    $id_pelamar   = intval($input['id_pelamar'] ?? 0);
    $status_akhir = trim((string)($input['status_akhir'] ?? ''));
    $ucapan       = trim((string)($input['ucapan'] ?? ''));

    if ($id_pelamar <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pilih kandidat terlebih dahulu.']);
        mysqli_close($conn);
        exit();
    }
    if (!in_array($status_akhir, ['Diterima', 'Tidak Diterima'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Status akhir harus Diterima atau Tidak Diterima.']);
        mysqli_close($conn);
        exit();
    }
    if ($ucapan === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Pesan ucapan tidak boleh kosong.']);
        mysqli_close($conn);
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE `pelamar` SET `hasil_akhir` = ?, `pesan_hasil_akhir` = ? WHERE `id_pelamar` = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $status_akhir, $ucapan, $id_pelamar);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($ok) {
        echo json_encode([
            'success'      => true,
            'message'      => "Keputusan akhir berhasil disimpan sebagai {$status_akhir}.",
            'id_pelamar'   => $id_pelamar,
            'status_akhir' => $status_akhir,
            'ucapan'       => $ucapan,
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($conn)]);
    }
    mysqli_close($conn);
    exit();
}

// GET: Ambil detail kandidat atau daftar kandidat
$id_pelamar = intval($input['id_pelamar'] ?? 0);
if ($id_pelamar > 0) {
    $stmt_g = mysqli_prepare($conn, "SELECT id_pelamar, nama_lengkap, email, hasil_akhir, pesan_hasil_akhir FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_g, "i", $id_pelamar);
    mysqli_stmt_execute($stmt_g);
    $res = mysqli_stmt_get_result($stmt_g);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt_g);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Kandidat tidak ditemukan.']);
        mysqli_close($conn);
        exit();
    }

    $nama = $row['nama_lengkap'] ?? 'Kandidat';
    $st   = in_array($row['hasil_akhir'] ?? '', ['Diterima', 'Tidak Diterima'], true) ? $row['hasil_akhir'] : 'Diterima';
    $msg  = trim((string)($row['pesan_hasil_akhir'] ?? ''));
    if ($msg === '') {
        $msg = ($st === 'Diterima')
            ? "Selamat {$nama},\n\nAnda dinyatakan DITERIMA sebagai karyawan Kebab Monster.\nTerima kasih atas usaha dan proses seleksi yang telah Anda jalani. Selamat bergabung bersama tim kami!"
            : "Halo {$nama},\n\nTerima kasih telah mengikuti proses seleksi karyawan di Kebab Monster. Setelah pertimbangan, saat ini Anda belum dinyatakan DITERIMA untuk bergabung.\n\nSemoga sukses selalu untuk langkah karier Anda berikutnya.";
    }

    echo json_encode([
        'success' => true,
        'data'    => array_merge($row, [
            'id_pelamar'     => intval($row['id_pelamar']),
            'status_efektif' => $st,
            'ucapan_efektif' => $msg,
        ]),
    ]);
    mysqli_close($conn);
    exit();
}

$list = [];
$q_all = mysqli_query($conn, "SELECT id_pelamar, nama_lengkap, email, hasil_akhir, pesan_hasil_akhir FROM `pelamar` ORDER BY nama_lengkap ASC");
if ($q_all) {
    while ($r = mysqli_fetch_assoc($q_all)) {
        $r['id_pelamar'] = intval($r['id_pelamar']);
        $list[] = $r;
    }
}

echo json_encode(['success' => true, 'data' => $list]);
mysqli_close($conn);