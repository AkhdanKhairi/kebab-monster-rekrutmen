<?php
// api/pelamar/profile.php
require_once __DIR__ . '/../_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Sesi pelamar tidak valid. Silakan login terlebih dahulu.');
}

$idPelamar = intval($_SESSION['user_id']);
$method    = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($method, ['GET', 'POST'], true)) {
    jsonResponse(405, false, 'Method tidak diizinkan. Gunakan GET atau POST.');
}

$conn = getDbConnection();

// Ambil daftar kolom aktual pada tabel pelamar
$kolomPelamar = [];
$qCols = mysqli_query($conn, "SHOW COLUMNS FROM `pelamar`");
if ($qCols) {
    while ($c = mysqli_fetch_assoc($qCols)) {
        $kolomPelamar[] = $c['Field'];
    }
}

function pastikanKolomPelamarApi($conn, array &$kolomPelamar, string $namaKolom, string $definisiSql = "VARCHAR(255) NULL"): bool {
    if (in_array($namaKolom, $kolomPelamar, true)) {
        return true;
    }
    $sqlAlter = "ALTER TABLE `pelamar` ADD COLUMN `$namaKolom` $definisiSql";
    if (@mysqli_query($conn, $sqlAlter)) {
        $kolomPelamar[] = $namaKolom;
        return true;
    }
    return false;
}

function ambilDataPelamarApi($conn, int $idPelamar): ?array {
    $stmt = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
    if (!$stmt) {
        return null;
    }
    mysqli_stmt_bind_param($stmt, 'i', $idPelamar);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if ($row) {
        unset($row['password']);
    }
    return $row ?: null;
}

function hitungKelengkapanProfilApi(array $data): array {
    $groups = [
        'datadiri'   => ['nama_lengkap', 'no_telepon', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_nikah', 'kode_pos', 'kota', 'no_ktp', 'kewarganegaraan', 'berat_badan', 'tinggi_badan', 'alamat'],
        'darurat'    => ['darurat_nama', 'darurat_hubungan', 'darurat_telp1', 'darurat_telp2', 'darurat_alamat'],
        'pendidikan' => ['pend_terakhir', 'pend_institusi', 'pend_jurusan', 'pend_nilai', 'pend_mulai', 'pend_selesai'],
        'kerja'      => ['kerja_perusahaan', 'kerja_telp', 'kerja_jabatan', 'kerja_jobdesk', 'kerja_alamat', 'kerja_alasan', 'kerja2_perusahaan', 'kerja2_telp', 'kerja2_jabatan', 'kerja2_jobdesk', 'kerja2_alamat', 'kerja2_alasan'],
        'magang'     => ['magang1_instansi', 'magang1_posisi', 'magang1_jobdesk'],
        'berkas'     => ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto'],
    ];

    $scores = [];
    $sum    = 0;
    foreach ($groups as $key => $fields) {
        $filled = 0;
        foreach ($fields as $f) {
            if (!empty(trim((string) ($data[$f] ?? '')))) {
                $filled++;
            }
        }
        $score        = (int) round(($filled / count($fields)) * 100);
        $scores[$key] = $score;
        $sum         += $score;
    }

    $scores['total'] = (int) round($sum / count($groups));

    $wajibMelamar = [
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
    foreach ($wajibMelamar as $kolom => $label) {
        if (empty(trim((string) ($data[$kolom] ?? '')))) {
            $fieldKosong[] = $label;
        }
    }

    return [
        'scores'               => $scores,
        'siap_melamar'         => empty($fieldKosong),
        'field_kosong_melamar' => $fieldKosong,
    ];
}

function cekInfoBerkasApi(array $data): array {
    $fileCols = ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto', 'magang1_sertifikat', 'magang2_sertifikat'];
    $uploadsDir = __DIR__ . '/../../uploads/';
    $info = [];

    foreach ($fileCols as $col) {
        $namaFile = trim((string) ($data[$col] ?? ''));
        $adaFile  = ($namaFile !== '' && file_exists($uploadsDir . $namaFile));
        $ext      = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        $info[$col] = [
            'filename' => $namaFile,
            'exists'   => $adaFile,
            'ext'      => $ext,
            'is_image' => in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true),
            'url'      => $namaFile !== '' ? '../uploads/' . rawurlencode($namaFile) : '',
        ];
    }
    return $info;
}

// =========================================================================
// GET: Ambil Profil Pelamar
// =========================================================================
if ($method === 'GET') {
    $data = ambilDataPelamarApi($conn, $idPelamar);
    mysqli_close($conn);

    if (!$data) {
        jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
    }

    $_SESSION['email'] = $data['email'] ?? '';
    $_SESSION['nama']  = $data['nama_lengkap'] ?? 'Pelamar';

    jsonResponse(200, true, 'Data profil pelamar berhasil diambil.', [
        'pelamar'     => $data,
        'kelengkapan' => hitungKelengkapanProfilApi($data),
        'files'       => cekInfoBerkasApi($data),
    ]);
}

// =========================================================================
// POST: Simpan Perubahan Profil + Upload Berkas
// =========================================================================
$existingData = ambilDataPelamarApi($conn, $idPelamar);
if (!$existingData) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
}

