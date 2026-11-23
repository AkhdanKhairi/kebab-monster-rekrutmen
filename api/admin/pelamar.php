<?php
// api/admin/pelamar.php
require_once __DIR__ . '/../_helper.php';

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$idAdmin = intval($_SESSION['user_id'] ?? 0);

if ($idAdmin <= 0 || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    jsonResponse(401, false, 'Silakan login terlebih dahulu sebagai HR/Admin.');
}

$conn = getDbConnection();

// Deteksi tabel lamaran & kolom status
$allTables = [];
$qTables = mysqli_query($conn, "SHOW TABLES");
if ($qTables) {
    while ($t = mysqli_fetch_row($qTables)) {
        $allTables[] = $t[0];
    }
}

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

$fkPelamar = in_array('id_pelamar', $colsLamaran, true) ? 'id_pelamar' : 'pelamar_id';
$pkLamaran = $colsLamaran[0] ?? 'id_lamaran';

$statusCol = '';
if ($tblLamaran !== '') {
    foreach (['status', 'status_lamaran', 'status_seleksi', 'tahap'] as $optS) {
        if (in_array($optS, $colsLamaran, true)) {
            $statusCol = $optS;
            break;
        }
    }
    if ($statusCol === '') {
        if (@mysqli_query($conn, "ALTER TABLE `{$tblLamaran}` ADD `status` VARCHAR(30) DEFAULT 'Pending'")) {
            $statusCol = 'status';
            $colsLamaran[] = 'status';
        }
    }
}

