<?php
// Nama File: admin/koreksi_hasil.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek sesi login HR / Admin
$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$id_admin  = intval($_SESSION['user_id'] ?? 0);

if ($id_admin <= 0 || !in_array($role_user, ['admin', 'hr', 'hrd'], true)) {
    echo "<script>alert('Silakan login terlebih dahulu sebagai HR/Admin!'); window.location='../login.php';</script>";
    exit();
}

// Koneksi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_sukses = "";
$pesan_error  = "";

// --- 1. DETEKSI OTOMATIS TABEL HASIL PSIKOTES / UJIAN ---
$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
if ($q_tables) {
    while ($t = mysqli_fetch_row($q_tables)) {
        $all_tables[] = $t[0];
    }
}

$sql_create_hasil = "CREATE TABLE IF NOT EXISTS `hasil_psikotes` (
    `id_hasil` INT(11) NOT NULL AUTO_INCREMENT,
    `id_pelamar` INT(11) NOT NULL,
    `id_lowongan` INT(11) NULL,
    `skor_akhir` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `jumlah_benar` INT(11) DEFAULT 0,
    `jumlah_salah` INT(11) DEFAULT 0,
    `total_soal` INT(11) DEFAULT 0,
    `kkm_nilai` DECIMAL(5,2) DEFAULT 70.00,
    `status_lulus` ENUM('Lulus', 'Tidak Lulus', 'Menunggu Review') DEFAULT 'Menunggu Review',
    `catatan_hr` TEXT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `tgl_ujian` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_hasil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $sql_create_hasil);

$tbl_hasil  = "hasil_psikotes";
$cols_hasil = [];
foreach ($all_tables as $tbl) {
    if (preg_match('/(hasil_psikotes|hasil_ujian|nilai_psikotes|hasil_tes)/i', $tbl)) {
        $tbl_hasil = $tbl;
        break;
    }
}

