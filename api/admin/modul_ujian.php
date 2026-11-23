<?php
// api/admin/modul_ujian.php
require_once __DIR__ . '/../_helper.php';

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$conn = getDbConnection();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

function rapikanUrutanModul(mysqli $conn): void
{
    $q = mysqli_query($conn, "SELECT `id_modul` FROM `modul_ujian` ORDER BY `urutan` ASC, `id_modul` ASC");
    if (!$q) {
        return;
    }
    $no = 1;
    while ($r = mysqli_fetch_assoc($q)) {
        $mid = intval($r['id_modul']);
        mysqli_query($conn, "UPDATE `modul_ujian` SET `urutan` = {$no} WHERE `id_modul` = {$mid}");
        $no++;
    }
}

if ($method === 'GET') {
    $idModul = intval($_GET['id_modul'] ?? 0);
    if ($idModul > 0) {
        $stmtM = mysqli_prepare($conn, "SELECT * FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
        if (!$stmtM) {
            mysqli_close($conn);
            jsonResponse(500, false, 'Gagal menyiapkan query detail modul.');
        }
        mysqli_stmt_bind_param($stmtM, 'i', $idModul);
        mysqli_stmt_execute($stmtM);
        $resM = mysqli_stmt_get_result($stmtM);
        $modul = $resM ? mysqli_fetch_assoc($resM) : null;
        mysqli_stmt_close($stmtM);

        if (!$modul) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Modul ujian tidak ditemukan!');
        }

        $soalList = [];
        $stmtS = mysqli_prepare($conn, "SELECT * FROM `soal_ujian` WHERE `id_modul` = ? ORDER BY `id_soal` ASC");
        if ($stmtS) {
            mysqli_stmt_bind_param($stmtS, 'i', $idModul);
            mysqli_stmt_execute($stmtS);
            $resS = mysqli_stmt_get_result($stmtS);
            if ($resS) {
                while ($s = mysqli_fetch_assoc($resS)) {
                    $s['id_soal']      = intval($s['id_soal'] ?? ($s['id'] ?? 0));
                    $s['id_modul']     = intval($s['id_modul']);
                    $s['pakai_alasan'] = intval($s['pakai_alasan'] ?? 0);
                    $soalList[]        = $s;
                }
            }
            mysqli_stmt_close($stmtS);
        }

        mysqli_close($conn);

        $modul['id_modul']     = intval($modul['id_modul']);
        $modul['durasi_menit'] = intval($modul['durasi_menit']);
        $modul['urutan']       = intval($modul['urutan']);

        jsonResponse(200, true, 'Detail modul berhasil dimuat.', [
            'modul'     => $modul,
            'soal_list' => $soalList,
        ]);
    }

    $modulList = [];
    $qAll = mysqli_query($conn, "SELECT * FROM `modul_ujian` ORDER BY `urutan` ASC, `id_modul` ASC");
    if ($qAll) {
        while ($row = mysqli_fetch_assoc($qAll)) {
            $row['id_modul']     = intval($row['id_modul']);
            $row['durasi_menit'] = intval($row['durasi_menit']);
            $row['urutan']       = intval($row['urutan']);
            $modulList[]         = $row;
        }
    }
    mysqli_close($conn);

    jsonResponse(200, true, 'Daftar modul ujian berhasil dimuat.', [
        'modul_list' => $modulList,
        'total'      => count($modulList),
    ]);
}