// Jika request dikirim sebagai JSON tanpa file, dukung juga fallback dari php://input
$input = $_POST;
if (empty($input)) {
    $rawBody = file_get_contents('php://input');
    $jsonBody = json_decode($rawBody, true);
    if (is_array($jsonBody)) {
        $input = $jsonBody;
    }
}

$emailInput = trim((string) ($input['email'] ?? ''));

$wajibTeks = [
    // 1. Data Diri
    'nama_lengkap'    => 'Nama Lengkap',
    'email'           => 'Email',
    'no_telepon'      => 'No Handphone',
    'kode_pos'        => 'Kode Pos',
    'jenis_kelamin'   => 'Jenis Kelamin',
    'kota'            => 'Kota / Kabupaten',
    'tempat_lahir'    => 'Tempat Lahir',
    'kecamatan'       => 'Kecamatan',
    'tanggal_lahir'   => 'Tanggal Lahir',
    'agama'           => 'Agama',
    'no_ktp'          => 'No KTP',
    'status_nikah'    => 'Status Pernikahan',
    'kewarganegaraan' => 'Kewarganegaraan',
    'jumlah_anak'     => 'Jumlah Anak',
    'berat_badan'     => 'Berat Badan',
    'tinggi_badan'    => 'Tinggi Badan',
    'alamat'          => 'Alamat Lengkap',

    // 2. Kontak Darurat
    'darurat_nama'     => 'Nama Keluarga (Kontak Darurat)',
    'darurat_hubungan' => 'Hubungan Keluarga',
    'darurat_telp1'    => 'No Telepon 1 (Kontak Darurat)',
    'darurat_telp2'    => 'No Telepon 2 (Kontak Darurat)',
    'darurat_alamat'   => 'Alamat Keluarga (Kontak Darurat)',

    // 3. Pendidikan
    'pend_terakhir'  => 'Pendidikan Terakhir',
    'pend_institusi' => 'Nama Sekolah / Universitas',
    'pend_jurusan'   => 'Jurusan',
    'pend_nilai'     => 'Nilai / IPK',
    'pend_mulai'     => 'Mulai Tanggal Pendidikan',
    'pend_selesai'   => 'Selesai Tanggal Pendidikan',

    // 4. Pengalaman Kerja 1 & 2
    'kerja_perusahaan'  => 'Nama Perusahaan (Kerja 1)',
    'kerja_telp'        => 'No. Telepon Perusahaan (Kerja 1)',
    'kerja_jabatan'     => 'Jabatan (Kerja 1)',
    'kerja_jobdesk'     => 'Jobdesk (Kerja 1)',
    'kerja_alamat'      => 'Alamat Perusahaan (Kerja 1)',
    'kerja_alasan'      => 'Alasan Berhenti (Kerja 1)',

    'kerja2_perusahaan' => 'Nama Perusahaan (Kerja 2)',
    'kerja2_telp'       => 'No. Telepon Perusahaan (Kerja 2)',
    'kerja2_jabatan'    => 'Jabatan (Kerja 2)',
    'kerja2_jobdesk'    => 'Jobdesk (Kerja 2)',
    'kerja2_alamat'     => 'Alamat Perusahaan (Kerja 2)',
    'kerja2_alasan'     => 'Alasan Berhenti (Kerja 2)',

    // 5. Internship / Magang 1 & 2
    'magang1_instansi' => 'Nama Instansi (Magang 1)',
    'magang1_posisi'   => 'Posisi / Peran (Magang 1)',
    'magang1_jobdesk'  => 'Jobdesk (Magang 1)',

    'magang2_instansi' => 'Nama Instansi (Magang 2)',
    'magang2_posisi'   => 'Posisi / Peran (Magang 2)',
    'magang2_jobdesk'  => 'Jobdesk (Magang 2)',
];

$fieldKosong = [];
foreach ($wajibTeks as $key => $label) {
    $val = trim((string) ($input[$key] ?? ''));
    if ($val === '') {
        $fieldKosong[] = $label;
    }
}

$wajibBerkas = [
    'ktp'      => 'Upload KTP',
    'ijazah'   => 'Upload Ijazah',
    'skck'     => 'Upload SKCK',
    'kk'       => 'Upload Kartu Keluarga (KK)',
    'pas_foto' => 'Upload Pas Foto',
];
foreach ($wajibBerkas as $fKey => $fLabel) {
    $adaUploadBaru = isset($_FILES[$fKey]) && $_FILES[$fKey]['error'] === UPLOAD_ERR_OK;
    $adaFileLama   = !empty(trim((string) ($existingData[$fKey] ?? '')));
    if (!$adaUploadBaru && !$adaFileLama) {
        $fieldKosong[] = $fLabel;
    }
}