$daftarStatusHr = ['Pending', 'Screening', 'Psikotes', 'Interview', 'Hasil Akhir'];
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'POST') {
    $input  = getRequestInput();
    $action = strtolower(trim((string) ($input['action'] ?? '')));

    if ($action === 'delete' || isset($input['hapus'])) {
        $idHapus = intval($input['id_pelamar'] ?? ($input['hapus'] ?? 0));
        if ($idHapus <= 0) {
            mysqli_close($conn);
            jsonResponse(400, false, 'ID pelamar tidak valid.');
        }

        $qFile = mysqli_query($conn, "SELECT `pas_foto`, `ktp`, `ijazah`, `skck`, `kk` FROM `pelamar` WHERE `id_pelamar` = {$idHapus} LIMIT 1");
        if ($qFile && ($fData = mysqli_fetch_assoc($qFile))) {
            $uploadsDir = realpath(__DIR__ . '/../../uploads');
            foreach (['pas_foto', 'ktp', 'ijazah', 'skck', 'kk'] as $b) {
                if (!empty($fData[$b])) {
                    $filePath = realpath(__DIR__ . '/../../uploads/' . basename((string) $fData[$b]));
                    if ($filePath && $uploadsDir && strpos($filePath, $uploadsDir) === 0 && file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
        }

        if ($tblLamaran !== '') {
            mysqli_query($conn, "DELETE FROM `{$tblLamaran}` WHERE `{$fkPelamar}` = {$idHapus}");
        }

        $ok = mysqli_query($conn, "DELETE FROM `pelamar` WHERE `id_pelamar` = {$idHapus}");
        $err = mysqli_error($conn);
        mysqli_close($conn);

        if (!$ok) {
            jsonResponse(500, false, 'Gagal menghapus data pelamar: ' . $err);
        }

        jsonResponse(200, true, 'Data pelamar berhasil dihapus!');
    }

    // Default POST: ubah status lamaran
    $idPelTarget = intval($input['id_pelamar'] ?? 0);
    $statusBaru  = trim((string) ($input['status_baru'] ?? 'Pending'));

    if ($tblLamaran === '' || $statusCol === '' || !in_array($statusBaru, $daftarStatusHr, true) || $idPelTarget <= 0) {
        mysqli_close($conn);
        jsonResponse(400, false, 'Tabel lamaran tidak ditemukan atau pelamar belum mengajukan lamaran.');
    }

    $stmtSt = mysqli_prepare($conn, "UPDATE `{$tblLamaran}` SET `{$statusCol}` = ? WHERE `{$fkPelamar}` = ?");
    if (!$stmtSt) {
        mysqli_close($conn);
        jsonResponse(500, false, 'Gagal menyiapkan query pembaruan status.');
    }

    mysqli_stmt_bind_param($stmtSt, 'si', $statusBaru, $idPelTarget);
    $ok = mysqli_stmt_execute($stmtSt);
    $err = mysqli_error($conn);
    mysqli_stmt_close($stmtSt);
    mysqli_close($conn);

    if (!$ok) {
        jsonResponse(500, false, 'Gagal memperbarui status lamaran: ' . $err);
    }

    jsonResponse(200, true, "Status lamaran berhasil diperbarui menjadi {$statusBaru}!", [
        'id_pelamar'  => $idPelTarget,
        'status_baru' => $statusBaru,
    ]);
}

if ($method !== 'GET') {
    mysqli_close($conn);
    jsonResponse(405, false, 'Method tidak diizinkan.');
}

// Rakit query relasi lamaran & lowongan
$joinSql            = '';
$kolomPosisiExpr    = "''";
$kolomTglLamarExpr = in_array('tgl_melamar', $colsLamaran, true) ? 'MAX(l.tgl_melamar)' : 'NULL';
$kolomStatusExpr    = ($statusCol !== '') ? "MAX(l.`{$statusCol}`)" : "''";
$kolomJmlLamarExpr  = ($tblLamaran !== '') ? "COUNT(l.`{$pkLamaran}`)" : '0';
$hasPosisiCol       = false;

if ($tblLamaran !== '') {
    $joinSql = "LEFT JOIN `{$tblLamaran}` l ON p.id_pelamar = l.`{$fkPelamar}`";

    foreach ($colsLamaran as $cLam) {
        if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $cLam) && !preg_match('/^id_/i', $cLam)) {
            $kolomPosisiExpr = "GROUP_CONCAT(DISTINCT l.`{$cLam}` SEPARATOR ', ')";
            $hasPosisiCol = true;
            break;
        }
    }

    if (!$hasPosisiCol) {
        $fkLowongan = '';
        foreach ($colsLamaran as $cLam) {
            if ($cLam !== $fkPelamar && preg_match('/(lowongan|loker|posisi)/i', $cLam)) {
                $fkLowongan = $cLam;
                break;
            }
        }

        if ($fkLowongan !== '') {
            foreach ($allTables as $tbl) {
                if ($tbl === 'pelamar' || $tbl === 'admin' || $tbl === $tblLamaran) {
                    continue;
                }
                $colsLow = [];
                $qClow = mysqli_query($conn, "SHOW COLUMNS FROM `{$tbl}`");
                if ($qClow) {
                    while ($c = mysqli_fetch_assoc($qClow)) {
                        $colsLow[] = $c['Field'];
                    }
                }
                $pkLow = $colsLow[0] ?? '';
                if ($pkLow === $fkLowongan || in_array($fkLowongan, $colsLow, true)) {
                    $namaColLow = '';
                    foreach ($colsLow as $cl) {
                        if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $cl) && !preg_match('/^id_/i', $cl)) {
                            $namaColLow = $cl;
                            break;
                        }
                    }
                    if ($namaColLow !== '') {
                        $joinSql .= " LEFT JOIN `{$tbl}` low ON l.`{$fkLowongan}` = low.`{$pkLow}`";
                        $kolomPosisiExpr = "GROUP_CONCAT(DISTINCT low.`{$namaColLow}` SEPARATOR ', ')";
                        $hasPosisiCol = true;
                        break;
                    }
                }
            }
        }
    }
}

$keywordRaw      = trim((string) ($_GET['cari'] ?? ''));
$filterPendRaw  = trim((string) ($_GET['pendidikan'] ?? ''));
$filterStatusRaw = trim((string) ($_GET['filter_status'] ?? ''));

$whereClauses = [];
$params       = [];
$types        = '';

