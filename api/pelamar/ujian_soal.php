<?php
// api/pelamar/ujian_soal.php
require_once __DIR__ . '/../_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai pelamar.');
}
$idPelamar = intval($_SESSION['user_id']);
$method    = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$conn = getDbConnection();

if ($method === 'GET') {
    $idModul = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($idModul <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID modul ujian tidak valid.');
    }

    $stmtPel = mysqli_prepare($conn, "SELECT `id_pelamar`, `nama_lengkap`, `email`, `pas_foto` FROM `pelamar` WHERE `id_pelamar` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtPel, 'i', $idPelamar);
    mysqli_stmt_execute($stmtPel);
    $resPel  = mysqli_stmt_get_result($stmtPel);
    $pelamar = $resPel ? mysqli_fetch_assoc($resPel) : null;
    mysqli_stmt_close($stmtPel);

    $stmtMod = mysqli_prepare($conn, "SELECT * FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtMod, 'i', $idModul);
    mysqli_stmt_execute($stmtMod);
    $resMod = mysqli_stmt_get_result($stmtMod);
    $modul  = $resMod ? mysqli_fetch_assoc($resMod) : null;
    mysqli_stmt_close($stmtMod);

    if (!$modul) {
        mysqli_close($conn);
        jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
    }

    $stmtRiw = mysqli_prepare($conn, "SELECT * FROM `riwayat_ujian_pelamar` WHERE `id_pelamar` = ? AND `id_modul` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtRiw, 'ii', $idPelamar, $idModul);
    mysqli_stmt_execute($stmtRiw);
    $resRiw  = mysqli_stmt_get_result($stmtRiw);
    $riwayat = $resRiw ? mysqli_fetch_assoc($resRiw) : null;
    mysqli_stmt_close($stmtRiw);

    if ($riwayat && ($riwayat['status_pengerjaan'] ?? '') === 'Selesai') {
        mysqli_close($conn);
        jsonResponse(409, false, 'Anda sudah menyelesaikan modul ini dan ujian telah dikunci!', [
            'already_completed' => true,
        ]);
    }

    if (!$riwayat) {
        $stmtInsR = mysqli_prepare($conn, "INSERT INTO `riwayat_ujian_pelamar` (`id_pelamar`, `id_modul`, `status_pengerjaan`, `waktu_mulai`) VALUES (?, ?, 'Sedang Dikerjakan', NOW())");
        if ($stmtInsR) {
            mysqli_stmt_bind_param($stmtInsR, 'ii', $idPelamar, $idModul);
            mysqli_stmt_execute($stmtInsR);
            mysqli_stmt_close($stmtInsR);
        }
    }

    $stmtSoal = mysqli_prepare($conn, "
        SELECT `id_soal`, `id_modul`, `pertanyaan`, `gambar_soal`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `pakai_alasan`
        FROM `soal_ujian`
        WHERE `id_modul` = ?
        ORDER BY `id_soal` ASC
    ");
    mysqli_stmt_bind_param($stmtSoal, 'i', $idModul);
    mysqli_stmt_execute($stmtSoal);
    $resSoal = mysqli_stmt_get_result($stmtSoal);

    $soalList = [];
    $soalIds  = [];
    if ($resSoal) {
        while ($row = mysqli_fetch_assoc($resSoal)) {
            $sid = intval($row['id_soal']);
            $row['id_soal']      = $sid;
            $row['id_modul']     = intval($row['id_modul']);
            $row['pakai_alasan'] = intval($row['pakai_alasan'] ?? 0);
            $soalList[]          = $row;
            $soalIds[]           = $sid;
        }
    }
    mysqli_stmt_close($stmtSoal);

    // Ambil jawaban yang sudah tersimpan sebelumnya (untuk auto-restore saat refresh)
    $jawabanTersimpan = [];
    if (!empty($soalIds)) {
        $stmtJwb = mysqli_prepare($conn, "SELECT `id_soal`, `jawaban_user` FROM `jawaban_pelamar` WHERE `id_pelamar` = ? AND `id_soal` = ? LIMIT 1");
        if ($stmtJwb) {
            foreach ($soalIds as $sid) {
                mysqli_stmt_bind_param($stmtJwb, 'ii', $idPelamar, $sid);
                mysqli_stmt_execute($stmtJwb);
                $resJwb = mysqli_stmt_get_result($stmtJwb);
                if ($resJwb && ($rowJwb = mysqli_fetch_assoc($resJwb))) {
                    $jawabanTersimpan[(string) $sid] = (string) ($rowJwb['jawaban_user'] ?? '');
                }
            }
            mysqli_stmt_close($stmtJwb);
        }
    }

    mysqli_close($conn);

    $pasFoto = trim((string) ($pelamar['pas_foto'] ?? ''));

    jsonResponse(200, true, 'Soal ujian berhasil dimuat.', [
        'pelamar' => [
            'id_pelamar'   => intval($pelamar['id_pelamar'] ?? $idPelamar),
            'nama_lengkap' => $pelamar['nama_lengkap'] ?? '',
            'email'        => $pelamar['email'] ?? '',
            'pas_foto'     => $pasFoto,
            'pas_foto_url' => $pasFoto !== '' ? '../uploads/' . $pasFoto : '',
        ],
        'modul'             => $modul,
        'soal_list'         => $soalList,
        'jawaban_tersimpan' => $jawabanTersimpan,
    ]);
}

if ($method === 'POST') {
    $input = getRequestInput();
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }

    $idModul = isset($input['id_modul']) ? intval($input['id_modul']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
    if ($idModul <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID modul ujian tidak valid.');
    }

    $action  = strtolower(trim((string) ($input['action'] ?? 'submit')));
    $isFinal = ($action === 'submit' || !empty($input['submit_ujian']));
    $jawaban = is_array($input['jawaban'] ?? null) ? $input['jawaban'] : [];

    $stmtMod = mysqli_prepare($conn, "SELECT * FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtMod, 'i', $idModul);
    mysqli_stmt_execute($stmtMod);
    $resMod = mysqli_stmt_get_result($stmtMod);
    $modul  = $resMod ? mysqli_fetch_assoc($resMod) : null;
    mysqli_stmt_close($stmtMod);

    if (!$modul) {
        mysqli_close($conn);
        jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
    }

    $stmtSoal = mysqli_prepare($conn, "SELECT `id_soal`, `kunci_jawaban` FROM `soal_ujian` WHERE `id_modul` = ?");
    mysqli_stmt_bind_param($stmtSoal, 'i', $idModul);
    mysqli_stmt_execute($stmtSoal);
    $resSoal = mysqli_stmt_get_result($stmtSoal);

    $mapSoal = [];
    if ($resSoal) {
        while ($s = mysqli_fetch_assoc($resSoal)) {
            $mapSoal[intval($s['id_soal'])] = $s;
        }
    }
    mysqli_stmt_close($stmtSoal);

    $stmtCek = mysqli_prepare($conn, "SELECT `id_jawaban` FROM `jawaban_pelamar` WHERE `id_pelamar` = ? AND `id_soal` = ? LIMIT 1");
    $stmtUpd = mysqli_prepare($conn, "UPDATE `jawaban_pelamar` SET `jawaban_user` = ?, `status_penilaian` = ?, `skor` = ? WHERE `id_pelamar` = ? AND `id_soal` = ?");
    $stmtIns = mysqli_prepare($conn, "INSERT INTO `jawaban_pelamar` (`id_pelamar`, `id_soal`, `jawaban_user`, `status_penilaian`, `skor`) VALUES (?, ?, ?, ?, ?)");

    $jenisModul = (string) ($modul['jenis_modul'] ?? '');

    foreach ($mapSoal as $idSoal => $soalRow) {
        if (!array_key_exists((string) $idSoal, $jawaban) && !array_key_exists($idSoal, $jawaban)) {
            continue;
        }
        $ans = $jawaban[$idSoal] ?? ($jawaban[(string) $idSoal] ?? '');
        if (is_array($ans) || trim((string) $ans) === '') {
            continue;
        }

        $jawabanUser     = trim((string) $ans);
        $skor            = 0;
        $statusPenilaian = 'Sudah Dinilai';

        if ($jenisModul === 'pilihan_ganda') {
            if (strtoupper($jawabanUser) === strtoupper(trim((string) ($soalRow['kunci_jawaban'] ?? '')))) {
                $skor = 100;
            }
        } else {
            $statusPenilaian = 'Belum Dinilai';
        }

        mysqli_stmt_bind_param($stmtCek, 'ii', $idPelamar, $idSoal);
        mysqli_stmt_execute($stmtCek);
        $resCek = mysqli_stmt_get_result($stmtCek);

        if ($resCek && mysqli_num_rows($resCek) > 0) {
            mysqli_stmt_bind_param($stmtUpd, 'ssiii', $jawabanUser, $statusPenilaian, $skor, $idPelamar, $idSoal);
            mysqli_stmt_execute($stmtUpd);
        } else {
            mysqli_stmt_bind_param($stmtIns, 'iissi', $idPelamar, $idSoal, $jawabanUser, $statusPenilaian, $skor);
            mysqli_stmt_execute($stmtIns);
        }
    }

    mysqli_stmt_close($stmtCek);
    mysqli_stmt_close($stmtUpd);
    mysqli_stmt_close($stmtIns);

    if ($isFinal) {
        $stmtSelesai = mysqli_prepare($conn, "UPDATE `riwayat_ujian_pelamar` SET `status_pengerjaan` = 'Selesai', `waktu_selesai` = NOW() WHERE `id_pelamar` = ? AND `id_modul` = ?");
        if ($stmtSelesai) {
            mysqli_stmt_bind_param($stmtSelesai, 'ii', $idPelamar, $idModul);
            mysqli_stmt_execute($stmtSelesai);
            mysqli_stmt_close($stmtSelesai);
        }
    }

    mysqli_close($conn);

    jsonResponse(200, true, $isFinal ? 'Ujian berhasil diselesaikan dan dikunci!' : 'Jawaban tersimpan otomatis.', [
        'is_final' => $isFinal,
    ]);
}

mysqli_close($conn);
jsonResponse(405, false, 'Method tidak diizinkan.');