if (!empty($fieldKosong)) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Data tidak dapat disimpan karena masih ada bagian yang kosong: ' . implode(', ', $fieldKosong) . '. (Untuk pengalaman kerja/magang yang tidak ada, isi dengan "Tidak ada").', [
        'field_kosong' => $fieldKosong,
    ]);
}

if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
    mysqli_close($conn);
    jsonResponse(422, false, 'Format alamat email tidak valid!');
}

$stmtEmail = mysqli_prepare($conn, "SELECT `id_pelamar` FROM `pelamar` WHERE `email` = ? AND `id_pelamar` != ? LIMIT 1");
if ($stmtEmail) {
    mysqli_stmt_bind_param($stmtEmail, 'si', $emailInput, $idPelamar);
    mysqli_stmt_execute($stmtEmail);
    $resEmail = mysqli_stmt_get_result($stmtEmail);
    if ($resEmail && mysqli_num_rows($resEmail) > 0) {
        mysqli_stmt_close($stmtEmail);
        mysqli_close($conn);
        jsonResponse(409, false, 'Alamat email tersebut sudah digunakan oleh akun pelamar lain!');
    }
    mysqli_stmt_close($stmtEmail);
}

$fieldValues = [
    'nama_lengkap'    => ['val' => trim((string) ($input['nama_lengkap'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'email'           => ['val' => $emailInput, 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'no_telepon'      => ['val' => trim((string) ($input['no_telepon'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'jenis_kelamin'   => ['val' => trim((string) ($input['jenis_kelamin'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'tempat_lahir'    => ['val' => trim((string) ($input['tempat_lahir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'tanggal_lahir'   => ['val' => trim((string) ($input['tanggal_lahir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'agama'           => ['val' => trim((string) ($input['agama'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'status_nikah'    => ['val' => trim((string) ($input['status_nikah'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kode_pos'        => ['val' => trim((string) ($input['kode_pos'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(20) NULL'],
    'kota'            => ['val' => trim((string) ($input['kota'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'kecamatan'       => ['val' => trim((string) ($input['kecamatan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'no_ktp'          => ['val' => trim((string) ($input['no_ktp'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kewarganegaraan' => ['val' => trim((string) ($input['kewarganegaraan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(50) NULL'],
    'jumlah_anak'     => ['val' => intval($input['jumlah_anak'] ?? 0), 'type' => 'int', 'def' => 'INT DEFAULT 0'],
    'berat_badan'     => ['val' => trim((string) ($input['berat_badan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'tinggi_badan'    => ['val' => trim((string) ($input['tinggi_badan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'alamat'          => ['val' => trim((string) ($input['alamat'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],

    'darurat_nama'     => ['val' => trim((string) ($input['darurat_nama'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'darurat_hubungan' => ['val' => trim((string) ($input['darurat_hubungan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'darurat_telp1'    => ['val' => trim((string) ($input['darurat_telp1'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'darurat_telp2'    => ['val' => trim((string) ($input['darurat_telp2'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'darurat_alamat'   => ['val' => trim((string) ($input['darurat_alamat'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],

    'pend_terakhir'  => ['val' => trim((string) ($input['pend_terakhir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(50) NULL'],
    'pend_institusi' => ['val' => trim((string) ($input['pend_institusi'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'pend_jurusan'   => ['val' => trim((string) ($input['pend_jurusan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'pend_nilai'     => ['val' => trim((string) ($input['pend_nilai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(50) NULL'],
    'pend_mulai'     => ['val' => trim((string) ($input['pend_mulai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'pend_selesai'   => ['val' => trim((string) ($input['pend_selesai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],

    'kerja_perusahaan' => ['val' => trim((string) ($input['kerja_perusahaan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'kerja_telp'       => ['val' => trim((string) ($input['kerja_telp'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja_mulai'      => ['val' => trim((string) ($input['kerja_mulai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja_akhir'      => ['val' => trim((string) ($input['kerja_akhir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja_jabatan'    => ['val' => trim((string) ($input['kerja_jabatan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'kerja_jobdesk'    => ['val' => trim((string) ($input['kerja_jobdesk'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],
    'kerja_alamat'     => ['val' => trim((string) ($input['kerja_alamat'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],
    'kerja_alasan'     => ['val' => trim((string) ($input['kerja_alasan'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],

    'kerja2_perusahaan' => ['val' => trim((string) ($input['kerja2_perusahaan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'kerja2_telp'       => ['val' => trim((string) ($input['kerja2_telp'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja2_mulai'      => ['val' => trim((string) ($input['kerja2_mulai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja2_akhir'      => ['val' => trim((string) ($input['kerja2_akhir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'kerja2_jabatan'    => ['val' => trim((string) ($input['kerja2_jabatan'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'kerja2_jobdesk'    => ['val' => trim((string) ($input['kerja2_jobdesk'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],
    'kerja2_alamat'     => ['val' => trim((string) ($input['kerja2_alamat'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],
    'kerja2_alasan'     => ['val' => trim((string) ($input['kerja2_alasan'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],

    'magang1_instansi' => ['val' => trim((string) ($input['magang1_instansi'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'magang1_mulai'    => ['val' => trim((string) ($input['magang1_mulai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'magang1_akhir'    => ['val' => trim((string) ($input['magang1_akhir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'magang1_posisi'   => ['val' => trim((string) ($input['magang1_posisi'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'magang1_jobdesk'  => ['val' => trim((string) ($input['magang1_jobdesk'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],

    'magang2_instansi' => ['val' => trim((string) ($input['magang2_instansi'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(150) NULL'],
    'magang2_mulai'    => ['val' => trim((string) ($input['magang2_mulai'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'magang2_akhir'    => ['val' => trim((string) ($input['magang2_akhir'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(30) NULL'],
    'magang2_posisi'   => ['val' => trim((string) ($input['magang2_posisi'] ?? '')), 'type' => 'str', 'def' => 'VARCHAR(100) NULL'],
    'magang2_jobdesk'  => ['val' => trim((string) ($input['magang2_jobdesk'] ?? '')), 'type' => 'str', 'def' => 'TEXT NULL'],
];

$setParts = [];
foreach ($fieldValues as $colName => $cfg) {
    if (!pastikanKolomPelamarApi($conn, $kolomPelamar, $colName, $cfg['def'])) {
        continue;
    }
    if ($cfg['type'] === 'int') {
        $setParts[] = "`$colName` = " . intval($cfg['val']);
    } else {
        $escaped    = mysqli_real_escape_string($conn, (string) $cfg['val']);
        $setParts[] = "`$colName` = '$escaped'";
    }
}

if (!empty($setParts)) {
    $updateSql = "UPDATE `pelamar` SET " . implode(', ', $setParts) . " WHERE `id_pelamar` = $idPelamar";
    if (!mysqli_query($conn, $updateSql)) {
        $err = mysqli_error($conn);
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyimpan data profil: ' . $err);
    }
}

// Penanganan Upload File
$targetDir = __DIR__ . '/../../uploads/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$fileFields = ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto', 'magang1_sertifikat', 'magang2_sertifikat'];
$uploadErrors = [];

foreach ($fileFields as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES[$field]['name'];
        $fileTmp  = $_FILES[$field]['tmp_name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ($field === 'pas_foto')
            ? ['jpg', 'jpeg', 'png']
            : ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($fileExt, $allowedExtensions, true)) {
            $uploadErrors[] = "Format file $field tidak didukung (gunakan " . strtoupper(implode(', ', $allowedExtensions)) . ").";
            continue;
        }

        $newFilename = $field . '_' . $idPelamar . '_' . time() . '.' . $fileExt;
        $targetFile  = $targetDir . $newFilename;

        if (move_uploaded_file($fileTmp, $targetFile)) {
            if (pastikanKolomPelamarApi($conn, $kolomPelamar, $field, "VARCHAR(255) NULL")) {
                $safeFile = mysqli_real_escape_string($conn, $newFilename);
                mysqli_query($conn, "UPDATE `pelamar` SET `$field` = '$safeFile' WHERE `id_pelamar` = $idPelamar");
            }
        } else {
            $uploadErrors[] = "Gagal mengunggah file $field ke server.";
        }
    }
}

if (!empty($uploadErrors)) {
    $updated = ambilDataPelamarApi($conn, $idPelamar) ?? [];
    mysqli_close($conn);
    jsonResponse(422, false, implode(' ', $uploadErrors), [
        'pelamar'     => $updated,
        'kelengkapan' => hitungKelengkapanProfilApi($updated),
        'files'       => cekInfoBerkasApi($updated),
    ]);
}

$updatedData = ambilDataPelamarApi($conn, $idPelamar) ?? [];
$_SESSION['email'] = $updatedData['email'] ?? '';
$_SESSION['nama']  = $updatedData['nama_lengkap'] ?? 'Pelamar';
mysqli_close($conn);

jsonResponse(200, true, 'Profil dan dokumen berhasil diperbarui!', [
    'pelamar'     => $updatedData,
    'kelengkapan' => hitungKelengkapanProfilApi($updatedData),
    'files'       => cekInfoBerkasApi($updatedData),
]);