$q_ch = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_hasil`");
if ($q_ch) {
    while ($c = mysqli_fetch_assoc($q_ch)) {
        $cols_hasil[] = $c['Field'];
    }
}

$pk_hasil   = $cols_hasil[0] ?? 'id_hasil';
$fk_pelamar = in_array('id_pelamar', $cols_hasil, true) ? 'id_pelamar' : 'pelamar_id';

if (!in_array('is_published', $cols_hasil, true)) {
    @mysqli_query($conn, "ALTER TABLE `$tbl_hasil` ADD `is_published` TINYINT(1) NOT NULL DEFAULT 0");
    $cols_hasil[] = 'is_published';
}
if (!in_array('catatan_hr', $cols_hasil, true)) {
    @mysqli_query($conn, "ALTER TABLE `$tbl_hasil` ADD `catatan_hr` TEXT NULL");
    $cols_hasil[] = 'catatan_hr';
}

$col_skor   = in_array('skor_akhir', $cols_hasil, true) ? 'skor_akhir' : (in_array('nilai', $cols_hasil, true) ? 'nilai' : 'skor');
$col_benar  = in_array('jumlah_benar', $cols_hasil, true) ? 'jumlah_benar' : (in_array('benar', $cols_hasil, true) ? 'benar' : '');
$col_salah  = in_array('jumlah_salah', $cols_hasil, true) ? 'jumlah_salah' : (in_array('salah', $cols_hasil, true) ? 'salah' : '');
$col_total  = in_array('total_soal', $cols_hasil, true) ? 'total_soal' : '';
$col_status = in_array('status_lulus', $cols_hasil, true) ? 'status_lulus' : (in_array('status', $cols_hasil, true) ? 'status' : '');
$col_tgl    = in_array('tgl_ujian', $cols_hasil, true) ? 'tgl_ujian' : (in_array('created_at', $cols_hasil, true) ? 'created_at' : '');

// --- 1B. SINKRONISASI OTOMATIS DARI TABEL JAWABAN / RIWAYAT UJIAN PELAMAR ---
foreach ($all_tables as $tbl_src) {
    if ($tbl_src === $tbl_hasil || $tbl_src === 'pelamar' || $tbl_src === 'admin') {
        continue;
    }
    if (preg_match('/(jawaban|riwayat_ujian|peserta_ujian|log_ujian|detail_tes)/i', $tbl_src)) {
        $cols_src = [];
        $q_csrc = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_src`");
        if ($q_csrc) {
            while ($cs = mysqli_fetch_assoc($q_csrc)) {
                $cols_src[] = $cs['Field'];
            }
        }
        $fk_src_pel = in_array('id_pelamar', $cols_src, true) ? 'id_pelamar' : (in_array('pelamar_id', $cols_src, true) ? 'pelamar_id' : '');
        if (!empty($fk_src_pel)) {
            $col_src_tgl = '';
            foreach (['tgl_selesai', 'tgl_ujian', 'waktu_submit', 'updated_at', 'created_at'] as $cand_tgl) {
                if (in_array($cand_tgl, $cols_src, true)) {
                    $col_src_tgl = $cand_tgl;
                    break;
                }
            }
            $select_tgl = !empty($col_src_tgl) ? "MAX(`$col_src_tgl`) AS waktu_tes" : "NOW() AS waktu_tes";
            $q_missing = mysqli_query($conn, "
                SELECT `$fk_src_pel` AS id_p, COUNT(*) AS jml_item, $select_tgl
                FROM `$tbl_src`
                WHERE `$fk_src_pel` > 0
                  AND `$fk_src_pel` NOT IN (SELECT `$fk_pelamar` FROM `$tbl_hasil`)
                GROUP BY `$fk_src_pel`
            ");
            if ($q_missing) {
                while ($m = mysqli_fetch_assoc($q_missing)) {
                    $id_p_sync = intval($m['id_p']);
                    $jml_item  = intval($m['jml_item']);
                    $wkt_tes   = !empty($m['waktu_tes']) ? mysqli_real_escape_string($conn, $m['waktu_tes']) : date('Y-m-d H:i:s');

                    $ins_cols = ["`$fk_pelamar`", "`$col_skor`"];
                    $ins_vals = [$id_p_sync, 0];

                    if (!empty($col_total)) {
                        $ins_cols[] = "`$col_total`";
                        $ins_vals[] = $jml_item;
                    }
                    if (!empty($col_status)) {
                        $ins_cols[] = "`$col_status`";
                        $ins_vals[] = "'Menunggu Review'";
                    }
                    if (!empty($col_tgl)) {
                        $ins_cols[] = "`$col_tgl`";
                        $ins_vals[] = "'$wkt_tes'";
                    }

                    mysqli_query($conn, "INSERT INTO `$tbl_hasil` (" . implode(', ', $ins_cols) . ") VALUES (" . implode(', ', $ins_vals) . ")");
                }
            }
        }
    }
}

// --- 2. DETEKSI TABEL LAMARAN UNTUK NAMA POSISI ---
$tbl_lamaran  = "";
$cols_lamaran = [];
foreach ($all_tables as $tbl) {
    if ($tbl === 'pelamar' || $tbl === 'admin' || $tbl === $tbl_hasil) {
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

$join_pos_sql = "";
$pos_expr     = "''";
if (!empty($tbl_lamaran)) {
    $fk_lam_pel   = in_array('id_pelamar', $cols_lamaran, true) ? 'id_pelamar' : 'pelamar_id';
    $join_pos_sql = "LEFT JOIN `$tbl_lamaran` l ON p.id_pelamar = l.`$fk_lam_pel`";
    foreach ($cols_lamaran as $cl) {
        if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $cl) && !preg_match('/^id_/i', $cl)) {
            $pos_expr = "GROUP_CONCAT(DISTINCT l.`$cl` SEPARATOR ', ')";
            break;
        }
    }
    if ($pos_expr === "''") {
        $fk_low = "";
        foreach ($cols_lamaran as $cl) {
            if ($cl !== $fk_lam_pel && preg_match('/(lowongan|loker|posisi)/i', $cl)) {
                $fk_low = $cl;
                break;
            }
        }
        if (!empty($fk_low)) {
            foreach ($all_tables as $tbl) {
                if (in_array($tbl, ['pelamar', 'admin', $tbl_hasil, $tbl_lamaran], true)) {
                    continue;
                }
                $cols_low = [];
                $q_clow = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
                if ($q_clow) {
                    while ($c = mysqli_fetch_assoc($q_clow)) {
                        $cols_low[] = $c['Field'];
                    }
                }
                $pk_low = $cols_low[0] ?? '';
                if ($pk_low === $fk_low || in_array($fk_low, $cols_low, true)) {
                    foreach ($cols_low as $clow) {
                        if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $clow) && !preg_match('/^id_/i', $clow)) {
                            $join_pos_sql .= " LEFT JOIN `$tbl` low ON l.`$fk_low` = low.`$pk_low`";
                            $pos_expr = "GROUP_CONCAT(DISTINCT low.`$clow` SEPARATOR ', ')";
                            break 2;
                        }
                    }
                }
            }
        }
    }
}

