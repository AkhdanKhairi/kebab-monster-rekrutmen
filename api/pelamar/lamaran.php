<?php
// api/pelamar/lamaran.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.');
}
$idPelamar = intval($_SESSION['user_id']);

$conn = getDbConnection();

// 1. Ambil profil pelamar & cek kelengkapan
$stmtPel = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
if (!$stmtPel) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil data pelamar.');
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
$isProfilLengkap = empty($fieldKosong);

// 2. Ambil semua lamaran milik pelamar (terpisah per id_lamaran)
$stmtList = mysqli_prepare($conn, "
    SELECT l.*, lw.posisi_pekerjaan, lw.divisi, lw.lokasi_pekerjaan, lw.shift
    FROM `lamaran` l
    JOIN `lowongan` lw ON l.id_lowongan = lw.id_lowongan
    WHERE l.id_pelamar = ?
    ORDER BY l.id_lamaran DESC
");
if (!$stmtList) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil riwayat lamaran.');
}
mysqli_stmt_bind_param($stmtList, 'i', $idPelamar);
mysqli_stmt_execute($stmtList);
$resList = mysqli_stmt_get_result($stmtList);

$lamaranList = [];
while ($row = mysqli_fetch_assoc($resList)) {
    // Prioritaskan hasil_akhir & pesan_hasil_akhir di baris lamaran masing-masing,
    // dan hanya fallback ke tabel pelamar jika hanya punya 1 lamaran (kompatibilitas data lama)
    $hasilPerLamaran = trim((string) ($row['hasil_akhir'] ?? ''));
    $pesanPerLamaran = trim((string) ($row['pesan_hasil_akhir'] ?? ''));

    $row['hasil_akhir_efektif']       = $hasilPerLamaran;
    $row['pesan_hasil_akhir_efektif'] = $pesanPerLamaran;
    $lamaranList[] = $row;
}
mysqli_stmt_close($stmtList);

// Fallback kompatibilitas jika admin lama masih mengisi hasil_akhir di tabel pelamar dan pelamar baru punya 1 lamaran
if (count($lamaranList) === 1 && $lamaranList[0]['hasil_akhir_efektif'] === '') {
    $legacyHasil = trim((string) ($pelamar['hasil_akhir'] ?? ''));
    $legacyPesan = trim((string) ($pelamar['pesan_hasil_akhir'] ?? ''));
    if ($legacyHasil !== '') {
        $lamaranList[0]['hasil_akhir_efektif'] = $legacyHasil;
    }
    if ($legacyPesan !== '') {
        $lamaranList[0]['pesan_hasil_akhir_efektif'] = $legacyPesan;
    }
}

// 3. Tentukan lamaran aktif yang dipilih (?id_lamaran=... atau default terbaru)
$selectedIdLamaran = isset($_GET['id_lamaran']) ? intval($_GET['id_lamaran']) : 0;
$selectedLamaran   = null;

if (!empty($lamaranList)) {
    if ($selectedIdLamaran > 0) {
        foreach ($lamaranList as $item) {
            if (intval($item['id_lamaran']) === $selectedIdLamaran) {
                $selectedLamaran = $item;
                break;
            }
        }
    }
    if (!$selectedLamaran) {
        $selectedLamaran = $lamaranList[0];
    }
}

// 4. Cek status sudah lamar untuk id_lowongan tertentu (opsional, untuk detail_karir.php)
$checkIdLowongan = isset($_GET['id_lowongan']) ? intval($_GET['id_lowongan']) : 0;
$existingForJob  = null;
if ($checkIdLowongan > 0) {
    foreach ($lamaranList as $item) {
        if (intval($item['id_lowongan']) === $checkIdLowongan) {
            $existingForJob = $item;
            break;
        }
    }
}

mysqli_close($conn);

$pasFoto = trim((string) ($pelamar['pas_foto'] ?? ''));

jsonResponse(200, true, 'Data lamaran pelamar berhasil diambil.', [
    'pelamar' => [
        'id_pelamar'   => intval($pelamar['id_pelamar']),
        'nama_lengkap' => $pelamar['nama_lengkap'] ?? '',
        'email'        => $pelamar['email'] ?? '',
        'no_telepon'   => $pelamar['no_telepon'] ?? '',
        'pas_foto'     => $pasFoto,
        'pas_foto_url' => $pasFoto !== '' ? '../uploads/' . $pasFoto : '',
    ],
    'kelengkapan' => [
        'is_lengkap'     => $isProfilLengkap,
        'missing_fields' => $fieldKosong,
    ],
    'lamaran_list'     => $lamaranList,
    'selected_lamaran' => $selectedLamaran,
    'existing_for_job' => $existingForJob,
]);