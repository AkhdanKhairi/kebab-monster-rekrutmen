<?php
// api/admin/lowongan.php
require_once __DIR__ . '/../_helper.php';

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$conn = getDbConnection();

// Deteksi / pastikan kolom status aktif ada di tabel lowongan
$colsLowongan = [];
$qCols = mysqli_query($conn, "SHOW COLUMNS FROM `lowongan`");
if ($qCols) {
    while ($c = mysqli_fetch_assoc($qCols)) {
        $colsLowongan[] = $c['Field'];
    }
}

$statusCol = '';
foreach (['status_aktif', 'status_lowongan', 'status'] as $candSt) {
    if (in_array($candSt, $colsLowongan, true)) {
        $statusCol = $candSt;
        break;
    }
}

if ($statusCol === '') {
    if (@mysqli_query($conn, "ALTER TABLE `lowongan` ADD COLUMN `status_aktif` VARCHAR(20) NOT NULL DEFAULT 'Aktif'")) {
        $statusCol = 'status_aktif';
        $colsLowongan[] = 'status_aktif';
    }
}

function isLowonganAktifApi($val): bool
{
    $v = strtolower(trim((string) ($val ?? '')));
    if ($v === '' || in_array($v, ['1', 'aktif', 'active', 'open', 'buka', 'true', 'ya'], true)) {
        return true;
    }
    return false;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    $idDetail = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($idDetail > 0) {
        $stmtOne = mysqli_prepare($conn, "SELECT * FROM `lowongan` WHERE `id_lowongan` = ? LIMIT 1");
        if (!$stmtOne) {
            mysqli_close($conn);
            jsonResponse(500, false, 'Gagal menyiapkan query detail lowongan.');
        }
        mysqli_stmt_bind_param($stmtOne, 'i', $idDetail);
        mysqli_stmt_execute($stmtOne);
        $resOne = mysqli_stmt_get_result($stmtOne);
        $rowOne = $resOne ? mysqli_fetch_assoc($resOne) : null;
        mysqli_stmt_close($stmtOne);
        mysqli_close($conn);

        if (!$rowOne) {
            jsonResponse(404, false, 'Data lowongan tidak ditemukan.');
        }

        $rowOne['id_lowongan'] = intval($rowOne['id_lowongan']);
        $rowOne['is_aktif']    = ($statusCol !== '') ? isLowonganAktifApi($rowOne[$statusCol] ?? 'Aktif') : true;

        jsonResponse(200, true, 'Detail lowongan berhasil dimuat.', ['lowongan' => $rowOne]);
    }

    $keyword      = trim((string) ($_GET['cari'] ?? ''));
    $filterDivisi = trim((string) ($_GET['divisi'] ?? ''));
    $filterStatus = trim((string) ($_GET['status'] ?? ''));

    $whereClauses = [];
    $params       = [];
    $types        = '';

    if ($keyword !== '') {
        $whereClauses[] = "(`posisi_pekerjaan` LIKE ? OR `divisi` LIKE ? OR `shift` LIKE ?)";
        $like = '%' . $keyword . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= 'sss';
    }

    if ($filterDivisi !== '') {
        $whereClauses[] = "`divisi` = ?";
        $params[] = $filterDivisi;
        $types .= 's';
    }

    if ($statusCol !== '' && $filterStatus === 'aktif') {
        $whereClauses[] = "LOWER(TRIM(COALESCE(`{$statusCol}`, 'Aktif'))) IN ('', '1', 'aktif', 'active', 'open', 'buka', 'true', 'ya')";
    } elseif ($statusCol !== '' && $filterStatus === 'nonaktif') {
        $whereClauses[] = "LOWER(TRIM(COALESCE(`{$statusCol}`, ''))) IN ('0', 'nonaktif', 'tidak aktif', 'inactive', 'non-aktif', 'tutup', 'closed', 'false')";
    }

    $whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
    $sqlList  = "SELECT * FROM `lowongan` {$whereSql} ORDER BY `id_lowongan` DESC";

    $rows = [];
    if ($types !== '') {
        $stmtList = mysqli_prepare($conn, $sqlList);
        mysqli_stmt_bind_param($stmtList, $types, ...$params);
        mysqli_stmt_execute($stmtList);
        $resList = mysqli_stmt_get_result($stmtList);
    } else {
        $resList = mysqli_query($conn, $sqlList);
        $stmtList = null;
    }

    if ($resList) {
        while ($r = mysqli_fetch_assoc($resList)) {
            $r['id_lowongan'] = intval($r['id_lowongan']);
            $r['is_aktif']    = ($statusCol !== '') ? isLowonganAktifApi($r[$statusCol] ?? 'Aktif') : true;
            $rows[] = $r;
        }
    }
    if ($stmtList) {
        mysqli_stmt_close($stmtList);
    }

    $divisiList = [];
    $qDiv = mysqli_query($conn, "SELECT DISTINCT `divisi` FROM `lowongan` WHERE `divisi` IS NOT NULL AND `divisi` != '' ORDER BY `divisi` ASC");
    if ($qDiv) {
        while ($d = mysqli_fetch_assoc($qDiv)) {
            $divisiList[] = (string) $d['divisi'];
        }
    }

    mysqli_close($conn);

    jsonResponse(200, true, 'Daftar lowongan berhasil dimuat.', [
        'lowongan_list' => $rows,
        'divisi_list'   => $divisiList,
        'total'         => count($rows),
    ]);
}