// --- 3. AKSI KOREKSI & TOGGLE PUBLISH HASIL KE HALAMAN PELAMAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_publish'])) {
    $id_h      = intval($_POST['id_hasil'] ?? 0);
    $pub_val   = intval($_POST['is_published'] ?? 0) === 1 ? 1 : 0;
    $catatan   = trim((string)($_POST['catatan_hr'] ?? ''));
    $st_lulus  = trim((string)($_POST['status_lulus'] ?? ''));
    $skor_baru = isset($_POST['skor_akhir']) ? max(0, min(100, floatval($_POST['skor_akhir']))) : null;

    if ($id_h > 0) {
        $set_sql = ["`is_published` = ?", "`catatan_hr` = ?"];
        $types   = "is";
        $params  = [$pub_val, $catatan];

        if ($skor_baru !== null) {
            $set_sql[] = "`$col_skor` = ?";
            $types    .= "d";
            $params[]  = $skor_baru;
        }
        if (!empty($col_status) && in_array($st_lulus, ['Lulus', 'Tidak Lulus', 'Menunggu Review'], true)) {
            $set_sql[] = "`$col_status` = ?";
            $types    .= "s";
            $params[]  = $st_lulus;
        }

        $types   .= "i";
        $params[] = $id_h;

        $stmt_upd = mysqli_prepare($conn, "UPDATE `$tbl_hasil` SET " . implode(", ", $set_sql) . " WHERE `$pk_hasil` = ?");
        if ($stmt_upd) {
            mysqli_stmt_bind_param($stmt_upd, $types, ...$params);
            if (mysqli_stmt_execute($stmt_upd)) {
                $pesan_sukses = ($pub_val === 1)
                    ? "Hasil koreksi disimpan & berhasil <strong>DITAMPILKAN</strong> ke halaman pelamar (detail_hasil_ujian.php)!"
                    : "Hasil koreksi disimpan & <strong>DISEMBUNYIKAN</strong> dari halaman pelamar.";
            } else {
                $pesan_error = "Gagal memperbarui data koreksi: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_upd);
        }
    }
}

// --- 4. AKSI PUBLISH MASSAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_massal'])) {
    $ids      = $_POST['pilih_hasil'] ?? [];
    $aksi_pub = intval($_POST['nilai_publish'] ?? 1) === 1 ? 1 : 0;
    if (!empty($ids) && is_array($ids)) {
        $clean_ids = array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) {
            return $v > 0;
        })));
        if (!empty($clean_ids)) {
            $placeholders = implode(',', array_fill(0, count($clean_ids), '?'));
            $types_m      = "i" . str_repeat('i', count($clean_ids));
            $stmt_m       = mysqli_prepare($conn, "UPDATE `$tbl_hasil` SET `is_published` = ? WHERE `$pk_hasil` IN ($placeholders)");
            if ($stmt_m) {
                $bind_m = array_merge([$aksi_pub], $clean_ids);
                mysqli_stmt_bind_param($stmt_m, $types_m, ...$bind_m);
                if (mysqli_stmt_execute($stmt_m)) {
                    $pesan_sukses = count($clean_ids) . " data hasil psikotes berhasil " . ($aksi_pub === 1 ? "ditampilkan ke pelamar." : "disembunyikan.");
                } else {
                    $pesan_error = "Gagal memperbarui status publikasi massal: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt_m);
            }
        } else {
            $pesan_error = "Pilih minimal 1 data hasil psikotes pada tabel.";
        }
    } else {
        $pesan_error = "Pilih minimal 1 data hasil psikotes pada tabel.";
    }
}

// --- 5. FILTER & QUERY DAFTAR HASIL PSIKOTES ---
$filter_cari_raw   = trim((string)($_GET['cari'] ?? ''));
$filter_status_raw = trim((string)($_GET['status'] ?? ''));
$filter_pub        = trim((string)($_GET['pub'] ?? ''));