if ($method === 'POST') {
    $input  = getRequestInput();
    $action = strtolower(trim((string) ($input['action'] ?? '')));

    // Hapus modul via POST
    if ($action === 'delete' || isset($input['hapus'])) {
        $idHapus = intval($input['id_modul'] ?? ($input['hapus'] ?? 0));
        if ($idHapus <= 0) {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID modul tidak valid.');
        }

        $qDel = mysqli_query($conn, "SELECT `urutan` FROM `modul_ujian` WHERE `id_modul` = {$idHapus} LIMIT 1");
        if (!$qDel || mysqli_num_rows($qDel) === 0) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
        }
        $dDel = mysqli_fetch_assoc($qDel);
        $delUrutan = intval($dDel['urutan']);

        $ok = mysqli_query($conn, "DELETE FROM `modul_ujian` WHERE `id_modul` = {$idHapus}");
        $err = mysqli_error($conn);
        if ($ok) {
            mysqli_query($conn, "UPDATE `modul_ujian` SET `urutan` = `urutan` - 1 WHERE `urutan` > {$delUrutan}");
            rapikanUrutanModul($conn);
        }
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal menghapus modul: ' . $err);
        }

        jsonResponse(200, true, 'Modul ujian berhasil dihapus dan urutan dirapikan!');
    }

    // Update tata cara & gambar petunjuk
    if ($action === 'update_tata_cara' || isset($input['update_tata_cara'])) {
        $idModul  = intval($input['id_modul'] ?? 0);
        $tataCara = trim((string) ($input['tata_cara'] ?? ''));

        if ($idModul <= 0) {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID modul tidak valid.');
        }

        $stmtCheck = mysqli_prepare($conn, "SELECT `id_modul`, `gambar_petunjuk` FROM `modul_ujian` WHERE `id_modul` = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck, 'i', $idModul);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $currModul = $resCheck ? mysqli_fetch_assoc($resCheck) : null;
        mysqli_stmt_close($stmtCheck);

        if (!$currModul) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Modul ujian tidak ditemukan!');
        }

        $gambarBaru = $currModul['gambar_petunjuk'] ?? '';
        $dirTujuan  = __DIR__ . '/../../assets/img/tatacara/';

        if (isset($_FILES['gambar_petunjuk']) && intval($_FILES['gambar_petunjuk']['error']) === UPLOAD_ERR_OK) {
            $ekstensi = strtolower(pathinfo((string) $_FILES['gambar_petunjuk']['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ekstensi, $allowedExt, true)) {
                mysqli_close($conn);
                jsonResponse(422, false, 'Format gambar tidak didukung (gunakan JPG, JPEG, PNG, GIF, atau WEBP).');
            }

            if (!is_dir($dirTujuan)) {
                @mkdir($dirTujuan, 0777, true);
            }

            $namaFileBaru = 'tatacara_' . $idModul . '_' . time() . '.' . $ekstensi;
            if (!move_uploaded_file((string) $_FILES['gambar_petunjuk']['tmp_name'], $dirTujuan . $namaFileBaru)) {
                mysqli_close($conn);
                jsonResponse(500, false, 'Gagal mengunggah gambar petunjuk.');
            }

            if (!empty($currModul['gambar_petunjuk'])) {
                $oldFile = realpath($dirTujuan . basename((string) $currModul['gambar_petunjuk']));
                $realDir = realpath($dirTujuan);
                if ($oldFile && $realDir && strpos($oldFile, $realDir) === 0 && file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }

            $gambarBaru = $namaFileBaru;
        }

        $stmtUpd = mysqli_prepare($conn, "UPDATE `modul_ujian` SET `tata_cara` = ?, `gambar_petunjuk` = ? WHERE `id_modul` = ?");
        mysqli_stmt_bind_param($stmtUpd, 'ssi', $tataCara, $gambarBaru, $idModul);
        $ok = mysqli_stmt_execute($stmtUpd);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtUpd);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal menyimpan tata cara: ' . $err);
        }

        jsonResponse(200, true, 'Tata cara ujian berhasil disimpan!', [
            'id_modul'        => $idModul,
            'gambar_petunjuk' => $gambarBaru,
        ]);
    }

    // Edit modul & geser urutan otomatis
    $idModulEdit = intval($input['id_modul'] ?? 0);
    if ($idModulEdit > 0 && (isset($input['edit_modul']) || $action === 'update' || $action === 'edit' || isset($input['urutan']))) {
        $namaModul      = trim((string) ($input['nama_modul'] ?? ''));
        $kategoriPosisi = trim((string) ($input['kategori_posisi'] ?? 'Semua'));
        $durasiMenit    = max(1, intval($input['durasi_menit'] ?? 1));
        $jenisModul     = trim((string) ($input['jenis_modul'] ?? 'pilihan_ganda'));
        $newUrutan      = intval($input['urutan'] ?? 1);

        if ($namaModul === '') {
            mysqli_close($conn);
            jsonResponse(422, false, 'Nama modul wajib diisi.');
        }

        $qOld = mysqli_query($conn, "SELECT `urutan` FROM `modul_ujian` WHERE `id_modul` = {$idModulEdit} LIMIT 1");
        if (!$qOld || mysqli_num_rows($qOld) === 0) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
        }
        $dOld = mysqli_fetch_assoc($qOld);
        $oldUrutan = intval($dOld['urutan'] ?? 1);

        $qMax = mysqli_query($conn, "SELECT MAX(`urutan`) AS max_u FROM `modul_ujian`");
        $dMax = $qMax ? mysqli_fetch_assoc($qMax) : null;
        $maxUrutan = max(1, intval($dMax['max_u'] ?? 1));

        if ($newUrutan < 1) {
            $newUrutan = 1;
        }
        if ($newUrutan > $maxUrutan) {
            $newUrutan = $maxUrutan;
        }

        if ($newUrutan !== $oldUrutan) {
            if ($newUrutan > $oldUrutan) {
                mysqli_query($conn, "UPDATE `modul_ujian` SET `urutan` = `urutan` - 1 WHERE `urutan` > {$oldUrutan} AND `urutan` <= {$newUrutan} AND `id_modul` != {$idModulEdit}");
            } else {
                mysqli_query($conn, "UPDATE `modul_ujian` SET `urutan` = `urutan` + 1 WHERE `urutan` >= {$newUrutan} AND `urutan` < {$oldUrutan} AND `id_modul` != {$idModulEdit}");
            }
        }

        $stmtUpd = mysqli_prepare(
            $conn,
            "UPDATE `modul_ujian` SET
                `nama_modul` = ?,
                `kategori_posisi` = ?,
                `durasi_menit` = ?,
                `jenis_modul` = ?,
                `urutan` = ?
             WHERE `id_modul` = ?"
        );

        if (!$stmtUpd) {
            mysqli_close($conn);
            jsonResponse(500, false, 'Gagal menyiapkan query pembaruan modul.');
        }

        mysqli_stmt_bind_param(
            $stmtUpd,
            'ssisii',
            $namaModul,
            $kategoriPosisi,
            $durasiMenit,
            $jenisModul,
            $newUrutan,
            $idModulEdit
        );

        $ok = mysqli_stmt_execute($stmtUpd);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtUpd);
        if ($ok) {
            rapikanUrutanModul($conn);
        }
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal memperbarui modul: ' . $err);
        }

        jsonResponse(200, true, 'Modul ujian berhasil diperbarui dan diurutkan ulang!', [
            'id_modul' => $idModulEdit,
            'urutan'   => $newUrutan,
        ]);
    }

    // Tambah modul baru (urutan otomatis)
    $namaModul      = trim((string) ($input['nama_modul'] ?? ''));
    $kategoriPosisi = trim((string) ($input['kategori_posisi'] ?? 'Semua'));
    $durasiMenit    = max(1, intval($input['durasi_menit'] ?? 0));
    $jenisModul     = trim((string) ($input['jenis_modul'] ?? 'pilihan_ganda'));

    if ($namaModul === '' || $durasiMenit <= 0) {
        mysqli_close($conn);
        jsonResponse(422, false, 'Nama modul dan durasi pengerjaan wajib diisi dengan benar.');
    }

    $qUrutan = mysqli_query($conn, "SELECT IFNULL(MAX(`urutan`), 0) + 1 AS next_urutan FROM `modul_ujian`");
    $dUrutan = $qUrutan ? mysqli_fetch_assoc($qUrutan) : ['next_urutan' => 1];
    $urutan  = intval($dUrutan['next_urutan'] ?? 1);

    $stmtIns = mysqli_prepare(
        $conn,
        "INSERT INTO `modul_ujian` (`nama_modul`, `kategori_posisi`, `durasi_menit`, `jenis_modul`, `urutan`)
         VALUES (?, ?, ?, ?, ?)"
    );

    if (!$stmtIns) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyiapkan query simpan modul.');
    }

    mysqli_stmt_bind_param($stmtIns, 'ssisi', $namaModul, $kategoriPosisi, $durasiMenit, $jenisModul, $urutan);
    $ok = mysqli_stmt_execute($stmtIns);
    $newId = mysqli_insert_id($conn);
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmtIns);
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal menambahkan modul: ' . $err);
    }

    jsonResponse(201, true, "Modul ujian baru berhasil ditambahkan dengan urutan otomatis ke-{$urutan}!", [
        'id_modul' => intval($newId),
        'urutan'   => $urutan,
    ]);
}

