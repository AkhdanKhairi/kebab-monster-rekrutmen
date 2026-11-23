<?php
// File: admin/hasil_akhir.php (Rekap Status Pelamar + Kirim Bareng + Sorting + Lowongan)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$id_admin  = intval($_SESSION['user_id'] ?? 0);

if ($id_admin <= 0 || !in_array($role_user, ['admin', 'hr', 'hrd'], true)) {
    echo "<script>alert('Silakan login terlebih dahulu sebagai HR/Admin!'); window.location='../login.php';</script>";
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Pastikan kolom hasil_akhir & pesan_hasil_akhir ada di tabel pelamar
$kolom_pelamar = [];
$q_cols = mysqli_query($conn, "SHOW COLUMNS FROM `pelamar`");
if ($q_cols) {
    while ($c = mysqli_fetch_assoc($q_cols)) {
        $kolom_pelamar[] = $c['Field'];
    }
}
if (!in_array('hasil_akhir', $kolom_pelamar, true)) {
    @mysqli_query($conn, "ALTER TABLE `pelamar` ADD COLUMN `hasil_akhir` VARCHAR(30) NULL");
    $kolom_pelamar[] = 'hasil_akhir';
}
if (!in_array('pesan_hasil_akhir', $kolom_pelamar, true)) {
    @mysqli_query($conn, "ALTER TABLE `pelamar` ADD COLUMN `pesan_hasil_akhir` TEXT NULL");
    $kolom_pelamar[] = 'pesan_hasil_akhir';
}

$pesan_sukses = "";
$pesan_error  = "";

// --- PROSES SIMPAN / KIRIM BARENG KE PELAMAR TERPILIH ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_hasil_bareng'])) {
    $ids_terpilih  = $_POST['id_pelamar'] ?? [];
    $status_massal = trim((string)($_POST['status_massal'] ?? ''));
    $pesan_massal  = trim((string)($_POST['pesan_massal'] ?? ''));
    $timpa_pesan   = isset($_POST['timpa_pesan']) ? 1 : 0;

    $allowed_status = ['Diterima', 'Tidak Diterima'];

    if (!is_array($ids_terpilih) || empty($ids_terpilih)) {
        $pesan_error = "Centang minimal 1 pelamar terlebih dahulu sebelum memproses bareng.";
    } elseif (!in_array($status_massal, $allowed_status, true)) {
        $pesan_error = "Pilih status hasil akhir (Diterima atau Tidak Diterima) terlebih dahulu.";
    } else {
        $clean_ids = [];
        foreach ($ids_terpilih as $raw_id) {
            $id_int = intval($raw_id);
            if ($id_int > 0) {
                $clean_ids[] = $id_int;
            }
        }
        $clean_ids = array_values(array_unique($clean_ids));

        if (empty($clean_ids)) {
            $pesan_error = "Data pelamar yang dipilih tidak valid.";
        } else {
            $placeholders = implode(',', array_fill(0, count($clean_ids), '?'));
            $types_ids    = str_repeat('i', count($clean_ids));

            if ($pesan_massal !== '') {
                if ($timpa_pesan === 1) {
                    $sql_upd = "UPDATE `pelamar` SET `hasil_akhir` = ?, `pesan_hasil_akhir` = ? WHERE `id_pelamar` IN ($placeholders)";
                    $stmt_upd = mysqli_prepare($conn, $sql_upd);
                    if ($stmt_upd) {
                        $params = array_merge([$status_massal, $pesan_massal], $clean_ids);
                        mysqli_stmt_bind_param($stmt_upd, "ss" . $types_ids, ...$params);
                    }
                } else {
                    $sql_upd = "UPDATE `pelamar`
                                SET `hasil_akhir` = ?,
                                    `pesan_hasil_akhir` = CASE
                                        WHEN `pesan_hasil_akhir` IS NULL OR TRIM(`pesan_hasil_akhir`) = '' THEN ?
                                        ELSE `pesan_hasil_akhir`
                                    END
                                WHERE `id_pelamar` IN ($placeholders)";
                    $stmt_upd = mysqli_prepare($conn, $sql_upd);
                    if ($stmt_upd) {
                        $params = array_merge([$status_massal, $pesan_massal], $clean_ids);
                        mysqli_stmt_bind_param($stmt_upd, "ss" . $types_ids, ...$params);
                    }
                }
            } else {
                $sql_upd = "UPDATE `pelamar` SET `hasil_akhir` = ? WHERE `id_pelamar` IN ($placeholders)";
                $stmt_upd = mysqli_prepare($conn, $sql_upd);
                if ($stmt_upd) {
                    $params = array_merge([$status_massal], $clean_ids);
                    mysqli_stmt_bind_param($stmt_upd, "s" . $types_ids, ...$params);
                }
            }

            if (!empty($stmt_upd) && mysqli_stmt_execute($stmt_upd)) {
                $jml_berhasil = mysqli_stmt_affected_rows($stmt_upd);
                mysqli_stmt_close($stmt_upd);
                $pesan_sukses = "Berhasil memperbarui status <strong>" . count($clean_ids) . " pelamar terpilih</strong> menjadi <strong>" . htmlspecialchars($status_massal) . "</strong>" . ($pesan_massal !== '' ? " beserta pesan hasil akhir." : ".");
                if ($jml_berhasil === 0) {
                    $pesan_sukses .= " (Status sudah sesuai sebelumnya.)";
                }
            } else {
                $pesan_error = "Gagal memperbarui data pelamar: " . mysqli_error($conn);
                if (!empty($stmt_upd)) {
                    mysqli_stmt_close($stmt_upd);
                }
            }
        }
    }
}

