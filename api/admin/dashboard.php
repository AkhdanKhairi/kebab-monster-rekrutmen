<?php
// api/admin/dashboard.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
if (empty($_SESSION['user_id']) || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$conn = getDbConnection();

$jmlScreening      = 0;
$jmlPsikotes       = 0;
$jmlHasilPsikotes = 0;
$jmlInterview      = 0;
$jmlKeputusan      = 0;
$lowonganList      = [];

$allTables = [];
$qTbl = mysqli_query($conn, "SHOW TABLES");
if ($qTbl) {
    while ($t = mysqli_fetch_row($qTbl)) {
        $allTables[] = $t[0];
    }
}

// 1. Deteksi otomatis tabel lamaran
$tblLamaran  = '';
$colsLamaran = [];
foreach ($allTables as $tbl) {
    if ($tbl === 'pelamar' || $tbl === 'admin') {
        continue;
    }
    $cols = [];
    $qC = mysqli_query($conn, "SHOW COLUMNS FROM `{$tbl}`");
    if ($qC) {
        while ($c = mysqli_fetch_assoc($qC)) {
            $cols[] = $c['Field'];
        }
    }
    if (in_array('tgl_melamar', $cols, true) || (in_array('id_pelamar', $cols, true) && count($cols) <= 15)) {
        $tblLamaran  = $tbl;
        $colsLamaran = $cols;
        if (in_array('tgl_melamar', $cols, true)) {
            break;
        }
    }
}

if ($tblLamaran !== '') {
    $statusCol = '';
    foreach (['status', 'status_lamaran', 'status_seleksi', 'tahap', 'progres'] as $optS) {
        if (in_array($optS, $colsLamaran, true)) {
            $statusCol = $optS;
            break;
        }
    }

    if ($statusCol !== '') {
        $q1 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`
            WHERE `{$statusCol}` LIKE '%Screening%'
               OR `{$statusCol}` LIKE '%Baru%'
               OR `{$statusCol}` LIKE '%Menunggu%'
               OR `{$statusCol}` LIKE '%Pending%'
               OR `{$statusCol}` LIKE '%Administrasi%'
               OR `{$statusCol}` IS NULL
               OR `{$statusCol}` = ''");
        if ($q1) {
            $jmlScreening = (int) (mysqli_fetch_assoc($q1)['jml'] ?? 0);
        }

        $q2 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`
            WHERE (`{$statusCol}` LIKE '%Psikotes%' OR `{$statusCol}` LIKE '%Tes%')
              AND `{$statusCol}` NOT LIKE '%Dinilai%'
              AND `{$statusCol}` NOT LIKE '%Hasil%'
              AND `{$statusCol}` NOT LIKE '%Selesai%'");
        if ($q2) {
            $jmlPsikotes = (int) (mysqli_fetch_assoc($q2)['jml'] ?? 0);
        }

        $q3 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`
            WHERE `{$statusCol}` LIKE '%Dinilai%'
               OR `{$statusCol}` LIKE '%Hasil%'
               OR `{$statusCol}` LIKE '%Lolos Psikotes%'");
        if ($q3) {
            $jmlHasilPsikotes = (int) (mysqli_fetch_assoc($q3)['jml'] ?? 0);
        }

        $q4 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`
            WHERE `{$statusCol}` LIKE '%Interview%'
               OR `{$statusCol}` LIKE '%Wawancara%'");
        if ($q4) {
            $jmlInterview = (int) (mysqli_fetch_assoc($q4)['jml'] ?? 0);
        }

        $q5 = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`
            WHERE `{$statusCol}` LIKE '%Keputusan%'
               OR `{$statusCol}` LIKE '%Diterima%'
               OR `{$statusCol}` LIKE '%Ditolak%'
               OR `{$statusCol}` LIKE '%Lolos%'");
        if ($q5) {
            $jmlKeputusan = (int) (mysqli_fetch_assoc($q5)['jml'] ?? 0);
        }
    } else {
        $qAll = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM `{$tblLamaran}`");
        if ($qAll) {
            $jmlScreening = (int) (mysqli_fetch_assoc($qAll)['jml'] ?? 0);
        }
    }
}

// 2. Ambil data Lowongan secara dinamis
$tblLowongan = '';
foreach (['lowongan', 'tb_lowongan', 'tbl_lowongan', 'data_lowongan', 'lowongan_kerja', 'loker', 'posisi'] as $candL) {
    if (in_array($candL, $allTables, true)) {
        $tblLowongan = $candL;
        break;
    }
}

if ($tblLowongan !== '') {
    $colsLowongan = [];
    $resC = mysqli_query($conn, "SHOW COLUMNS FROM `{$tblLowongan}`");
    if ($resC) {
        while ($rc = mysqli_fetch_assoc($resC)) {
            $colsLowongan[] = $rc['Field'];
        }
    }

    $pkLowongan = $colsLowongan[0] ?? 'id_lowongan';
    $titleCol   = $colsLowongan[1] ?? ($colsLowongan[0] ?? 'nama_lowongan');
    foreach (['nama_lowongan', 'posisi', 'judul_lowongan', 'judul', 'nama', 'nama_posisi', 'job_title'] as $opt) {
        if (in_array($opt, $colsLowongan, true)) {
            $titleCol = $opt;
            break;
        }
    }

    $fkInLamaran = '';
    if ($tblLamaran !== '') {
        foreach ([$pkLowongan, 'id_lowongan', 'lowongan_id', 'id_loker', 'id_posisi'] as $fkOpt) {
            if (in_array($fkOpt, $colsLamaran, true)) {
                $fkInLamaran = $fkOpt;
                break;
            }
        }
    }

    $resLow = mysqli_query($conn, "SELECT * FROM `{$tblLowongan}`");
    if ($resLow) {
        while ($rowL = mysqli_fetch_assoc($resLow)) {
            $namaJob = (string) ($rowL[$titleCol] ?? 'Staff Kebab Monster');
            $idL     = $rowL[$pkLowongan] ?? '';
            $pendaftarCount = 0;

            if ($tblLamaran !== '' && $fkInLamaran !== '' && $idL !== '') {
                $idLSafe = mysqli_real_escape_string($conn, (string) $idL);
                $qCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `{$tblLamaran}` WHERE `{$fkInLamaran}` = '{$idLSafe}'");
                if ($qCount) {
                    $pendaftarCount = (int) (mysqli_fetch_assoc($qCount)['total'] ?? 0);
                }
            }

            $lowonganList[] = [
                'nama'  => $namaJob,
                'total' => $pendaftarCount,
            ];
        }
    }
}

mysqli_close($conn);

$chartLabels = array_map(fn($item) => $item['nama'], $lowonganList);
$chartValues = array_map(fn($item) => $item['total'], $lowonganList);
$maxPendaftar = !empty($chartValues) ? max($chartValues) : 0;

jsonResponse(200, true, 'Data dashboard HR/Admin berhasil dimuat.', [
    'ringkasan' => [
        'jml_screening'      => $jmlScreening,
        'jml_psikotes'       => $jmlPsikotes,
        'jml_hasil_psikotes' => $jmlHasilPsikotes,
        'jml_interview'      => $jmlInterview,
        'jml_keputusan'      => $jmlKeputusan,
    ],
    'lowongan_list' => $lowonganList,
    'chart_labels'  => $chartLabels,
    'chart_values'  => $chartValues,
    'max_pendaftar' => $maxPendaftar,
]);