if ($keywordRaw !== '') {
    $whereClauses[] = "(p.nama_lengkap LIKE ? OR p.email LIKE ? OR p.kota LIKE ? OR p.no_telepon LIKE ?)";
    $like = '%' . $keywordRaw . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

if ($filterPendRaw !== '') {
    $whereClauses[] = "p.pend_terakhir LIKE ?";
    $params[] = '%' . $filterPendRaw . '%';
    $types .= 's';
}

if ($filterStatusRaw !== '' && $statusCol !== '') {
    $whereClauses[] = "l.`{$statusCol}` = ?";
    $params[] = $filterStatusRaw;
    $types .= 's';
}

$whereSql = count($whereClauses) > 0 ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$allowedSort = [
    'nama'       => 'p.nama_lengkap',
    'pendidikan' => 'p.pend_terakhir',
    'nikah'      => 'p.status_nikah',
    'kota'       => 'p.kota',
    'lamaran'    => $hasPosisiCol ? 'posisi_dilamar' : 'p.nama_lengkap',
    'status'     => 'status_lamaran_terkini',
    'tgl_daftar' => 'tgl_daftar_final',
];

$sortBy  = (string) ($_GET['sort'] ?? 'tgl_daftar');
$orderBy = strtoupper((string) ($_GET['order'] ?? 'DESC'));

if (!array_key_exists($sortBy, $allowedSort)) {
    $sortBy = 'tgl_daftar';
}
if ($orderBy !== 'ASC' && $orderBy !== 'DESC') {
    $orderBy = 'DESC';
}
$orderColumn = $allowedSort[$sortBy];

$sql = "SELECT
            p.*,
            {$kolomPosisiExpr} AS posisi_dilamar,
            COALESCE({$kolomTglLamarExpr}, p.tgl_buat) AS tgl_daftar_final,
            {$kolomStatusExpr} AS status_lamaran_terkini,
            {$kolomJmlLamarExpr} AS jumlah_lamaran
        FROM `pelamar` p
        {$joinSql}
        {$whereSql}
        GROUP BY p.id_pelamar
        ORDER BY {$orderColumn} {$orderBy}";

if ($types !== '') {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $query = mysqli_stmt_get_result($stmt);
} else {
    $query = mysqli_query($conn, $sql);
    $stmt = null;
}

$pelamarList = [];
$uploadsDir = realpath(__DIR__ . '/../../uploads');

if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $fotoFile = !empty($row['pas_foto']) ? basename((string) $row['pas_foto']) : '';
        $fotoExists = false;
        if ($fotoFile !== '' && $uploadsDir) {
            $fp = realpath(__DIR__ . '/../../uploads/' . $fotoFile);
            if ($fp && strpos($fp, $uploadsDir) === 0 && file_exists($fp)) {
                $fotoExists = true;
            }
        }

        $sudahMelamar  = (!empty($row['posisi_dilamar']) || intval($row['jumlah_lamaran'] ?? 0) > 0);
        $statusSaatIni = !empty($row['status_lamaran_terkini']) ? (string) $row['status_lamaran_terkini'] : 'Pending';
        $statusNorm    = ucwords(strtolower(trim($statusSaatIni)));
        if (!in_array($statusNorm, $daftarStatusHr, true)) {
            $statusNorm = 'Pending';
        }

        $row['id_pelamar']       = intval($row['id_pelamar']);
        $row['foto_file']        = $fotoFile;
        $row['foto_exists']      = $fotoExists;
        $row['sudah_melamar']    = $sudahMelamar;
        $row['status_norm']      = $statusNorm;
        $row['status_css_class'] = str_replace(' ', '-', $statusNorm);
        $pelamarList[]           = $row;
    }
}

if ($stmt) {
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);

jsonResponse(200, true, 'Data pelamar berhasil dimuat.', [
    'pelamar_list'     => $pelamarList,
    'total'            => count($pelamarList),
    'daftar_status_hr' => $daftarStatusHr,
]);