// --- DETEKSI OTOMATIS TABEL LAMARAN & LOWONGAN ---
$all_tables = [];
$q_tbl = mysqli_query($conn, "SHOW TABLES");
if ($q_tbl) {
    while ($t = mysqli_fetch_row($q_tbl)) {
        $all_tables[] = $t[0];
    }
}

$tbl_lamaran  = "";
$cols_lamaran = [];
foreach ($all_tables as $tbl) {
    if ($tbl === 'pelamar' || $tbl === 'admin') {
        continue;
    }
    $cols = [];
    $q_c = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
    if ($q_c) {
        while ($c = mysqli_fetch_assoc($q_c)) {
            $cols[] = $c['Field'];
        }
    }
    if (in_array('tgl_melamar', $cols, true) || (in_array('id_pelamar', $cols, true) && count($cols) <= 15)) {
        $tbl_lamaran  = $tbl;
        $cols_lamaran = $cols;
        if (in_array('tgl_melamar', $cols, true)) {
            break;
        }
    }
}

$join_sql          = "";
$posisi_expr       = "''";
$posisi_filter_col = "";
$daftar_lowongan   = [];

if (!empty($tbl_lamaran)) {
    $fk_pelamar = in_array('id_pelamar', $cols_lamaran, true) ? 'id_pelamar' : (in_array('pelamar_id', $cols_lamaran, true) ? 'pelamar_id' : '');
    if ($fk_pelamar !== '') {
        $join_sql = " LEFT JOIN `$tbl_lamaran` l ON p.id_pelamar = l.`$fk_pelamar`";

        $direct_pos_col = "";
        foreach ($cols_lamaran as $c_lam) {
            if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $c_lam) && !preg_match('/^id_/i', $c_lam)) {
                $direct_pos_col = $c_lam;
                break;
            }
        }

        if ($direct_pos_col !== '') {
            $posisi_expr = "GROUP_CONCAT(DISTINCT l.`$direct_pos_col` ORDER BY l.`$direct_pos_col` SEPARATOR ', ')";
            $posisi_filter_col = "l.`$direct_pos_col`";
            $q_opt = mysqli_query($conn, "SELECT DISTINCT `$direct_pos_col` AS nama_low FROM `$tbl_lamaran` WHERE `$direct_pos_col` IS NOT NULL AND TRIM(`$direct_pos_col`) != '' ORDER BY `$direct_pos_col` ASC");
            if ($q_opt) {
                while ($ro = mysqli_fetch_assoc($q_opt)) {
                    $daftar_lowongan[] = trim((string)$ro['nama_low']);
                }
            }
        } else {
            $fk_lowongan = "";
            foreach ($cols_lamaran as $c_lam) {
                if ($c_lam !== $fk_pelamar && preg_match('/(lowongan|loker|posisi)/i', $c_lam)) {
                    $fk_lowongan = $c_lam;
                    break;
                }
            }

            if ($fk_lowongan !== '') {
                foreach ($all_tables as $tbl_low) {
                    if (in_array($tbl_low, ['pelamar', 'admin', $tbl_lamaran], true)) {
                        continue;
                    }
                    $cols_low = [];
                    $q_cl = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_low`");
                    if ($q_cl) {
                        while ($c = mysqli_fetch_assoc($q_cl)) {
                            $cols_low[] = $c['Field'];
                        }
                    }
                    $pk_low = $cols_low[0] ?? '';
                    if ($pk_low !== '' && ($pk_low === $fk_lowongan || in_array($fk_lowongan, $cols_low, true))) {
                        $nama_col_low = "";
                        foreach (['nama_lowongan', 'posisi', 'judul_lowongan', 'judul', 'nama', 'nama_posisi', 'job_title'] as $cand_n) {
                            if (in_array($cand_n, $cols_low, true)) {
                                $nama_col_low = $cand_n;
                                break;
                            }
                        }
                        if ($nama_col_low === '') {
                            foreach ($cols_low as $cl) {
                                if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $cl) && !preg_match('/^id_/i', $cl)) {
                                    $nama_col_low = $cl;
                                    break;
                                }
                            }
                        }
                        if ($nama_col_low !== '') {
                            $join_sql .= " LEFT JOIN `$tbl_low` low ON l.`$fk_lowongan` = low.`$pk_low`";
                            $posisi_expr = "GROUP_CONCAT(DISTINCT low.`$nama_col_low` ORDER BY low.`$nama_col_low` SEPARATOR ', ')";
                            $posisi_filter_col = "low.`$nama_col_low`";

                            $q_opt = mysqli_query($conn, "SELECT DISTINCT `$nama_col_low` AS nama_low FROM `$tbl_low` WHERE `$nama_col_low` IS NOT NULL AND TRIM(`$nama_col_low`) != '' ORDER BY `$nama_col_low` ASC");
                            if ($q_opt) {
                                while ($ro = mysqli_fetch_assoc($q_opt)) {
                                    $daftar_lowongan[] = trim((string)$ro['nama_low']);
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
}

$daftar_lowongan = array_values(array_unique(array_filter($daftar_lowongan)));

// --- HITUNG STATISTIK KESELURUHAN ---
$stat_total    = 0;
$stat_diterima = 0;
$stat_ditolak  = 0;
$stat_menunggu = 0;

$q_stat = mysqli_query($conn, "SELECT hasil_akhir FROM `pelamar`");
if ($q_stat) {
    while ($rs = mysqli_fetch_assoc($q_stat)) {
        $stat_total++;
        $st_s = trim((string)($rs['hasil_akhir'] ?? ''));
        if ($st_s === 'Diterima') {
            $stat_diterima++;
        } elseif ($st_s === 'Tidak Diterima') {
            $stat_ditolak++;
        } else {
            $stat_menunggu++;
        }
    }
}

// --- FILTER, PENCARIAN & SORTING ---
$cari_nama       = trim((string)($_GET['cari'] ?? ''));
$filter_st       = trim((string)($_GET['filter_status'] ?? ''));
$filter_lowongan = trim((string)($_GET['filter_lowongan'] ?? ''));
$sort_by         = trim((string)($_GET['sort_by'] ?? 'nama'));
$sort_dir        = (strtolower((string)($_GET['sort_dir'] ?? 'asc')) === 'desc') ? 'DESC' : 'ASC';

$where_parts = [];
if ($cari_nama !== '') {
    $cari_esc = mysqli_real_escape_string($conn, $cari_nama);
    $where_parts[] = "(p.nama_lengkap LIKE '%$cari_esc%' OR p.email LIKE '%$cari_esc%')";
}
if ($filter_st === 'Diterima' || $filter_st === 'Tidak Diterima') {
    $fst_esc = mysqli_real_escape_string($conn, $filter_st);
    $where_parts[] = "p.hasil_akhir = '$fst_esc'";
} elseif ($filter_st === 'Belum Ditentukan') {
    $where_parts[] = "(p.hasil_akhir IS NULL OR TRIM(p.hasil_akhir) = '' OR p.hasil_akhir NOT IN ('Diterima', 'Tidak Diterima'))";
}
if ($filter_lowongan !== '' && $posisi_filter_col !== '') {
    $flow_esc = mysqli_real_escape_string($conn, $filter_lowongan);
    $where_parts[] = "$posisi_filter_col = '$flow_esc'";
}

$where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

$status_rank_expr = "CASE
    WHEN p.hasil_akhir = 'Diterima' THEN 1
    WHEN p.hasil_akhir = 'Tidak Diterima' THEN 2
    ELSE 3
END";

switch ($sort_by) {
    case 'lowongan':
        $order_sql = "ORDER BY ($posisi_expr IS NULL OR $posisi_expr = '') ASC, $posisi_expr $sort_dir, p.nama_lengkap ASC";
        break;
    case 'email':
        $order_sql = "ORDER BY COALESCE(p.email, '') $sort_dir, p.nama_lengkap ASC";
        break;
    case 'status':
        $order_sql = "ORDER BY $status_rank_expr $sort_dir, p.nama_lengkap ASC";
        break;
    case 'nama':
    default:
        $sort_by = 'nama';
        $order_sql = "ORDER BY p.nama_lengkap $sort_dir";
        break;
}

function buildSortUrl(string $kolom, string $aktifKolom, string $aktifDir, string $cari, string $filterSt, string $filterLow): string {
    $nextDir = ($aktifKolom === $kolom && strtoupper($aktifDir) === 'ASC') ? 'desc' : 'asc';
    return '?' . http_build_query([
        'cari'            => $cari,
        'filter_status'   => $filterSt,
        'filter_lowongan' => $filterLow,
        'sort_by'         => $kolom,
        'sort_dir'        => $nextDir,
    ]);
}

function sortArrow(string $kolom, string $aktifKolom, string $aktifDir): string {
    if ($aktifKolom !== $kolom) {
        return '↕️';
    }
    return (strtoupper($aktifDir) === 'ASC') ? '🔼' : '🔽';
}

$pelamar_list = [];
$sql_list = "SELECT
                p.id_pelamar,
                p.nama_lengkap,
                p.email,
                p.hasil_akhir,
                p.pesan_hasil_akhir,
                $posisi_expr AS posisi_dilamar
             FROM `pelamar` p
             $join_sql
             $where_sql
             GROUP BY p.id_pelamar
             $order_sql";

$q_pelamar = mysqli_query($conn, $sql_list);
if ($q_pelamar) {
    while ($row = mysqli_fetch_assoc($q_pelamar)) {
        $pelamar_list[] = $row;
    }
}

mysqli_close($conn);
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .ha-page {
        max-width: 1180px;
        margin: 14px auto 40px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: #1e293b;
    }
    .ha-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }
    .ha-top-bar h1 {
        margin: 0 0 4px 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }
    .ha-top-bar p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
    }
    .btn-top-link {
        text-decoration: none;
        background: #047857;
        color: #ffffff;
        min-height: 40px;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.15s ease, transform 0.15s ease;
    }
    .btn-top-link:hover {
        background: #065f46;
        transform: translateY(-1px);
    }

    .alert-ok {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
        padding: 11px 15px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .alert-err {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 11px 15px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .ha-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 15px 16px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .stat-label { font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; }
    .stat-num { font-size: 23px; font-weight: 800; margin-top: 3px; color: #0f172a; }
    .stat-icon {
        width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 19px;
    }
    .ic-all { background: #f1f5f9; }
    .ic-ok  { background: #dcfce7; }
    .ic-no  { background: #fee2e2; }
    .ic-pd  { background: #fef3c7; }

    .ha-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .ha-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }
    .ha-card-title {
        margin: 0;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
    }

    /* Filter & Sorting Bar (GET) */
    .ha-filter-bar {
        display: grid;
        grid-template-columns: minmax(160px, 1.3fr) repeat(4, minmax(125px, 1fr)) auto;
        gap: 9px;
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 11px;
        margin-bottom: 14px;
    }
    .ha-input, .ha-select {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        color: #1e293b;
        background: #ffffff;
        box-sizing: border-box;
    }
    .ha-input:focus, .ha-select:focus, .ha-textarea:focus {
        outline: none;
        border-color: #047857;
        box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.12);
    }
    .ha-filter-btns {
        display: flex;
        gap: 6px;
        align-items: center;
        white-space: nowrap;
    }
    .btn-cari-ha {
        background: #047857;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-cari-ha:hover { background: #065f46; }
    .btn-reset-ha {
        text-decoration: none;
        background: #e2e8f0;
        color: #334155;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 12px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .btn-reset-ha:hover { background: #cbd5e1; }

    /* Panel Kirim Bareng (POST) */
    .bulk-action-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .bulk-top-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }
    .bulk-selected-pill {
        background: #065f46;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 999px;
    }
    .bulk-controls-grid {
        display: grid;
        grid-template-columns: 210px minmax(220px, 1fr) auto;
        gap: 10px;
        align-items: start;
    }
    .ha-textarea {
        width: 100%;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        color: #1e293b;
        background: #ffffff;
        box-sizing: border-box;
        resize: vertical;
    }
    .bulk-right-col {
        display: flex;
        flex-direction: column;
        gap: 7px;
        align-items: flex-end;
    }
    .btn-kirim-bareng {
        background: #047857;
        color: #ffffff;
        border: none;
        border-radius: 9px;
        min-height: 40px;
        padding: 9px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }
    .btn-kirim-bareng:hover { background: #065f46; }
    .chk-timpa {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #166534;
        font-weight: 600;
        cursor: pointer;
    }

    .mobile-select-all-ha {
        display: none;
    }

    .cb-ha-row,
    #checkAllHa,
    #checkAllHaMobile {
        width: 18px;
        height: 18px;
        accent-color: #047857;
        cursor: pointer;
    }

    .ha-table-wrap { overflow-x: auto; }
    .ha-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ha-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        padding: 12px 14px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .sort-th-link {
        color: #334155;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .sort-th-link:hover { color: #047857; }
    .ha-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .ha-table tbody tr.pelamar-row { cursor: pointer; }
    .ha-table tbody tr.pelamar-row:hover td { background: #f8fafc; }
    .ha-table tbody tr.pelamar-row.row-checked td { background: #ecfdf5; }

    .lowongan-tag {
        display: inline-block;
        background: #e0f2fe;
        color: #075985;
        font-size: 12px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
    }
    .lowongan-empty {
        color: #94a3b8;
        font-size: 12px;
        font-style: italic;
    }

    .pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }
    .pill-ok { background: #dcfce7; color: #166534; }
    .pill-no { background: #fee2e2; color: #991b1b; }
    .pill-pd { background: #fef3c7; color: #92400e; }

    .pesan-preview {
        display: block;
        font-size: 11.5px;
        color: #64748b;
        margin-top: 3px;
        max-width: 340px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-atur-ucapan {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        text-decoration: none;
        background: #047857;
        color: #ffffff;
        min-height: 36px;
        padding: 7px 13px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-atur-ucapan:hover { background: #065f46; }

    @media (max-width: 1120px) {
        .ha-filter-bar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .ha-filter-btns {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
    @media (max-width: 1020px) {
        .bulk-controls-grid {
            grid-template-columns: 1fr;
        }
        .bulk-right-col {
            align-items: flex-start;
        }
    }
    @media (max-width: 960px) {
        .ha-stats { grid-template-columns: repeat(2, 1fr); }
    }

    /* Mode HP: ubah tabel menjadi kartu interaktif & perbesar target sentuh */
    @media (max-width: 768px) {
        .ha-page {
            margin: 8px auto 30px auto;
            padding: 0 8px;
        }
        .ha-top-bar h1 {
            font-size: 17px;
        }
        .btn-top-link {
            width: 100%;
            min-height: 42px;
            font-size: 13.5px;
        }
        .ha-card {
            padding: 14px 12px;
            border-radius: 12px;
        }
        .ha-filter-bar {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .ha-filter-btns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }
        .ha-input,
        .ha-select,
        .ha-textarea {
            min-height: 42px;
            font-size: 16px;
        }
        .btn-cari-ha,
        .btn-reset-ha {
            min-height: 42px;
            font-size: 14px;
        }
        .bulk-action-box {
            padding: 12px;
        }
        .bulk-right-col {
            width: 100%;
            align-items: stretch;
        }
        .btn-kirim-bareng {
            width: 100%;
            min-height: 44px;
            font-size: 14px;
        }

        .mobile-select-all-ha {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            padding: 10px 12px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
        }

        .ha-table {
            display: block;
        }
        .ha-table thead {
            display: none;
        }
        .ha-table tbody {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ha-table tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .ha-table tbody tr.row-checked {
            background: #ecfdf5;
            border-color: #6ee7b7;
        }
        .ha-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e2e8f0;
            text-align: right;
            font-size: 13px;
            word-break: break-word;
        }
        .ha-table td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .ha-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #065f46;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .ha-table td[data-label="Pilih"] {
            align-items: center;
            background: #f8fafc;
            margin: -12px -12px 4px -12px;
            padding: 9px 12px;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 10px 10px 0 0;
        }
        .ha-table tbody tr.row-checked td[data-label="Pilih"] {
            background: #d1fae5;
        }
        .cb-ha-row,
        #checkAllHaMobile {
            width: 20px;
            height: 20px;
        }
        .pesan-preview {
            max-width: 100%;
            white-space: normal;
            text-align: right;
        }
        .btn-atur-ucapan {
            width: 100%;
            min-height: 40px;
            font-size: 13px;
        }
    }

    @media (max-width: 560px) {
        .ha-stats { grid-template-columns: 1fr; }
    }
</style>

<div class="ha-page">
    <div class="ha-top-bar">
        <div>
            <h1>Rekap Status Akhir Pelamar</h1>
            <p>Pantau status penerimaan karyawan Kebab Monster, atur keputusan bareng, atau buka ucapan hasil akhir per kandidat.</p>
        </div>
        <a href="ucapan_hasil_akhir.php" class="btn-top-link">✍️ Buka Halaman Ucapan Hasil Akhir</a>
    </div>

    <div id="haApiAlertBox">
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-ok">✅ <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert-err">❌ <?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>
    </div>

    <div class="ha-stats">
        <div class="stat-card">
            <div>
                <div class="stat-label">Total Pelamar</div>
                <div class="stat-num"><?php echo intval($stat_total); ?></div>
            </div>
            <div class="stat-icon ic-all">👥</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Diterima</div>
                <div class="stat-num" style="color:#059669;"><?php echo intval($stat_diterima); ?></div>
            </div>
            <div class="stat-icon ic-ok">✅</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Tidak Diterima</div>
                <div class="stat-num" style="color:#dc2626;"><?php echo intval($stat_ditolak); ?></div>
            </div>
            <div class="stat-icon ic-no">❌</div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-label">Belum Diputuskan</div>
                <div class="stat-num" style="color:#d97706;"><?php echo intval($stat_menunggu); ?></div>
            </div>
            <div class="stat-icon ic-pd">⏳</div>
        </div>
    </div>

    <!-- Form GET terpisah untuk filter & sorting -->
    <form method="GET" action="" id="formFilterHasilAkhir"></form>

    <!-- Form POST untuk kirim / update hasil akhir bareng -->
    <form method="POST" action="" id="formKirimBareng">
        <input type="hidden" name="kirim_hasil_bareng" value="1">

        <div class="ha-card">
            <div class="ha-card-header">
                <h2 class="ha-card-title">📋 Rekap Status Pelamar</h2>
                <span style="font-size:12.5px; color:#64748b; font-weight:600;">
                    Menampilkan <strong><?php echo count($pelamar_list); ?></strong> dari <?php echo intval($stat_total); ?> pelamar
                </span>
            </div>

            <!-- Bar Pencarian, Filter Lowongan, Filter Status & Sorting -->
            <div class="ha-filter-bar">
                <input
                    type="text"
                    name="cari"
                    form="formFilterHasilAkhir"
                    class="ha-input"
                    placeholder="Cari nama atau email pelamar..."
                    value="<?php echo htmlspecialchars($cari_nama); ?>"
                >

                <select name="filter_lowongan" form="formFilterHasilAkhir" class="ha-select" onchange="this.form.submit()">
                    <option value="">Semua Lowongan</option>
                    <?php foreach ($daftar_lowongan as $nama_low_opt): ?>
                        <option value="<?php echo htmlspecialchars($nama_low_opt); ?>" <?php echo ($filter_lowongan === $nama_low_opt) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($nama_low_opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="filter_status" form="formFilterHasilAkhir" class="ha-select" onchange="this.form.submit()">
                    <option value="" <?php echo ($filter_st === '') ? 'selected' : ''; ?>>Semua Status Akhir</option>
                    <option value="Diterima" <?php echo ($filter_st === 'Diterima') ? 'selected' : ''; ?>>✅ Diterima</option>
                    <option value="Tidak Diterima" <?php echo ($filter_st === 'Tidak Diterima') ? 'selected' : ''; ?>>❌ Tidak Diterima</option>
                    <option value="Belum Ditentukan" <?php echo ($filter_st === 'Belum Ditentukan') ? 'selected' : ''; ?>>⏳ Belum Ditentukan</option>
                </select>

                <select name="sort_by" form="formFilterHasilAkhir" class="ha-select" onchange="this.form.submit()">
                    <option value="nama" <?php echo ($sort_by === 'nama') ? 'selected' : ''; ?>>Sort: Nama Pelamar</option>
                    <option value="lowongan" <?php echo ($sort_by === 'lowongan') ? 'selected' : ''; ?>>Sort: Lowongan</option>
                    <option value="email" <?php echo ($sort_by === 'email') ? 'selected' : ''; ?>>Sort: Email</option>
                    <option value="status" <?php echo ($sort_by === 'status') ? 'selected' : ''; ?>>Sort: Status Akhir</option>
                </select>

                <select name="sort_dir" form="formFilterHasilAkhir" class="ha-select" onchange="this.form.submit()">
                    <option value="asc" <?php echo (strtolower($sort_dir) === 'asc') ? 'selected' : ''; ?>>Naik (A → Z / Status)</option>
                    <option value="desc" <?php echo (strtolower($sort_dir) === 'desc') ? 'selected' : ''; ?>>Turun (Z → A / Status)</option>
                </select>

                <div class="ha-filter-btns">
                    <button type="submit" form="formFilterHasilAkhir" class="btn-cari-ha">🔍 Terapkan</button>
                    <?php if ($cari_nama !== '' || $filter_st !== '' || $filter_lowongan !== '' || $sort_by !== 'nama' || strtolower($sort_dir) !== 'asc'): ?>
                        <a href="hasil_akhir.php" class="btn-reset-ha">Reset</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Panel Aksi Kirim Bareng -->
            <div class="bulk-action-box">
                <div class="bulk-top-row">
                    <div style="font-size:13px; font-weight:700; color:#065f46;">
                        📦 Proses & Kirim Hasil Akhir Bareng
                    </div>
                    <span class="bulk-selected-pill" id="labelTerpilih">0 pelamar dipilih</span>
                </div>

                <div class="bulk-controls-grid">
                    <select name="status_massal" id="statusMassal" class="ha-select">
                        <option value="">-- Pilih Status Bareng --</option>
                        <option value="Diterima">✅ Diterima</option>
                        <option value="Tidak Diterima">❌ Tidak Diterima</option>
                    </select>

                    <textarea
                        name="pesan_massal"
                        rows="2"
                        class="ha-textarea"
                        placeholder="Pesan hasil akhir untuk pelamar terpilih (opsional, jika dikosongkan hanya memperbarui status)..."
                    ></textarea>

                    <div class="bulk-right-col">
                        <button type="submit" class="btn-kirim-bareng">🚀 Simpan & Kirim Bareng</button>
                        <label class="chk-timpa">
                            <input type="checkbox" name="timpa_pesan" value="1">
                            <span>Timpa pesan lama jika sudah ada</span>
                        </label>
                    </div>
                </div>
            </div>

            <?php if (!empty($pelamar_list)): ?>
                <label class="mobile-select-all-ha" for="checkAllHaMobile">
                    <span>Pilih Semua Pelamar Ditampilkan</span>
                    <input type="checkbox" id="checkAllHaMobile" onclick="toggleAllHa(this)">
                </label>
            <?php endif; ?>

            <div class="ha-table-wrap">
                <table class="ha-table">
                    <thead>
                        <tr>
                            <th style="width:38px; text-align:center;">
                                <input type="checkbox" id="checkAllHa" onclick="toggleAllHa(this)" title="Pilih semua">
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars(buildSortUrl('nama', $sort_by, $sort_dir, $cari_nama, $filter_st, $filter_lowongan)); ?>" class="sort-th-link">
                                    Nama Pelamar <?php echo sortArrow('nama', $sort_by, $sort_dir); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars(buildSortUrl('lowongan', $sort_by, $sort_dir, $cari_nama, $filter_st, $filter_lowongan)); ?>" class="sort-th-link">
                                    Lowongan <?php echo sortArrow('lowongan', $sort_by, $sort_dir); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars(buildSortUrl('email', $sort_by, $sort_dir, $cari_nama, $filter_st, $filter_lowongan)); ?>" class="sort-th-link">
                                    Email <?php echo sortArrow('email', $sort_by, $sort_dir); ?>
                                </a>
                            </th>
                            <th>
                                <a href="<?php echo htmlspecialchars(buildSortUrl('status', $sort_by, $sort_dir, $cari_nama, $filter_st, $filter_lowongan)); ?>" class="sort-th-link">
                                    Status Akhir <?php echo sortArrow('status', $sort_by, $sort_dir); ?>
                                </a>
                            </th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pelamar_list)): ?>
                            <?php foreach ($pelamar_list as $p): ?>
                                <?php
                                    $pid_row   = intval($p['id_pelamar']);
                                    $low_row   = trim((string)($p['posisi_dilamar'] ?? ''));
                                    $st_row    = trim((string)($p['hasil_akhir'] ?? ''));
                                    $pesan_row = trim((string)($p['pesan_hasil_akhir'] ?? ''));
                                ?>
                                <tr class="pelamar-row" onclick="toggleRowHa(event, this)">
                                    <td data-label="Pilih" style="text-align:center;" onclick="event.stopPropagation();">
                                        <input
                                            type="checkbox"
                                            name="id_pelamar[]"
                                            value="<?php echo $pid_row; ?>"
                                            class="cb-ha-row"
                                            onchange="syncHaSelection()"
                                        >
                                    </td>
                                    <td data-label="Nama Pelamar">
                                        <div>
                                            <strong><?php echo htmlspecialchars($p['nama_lengkap'] ?? '-'); ?></strong>
                                            <?php if ($pesan_row !== ''): ?>
                                                <span class="pesan-preview" title="<?php echo htmlspecialchars($pesan_row); ?>">
                                                    💬 <?php echo htmlspecialchars($pesan_row); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Lowongan">
                                        <?php if ($low_row !== ''): ?>
                                            <span class="lowongan-tag"><?php echo htmlspecialchars($low_row); ?></span>
                                        <?php else: ?>
                                            <span class="lowongan-empty">Belum melamar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Email"><?php echo htmlspecialchars($p['email'] ?: '-'); ?></td>
                                    <td data-label="Status Akhir">
                                        <?php if ($st_row === 'Diterima'): ?>
                                            <span class="pill-badge pill-ok">✅ Diterima</span>
                                        <?php elseif ($st_row === 'Tidak Diterima'): ?>
                                            <span class="pill-badge pill-no">❌ Tidak Diterima</span>
                                        <?php else: ?>
                                            <span class="pill-badge pill-pd">⏳ Belum Ditentukan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Aksi" style="text-align:right;" onclick="event.stopPropagation();">
                                        <a href="ucapan_hasil_akhir.php?id_pelamar=<?php echo $pid_row; ?>" class="btn-atur-ucapan">
                                            ✍️ Ucapan Hasil Akhir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align:center; padding:24px; color:#64748b;">Tidak ada data pelamar yang sesuai dengan filter.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
function toggleRowHa(event, rowEl) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'A' || event.target.tagName === 'BUTTON') {
        return;
    }
    const cb = rowEl.querySelector('.cb-ha-row');
    if (cb) {
        cb.checked = !cb.checked;
        syncHaSelection();
    }
}

function toggleAllHa(masterEl) {
    document.querySelectorAll('.cb-ha-row').forEach(cb => {
        cb.checked = masterEl.checked;
    });
    syncHaSelection();
}

function syncHaSelection() {
    const allCbs = document.querySelectorAll('.cb-ha-row');
    let count = 0;
    document.querySelectorAll('.pelamar-row').forEach(row => {
        const cb = row.querySelector('.cb-ha-row');
        const checked = !!(cb && cb.checked);
        row.classList.toggle('row-checked', checked);
        if (checked) count++;
    });

    const allChecked = (allCbs.length > 0 && count === allCbs.length);
    const masterEl = document.getElementById('checkAllHa');
    const mobileEl = document.getElementById('checkAllHaMobile');
    if (masterEl) masterEl.checked = allChecked;
    if (mobileEl) mobileEl.checked = allChecked;

    const labelEl = document.getElementById('labelTerpilih');
    if (labelEl) {
        labelEl.textContent = count + ' pelamar dipilih';
    }
}

document.getElementById('formKirimBareng').addEventListener('submit', function(e) {
    const form = this;
    const checkedInputs = Array.from(document.querySelectorAll('.cb-ha-row:checked'));
    const count = checkedInputs.length;
    const statusVal = document.getElementById('statusMassal').value;
    if (count === 0) {
        e.preventDefault();
        alert('Silakan centang minimal 1 pelamar terlebih dahulu.');
        return;
    }
    if (!statusVal) {
        e.preventDefault();
        alert('Silakan pilih status hasil akhir (Diterima atau Tidak Diterima) terlebih dahulu.');
        return;
    }

    if (!window.fetch) {
        return;
    }

    e.preventDefault();

    const fd = new FormData(form);
    const payload = {
        id_pelamar: checkedInputs.map(cb => parseInt(cb.value, 10)).filter(id => id > 0),
        status_massal: statusVal,
        pesan_massal: String(fd.get('pesan_massal') || '').trim(),
        timpa_pesan: fd.get('timpa_pesan') ? 1 : 0
    };

    const submitBtn = form.querySelector('.btn-kirim-bareng');
    if (submitBtn) submitBtn.disabled = true;

    const apiEndpoints = [
        '../api/admin/hasil_akhir.php',
        '../api/hasil_akhir.php'
    ];

    const postHasilAkhir = async () => {
        for (const url of apiEndpoints) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.status === 404) {
                continue;
            }
            return res.json();
        }
        throw new Error('Endpoint API tidak ditemukan.');
    };

    postHasilAkhir()
        .then(data => {
            if (!data || !data.success) {
                throw new Error((data && data.message) || 'Gagal memperbarui status hasil akhir.');
            }
            const alertBox = document.getElementById('haApiAlertBox');
            if (alertBox) {
                alertBox.innerHTML = '<div class="alert-ok">✅ ' +
                    String(data.message || 'Status hasil akhir berhasil diperbarui.').replace(/</g, '&lt;') +
                    '</div>';
            }
            window.location.reload();
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            form.submit();
        });
});

syncHaSelection();
</script>

<?php
include __DIR__ . '/../includes/footer_admin.php';
?>