if ($method === 'POST') {
    $input  = getRequestInput();
    $action = strtolower(trim((string) ($input['action'] ?? '')));

    // Toggle status aktif/nonaktif
    if ($action === 'toggle_status' || isset($input['toggle_status'])) {
        $idToggle = intval($input['id_lowongan'] ?? ($input['toggle_status'] ?? 0));
        if ($idToggle <= 0 || $statusCol === '') {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID lowongan atau kolom status tidak valid.');
        }

        $stmtGet = mysqli_prepare($conn, "SELECT `{$statusCol}` AS st_val, `posisi_pekerjaan` FROM `lowongan` WHERE `id_lowongan` = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtGet, 'i', $idToggle);
        mysqli_stmt_execute($stmtGet);
        $resGet = mysqli_stmt_get_result($stmtGet);
        $rowL   = $resGet ? mysqli_fetch_assoc($resGet) : null;
        mysqli_stmt_close($stmtGet);

        if (!$rowL) {
            mysqli_close($conn);
            jsonResponse(404, false, 'Lowongan tidak ditemukan.');
        }

        $sedangAktif = isLowonganAktifApi($rowL['st_val'] ?? '');
        $rawSt       = strtolower(trim((string) ($rowL['st_val'] ?? '')));

        if (in_array($rawSt, ['0', '1'], true)) {
            $nilaiBaru = $sedangAktif ? '0' : '1';
        } elseif (in_array($rawSt, ['active', 'inactive'], true)) {
            $nilaiBaru = $sedangAktif ? 'Inactive' : 'Active';
        } else {
            $nilaiBaru = $sedangAktif ? 'Nonaktif' : 'Aktif';
        }

        $stmtUpd = mysqli_prepare($conn, "UPDATE `lowongan` SET `{$statusCol}` = ? WHERE `id_lowongan` = ?");
        mysqli_stmt_bind_param($stmtUpd, 'si', $nilaiBaru, $idToggle);
        $ok = mysqli_stmt_execute($stmtUpd);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtUpd);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal mengubah status lowongan: ' . $err);
        }

        $labelAksi = $sedangAktif ? 'dimatikan (tidak bisa dilamar pelamar)' : 'diaktifkan (bisa dilamar pelamar)';
        jsonResponse(200, true, "Lowongan {$rowL['posisi_pekerjaan']} berhasil {$labelAksi}.", [
            'id_lowongan' => $idToggle,
            'is_aktif'    => !$sedangAktif,
        ]);
    }

    // Hapus lowongan via POST
    if ($action === 'delete' || isset($input['hapus'])) {
        $idHapus = intval($input['id_lowongan'] ?? ($input['hapus'] ?? 0));
        if ($idHapus <= 0) {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID lowongan tidak valid.');
        }

        $cekLam = mysqli_query($conn, "SHOW TABLES LIKE 'lamaran'");
        if ($cekLam && mysqli_num_rows($cekLam) > 0) {
            $stmtDelLam = mysqli_prepare($conn, "DELETE FROM `lamaran` WHERE `id_lowongan` = ?");
            if ($stmtDelLam) {
                mysqli_stmt_bind_param($stmtDelLam, 'i', $idHapus);
                mysqli_stmt_execute($stmtDelLam);
                mysqli_stmt_close($stmtDelLam);
            }
        }

        $stmtDel = mysqli_prepare($conn, "DELETE FROM `lowongan` WHERE `id_lowongan` = ?");
        mysqli_stmt_bind_param($stmtDel, 'i', $idHapus);
        $ok = mysqli_stmt_execute($stmtDel);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtDel);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal menghapus data lowongan: ' . $err);
        }

        jsonResponse(200, true, 'Data lowongan pekerjaan berhasil dihapus!');
    }

    // Tambah atau Edit lowongan
    $idLowongan         = intval($input['id_lowongan'] ?? 0);
    $divisi             = trim((string) ($input['divisi'] ?? ''));
    $posisiPekerjaan   = trim((string) ($input['posisi_pekerjaan'] ?? ''));
    $tglDibuat         = trim((string) ($input['tgl_dibuat'] ?? date('Y-m-d')));
    $shift              = trim((string) ($input['shift'] ?? ''));
    $jurusanAkademik   = trim((string) ($input['jurusan_akademik'] ?? ''));
    $lokasiPekerjaan   = mb_substr(trim((string) ($input['lokasi_pekerjaan'] ?? '')), 0, 80);
    $nip                = trim((string) ($input['nip'] ?? ''));
    $benefit            = trim((string) ($input['benefit'] ?? ''));
    $deskripsiPekerjaan = trim((string) ($input['deskripsi_pekerjaan'] ?? ''));
    $tugasTanggungJawab = trim((string) ($input['tugas_tanggung_jawab'] ?? ''));
    $kualifikasi        = trim((string) ($input['kualifikasi'] ?? ''));

    if ($divisi === '' || $posisiPekerjaan === '' || $tglDibuat === '' || $shift === '' || $jurusanAkademik === '' || $lokasiPekerjaan === '' || $nip === '') {
        mysqli_close($conn);
        jsonResponse(422, false, 'Semua kolom wajib pada lowongan harus diisi.');
    }

    if ($idLowongan > 0) {
        $sqlUpd = "UPDATE `lowongan` SET
            `divisi` = ?,
            `posisi_pekerjaan` = ?,
            `tgl_dibuat` = ?,
            `shift` = ?,
            `jurusan_akademik` = ?,
            `lokasi_pekerjaan` = ?,
            `deskripsi_pekerjaan` = ?,
            `tugas_tanggung_jawab` = ?,
            `kualifikasi` = ?,
            `nip` = ?,
            `benefit` = ?
            WHERE `id_lowongan` = ?";

        $stmtUpd = mysqli_prepare($conn, $sqlUpd);
        if (!$stmtUpd) {
            mysqli_close($conn);
            jsonResponse(500, false, 'Gagal menyiapkan query update lowongan.');
        }

        mysqli_stmt_bind_param(
            $stmtUpd,
            'sssssssssssi',
            $divisi,
            $posisiPekerjaan,
            $tglDibuat,
            $shift,
            $jurusanAkademik,
            $lokasiPekerjaan,
            $deskripsiPekerjaan,
            $tugasTanggungJawab,
            $kualifikasi,
            $nip,
            $benefit,
            $idLowongan
        );

        $ok = mysqli_stmt_execute($stmtUpd);
        $err = mysqli_error($conn);
        mysqli_stmt_close($stmtUpd);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal memperbarui data: ' . $err);
        }

        jsonResponse(200, true, 'Data lowongan berhasil diperbarui!', ['id_lowongan' => $idLowongan]);
    }

    $view = 0;
    $sqlIns = "INSERT INTO `lowongan` (
        `posisi_pekerjaan`, `divisi`, `shift`, `tgl_dibuat`, `jurusan_akademik`,
        `lokasi_pekerjaan`, `deskripsi_pekerjaan`, `tugas_tanggung_jawab`,
        `kualifikasi`, `nip`, `benefit`, `view`
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtIns = mysqli_prepare($conn, $sqlIns);
    if (!$stmtIns) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyiapkan query simpan lowongan.');
    }

    mysqli_stmt_bind_param(
        $stmtIns,
        'sssssssssssi',
        $posisiPekerjaan,
        $divisi,
        $shift,
        $tglDibuat,
        $jurusanAkademik,
        $lokasiPekerjaan,
        $deskripsiPekerjaan,
        $tugasTanggungJawab,
        $kualifikasi,
        $nip,
        $benefit,
        $view
    );

    $ok = mysqli_stmt_execute($stmtIns);
    $newId = mysqli_insert_id($conn);
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmtIns);
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal menyimpan data: ' . $err);
    }

    jsonResponse(201, true, 'Data lowongan berhasil disimpan!', ['id_lowongan' => intval($newId)]);
}

