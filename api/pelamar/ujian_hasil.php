<?php
// api/pelamar/ujian_hasil.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.');
}
$idPelamar = intval($_SESSION['user_id']);
$idModul   = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['id_modul']) ? intval($_GET['id_modul']) : 0);

$conn = getDbConnection();

function ambilNamaModulApi(array $m): string {
    $nama = $m['nama_modul']
        ?? ($m['judul_modul']
        ?? ($m['nama']
        ?? ($m['judul'] ?? '')));
    $nama = trim((string) $nama);
    return $nama !== '' ? $nama : ('Modul #' . intval($m['id_modul'] ?? 0));
}

// 1. Profil pelamar
$qPelamar = mysqli_query($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = $idPelamar LIMIT 1");
$profile  = $qPelamar ? mysqli_fetch_assoc($qPelamar) : null;

if (!$profile) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
}

// 2. Deteksi posisi lamaran terakhir
$posisiDilamar   = '';
$kategoriPelamar = 'semua';

$allTables = [];
$qTbl      = mysqli_query($conn, "SHOW TABLES");
while ($qTbl && ($t = mysqli_fetch_row($qTbl))) {
    $allTables[] = $t[0];
}
$tblLowongan = in_array('lowongan', $allTables, true)
    ? 'lowongan'
    : (in_array('lowongan_kerja', $allTables, true) ? 'lowongan_kerja' : '');

$qLamRaw = mysqli_query($conn, "SELECT * FROM `lamaran` WHERE `id_pelamar` = $idPelamar ORDER BY `id_lamaran` DESC LIMIT 1");
$dataLamaran = null;
if ($qLamRaw && ($rowLam = mysqli_fetch_assoc($qLamRaw))) {
    $dataLamaran = $rowLam;
    foreach ($rowLam as $colK => $colV) {
        if (is_string($colV) && !preg_match('/^(id_|status|tanggal|waktu|created|updated|file|cv|foto)/i', $colK)) {
            if (preg_match('/(crew|kru|staff|staf|office|admin|outlet|kasir)/i', $colV)) {
                $posisiDilamar = $colV;
                break;
            }
        }
    }

    $idLow = intval($rowLam['id_lowongan'] ?? 0);
    if ($idLow > 0 && $tblLowongan !== '') {
        $qLow = mysqli_query($conn, "SELECT * FROM `$tblLowongan` WHERE `id_lowongan` = $idLow LIMIT 1");
        if ($qLow && ($rowLow = mysqli_fetch_assoc($qLow))) {
            $posisiDilamar = $rowLow['posisi_pekerjaan']
                ?? ($rowLow['posisi']
                ?? ($rowLow['judul_lowongan']
                ?? ($rowLow['nama_lowongan']
                ?? ($rowLow['nama_posisi']
                ?? ($rowLow['kategori'] ?? $posisiDilamar)))));

            if (empty($posisiDilamar)) {
                foreach ($rowLow as $lk => $lv) {
                    if (is_string($lv) && preg_match('/(crew|kru|staff|staf|office|admin|outlet)/i', $lv)) {
                        $posisiDilamar = $lv;
                        break;
                    }
                }
            }
        }
    }
}

$posisiLower = strtolower(trim((string) $posisiDilamar));
if (preg_match('/(crew|kru|outlet|kitchen|kasir)/i', $posisiLower)) {
    $kategoriPelamar = 'crew';
} elseif (preg_match('/(staff|staf|admin|office|finance|hrd|marketing|it)/i', $posisiLower)) {
    $kategoriPelamar = 'staff';
}

// 3. Deteksi kolom bobot pada soal_ujian
$colsSoalUjian = [];
$qColS = mysqli_query($conn, "SHOW COLUMNS FROM `soal_ujian`");
if ($qColS) {
    while ($c = mysqli_fetch_assoc($qColS)) {
        $colsSoalUjian[] = $c['Field'];
    }
}
$colBobotSoal = '';
foreach (['bobot_nilai', 'bobot', 'poin', 'skor_soal', 'nilai'] as $kandidatBobot) {
    if (in_array($kandidatBobot, $colsSoalUjian, true)) {
        $colBobotSoal = $kandidatBobot;
        break;
    }
}
$selectBobotSql = $colBobotSoal !== ''
    ? "COALESCE(NULLIF(s.`$colBobotSoal`, 0), 10) AS bobot_s"
    : "10 AS bobot_s";

