<?php
// api/admin/profil.php
require_once __DIR__ . '/../_helper.php';

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$conn   = getDbConnection();

if ($method === 'GET') {
    $stmt = mysqli_prepare($conn, "SELECT `id_admin`, `nama`, `username`, `jekel`, `tempat_lhr`, `tgl_lhr`, `agama`, `no_tlp`, `jabatan`, `alamat` FROM `admin` WHERE `id_admin` = ? LIMIT 1");
    if (!$stmt) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyiapkan query profil admin.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $idAdmin);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $data = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if (!$data) {
        jsonResponse(404, false, 'Data profil admin tidak ditemukan.');
    }

    jsonResponse(200, true, 'Profil admin berhasil dimuat.', ['profil' => $data]);
}

if ($method === 'POST') {
    $input = getRequestInput();

    $nama       = trim((string) ($input['nama'] ?? ''));
    $username   = trim((string) ($input['username'] ?? ''));
    $jekel      = trim((string) ($input['jekel'] ?? ''));
    $tempatLhr  = trim((string) ($input['tempat_lhr'] ?? ''));
    $tglLhr     = trim((string) ($input['tgl_lhr'] ?? ''));
    $agama      = trim((string) ($input['agama'] ?? ''));
    $noTlp      = trim((string) ($input['no_tlp'] ?? ''));
    $jabatan    = trim((string) ($input['jabatan'] ?? ''));
    $alamat     = mb_substr(trim((string) ($input['alamat'] ?? '')), 0, 80);

    if ($nama === '' || $username === '' || $jekel === '' || $tempatLhr === '' || $tglLhr === '' || $agama === '' || $noTlp === '' || $jabatan === '' || $alamat === '') {
        mysqli_close($conn);
        jsonResponse(422, false, 'Semua kolom bertanda bintang (*) wajib diisi.');
    }

    $sql = "UPDATE `admin` SET
        `nama` = ?,
        `username` = ?,
        `jekel` = ?,
        `tempat_lhr` = ?,
        `tgl_lhr` = ?,
        `agama` = ?,
        `no_tlp` = ?,
        `jabatan` = ?,
        `alamat` = ?
        WHERE `id_admin` = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyiapkan query update profil.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'sssssssssi',
        $nama,
        $username,
        $jekel,
        $tempatLhr,
        $tglLhr,
        $agama,
        $noTlp,
        $jabatan,
        $alamat,
        $idAdmin
    );

    if (!mysqli_stmt_execute($stmt)) {
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal memperbarui data: ' . $err);
    }

    mysqli_stmt_close($stmt);
    $_SESSION['nama']     = $nama;
    $_SESSION['username'] = $username;

    $stmtGet = mysqli_prepare($conn, "SELECT `id_admin`, `nama`, `username`, `jekel`, `tempat_lhr`, `tgl_lhr`, `agama`, `no_tlp`, `jabatan`, `alamat` FROM `admin` WHERE `id_admin` = ? LIMIT 1");
    $updated = null;
    if ($stmtGet) {
        mysqli_stmt_bind_param($stmtGet, 'i', $idAdmin);
        mysqli_stmt_execute($stmtGet);
        $resGet = mysqli_stmt_get_result($stmtGet);
        $updated = $resGet ? mysqli_fetch_assoc($resGet) : null;
        mysqli_stmt_close($stmtGet);
    }

    mysqli_close($conn);
    jsonResponse(200, true, 'Data profil HR/Admin berhasil diperbarui!', ['profil' => $updated]);
}

mysqli_close($conn);
jsonResponse(405, false, 'Method tidak diizinkan.');