$filter_cari   = mysqli_real_escape_string($conn, $filter_cari_raw);
$filter_status = mysqli_real_escape_string($conn, $filter_status_raw);

$where = [];
if ($filter_cari !== '') {
    $where[] = "(p.nama_lengkap LIKE '%$filter_cari%' OR p.email LIKE '%$filter_cari%')";
}
if ($filter_status !== '' && !empty($col_status)) {
    $where[] = "h.`$col_status` = '$filter_status'";
}
if ($filter_pub !== '' && in_array($filter_pub, ['0', '1'], true)) {
    $where[] = "h.is_published = " . intval($filter_pub);
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$order_sql = !empty($col_tgl) ? "ORDER BY h.`$col_tgl` DESC" : "ORDER BY h.`$pk_hasil` DESC";

$sql_list = "SELECT h.*, p.nama_lengkap, p.email, p.no_telepon,
                    $pos_expr AS posisi_dilamar
             FROM `$tbl_hasil` h
             INNER JOIN `pelamar` p ON h.`$fk_pelamar` = p.id_pelamar
             $join_pos_sql
             $where_sql
             GROUP BY h.`$pk_hasil`
             $order_sql";

$q_list     = mysqli_query($conn, $sql_list);
$total_data = $q_list ? mysqli_num_rows($q_list) : 0;

$cnt_pub   = intval((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM `$tbl_hasil` WHERE is_published = 1"))['jml'] ?? 0));
$cnt_lulus = !empty($col_status) ? intval((mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jml FROM `$tbl_hasil` WHERE `$col_status` = 'Lulus'"))['jml'] ?? 0)) : 0;

include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .psikotes-wrapper {
        max-width: 1360px;
        margin: 14px auto 40px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .page-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-heading {
        font-size: 18px;
        font-weight: 800;
        color: #111;
        margin: 0;
        text-transform: uppercase;
    }
    .stats-row-pills {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .stat-pill {
        background-color: #ffffff;
        border: 1.5px solid #0b6623;
        color: #0b6623;
        padding: 6px 13px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }

    .card-outer-box {
        background-color: #f4f5f9;
        border-radius: 14px;
        padding: 15px;
        border: 1px solid #e5e7eb;
    }
    .card-banner {
        background-color: #0b6623;
        color: #ffffff;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin-bottom: 14px;
        text-align: center;
    }
    .card-inner-white {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 16px;
    }

    .filter-bar {
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .form-ctrl {
        min-height: 38px;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        color: #1e293b;
        background: #ffffff;
    }
    .form-ctrl:focus {
        outline: none;
        border-color: #0b6623;
        box-shadow: 0 0 0 2px rgba(11, 102, 35, 0.12);
    }

    .bulk-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .bulk-btns-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-bulk {
        min-height: 36px;
        border: none;
        border-radius: 8px;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .mobile-select-all-kh {
        display: none;
    }

    .cb-hasil,
    #checkAllKh,
    #checkAllKhMobile {
        width: 18px;
        height: 18px;
        accent-color: #0b6623;
        cursor: pointer;
    }

    .hasil-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .hasil-table th {
        background-color: #e8f5e9;
        color: #0b6623;
        font-weight: 700;
        text-align: left;
        padding: 11px 9px;
        border-bottom: 2px solid #c8e6c9;
        white-space: nowrap;
    }
    .hasil-table td {
        padding: 11px 9px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        color: #334155;
    }
    .hasil-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .row-koreksi-form {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin: 0;
    }

    .input-skor-edit {
        width: 72px;
        min-height: 34px;
        padding: 5px 7px;
        border-radius: 7px;
        font-size: 13.5px;
        font-weight: 800;
        text-align: center;
        border: 1.5px solid #cbd5e1;
    }
    .skor-tinggi { background: #dcfce7; color: #166534; border-color: #86efac; }
    .skor-rendah { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

    .pub-badge-on  { background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 14px; font-size: 11.5px; font-weight: 700; display: inline-block; }
    .pub-badge-off { background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 14px; font-size: 11.5px; font-weight: 700; display: inline-block; }

    .aksi-koreksi-group {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }
    .btn-aksi-pub {
        min-height: 34px;
        border: none;
        padding: 6px 11px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-decoration: none;
    }
    .btn-koreksi-essay { background: #f59e0b; color: #ffffff; }
    .btn-koreksi-essay:hover { background: #d97706; }
    .btn-show-pelamar { background: #0b6623; color: #ffffff; }
    .btn-show-pelamar:hover { background: #084d1a; }
    .btn-hide-pelamar { background: #e2e8f0; color: #334155; }
    .btn-hide-pelamar:hover { background: #cbd5e1; }
    .btn-save-only { background: #1d4ed8; color: #ffffff; }
    .btn-save-only:hover { background: #1e40af; }

    .alert-msg-success { background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 10px 15px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
    .alert-msg-error { background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 10px 15px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }

    /* Mode HP: ubah tabel menjadi kartu interaktif */
    @media (max-width: 768px) {
        .psikotes-wrapper {
            margin: 8px auto 28px auto;
            padding: 0 8px;
        }
        .page-heading {
            font-size: 16.5px;
        }
        .card-outer-box {
            padding: 10px;
            border-radius: 12px;
        }
        .card-inner-white {
            padding: 12px;
        }
        .filter-bar {
            flex-direction: column;
            gap: 8px;
        }
        .form-ctrl {
            width: 100%;
            min-height: 42px;
            font-size: 16px;
        }
        .bulk-toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .bulk-btns-group {
            display: grid;
            grid-template-columns: 1fr;
            width: 100%;
        }
        .btn-bulk {
            width: 100%;
            min-height: 42px;
            font-size: 13.5px;
        }

        .mobile-select-all-kh {
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

        .hasil-table {
            display: block;
        }
        .hasil-table thead {
            display: none;
        }
        .hasil-table tbody {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .hasil-table tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .hasil-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: right;
            font-size: 13px;
            word-break: break-word;
        }
        .hasil-table td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .hasil-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #0b6623;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .hasil-table td[data-label="Pilih"] {
            align-items: center;
            background: #f8fafc;
            margin: -12px -12px 4px -12px;
            padding: 9px 12px;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 10px 10px 0 0;
        }
        .hasil-table td[data-label="Kelulusan & Catatan"] {
            flex-direction: column;
            align-items: stretch;
            text-align: left;
        }
        .cb-hasil,
        #checkAllKhMobile {
            width: 20px;
            height: 20px;
        }
        .input-skor-edit {
            width: 100%;
            min-height: 42px;
            font-size: 16px;
        }
        .row-koreksi-form .form-ctrl {
            min-height: 42px;
            font-size: 16px;
        }
        .aksi-koreksi-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .aksi-koreksi-group .btn-aksi-pub:first-child {
            grid-column: 1 / -1;
        }
        .btn-aksi-pub {
            min-height: 38px;
            font-size: 12.5px;
        }
    }
</style>

<div class="psikotes-wrapper">
    <div class="page-header-row">
        <h1 class="page-heading">KOREKSI & PUBLIKASI HASIL PSIKOTES PELAMAR</h1>
        <div class="stats-row-pills">
            <span class="stat-pill">📊 Total Peserta: <?php echo intval($total_data); ?></span>
            <span class="stat-pill">✅ Lulus: <?php echo intval($cnt_lulus); ?></span>
            <span class="stat-pill">👁️ Tampil di Pelamar: <?php echo intval($cnt_pub); ?></span>
        </div>
    </div>

    <div id="koreksiApiAlertBox">
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-msg-success">✅ <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert-msg-error">❌ <?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>
    </div>

    <div class="card-outer-box">
        <div class="card-banner"><span>📋</span> Daftar Nilai Psikotes & Kontrol Tampilan Halaman Pelamar</div>
        <div class="card-inner-white">
            <!-- Form Filter -->
            <form method="GET" action="koreksi_hasil.php" class="filter-bar">
                <input type="text" name="cari" class="form-ctrl" style="flex:1; min-width:200px;" placeholder="Cari nama atau email pelamar..." value="<?php echo htmlspecialchars($filter_cari_raw); ?>">
                <select name="status" class="form-ctrl">
                    <option value="">-- Semua Status Kelulusan --</option>
                    <option value="Lulus" <?php echo ($filter_status_raw === 'Lulus') ? 'selected' : ''; ?>>Lulus</option>
                    <option value="Tidak Lulus" <?php echo ($filter_status_raw === 'Tidak Lulus') ? 'selected' : ''; ?>>Tidak Lulus</option>
                    <option value="Menunggu Review" <?php echo ($filter_status_raw === 'Menunggu Review') ? 'selected' : ''; ?>>Menunggu Review</option>
                </select>
                <select name="pub" class="form-ctrl">
                    <option value="">-- Semua Status Tampilan --</option>
                    <option value="1" <?php echo ($filter_pub === '1') ? 'selected' : ''; ?>>Sudah Ditampilkan ke Pelamar</option>
                    <option value="0" <?php echo ($filter_pub === '0') ? 'selected' : ''; ?>>Masih Tersembunyi (Draft HR)</option>
                </select>
                <button type="submit" class="btn-bulk" style="background:#0b6623; color:#fff; padding:8px 16px;">🔍 Filter</button>
                <?php if ($filter_cari_raw !== '' || $filter_status_raw !== '' || $filter_pub !== ''): ?>
                    <a href="koreksi_hasil.php" class="btn-bulk" style="background:#e2e8f0; color:#334155; text-decoration:none; padding:8px 14px;">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Form Aksi Massal -->
            <form method="POST" action="koreksi_hasil.php" id="formMassalHasil">
                <input type="hidden" name="publish_massal" value="1">
                <input type="hidden" name="nilai_publish" id="nilaiPublishMassal" value="1">
                <div class="bulk-toolbar">
                    <div class="bulk-btns-group">
                        <button type="button" onclick="submitMassal(1)" class="btn-bulk" style="background:#0b6623; color:#fff;">
                            👁️ Tampilkan Hasil Terpilih ke Pelamar
                        </button>
                        <button type="button" onclick="submitMassal(0)" class="btn-bulk" style="background:#64748b; color:#fff;">
                            🙈 Sembunyikan Terpilih
                        </button>
                    </div>
                    <span style="font-size:12px; color:#64748b;">* Hasil yang ditampilkan akan muncul di halaman <code>detail_hasil_ujian.php</code> pelamar.</span>
                </div>
            </form>

            <?php if ($q_list && $total_data > 0): ?>
                <label class="mobile-select-all-kh" for="checkAllKhMobile">
                    <span>Pilih Semua Peserta</span>
                    <input type="checkbox" id="checkAllKhMobile" onclick="centangSemua(this)">
                </label>
            <?php endif; ?>

            <div>
                <table class="hasil-table">
                    <thead>
                        <tr>
                            <th style="width:34px; text-align:center;"><input type="checkbox" id="checkAllKh" onclick="centangSemua(this)"></th>
                            <th>Pelamar & Posisi</th>
                            <th>Tanggal Tes</th>
                            <th>Skor Koreksi</th>
                            <th>Rincian</th>
                            <th>Kelulusan & Catatan HR</th>
                            <th>Status di Pelamar</th>
                            <th style="text-align:center;">Kontrol Tampilan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($q_list && $total_data > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($q_list)): ?>
                                <?php
                                    $id_h      = intval($row[$pk_hasil]);
                                    $id_pel_r  = intval($row[$fk_pelamar]);
                                    $skor      = floatval($row[$col_skor] ?? 0);
                                    $kkm       = floatval($row['kkm_nilai'] ?? 70);
                                    $st_lulus  = !empty($col_status) ? ($row[$col_status] ?? 'Menunggu Review') : ($skor >= $kkm ? 'Lulus' : 'Tidak Lulus');
                                    $is_pub    = intval($row['is_published'] ?? 0);
                                    $tgl_tes   = (!empty($col_tgl) && !empty($row[$col_tgl])) ? date('d M Y, H:i', strtotime($row[$col_tgl])) : '-';
                                ?>
                                <tr>
                                    <td data-label="Pilih" style="text-align:center;">
                                        <input type="checkbox" form="formMassalHasil" name="pilih_hasil[]" value="<?php echo $id_h; ?>" class="cb-hasil" onchange="syncKhMaster()">
                                    </td>
                                    <td data-label="Pelamar & Posisi">
                                        <div>
                                            <strong style="color:#111; display:block;"><?php echo htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></strong>
                                            <span style="font-size:11.5px; color:#0369a1; font-weight:600; display:block;"><?php echo htmlspecialchars($row['posisi_dilamar'] ?: 'Pelamar'); ?></span>
                                            <span style="font-size:11.5px; color:#64748b;">✉️ <?php echo htmlspecialchars($row['email'] ?? '-'); ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Tanggal Tes" style="font-size:12px;"><?php echo htmlspecialchars($tgl_tes); ?></td>
                                    <td data-label="Skor Koreksi">
                                        <input type="number" step="0.01" min="0" max="100" form="formRow_<?php echo $id_h; ?>" name="skor_akhir"
                                               class="input-skor-edit <?php echo ($skor >= $kkm) ? 'skor-tinggi' : 'skor-rendah'; ?>"
                                               value="<?php echo htmlspecialchars(number_format($skor, 0, '.', '')); ?>">
                                    </td>
                                    <td data-label="Rincian" style="font-size:12px;">
                                        <div>
                                            <?php if (!empty($col_benar)): ?>
                                                <span style="color:#166534; font-weight:600;">✔ Benar: <?php echo intval($row[$col_benar]); ?></span><br>
                                            <?php endif; ?>
                                            <?php if (!empty($col_salah)): ?>
                                                <span style="color:#991b1b;">✖ Salah: <?php echo intval($row[$col_salah]); ?></span>
                                            <?php endif; ?>
                                            <?php if (empty($col_benar) && empty($col_salah)): ?>
                                                <span style="color:#64748b;">-</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td data-label="Kelulusan & Catatan">
                                        <form method="POST" action="koreksi_hasil.php" id="formRow_<?php echo $id_h; ?>" class="row-koreksi-form">
                                            <input type="hidden" name="toggle_publish" value="1">
                                            <input type="hidden" name="id_hasil" value="<?php echo $id_h; ?>">
                                            <select name="status_lulus" class="form-ctrl" style="padding:6px 9px; font-size:12px; font-weight:700; width:100%;">
                                                <?php foreach (['Menunggu Review', 'Lulus', 'Tidak Lulus'] as $opt_st): ?>
                                                    <option value="<?php echo $opt_st; ?>" <?php echo ($st_lulus === $opt_st) ? 'selected' : ''; ?>>
                                                        <?php echo $opt_st; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" name="catatan_hr" class="form-ctrl" style="padding:6px 9px; font-size:12px; width:100%; box-sizing:border-box;" placeholder="Catatan untuk pelamar..." value="<?php echo htmlspecialchars($row['catatan_hr'] ?? ''); ?>">
                                        </form>
                                    </td>
                                    <td data-label="Status di Pelamar">
                                        <?php if ($is_pub === 1): ?>
                                            <span class="pub-badge-on">👁️ Tampil di Pelamar</span>
                                        <?php else: ?>
                                            <span class="pub-badge-off">🙈 Belum Ditampilkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Kontrol" style="text-align:center;">
                                        <div class="aksi-koreksi-group">
                                            <a href="detail_koreksi_essay.php?id_pelamar=<?php echo $id_pel_r; ?>&id_hasil=<?php echo $id_h; ?>"
                                               class="btn-aksi-pub btn-koreksi-essay"
                                               title="Koreksi Jawaban Essay Per Modul">
                                                ✍️ Koreksi Essay
                                            </a>
                                            <?php if ($is_pub === 0): ?>
                                                <button type="submit" form="formRow_<?php echo $id_h; ?>" name="is_published" value="1" class="btn-aksi-pub btn-show-pelamar" title="Simpan koreksi & munculkan ke detail_hasil_ujian.php">
                                                    📢 Munculkan Hasil
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" form="formRow_<?php echo $id_h; ?>" name="is_published" value="0" class="btn-aksi-pub btn-hide-pelamar" title="Sembunyikan dari pelamar">
                                                    🙈 Sembunyikan
                                                </button>
                                                <button type="submit" form="formRow_<?php echo $id_h; ?>" name="is_published" value="1" class="btn-aksi-pub btn-save-only" title="Simpan koreksi nilai/catatan">
                                                    💾 Simpan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:30px; color:#64748b;">
                                    Belum ada data hasil psikotes pelamar.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const koreksiApiEndpoints = [
    '../api/admin/koreksi_hasil.php',
    '../api/koreksi_hasil.php'
];

async function postKoreksiApi(payload) {
    for (const url of koreksiApiEndpoints) {
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
    throw new Error('Endpoint API koreksi_hasil tidak ditemukan.');
}

function tampilkanAlertKoreksi(isSuccess, msg) {
    const box = document.getElementById('koreksiApiAlertBox');
    const safeMsg = String(msg || '').replace(/</g, '&lt;');
    if (box) {
        box.innerHTML = isSuccess
            ? '<div class="alert-msg-success">✅ ' + safeMsg + '</div>'
            : '<div class="alert-msg-error">❌ ' + safeMsg + '</div>';
    }
}

function centangSemua(source) {
    document.querySelectorAll('.cb-hasil').forEach(cb => {
        cb.checked = source.checked;
    });
    syncKhMaster();
}

function syncKhMaster() {
    const allCbs = document.querySelectorAll('.cb-hasil');
    let checkedCount = 0;
    allCbs.forEach(cb => {
        if (cb.checked) checkedCount++;
    });
    const allChecked = (allCbs.length > 0 && checkedCount === allCbs.length);
    const deskMaster = document.getElementById('checkAllKh');
    const mobMaster  = document.getElementById('checkAllKhMobile');
    if (deskMaster) deskMaster.checked = allChecked;
    if (mobMaster) mobMaster.checked = allChecked;
}

function submitMassal(val) {
    const checkedBoxes = Array.from(document.querySelectorAll('.cb-hasil:checked'));
    if (checkedBoxes.length === 0) {
        alert('Silakan pilih minimal 1 data hasil psikotes pada tabel terlebih dahulu.');
        return;
    }
    const form = document.getElementById('formMassalHasil');
    document.getElementById('nilaiPublishMassal').value = val;

    if (!window.fetch) {
        form.submit();
        return;
    }

    const payload = {
        publish_massal: 1,
        nilai_publish: val ? 1 : 0,
        pilih_hasil: checkedBoxes.map(cb => parseInt(cb.value, 10)).filter(id => id > 0)
    };

    postKoreksiApi(payload)
        .then(data => {
            if (!data || !data.success) {
                throw new Error((data && data.message) || 'Gagal memperbarui status publikasi massal.');
            }
            tampilkanAlertKoreksi(true, data.message || 'Status publikasi massal berhasil diperbarui.');
            window.location.reload();
        })
        .catch(() => {
            form.submit();
        });
}

document.querySelectorAll('.row-koreksi-form').forEach(rowForm => {
    rowForm.addEventListener('submit', function(e) {
        if (!window.fetch) {
            return;
        }
        e.preventDefault();

        const submitter = e.submitter;
        const pubVal = submitter && submitter.name === 'is_published'
            ? (parseInt(submitter.value, 10) === 1 ? 1 : 0)
            : 0;

        const formId = rowForm.getAttribute('id');
        const skorInput = formId
            ? document.querySelector('input[name="skor_akhir"][form="' + formId + '"]')
            : null;

        const idHasil = parseInt(rowForm.querySelector('input[name="id_hasil"]')?.value || '0', 10);
        const statusLulus = String(rowForm.querySelector('select[name="status_lulus"]')?.value || '');
        const catatanHr = String(rowForm.querySelector('input[name="catatan_hr"]')?.value || '').trim();
        const skorAkhir = skorInput ? parseFloat(skorInput.value) : 0;

        if (submitter) submitter.disabled = true;

        const payload = {
            id_hasil: idHasil,
            is_published: pubVal,
            skor_akhir: Number.isFinite(skorAkhir) ? skorAkhir : 0,
            status_lulus: statusLulus,
            catatan_hr: catatanHr
        };

        postKoreksiApi(payload)
            .then(data => {
                if (!data || !data.success) {
                    throw new Error((data && data.message) || 'Gagal menyimpan koreksi.');
                }
                tampilkanAlertKoreksi(true, data.message || 'Data koreksi & publikasi berhasil disimpan.');
                window.location.reload();
            })
            .catch(() => {
                if (submitter) submitter.disabled = false;
                if (submitter && submitter.name === 'is_published') {
                    const hiddenPub = document.createElement('input');
                    hiddenPub.type = 'hidden';
                    hiddenPub.name = 'is_published';
                    hiddenPub.value = String(pubVal);
                    rowForm.appendChild(hiddenPub);
                }
                rowForm.submit();
            });
    });
});
</script>

<?php
mysqli_close($conn);
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>