// 4. Ringkasan hasil_psikotes
$hasilPsikotes = null;
$isPublished   = 0;
$isTidakLulus  = false;

$qHasil = mysqli_query($conn, "SELECT * FROM `hasil_psikotes` WHERE `id_pelamar` = $idPelamar ORDER BY `id_hasil` DESC LIMIT 1");
if ($qHasil && mysqli_num_rows($qHasil) > 0) {
    $hasilPsikotes = mysqli_fetch_assoc($qHasil);
    $isPublished   = intval($hasilPsikotes['is_published'] ?? 0);

    if ($isPublished === 1) {
        $stLulusLow = strtolower(trim((string) ($hasilPsikotes['status_lulus'] ?? '')));
        if (preg_match('/(tidak|gagal|tolak|unqualified|fail)/i', $stLulusLow)) {
            $isTidakLulus = true;
        }
    }
}

// 5. Modul ujian & skor per modul
$daftarModul = [];
$qAllModul  = mysqli_query($conn, "SELECT * FROM `modul_ujian` ORDER BY `urutan` ASC, `id_modul` ASC");
$no = 1;
if ($qAllModul) {
    while ($m = mysqli_fetch_assoc($qAllModul)) {
        $idm    = intval($m['id_modul']);
        $katMod = strtolower(trim((string) ($m['kategori_posisi'] ?? 'semua')));
        if ($kategoriPelamar === 'crew' && $katMod === 'staff') {
            continue;
        }
        if ($kategoriPelamar === 'staff' && $katMod === 'crew') {
            continue;
        }

        $statusKerja  = 'Belum Dikerjakan';
        $waktuSelesai = '-';
        $qRiw = mysqli_query($conn, "SELECT * FROM `riwayat_ujian_pelamar` WHERE `id_pelamar` = $idPelamar AND `id_modul` = $idm LIMIT 1");
        if ($qRiw && ($rw = mysqli_fetch_assoc($qRiw))) {
            $statusRiwayat = trim((string) ($rw['status_pengerjaan'] ?? ''));
            if ($statusRiwayat === 'Selesai') {
                $statusKerja = 'Selesai';
            } elseif ($statusRiwayat !== '') {
                $statusKerja = $statusRiwayat;
            }
            $waktuSelesai = !empty($rw['waktu_selesai'])
                ? date('d M Y, H:i', strtotime((string) $rw['waktu_selesai'])) . ' WIB'
                : '-';
        }

        $poinModul   = 0;
        $bobotModul  = 0;
        $jmlDijawab  = 0;
        $qSkorMod    = mysqli_query($conn, "
            SELECT j.*, s.kunci_jawaban,
                   $selectBobotSql
            FROM `jawaban_pelamar` j
            INNER JOIN `soal_ujian` s ON j.id_soal = s.id_soal
            WHERE j.id_pelamar = $idPelamar AND s.id_modul = $idm
        ");
        if ($qSkorMod) {
            while ($jm = mysqli_fetch_assoc($qSkorMod)) {
                $jmlDijawab++;
                $bobotS = floatval($jm['bobot_s'] ?? 10);
                $bobotModul += $bobotS;

                if (isset($jm['is_dikoreksi']) && intval($jm['is_dikoreksi']) === 1) {
                    $poinModul += floatval($jm['nilai_diperoleh'] ?? 0);
                } elseif (!empty($jm['kunci_jawaban']) && strcasecmp(trim((string) ($jm['jawaban_user'] ?? '')), trim((string) $jm['kunci_jawaban'])) === 0) {
                    $poinModul += $bobotS;
                } elseif (isset($jm['skor']) && floatval($jm['skor']) > 0) {
                    $poinModul += (floatval($jm['skor']) / 100) * $bobotS;
                }
            }
        }

        $daftarModul[] = [
            'id_modul'          => $idm,
            'nomor_urut'        => $no++,
            'nama_modul_tampil' => ambilNamaModulApi($m),
            'status_kerja'      => $statusKerja,
            'waktu_selesai'     => $waktuSelesai,
            'jml_dijawab'       => $jmlDijawab,
            'skor_modul'        => ($bobotModul > 0) ? round(($poinModul / $bobotModul) * 100, 1) : 0,
        ];
    }
}

$selectedIdModul = $idModul;
if ($selectedIdModul <= 0) {
    foreach ($daftarModul as $mItem) {
        if ($mItem['jml_dijawab'] > 0 || $mItem['status_kerja'] === 'Selesai') {
            $selectedIdModul = $mItem['id_modul'];
            break;
        }
    }
    if ($selectedIdModul <= 0 && !empty($daftarModul)) {
        $selectedIdModul = $daftarModul[0]['id_modul'];
    }
}

// 6. Data untuk rekap hasil_ujian.php
$statusKonfirmasi = $dataLamaran['status_konfirmasi_ujian'] ?? 'Menunggu';
$qCekPenilaian    = mysqli_query($conn, "SELECT `id_jawaban` FROM `jawaban_pelamar` WHERE `id_pelamar` = $idPelamar AND `status_penilaian` = 'Belum Dinilai' LIMIT 1");
$adaBelumDinilai  = ($qCekPenilaian && mysqli_num_rows($qCekPenilaian) > 0);
$belumDikonfirmasi = ($statusKonfirmasi === 'Menunggu' || $adaBelumDinilai);

$rekapModul = [];
if (!$belumDikonfirmasi) {
    $qModulSelesai = mysqli_query($conn, "
        SELECT DISTINCT m.id_modul, m.nama_modul, m.urutan
        FROM `modul_ujian` m
        JOIN `soal_ujian` s ON m.id_modul = s.id_modul
        JOIN `jawaban_pelamar` jp ON s.id_soal = jp.id_soal
        WHERE jp.id_pelamar = $idPelamar
        ORDER BY m.urutan ASC
    ");
    while ($qModulSelesai && ($modS = mysqli_fetch_assoc($qModulSelesai))) {
        $idmS = intval($modS['id_modul']);
        $qSkor = mysqli_query($conn, "
            SELECT AVG(jp.skor) AS rata_rata
            FROM `jawaban_pelamar` jp
            JOIN `soal_ujian` s ON jp.id_soal = s.id_soal
            WHERE jp.id_pelamar = $idPelamar AND s.id_modul = $idmS
        ");
        $dSkor = $qSkor ? mysqli_fetch_assoc($qSkor) : null;
        $rekapModul[] = [
            'id_modul'   => $idmS,
            'nama_modul' => $modS['nama_modul'] ?? ('Modul #' . $idmS),
            'skor_akhir' => round(floatval($dSkor['rata_rata'] ?? 0)),
        ];
    }
}

mysqli_close($conn);

$pasFoto = trim((string) ($profile['pas_foto'] ?? ''));

jsonResponse(200, true, 'Data hasil ujian berhasil diambil.', [
    'pelamar' => [
        'id_pelamar'   => intval($profile['id_pelamar']),
        'nama_lengkap' => $profile['nama_lengkap'] ?? '-',
        'email'        => $profile['email'] ?? '-',
        'pas_foto'     => $pasFoto,
        'pas_foto_url' => $pasFoto !== '' ? '../uploads/' . $pasFoto : '',
    ],
    'posisi_dilamar'     => $posisiDilamar,
    'hasil_psikotes'     => $hasilPsikotes,
    'is_published'       => $isPublished,
    'is_tidak_lulus'     => $isTidakLulus,
    'daftar_modul'       => $daftarModul,
    'selected_id_modul'  => $selectedIdModul,
    'belum_dikonfirmasi' => $belumDikonfirmasi,
    'rekap_modul'        => $rekapModul,
]);