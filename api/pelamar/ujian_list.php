<?php
// api/pelamar/ujian_list.php
require_once __DIR__ . '/../_helper.php';

requireMethod('GET');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') { jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.'); }$idPelamar = intval($_SESSION['user_id']);$idModul   = isset($_GET['id']) ? intval($_GET['id']) : 0;

$conn = getDbConnection();

// 1. Profil pelamar
$stmtPel = mysqli_prepare($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
if (!$stmtPel) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil data pelamar.');
}
mysqli_stmt_bind_param($stmtPel, 'i',$idPelamar);
mysqli_stmt_execute($stmtPel);$resPel  = mysqli_stmt_get_result($stmtPel);$pelamar = $resPel ? mysqli_fetch_assoc($resPel) : null;
mysqli_stmt_close($stmtPel);

if (!$pelamar) {
    mysqli_close($conn);
    jsonResponse(404, false, 'Data pelamar tidak ditemukan.');
}

// 2. Posisi lamaran terakhir untuk kategori Crew / Staff / Semua
$stmtLam = mysqli_prepare($conn, "
    SELECT l.*, lw.posisi_pekerjaan, lw.divisi
    FROM `lamaran` l
    JOIN `lowongan` lw ON l.id_lowongan = lw.id_lowongan
    WHERE l.id_pelamar = ?
    ORDER BY l.id_lamaran DESC LIMIT 1
");
$dataLamaran = null;
if ($stmtLam) {
    mysqli_stmt_bind_param($stmtLam, 'i',$idPelamar);
    mysqli_stmt_execute($stmtLam);$resLam      = mysqli_stmt_get_result($stmtLam);$dataLamaran = $resLam ? mysqli_fetch_assoc($resLam) : null;
    mysqli_stmt_close($stmtLam);
}

$kategoriPosisi = 'Semua';
if ($dataLamaran) {
    $posisiLower = strtolower((string) ($dataLamaran['posisi_pekerjaan'] ?? ''));
    if (strpos($posisiLower, 'crew') !== false || strpos($posisiLower, 'store') !== false) {$kategoriPosisi = 'Crew';
    } else {
        $kategoriPosisi = 'Staff';
    }
}

// Jika diminta detail satu modul (untuk cara_ujian.php)
if ($idModul > 0) {
    $stmtMod = mysqli_prepare($conn, "SELECT * FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
    if (!$stmtMod) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal mengambil detail modul ujian.');
    }
    mysqli_stmt_bind_param($stmtMod, 'i',$idModul);
    mysqli_stmt_execute($stmtMod);$resMod = mysqli_stmt_get_result($stmtMod);$modul  = $resMod ? mysqli_fetch_assoc($resMod) : null;
    mysqli_stmt_close($stmtMod);

    mysqli_close($conn);

    if (!$modul) {
        jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
    }

    $pasFoto = trim((string) ($pelamar['pas_foto'] ?? ''));
    jsonResponse(200, true, 'Detail modul ujian berhasil diambil.', [
        'pelamar' => [
            'id_pelamar'   => intval($pelamar['id_pelamar']),
            'nama_lengkap' => $pelamar['nama_lengkap'] ?? '',
            'email'        => $pelamar['email'] ?? '',
            'pas_foto'     => $pasFoto,
            'pas_foto_url' => $pasFoto !== '' ? '../uploads/' .$pasFoto : '',
        ],
        'kategori_posisi' => $kategoriPosisi,
        'modul'           => $modul,
    ]);
}

// 3. Daftar modul sesuai kategori posisi
$stmtModList = mysqli_prepare($conn, "
    SELECT * FROM `modul_ujian`
    WHERE `kategori_posisi` = ? OR `kategori_posisi` = 'Semua'
    ORDER BY `urutan` ASC, `id_modul` ASC
");
if (!$stmtModList) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal mengambil daftar modul ujian.');
}
mysqli_stmt_bind_param($stmtModList, 's',$kategoriPosisi);
mysqli_stmt_execute($stmtModList);
$resModList = mysqli_stmt_get_result($stmtModList);

$stmtRiw = mysqli_prepare($conn, "
    SELECT `status_pengerjaan`, `waktu_mulai`, `waktu_selesai`
    FROM `riwayat_ujian_pelamar`
    WHERE `id_pelamar` = ? AND `id_modul` = ?
    LIMIT 1
");

$daftarModul      = [];$sebelumnyaSelesai = true;
$no               = 1;

if ($resModList) {
    while ($row = mysqli_fetch_assoc($resModList)) {
        $idm    = intval($row['id_modul']);
        $status = 'Belum Mulai';$mulai  = null;
        $selesai = null;

        if ($stmtRiw) {
            mysqli_stmt_bind_param($stmtRiw, 'ii', $idPelamar,$idm);
            mysqli_stmt_execute($stmtRiw);$resRiw = mysqli_stmt_get_result($stmtRiw);$riw    = $resRiw ? mysqli_fetch_assoc($resRiw) : null;
            if ($riw) {
                if (trim((string) ($riw['status_pengerjaan'] ?? '')) !== '') {
                    $status = trim((string)$riw['status_pengerjaan']);
                }
                $mulai   =$riw['waktu_mulai'] ?? null;
                $selesai =$riw['waktu_selesai'] ?? null;
            }
        }

        $isSelesai = ($status === 'Selesai');$terbuka   = (!$isSelesai &&$sebelumnyaSelesai);

        $row['nomor_urut']        =$no++;
        $row['id_modul']          =$idm;
        $row['durasi_menit']      = intval($row['durasi_menit'] ?? 0);
        $row['status_pengerjaan'] =$status;
        $row['waktu_mulai']       =$mulai;
        $row['waktu_selesai']     =$selesai;
        $row['terbuka']           =$terbuka;

        if (!$isSelesai) {$sebelumnyaSelesai = false;
        }

        $daftarModul[] =$row;
    }
}
if ($stmtRiw) {
    mysqli_stmt_close($stmtRiw);
}
mysqli_stmt_close($stmtModList);
mysqli_close($conn);

$pasFoto = trim((string) ($pelamar['pas_foto'] ?? ''));

jsonResponse(200, true, 'Daftar modul psikotes berhasil diambil.', [
    'pelamar' => [
        'id_pelamar'   => intval($pelamar['id_pelamar']),
        'nama_lengkap' => $pelamar['nama_lengkap'] ?? '',
        'email'        => $pelamar['email'] ?? '',
        'pas_foto'     => $pasFoto,
        'pas_foto_url' => $pasFoto !== '' ? '../uploads/' .$pasFoto : '',
    ],
    'kategori_posisi' => $kategoriPosisi,
    'daftar_modul'    => $daftarModul,
]);