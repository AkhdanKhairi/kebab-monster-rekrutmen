<?php
// api/pelamar/lamar.php
require_once __DIR__ . '/../_helper.php';

requireMethod('POST');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.');
}
$idPelamar = intval($_SESSION['user_id']);

$input      = getRequestInput();
$idLowongan = isset($input['id_lowongan']) ? intval($input['id_lowongan']) : 0;

if ($idLowongan <= 0) {
    jsonResponse(400, false, 'Lowongan tidak valid.');
}

$conn = getDbConnection();

// 1. Cek profil & kelengkapan data wajib pelamar
$stmtPel = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
if (!$stmtPel) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memeriksa data pelamar.');
}
mysqli_stmt_bind_param($stmtPel, 'i', $idPelamar);
mysqli_stmt_execute($stmtPel);
$resPel  = mysqli_stmt_get_result($stmtPel);
$pelamar = $resPel ? mysqli_fetch_assoc($resPel) : null;
mysqli_stmt_close($stmtPel);

if (!$pelamar) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
}

$wajibCek = [
    'nama_lengkap'   => 'Nama Lengkap',
    'no_telepon'     => 'No Handphone',
    'jenis_kelamin'  => 'Jenis Kelamin',
    'tanggal_lahir'  => 'Tanggal Lahir',
    'no_ktp'         => 'No KTP',
    'alamat'         => 'Alamat Lengkap',
    'pend_terakhir'  => 'Pendidikan Terakhir',
    'pend_institusi' => 'Nama Sekolah / Universitas',
    'ktp'            => 'Upload KTP',
    'pas_foto'       => 'Upload Pas Foto',
];

$fieldKosong = [];
foreach ($wajibCek as $kolom => $label) {
    if (trim((string) ($pelamar[$kolom] ?? '')) === '') {
        $fieldKosong[] = $label;
    }
}

if (!empty($fieldKosong)) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Profil & berkas wajib belum lengkap. Lengkapi terlebih dahulu sebelum melamar.', [
        'missing_fields' => $fieldKosong,
    ]);
}

// 2. Cek apakah lowongan ada dan masih aktif
$stmtLow = mysqli_prepare($conn, "SELECT id_lowongan, posisi_pekerjaan, status_aktif FROM `lowongan` WHERE `id_lowongan` = ? LIMIT 1");
if (!$stmtLow) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memeriksa data lowongan.');
}
mysqli_stmt_bind_param($stmtLow, 'i', $idLowongan);
mysqli_stmt_execute($stmtLow);
$resLow   = mysqli_stmt_get_result($stmtLow);
$lowongan = $resLow ? mysqli_fetch_assoc($resLow) : null;
mysqli_stmt_close($stmtLow);

if (!$lowongan) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Lowongan tidak ditemukan.');
}

$rawStatus = strtolower(trim((string) ($lowongan['status_aktif'] ?? 'Aktif')));
$isAktif   = !in_array($rawStatus, [
    '0', 'nonaktif', 'tidak aktif', 'inactive', 'non-aktif', 'tutup', 'closed', 'false', 'tidak'
], true);

if (!$isAktif) {
    mysqli_close($conn);
    jsonResponse(400, false, 'Lowongan ini sedang nonaktif/ditutup sehingga lamaran tidak dapat dikirim.');
}

// 3. Cek apakah pelamar sudah melamar posisi lowongan INI sebelumnya
//    (Pelamar yang sama TETAP BISA melamar ke id_lowongan lain sebagai record terpisah)
$stmtCek = mysqli_prepare($conn, "SELECT id_lamaran FROM `lamaran` WHERE `id_pelamar` = ? AND `id_lowongan` = ? LIMIT 1");
if (!$stmtCek) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memeriksa riwayat lamaran.');
}
mysqli_stmt_bind_param($stmtCek, 'ii', $idPelamar, $idLowongan);
mysqli_stmt_execute($stmtCek);
$resCek = mysqli_stmt_get_result($stmtCek);

if ($resCek && mysqli_num_rows($resCek) > 0) {
    $existing = mysqli_fetch_assoc($resCek);
    mysqli_stmt_close($stmtCek);
    mysqli_close($conn);
    jsonResponse(409, false, 'Anda sudah pernah mengirimkan lamaran untuk posisi ini!', [
        'id_lamaran' => intval($existing['id_lamaran']),
    ]);
}
mysqli_stmt_close($stmtCek);

// 4. Simpan record lamaran baru yang berdiri sendiri per posisi
$stmtIns = mysqli_prepare(
    $conn,
    "INSERT INTO `lamaran` (`id_lowongan`, `id_pelamar`, `tgl_melamar`, `status_lamaran`) VALUES (?, ?, NOW(), 'Pending')"
);
if (!$stmtIns) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query pengiriman lamaran.');
}

mysqli_stmt_bind_param($stmtIns, 'ii', $idLowongan, $idPelamar);
if (!mysqli_stmt_execute($stmtIns)) {
    mysqli_stmt_close($stmtIns);
    mysqli_close($conn);
    jsonResponse(500, false, 'Terjadi kesalahan sistem, gagal mengirim lamaran.');
}

$newLamaranId = mysqli_insert_id($conn);
mysqli_stmt_close($stmtIns);
mysqli_close($conn);

jsonResponse(201, true, 'Lamaran berhasil dikirim! HRD akan segera meninjau profil dan berkas Anda.', [
    'lamaran' => [
        'id_lamaran'       => $newLamaranId,
        'id_lowongan'      => $idLowongan,
        'posisi_pekerjaan' => $lowongan['posisi_pekerjaan'] ?? '',
        'status_lamaran'   => 'Pending',
    ],
]);