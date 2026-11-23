<?php
// api/admin/soal_ujian.php
require_once __DIR__ . '/../_helper.php';

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$conn = getDbConnection();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

function uploadGambarSoal(array $fileArr, int $idModul, string $oldFile = ''): array
{
    $dirSoal = __DIR__ . '/../../assets/img/soal/';
    $ekstensi = strtolower(pathinfo((string) ($fileArr['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ekstensi, $allowedExt, true)) {
        return ['ok' => false, 'message' => 'Format gambar soal tidak didukung (gunakan JPG, JPEG, PNG, GIF, atau WEBP).'];
    }

    if (!is_dir($dirSoal)) {
        @mkdir($dirSoal, 0777, true);
    }

    $namaBaru = 'soal_' . $idModul . '_' . time() . '_' . random_int(100, 999) . '.' . $ekstensi;
    if (!move_uploaded_file((string) ($fileArr['tmp_name'] ?? ''), $dirSoal . $namaBaru)) {
        return ['ok' => false, 'message' => 'Gagal mengunggah gambar soal.'];
    }

    if ($oldFile !== '') {
        $realDir = realpath($dirSoal);
        $oldPath = realpath($dirSoal . basename($oldFile));
        if ($oldPath && $realDir && strpos($oldPath, $realDir) === 0 && file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    return ['ok' => true, 'filename' => $namaBaru];
}

if ($method === 'GET') {
    $idSoal = intval($_GET['id_soal'] ?? 0);
    if ($idSoal <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID soal tidak valid.');
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT s.*, m.jenis_modul, m.nama_modul
         FROM `soal_ujian` s
         JOIN `modul_ujian` m ON s.id_modul = m.id_modul
         WHERE s.id_soal = ?
         LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'i', $idSoal);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $soal = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    if (!$soal) {
        jsonResponse(404, false, 'Soal Tes tidak ditemukan!');
    }

    $soal['id_soal']      = intval($soal['id_soal']);
    $soal['id_modul']     = intval($soal['id_modul']);
    $soal['pakai_alasan'] = intval($soal['pakai_alasan'] ?? 0);

    jsonResponse(200, true, 'Detail soal berhasil dimuat.', ['soal' => $soal]);
}

if ($method === 'POST') {
    $input  = getRequestInput();
    $action = strtolower(trim((string) ($input['action'] ?? '')));

    // Hapus soal
    if ($action === 'delete' || isset($input['hapus'])) {
        $idHapus = intval($input['id_soal'] ?? ($input['hapus'] ?? 0));
        if ($idHapus <= 0) {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID soal tidak valid.');
        }

        $qImg = mysqli_query($conn, "SELECT `gambar_soal` FROM `soal_ujian` WHERE `id_soal` = {$idHapus} LIMIT 1");
        if ($qImg && ($dImg = mysqli_fetch_assoc($qImg)) && !empty($dImg['gambar_soal'])) {
            $dirSoal = realpath(__DIR__ . '/../../assets/img/soal/');
            $imgPath = realpath(__DIR__ . '/../../assets/img/soal/' . basename((string) $dImg['gambar_soal']));
            if ($imgPath && $dirSoal && strpos($imgPath, $dirSoal) === 0 && file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }

        $ok = mysqli_query($conn, "DELETE FROM `soal_ujian` WHERE `id_soal` = {$idHapus}");
        $err = mysqli_error($conn);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal menghapus soal: ' . $err);
        }

        jsonResponse(200, true, 'Soal berhasil dihapus!');
    }

    $idSoalUpdate = intval($input['id_soal'] ?? 0);
    $pertanyaan   = trim((string) ($input['pertanyaan'] ?? ''));
    $pakaiAlasan  = (!empty($input['pakai_alasan']) && $input['pakai_alasan'] !== '0' && $input['pakai_alasan'] !== false) ? 1 : 0;

    if ($pertanyaan === '') {
        mysqli_close($conn);
        jsonResponse(422, false, 'Pertanyaan / soal wajib diisi.');
    }

    // Update soal yang sudah ada
    if ($idSoalUpdate > 0) {
        $stmtS = mysqli_prepare(
            $conn,
            "SELECT s.*, m.jenis_modul
             FROM `soal_ujian` s
             JOIN `modul_ujian` m ON s.id_modul = m.id_modul
             WHERE s.id_soal = ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmtS, 'i', $idSoalUpdate);
        mysqli_stmt_execute($stmtS);
        $resS = mysqli_stmt_get_result($stmtS);
        $existing = $resS ? mysqli_fetch_assoc($resS) : null;
        mysqli_stmt_close($stmtS);

        if (!$existing) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Soal Tes tidak ditemukan!');
        }

        $idModul      = intval($existing['id_modul']);
        $jenisModul   = (string) ($existing['jenis_modul'] ?? 'essay');
        $namaGambar   = (string) ($existing['gambar_soal'] ?? '');

        if (isset($_FILES['gambar_soal']) && intval($_FILES['gambar_soal']['error']) === UPLOAD_ERR_OK) {
            $up = uploadGambarSoal($_FILES['gambar_soal'], $idModul, $namaGambar);
            if (!$up['ok']) {
                mysqli_close($conn);
                jsonResponse(422, false, $up['message']);
            }
            $namaGambar = $up['filename'];
        }

        if ($jenisModul === 'pilihan_ganda') {
            $opsiA = trim((string) ($input['opsi_a'] ?? ''));
            $opsiB = trim((string) ($input['opsi_b'] ?? ''));
            $opsiC = trim((string) ($input['opsi_c'] ?? ''));
            $opsiD = trim((string) ($input['opsi_d'] ?? ''));
            $kunci = trim((string) ($input['kunci_jawaban'] ?? ''));

            if ($opsiA === '' || $opsiB === '' || $opsiC === '' || $opsiD === '' || $kunci === '') {
                mysqli_close($conn);
                jsonResponse(422, false, 'Pilihan A–D dan kunci jawaban wajib diisi untuk soal pilihan ganda.');
            }

            $stmtUpd = mysqli_prepare(
                $conn,
                "UPDATE `soal_ujian`
                 SET `pertanyaan` = ?, `gambar_soal` = ?, `opsi_a` = ?, `opsi_b` = ?, `opsi_c` = ?, `opsi_d` = ?, `kunci_jawaban` = ?, `pakai_alasan` = ?
                 WHERE `id_soal` = ?"
            );
            mysqli_stmt_bind_param($stmtUpd, 'sssssssii', $pertanyaan, $namaGambar, $opsiA, $opsiB, $opsiC, $opsiD, $kunci, $pakaiAlasan, $idSoalUpdate);
        } elseif ($jenisModul === 'benar_salah') {
            $kunci = trim((string) ($input['kunci_jawaban'] ?? ''));
            $stmtUpd = mysqli_prepare(
                $conn,
                "UPDATE `soal_ujian`
                 SET `pertanyaan` = ?, `gambar_soal` = ?, `kunci_jawaban` = ?, `pakai_alasan` = ?
                 WHERE `id_soal` = ?"
            );
            mysqli_stmt_bind_param($stmtUpd, 'sssii', $pertanyaan, $namaGambar, $kunci, $pakaiAlasan, $idSoalUpdate);
        } else {
            $stmtUpd = mysqli_prepare(
                $conn,
                "UPDATE `soal_ujian`
                 SET `pertanyaan` = ?, `gambar_soal` = ?, `pakai_alasan` = ?
                 WHERE `id_soal` = ?"
            );
            mysqli_stmt_bind_param($stmtUpd, 'ssii', $pertanyaan, $namaGambar, $pakaiAlasan, $idSoalUpdate);
        }

        $ok = mysqli_stmt_execute($stmtUpd);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtUpd);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal memperbarui soal: ' . $err);
        }

        jsonResponse(200, true, 'Soal berhasil diperbarui!', [
            'id_soal'  => $idSoalUpdate,
            'id_modul' => $idModul,
        ]);
    }

    // Tambah soal baru
    $idModul = intval($input['id_modul'] ?? 0);
    if ($idModul <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID modul tidak valid.');
    }

    $stmtM = mysqli_prepare($conn, "SELECT `jenis_modul` FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtM, 'i', $idModul);
    mysqli_stmt_execute($stmtM);
    $resM = mysqli_stmt_get_result($stmtM);
    $modul = $resM ? mysqli_fetch_assoc($resM) : null;
    mysqli_stmt_close($stmtM);

    if (!$modul) {
        mysqli_close($conn);
        jsonResponse(404, false, 'Modul ujian tidak ditemukan!');
    }

    $jenisModul = (string) ($modul['jenis_modul'] ?? 'essay');
    $namaGambar = '';

    if (isset($_FILES['gambar_soal']) && intval($_FILES['gambar_soal']['error']) === UPLOAD_ERR_OK) {
        $up = uploadGambarSoal($_FILES['gambar_soal'], $idModul, '');
        if (!$up['ok']) {
            mysqli_close($conn);
            jsonResponse(422, false, $up['message']);
        }
        $namaGambar = $up['filename'];
    }

    if ($jenisModul === 'pilihan_ganda') {
        $opsiA = trim((string) ($input['opsi_a'] ?? ''));
        $opsiB = trim((string) ($input['opsi_b'] ?? ''));
        $opsiC = trim((string) ($input['opsi_c'] ?? ''));
        $opsiD = trim((string) ($input['opsi_d'] ?? ''));
        $kunci = trim((string) ($input['kunci_jawaban'] ?? ''));

        if ($opsiA === '' || $opsiB === '' || $opsiC === '' || $opsiD === '' || $kunci === '') {
            mysqli_close($conn);
            jsonResponse(422, false, 'Pilihan A–D dan kunci jawaban wajib diisi untuk soal pilihan ganda.');
        }

        $stmtIns = mysqli_prepare(
            $conn,
            "INSERT INTO `soal_ujian` (`id_modul`, `pertanyaan`, `gambar_soal`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `kunci_jawaban`, `pakai_alasan`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmtIns, 'isssssssi', $idModul, $pertanyaan, $namaGambar, $opsiA, $opsiB, $opsiC, $opsiD, $kunci, $pakaiAlasan);
    } elseif ($jenisModul === 'benar_salah') {
        $kunci = trim((string) ($input['kunci_jawaban'] ?? ''));
        $stmtIns = mysqli_prepare(
            $conn,
            "INSERT INTO `soal_ujian` (`id_modul`, `pertanyaan`, `gambar_soal`, `kunci_jawaban`, `pakai_alasan`)
             VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmtIns, 'isssi', $idModul, $pertanyaan, $namaGambar, $kunci, $pakaiAlasan);
    } else {
        $stmtIns = mysqli_prepare(
            $conn,
            "INSERT INTO `soal_ujian` (`id_modul`, `pertanyaan`, `gambar_soal`, `pakai_alasan`)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmtIns, 'issi', $idModul, $pertanyaan, $namaGambar, $pakaiAlasan);
    }

    $ok = mysqli_stmt_execute($stmtIns);
    $newId = mysqli_insert_id($conn);
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmtIns);
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal menyimpan soal: ' . $err);
    }

    jsonResponse(201, true, 'Soal berhasil ditambahkan!', [
        'id_soal'  => intval($newId),
        'id_modul' => $idModul,
    ]);
}

mysqli_close($conn);
jsonResponse(405, false, 'Method tidak diizinkan.');