if ($method === 'DELETE') {
    $idHapus = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($idHapus <= 0) {
        $input = getRequestInput();
        $idHapus = intval($input['id_lowongan'] ?? ($input['id'] ?? 0));
    }

    if ($idHapus <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'ID lowongan tidak valid.');
    }

    $cekLam = mysqli_query($conn, "SHOW TABLES LIKE 'lamaran'");
    if ($cekLam && mysqli_num_rows($cekLam) > 0) {
        $stmtDelLam = mysqli_prepare($conn, "DELETE FROM `lamaran` WHERE `id_lowongan` = ?");
        if ($stmtDelLam) {
            mysqli_stmt_bind_param($stmtDelLam, 'i', $idHapus);
            mysqli_stmt_execute($stmtDelLam);
            mysqli_stmt_close($stmtDelLam);
        }
    }

    $stmtDel = mysqli_prepare($conn, "DELETE FROM `lowongan` WHERE `id_lowongan` = ?");
    mysqli_stmt_bind_param($stmtDel, 'i', $idHapus);
    $ok = mysqli_stmt_execute($stmtDel);
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmtDel);
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal menghapus data lowongan: ' . $err);
    }

    jsonResponse(200, true, 'Data lowongan pekerjaan berhasil dihapus!');
}

mysqli_close($conn);
jsonResponse(405, false, 'Method tidak diizinkan.');