if ($method === 'DELETE') {
    $idHapus = intval($_GET['id_modul'] ?? ($_GET['id'] ?? 0));
    if ($idHapus <= 0) {
        $input = getRequestInput();
        $idHapus = intval($input['id_modul'] ?? ($input['id'] ?? 0));
    }

    if ($idHapus <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID modul tidak valid.');
    }

    $qDel = mysqli_query($conn, "SELECT `urutan` FROM `modul_ujian` WHERE `id_modul` = {$idHapus} LIMIT 1");
    if (!$qDel || mysqli_num_rows($qDel) === 0) {
        mysqli_close($conn);
        jsonResponse(404, false, 'Modul ujian tidak ditemukan.');
    }
    $dDel = mysqli_fetch_assoc($qDel);
    $delUrutan = intval($dDel['urutan']);

    $ok = mysqli_query($conn, "DELETE FROM `modul_ujian` WHERE `id_modul` = {$idHapus}");
    $err = mysqli_error($conn);
    if ($ok) {
        mysqli_query($conn, "UPDATE `modul_ujian` SET `urutan` = `urutan` - 1 WHERE `urutan` > {$delUrutan}");
        rapikanUrutanModul($conn);
    }
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal menghapus modul: ' . $err);
    }

    jsonResponse(200, true, 'Modul ujian berhasil dihapus dan urutan dirapikan!');
}

mysqli_close($conn);
jsonResponse(405, false, 'Method tidak diizinkan.');