<?php
// Nama File: admin/jadwal_seleksi.php
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

// --- KONFIGURASI SMTP GMAIL HR ---
$smtp_email    = 'recruitment@kebabmonster.id';
$smtp_password = 'Akhdan040804';
$smtp_sender   = 'HR Recruitment Kebab Monster';

// Load PHPMailer (mendukung Composer maupun manual folder includes/PHPMailer)
$phpmailer_ready = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $phpmailer_ready = true;
} elseif (file_exists(__DIR__ . '/../includes/PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
    $phpmailer_ready = true;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function kirimEmailUndangan($emailTujuan, $namaPelamar, $posisi, $jenisTes, $tglIndo, $jamIndo, $metode, $lokasiLink, $catatanHr, $smtp_email, $smtp_password, $smtp_sender, $phpmailer_ready) {
    if (empty($emailTujuan) || !filter_var($emailTujuan, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $subjek = "Undangan {$jenisTes} - Kebab Monster ({$namaPelamar})";
    $catatanHtml = !empty($catatanHr) ? "<p><strong>Catatan Penting:</strong><br>" . nl2br(htmlspecialchars($catatanHr)) . "</p>" : "";

    $pesanHtml = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; overflow: hidden;'>
        <div style='background-color: #0b6623; color: #ffffff; padding: 20px; text-align: center;'>
            <h2 style='margin: 0;'>UNDANGAN TAHAP " . htmlspecialchars($jenisTes) . "</h2>
            <p style='margin: 5px 0 0; font-size: 13px;'>Tim Rekrutmen Kebab Monster</p>
        </div>
        <div style='padding: 25px; color: #333; line-height: 1.6;'>
            <p>Halo <strong>" . htmlspecialchars($namaPelamar) . "</strong>,</p>
            <p>Selamat! Berdasarkan hasil evaluasi lamaran Anda untuk posisi <strong>" . htmlspecialchars($posisi ?: 'Karyawan Kebab Monster') . "</strong>, kami mengundang Anda untuk mengikuti tahap <strong>" . htmlspecialchars($jenisTes) . "</strong> dengan jadwal berikut:</p>
            <table style='width: 100%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px; margin: 15px 0; font-size: 14px;'>
                <tr><td style='padding: 6px; width: 130px;'><strong>Tahap Seleksi</strong></td><td>: " . htmlspecialchars($jenisTes) . "</td></tr>
                <tr><td style='padding: 6px;'><strong>Hari / Tanggal</strong></td><td>: " . htmlspecialchars($tglIndo) . "</td></tr>
                <tr><td style='padding: 6px;'><strong>Waktu</strong></td><td>: " . htmlspecialchars($jamIndo) . " WIB</td></tr>
                <tr><td style='padding: 6px;'><strong>Metode</strong></td><td>: " . htmlspecialchars($metode) . "</td></tr>
                <tr><td style='padding: 6px; vertical-align: top;'><strong>Lokasi / Link</strong></td><td>: " . nl2br(htmlspecialchars($lokasiLink)) . "</td></tr>
            </table>
            {$catatanHtml}
            <p>Mohon hadir tepat waktu dan melakukan konfirmasi melalui portal pelamar atau membalas email ini.</p>
            <p style='margin-top: 25px;'>Salam hangat,<br><strong>HRD Kebab Monster</strong></p>
        </div>
    </div>";

    if ($phpmailer_ready) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_email;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($smtp_email, $smtp_sender);
            $mail->addAddress($emailTujuan, $namaPelamar);

            $mail->isHTML(true);
            $mail->Subject = $subjek;
            $mail->Body    = $pesanHtml;

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    } else {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: {$smtp_sender} <{$smtp_email}>\r\n";
        return @mail($emailTujuan, $subjek, $pesanHtml, $headers);
    }
}

$pesan_sukses = "";
$pesan_error  = "";

// --- 1. DETEKSI OTOMATIS TABEL LAMARAN & LOWONGAN ---
$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
if ($q_tables) {
    while ($t = mysqli_fetch_row($q_tables)) {
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

$fk_pelamar = in_array('id_pelamar', $cols_lamaran, true) ? 'id_pelamar' : 'pelamar_id';

$status_col = "";
if (!empty($tbl_lamaran)) {
    foreach (['status', 'status_lamaran', 'status_seleksi', 'tahap'] as $opt_s) {
        if (in_array($opt_s, $cols_lamaran, true)) {
            $status_col = $opt_s;
            break;
        }
    }
    if (empty($status_col)) {
        if (mysqli_query($conn, "ALTER TABLE `$tbl_lamaran` ADD `status` VARCHAR(30) DEFAULT 'Pending'")) {
            $status_col = 'status';
            $cols_lamaran[] = 'status';
        }
    }
}

// --- 2. PASTIKAN TABEL JADWAL_SELEKSI TERSEDIA ---
$sql_create_jadwal = "CREATE TABLE IF NOT EXISTS `jadwal_seleksi` (
    `id_jadwal` INT(11) NOT NULL AUTO_INCREMENT,
    `id_pelamar` INT(11) NOT NULL,
    `jenis_tes` ENUM('Psikotes', 'Interview') NOT NULL,
    `tanggal_jadwal` DATE NOT NULL,
    `waktu_jadwal` TIME NOT NULL,
    `metode` ENUM('Offline / Datang Langsung', 'Online / Zoom / Meet') DEFAULT 'Offline / Datang Langsung',
    `lokasi_atau_link` TEXT NOT NULL,
    `catatan_hr` TEXT NULL,
    `status_kehadiran` ENUM('Dijadwalkan', 'Hadir / Selesai', 'Tidak Hadir', 'Reschedule') DEFAULT 'Dijadwalkan',
    `status_email` ENUM('Belum Dikirim', 'Terkirim', 'Gagal') DEFAULT 'Belum Dikirim',
    `tgl_dibuat` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_jadwal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($conn, $sql_create_jadwal);

$q_cek_col_em = mysqli_query($conn, "SHOW COLUMNS FROM `jadwal_seleksi` LIKE 'status_email'");
if ($q_cek_col_em && mysqli_num_rows($q_cek_col_em) === 0) {
    mysqli_query($conn, "ALTER TABLE `jadwal_seleksi` ADD `status_email` ENUM('Belum Dikirim', 'Terkirim', 'Gagal') DEFAULT 'Belum Dikirim'");
}

// --- 3. RAKIT RELASI POSISI PELAMAR & DAFTAR LAMARAN TERSEDIA ---
$join_opt_sql       = "";
$posisi_opt_expr    = "''";
$tbl_lowongan_ref   = "";
$col_judul_lowongan = "";

if (!empty($tbl_lamaran)) {
    $join_opt_sql = "LEFT JOIN `$tbl_lamaran` l ON p.id_pelamar = l.`$fk_pelamar`";
    foreach ($cols_lamaran as $c_lam) {
        if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $c_lam) && !preg_match('/^id_/i', $c_lam)) {
            $posisi_opt_expr = "GROUP_CONCAT(DISTINCT l.`$c_lam` SEPARATOR ', ')";
            break;
        }
    }
    if ($posisi_opt_expr === "''") {
        $fk_low = "";
        foreach ($cols_lamaran as $c_lam) {
            if ($c_lam !== $fk_pelamar && preg_match('/(lowongan|loker|posisi)/i', $c_lam)) {
                $fk_low = $c_lam;
                break;
            }
        }
        if (!empty($fk_low)) {
            foreach ($all_tables as $tbl) {
                if ($tbl === 'pelamar' || $tbl === 'admin' || $tbl === $tbl_lamaran) {
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
                    foreach ($cols_low as $cl) {
                        if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $cl) && !preg_match('/^id_/i', $cl)) {
                            $join_opt_sql .= " LEFT JOIN `$tbl` low ON l.`$fk_low` = low.`$pk_low`";
                            $posisi_opt_expr = "GROUP_CONCAT(DISTINCT low.`$cl` SEPARATOR ', ')";
                            $tbl_lowongan_ref = $tbl;
                            $col_judul_lowongan = $cl;
                            break 2;
                        }
                    }
                }
            }
        }
    }
}

$daftar_lamaran_tersedia = [];
if (!empty($tbl_lowongan_ref) && !empty($col_judul_lowongan)) {
    $q_loker = mysqli_query($conn, "SELECT DISTINCT `$col_judul_lowongan` AS nama_lamaran FROM `$tbl_lowongan_ref` WHERE `$col_judul_lowongan` IS NOT NULL AND TRIM(`$col_judul_lowongan`) != '' ORDER BY `$col_judul_lowongan` ASC");
    if ($q_loker) {
        while ($ql = mysqli_fetch_assoc($q_loker)) {
            $daftar_lamaran_tersedia[] = trim($ql['nama_lamaran']);
        }
    }
} elseif (!empty($tbl_lamaran) && $posisi_opt_expr !== "''") {
    preg_match('/l\.`([^`]+)`/', $posisi_opt_expr, $m_col);
    if (!empty($m_col[1])) {
        $col_l = $m_col[1];
        $q_loker = mysqli_query($conn, "SELECT DISTINCT `$col_l` AS nama_lamaran FROM `$tbl_lamaran` WHERE `$col_l` IS NOT NULL AND TRIM(`$col_l`) != '' ORDER BY `$col_l` ASC");
        if ($q_loker) {
            while ($ql = mysqli_fetch_assoc($q_loker)) {
                $daftar_lamaran_tersedia[] = trim($ql['nama_lamaran']);
            }
        }
    }
}

// --- 4. PROSES SIMPAN JADWAL MASSAL DARI CHECKBOX TABEL PELAMAR BELUM DIUNDANG ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_jadwal_massal'])) {
    $pelamar_ids    = $_POST['id_pelamar'] ?? [];
    $jenis_tes      = trim($_POST['jenis_tes'] ?? 'Psikotes');
    $tanggal_jadwal = trim($_POST['tanggal_jadwal'] ?? '');
    $waktu_jadwal   = trim($_POST['waktu_jadwal'] ?? '');
    $metode         = trim($_POST['metode'] ?? 'Offline / Datang Langsung');
    $lokasi_link    = trim($_POST['lokasi_atau_link'] ?? '');
    $catatan_hr     = trim($_POST['catatan_hr'] ?? '');
    $sinkron_status = isset($_POST['sinkron_status']) ? 1 : 0;
    $kirim_email    = isset($_POST['kirim_email_otomatis']) ? 1 : 0;

    if (!in_array($jenis_tes, ['Psikotes', 'Interview'], true)) {
        $jenis_tes = 'Psikotes';
    }
    if (!in_array($metode, ['Offline / Datang Langsung', 'Online / Zoom / Meet'], true)) {
        $metode = 'Offline / Datang Langsung';
    }

    if (!empty($pelamar_ids) && is_array($pelamar_ids) && $tanggal_jadwal !== '' && $waktu_jadwal !== '' && $lokasi_link !== '') {
        $berhasil_simpan = 0;
        $email_terkirim  = 0;
        $email_gagal     = 0;

        $tgl_indo = date('d M Y', strtotime($tanggal_jadwal));
        $jam_indo = date('H:i', strtotime($waktu_jadwal));

        $stmt_ins = mysqli_prepare(
            $conn,
            "INSERT INTO `jadwal_seleksi`
            (`id_pelamar`, `jenis_tes`, `tanggal_jadwal`, `waktu_jadwal`, `metode`, `lokasi_atau_link`, `catatan_hr`, `status_kehadiran`, `status_email`)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Dijadwalkan', ?)"
        );

        foreach ($pelamar_ids as $raw_id) {
            $id_pel = intval($raw_id);
            if ($id_pel <= 0) {
                continue;
            }

            $q_info = mysqli_query(
                $conn,
                "SELECT p.nama_lengkap, p.email, $posisi_opt_expr AS posisi_dilamar
                 FROM pelamar p $join_opt_sql WHERE p.id_pelamar = $id_pel GROUP BY p.id_pelamar"
            );
            $info_pel = $q_info ? mysqli_fetch_assoc($q_info) : null;

            $status_email_db = 'Belum Dikirim';
            if ($kirim_email === 1 && $info_pel) {
                $terkirim = kirimEmailUndangan(
                    $info_pel['email'],
                    $info_pel['nama_lengkap'],
                    $info_pel['posisi_dilamar'] ?? '',
                    $jenis_tes,
                    $tgl_indo,
                    $jam_indo,
                    $metode,
                    $lokasi_link,
                    $catatan_hr,
                    $smtp_email,
                    $smtp_password,
                    $smtp_sender,
                    $phpmailer_ready
                );
                if ($terkirim) {
                    $status_email_db = 'Terkirim';
                    $email_terkirim++;
                } else {
                    $status_email_db = 'Gagal';
                    $email_gagal++;
                }
            }

            if ($stmt_ins) {
                mysqli_stmt_bind_param(
                    $stmt_ins,
                    "isssssss",
                    $id_pel,
                    $jenis_tes,
                    $tanggal_jadwal,
                    $waktu_jadwal,
                    $metode,
                    $lokasi_link,
                    $catatan_hr,
                    $status_email_db
                );
                if (mysqli_stmt_execute($stmt_ins)) {
                    $berhasil_simpan++;
                    if ($sinkron_status === 1 && !empty($tbl_lamaran) && !empty($status_col)) {
                        $esc_jenis = mysqli_real_escape_string($conn, $jenis_tes);
                        mysqli_query($conn, "UPDATE `$tbl_lamaran` SET `$status_col` = '$esc_jenis' WHERE `$fk_pelamar` = $id_pel");
                    }
                }
            }
        }

        if ($stmt_ins) {
            mysqli_stmt_close($stmt_ins);
        }

        $pesan_sukses = "Berhasil mengirim undangan & menjadwalkan <strong>{$berhasil_simpan} pelamar</strong> untuk tahap <strong>" . htmlspecialchars($jenis_tes) . "</strong>! Data otomatis dipindah ke tabel <strong>Sudah Dikirim Undangan</strong>.";
        if ($kirim_email === 1) {
            $pesan_sukses .= " (Email terkirim: <strong>{$email_terkirim}</strong>" . ($email_gagal > 0 ? ", gagal: <strong>{$email_gagal}</strong>" : "") . ")";
        }
    } else {
        $pesan_error = "Centang minimal 1 pelamar pada tabel, lalu lengkapi tanggal, jam, dan lokasi/link jadwal.";
    }
}

// --- 5. HAPUS JADWAL UNDANGAN PELAMAR (KEMBALI KE BELUM DIUNDANG) ---
if (isset($_GET['hapus_jadwal_pelamar'])) {
    $id_pel_del = intval($_GET['hapus_jadwal_pelamar']);
    if ($id_pel_del > 0 && mysqli_query($conn, "DELETE FROM `jadwal_seleksi` WHERE `id_pelamar` = $id_pel_del")) {
        $pesan_sukses = "Jadwal undangan dihapus dan pelamar dikembalikan ke daftar <strong>Belum Diundang</strong>.";
    }
}

// --- 6. FILTER & SORTING ---
$filter_cari  = trim($_GET['cari'] ?? '');
$sort_lamaran = trim($_GET['sort_lamaran'] ?? '');
$sort_by      = $_GET['sort_by'] ?? 'nama';
$sort_dir     = (strtolower($_GET['sort_dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

$where_parts = [];
if ($filter_cari !== '') {
    $esc_cari = mysqli_real_escape_string($conn, $filter_cari);
    $where_parts[] = "(p.nama_lengkap LIKE '%$esc_cari%' OR p.email LIKE '%$esc_cari%' OR p.no_telepon LIKE '%$esc_cari%')";
}
$where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

$prioritas_lamaran_sql = "";
if ($sort_lamaran !== '') {
    $esc_lam = mysqli_real_escape_string($conn, $sort_lamaran);
    $prioritas_lamaran_sql = "CASE WHEN COALESCE($posisi_opt_expr, '') LIKE '%$esc_lam%' THEN 0 ELSE 1 END, ";
}

switch ($sort_by) {
    case 'posisi':
        $order_sql_belum = "ORDER BY $prioritas_lamaran_sql COALESCE($posisi_opt_expr, '') $sort_dir, p.nama_lengkap ASC";
        $order_sql_sudah = "ORDER BY $prioritas_lamaran_sql COALESCE($posisi_opt_expr, '') $sort_dir, p.nama_lengkap ASC";
        break;
    case 'jadwal':
        $order_sql_belum = "ORDER BY $prioritas_lamaran_sql p.nama_lengkap $sort_dir";
        $order_sql_sudah = "ORDER BY $prioritas_lamaran_sql MAX(CONCAT(j.tanggal_jadwal, ' ', j.waktu_jadwal)) $sort_dir, p.nama_lengkap ASC";
        break;
    case 'nama':
    default:
        $order_sql_belum = "ORDER BY $prioritas_lamaran_sql p.nama_lengkap $sort_dir";
        $order_sql_sudah = "ORDER BY $prioritas_lamaran_sql p.nama_lengkap $sort_dir";
        break;
}

// A. Query Pelamar yang BELUM dikirim undangannya
$sql_belum = "SELECT
                p.id_pelamar,
                p.nama_lengkap,
                p.email,
                p.no_telepon,
                $posisi_opt_expr AS posisi_dilamar,
                COUNT(j.id_jadwal) AS jml_undangan
              FROM `pelamar` p
              $join_opt_sql
              LEFT JOIN `jadwal_seleksi` j ON p.id_pelamar = j.id_pelamar
              $where_sql
              GROUP BY p.id_pelamar
              HAVING jml_undangan = 0
              $order_sql_belum";

$q_belum    = mysqli_query($conn, $sql_belum);
$rows_belum = [];
$all_ids    = [];

if ($q_belum) {
    while ($r = mysqli_fetch_assoc($q_belum)) {
        $r['jml_undangan'] = 0;
        $rows_belum[] = $r;
        $all_ids[] = intval($r['id_pelamar']);
    }
}

// B. Query Pelamar yang SUDAH dikirim undangannya
$sql_sudah = "SELECT
                p.id_pelamar,
                p.nama_lengkap,
                p.email,
                p.no_telepon,
                $posisi_opt_expr AS posisi_dilamar,
                COUNT(j.id_jadwal) AS jml_undangan,
                MAX(j.jenis_tes) AS tahap_terakhir,
                MAX(CONCAT(j.tanggal_jadwal, ' ', j.waktu_jadwal)) AS jadwal_terdekat,
                MAX(j.status_email) AS status_email_terakhir,
                MAX(j.lokasi_atau_link) AS lokasi_terakhir
              FROM `pelamar` p
              $join_opt_sql
              INNER JOIN `jadwal_seleksi` j ON p.id_pelamar = j.id_pelamar
              $where_sql
              GROUP BY p.id_pelamar
              HAVING jml_undangan > 0
              $order_sql_sudah";

$q_sudah    = mysqli_query($conn, $sql_sudah);
$rows_sudah = [];

if ($q_sudah) {
    while ($r = mysqli_fetch_assoc($q_sudah)) {
        $r['jml_undangan'] = intval($r['jml_undangan']);
        $rows_sudah[] = $r;
        $all_ids[] = intval($r['id_pelamar']);
    }
}

$total_belum = count($rows_belum);
$total_sudah = count($rows_sudah);

// Riwayat jadwal per pelamar untuk modal Detail
$riwayat_by_pelamar = [];
if (!empty($all_ids)) {
    $ids_str = implode(',', array_unique($all_ids));
    $q_hist  = mysqli_query($conn, "SELECT * FROM `jadwal_seleksi` WHERE `id_pelamar` IN ($ids_str) ORDER BY `tanggal_jadwal` DESC, `waktu_jadwal` DESC");
    if ($q_hist) {
        while ($qh = mysqli_fetch_assoc($q_hist)) {
            $pid = intval($qh['id_pelamar']);
            $riwayat_by_pelamar[$pid][] = [
                'id_jadwal'        => intval($qh['id_jadwal']),
                'jenis_tes'        => $qh['jenis_tes'],
                'tanggal_jadwal'   => $qh['tanggal_jadwal'],
                'waktu_jadwal'     => substr($qh['waktu_jadwal'], 0, 5),
                'metode'           => $qh['metode'],
                'lokasi_atau_link' => $qh['lokasi_atau_link'],
                'catatan_hr'       => $qh['catatan_hr'] ?? '',
                'status_kehadiran' => $qh['status_kehadiran'],
                'status_email'     => $qh['status_email'] ?? 'Belum Dikirim',
            ];
        }
    }
}

// Kumpulkan opsi lamaran dari data pelamar jika tabel referensi kosong
if (empty($daftar_lamaran_tersedia)) {
    $set_lam = [];
    foreach (array_merge($rows_belum, $rows_sudah) as $rp) {
        if (!empty($rp['posisi_dilamar'])) {
            foreach (explode(',', $rp['posisi_dilamar']) as $item_l) {
                $item_trim = trim($item_l);
                if ($item_trim !== '') {
                    $set_lam[$item_trim] = true;
                }
            }
        }
    }
    $daftar_lamaran_tersedia = array_keys($set_lam);
    sort($daftar_lamaran_tersedia);
}

function linkSort(string $kolom, string $aktifKolom, string $aktifDir, string $cari, string $sortLamaran): string {
    $nextDir = ($aktifKolom === $kolom && strtoupper($aktifDir) === 'ASC') ? 'desc' : 'asc';
    $params = http_build_query([
        'cari'         => $cari,
        'sort_lamaran' => $sortLamaran,
        'sort_by'      => $kolom,
        'sort_dir'     => $nextDir,
    ]);
    return '?' . $params;
}

function ikonSort(string $kolom, string $aktifKolom, string $aktifDir): string {
    if ($aktifKolom !== $kolom) {
        return '↕️';
    }
    return (strtoupper($aktifDir) === 'ASC') ? '🔼' : '🔽';
}

include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .jadwal-page {
        max-width: 100%;
        margin: 12px auto 36px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-sizing: border-box;
    }

    .top-bar-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .page-title-main {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
    }

    .page-subtitle-main {
        margin: 3px 0 0;
        font-size: 12.5px;
        color: #64748b;
    }

    .summary-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .summary-chip {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
    }

    .chip-sudah {
        background: #dcfce7;
        border-color: #86efac;
        color: #166534;
    }

    .chip-belum {
        background: #fef3c7;
        border-color: #fcd34d;
        color: #92400e;
    }

    .main-split-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 16px;
        align-items: start;
    }

    .left-tables-stack {
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-width: 0;
    }

    .panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        min-width: 0;
    }

    .panel-head {
        background: #0b6623;
        color: #ffffff;
        padding: 11px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .panel-head-terkirim {
        background: #14532d;
        color: #ffffff;
        padding: 11px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .panel-head h2, .panel-head-terkirim h2 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .panel-body {
        padding: 14px;
    }

    .filter-sort-bar {
        display: grid;
        grid-template-columns: minmax(150px, 1.4fr) repeat(3, minmax(120px, 1fr)) auto;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 10px;
        border-radius: 10px;
    }

    .input-ui, .select-ui {
        width: 100%;
        min-width: 0;
        min-height: 36px;
        padding: 7px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 12.5px;
        color: #1e293b;
        background: #ffffff;
        box-sizing: border-box;
    }

    .input-ui:focus, .select-ui:focus, .textarea-ui:focus {
        outline: none;
        border-color: #0b6623;
        box-shadow: 0 0 0 2px rgba(11, 102, 35, 0.12);
    }

    .filter-actions-inline {
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .btn-filter-ui {
        background: #0b6623;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        min-height: 36px;
        padding: 7px 14px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-filter-ui:hover { background: #084d1a; }

    .btn-reset-link {
        color: #475569;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        min-height: 36px;
        padding: 7px 11px;
        border-radius: 8px;
        background: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-reset-link:hover { background: #cbd5e1; }

    .mobile-select-all-bar {
        display: none;
    }

    .table-fit-wrap {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow-x: auto;
    }

    .pelamar-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .pelamar-table th {
        background: #f1f5f9;
        color: #0f172a;
        font-weight: 700;
        padding: 10px 9px;
        text-align: left;
        border-bottom: 2px solid #cbd5e1;
        font-size: 12px;
        line-height: 1.25;
        white-space: nowrap;
    }

    .sort-link {
        color: #0f172a;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .sort-link:hover { color: #0b6623; }

    .pelamar-table td {
        padding: 9px 9px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
        color: #334155;
        line-height: 1.4;
    }

    .pelamar-table tbody tr.selectable-row {
        transition: background-color 0.15s ease;
        cursor: pointer;
    }

    .pelamar-table tbody tr.selectable-row:hover {
        background-color: #f8fafc;
    }

    .pelamar-table tbody tr.row-selected {
        background-color: #ecfdf5 !important;
    }

    .cb-pelamar-row,
    #checkAllRows,
    #checkAllMobile {
        width: 18px;
        height: 18px;
        accent-color: #0b6623;
        cursor: pointer;
    }

    .badge-diundang {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .badge-belum-diundang {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
        padding: 3px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .sub-info {
        display: block;
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
        word-break: break-word;
    }

    .aksi-stack {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 5px;
    }

    .btn-detail-sm,
    .btn-wa-sm,
    .btn-del-sm {
        min-height: 32px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        cursor: pointer;
    }

    .btn-detail-sm {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .btn-detail-sm:hover { background: #bae6fd; }

    .btn-wa-sm {
        background: #16a34a;
        color: #ffffff;
        border: none;
    }
    .btn-wa-sm:hover { background: #15803d; }

    .btn-del-sm {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .btn-del-sm:hover { background: #fecaca; }

    .sticky-form-box {
        position: sticky;
        top: 90px;
    }

    .selected-counter-banner {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e3a8a;
        padding: 9px 12px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 11px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-field {
        margin-bottom: 11px;
    }

    .form-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .textarea-ui {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 12.5px;
        color: #1e293b;
        box-sizing: border-box;
        resize: vertical;
    }

    .opt-box {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 600;
        margin-bottom: 9px;
        cursor: pointer;
    }

    .opt-green {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .opt-blue {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    .btn-submit-jadwal {
        width: 100%;
        min-height: 44px;
        background: #0b6623;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-submit-jadwal:hover { background: #084d1a; }

    .alert-ok {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
        padding: 10px 14px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .alert-err {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 10px 14px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    /* Modal Detail Pelamar */
    .modal-backdrop-detail {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 14px;
    }

    .modal-backdrop-detail.open {
        display: flex;
    }

    .modal-card-detail {
        background: #ffffff;
        border-radius: 14px;
        width: 100%;
        max-width: 650px;
        max-height: 88vh;
        overflow-y: auto;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        border: 1px solid #e2e8f0;
    }

    .modal-head-detail {
        background: #0b6623;
        color: #ffffff;
        padding: 13px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }

    .modal-head-detail h3 {
        margin: 0;
        font-size: 14.5px;
        font-weight: 700;
    }

    .btn-close-modal {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        min-width: 34px;
        min-height: 34px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
    }

    .modal-body-detail {
        padding: 16px;
    }

    .detail-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 14px;
        font-size: 12.5px;
    }

    .detail-label {
        display: block;
        font-size: 10.5px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
    }

    .detail-val {
        color: #0f172a;
        font-weight: 600;
        margin-top: 2px;
        word-break: break-word;
    }

    .riwayat-item {
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        padding: 10px 12px;
        margin-bottom: 8px;
        background: #ffffff;
        font-size: 12.5px;
    }

    @media (max-width: 1240px) {
        .filter-sort-bar {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .filter-actions-inline {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }

    @media (max-width: 1080px) {
        .main-split-layout {
            grid-template-columns: 1fr;
        }
        .sticky-form-box {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .jadwal-page {
            margin: 6px auto 28px auto;
            padding: 0 8px;
        }
        .page-title-main {
            font-size: 16px;
        }
        .panel-body {
            padding: 12px;
        }
        .filter-sort-bar {
            grid-template-columns: 1fr;
            gap: 8px;
        }
        .filter-actions-inline {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }
        .input-ui,
        .select-ui,
        .textarea-ui {
            min-height: 42px;
            font-size: 16px;
        }
        .btn-filter-ui,
        .btn-reset-link {
            min-height: 42px;
            font-size: 14px;
        }

        .mobile-select-all-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 12px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .table-fit-wrap {
            border: none;
            overflow: visible;
        }
        .pelamar-table {
            display: block;
        }
        .pelamar-table thead {
            display: none;
        }
        .pelamar-table tbody {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .pelamar-table tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        }
        .pelamar-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e2e8f0;
            text-align: right;
            font-size: 13px;
        }
        .pelamar-table td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .pelamar-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #0b6623;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .pelamar-table td[data-label="Pilih"] {
            align-items: center;
            background: #f8fafc;
            margin: -12px -12px 4px -12px;
            padding: 9px 12px;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 10px 10px 0 0;
        }
        .cb-pelamar-row,
        #checkAllMobile {
            width: 20px;
            height: 20px;
        }
        .aksi-stack {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(95px, 1fr));
            gap: 6px;
        }
        .btn-detail-sm,
        .btn-wa-sm,
        .btn-del-sm {
            min-height: 38px;
            font-size: 12.5px;
        }
    }

    @media (max-width: 640px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
        .detail-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="jadwal-page">
    <div class="top-bar-flex">
        <div>
            <h1 class="page-title-main">PENJADWALAN PSIKOTES & INTERVIEW PELAMAR</h1>
            <p class="page-subtitle-main">Pelamar belum diundang ada di tabel atas; yang sudah dikirim undangannya dipisah ke tabel bawah.</p>
        </div>
        <div class="summary-badges">
            <span class="summary-chip chip-belum">⏳ Belum Diundang: <?php echo intval($total_belum); ?></span>
            <span class="summary-chip chip-sudah">✅ Sudah Dikirim Undangan: <?php echo intval($total_sudah); ?></span>
        </div>
    </div>

    <div id="jadwalApiAlertBox">
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-ok">✅ <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert-err">❌ <?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>
    </div>

    <!-- FORM TERPISAH KHUSUS FILTER & SORTING (GET) -->
    <form method="GET" action="" id="formFilterPelamar"></form>

    <!-- FORM UTAMA PENJADWALAN MASSAL (POST) -->
    <form method="POST" action="" id="formJadwalTabel">
        <input type="hidden" name="simpan_jadwal_massal" value="1">

        <div class="main-split-layout">
            <!-- KOLOM KIRI: TABEL BELUM DIUNDANG (ATAS) & SUDAH DIKIRIM UNDANGAN (BAWAH) -->
            <div class="left-tables-stack">
                <!-- 1. TABEL PELAMAR TERDAFTAR (BELUM DIUNDANG) -->
                <div class="panel-card">
                    <div class="panel-head">
                        <h2><span>📋</span> Daftar Pelamar Terdaftar (Belum Diundang)</h2>
                        <span style="font-size: 12px; opacity: 0.92;"><?php echo intval($total_belum); ?> pelamar • Centang untuk mengundang</span>
                    </div>

                    <div class="panel-body">
                        <div class="filter-sort-bar">
                            <input
                                type="text"
                                name="cari"
                                form="formFilterPelamar"
                                class="input-ui"
                                placeholder="Cari nama, email, no. HP..."
                                value="<?php echo htmlspecialchars($filter_cari); ?>"
                            >

                            <select name="sort_lamaran" form="formFilterPelamar" class="select-ui" onchange="this.form.submit()" title="Urutkan berdasarkan lamaran tersedia">
                                <option value="">Lamaran Tersedia</option>
                                <?php foreach ($daftar_lamaran_tersedia as $nama_lam): ?>
                                    <option value="<?php echo htmlspecialchars($nama_lam); ?>" <?php echo ($sort_lamaran === $nama_lam) ? 'selected' : ''; ?>>
                                        📌 <?php echo htmlspecialchars($nama_lam); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="sort_by" form="formFilterPelamar" class="select-ui" onchange="this.form.submit()" title="Urutkan kolom">
                                <option value="nama" <?php echo ($sort_by === 'nama') ? 'selected' : ''; ?>>Sort: Nama Pelamar</option>
                                <option value="posisi" <?php echo ($sort_by === 'posisi') ? 'selected' : ''; ?>>Sort: Posisi</option>
                                <option value="jadwal" <?php echo ($sort_by === 'jadwal') ? 'selected' : ''; ?>>Sort: Jadwal</option>
                            </select>

                            <select name="sort_dir" form="formFilterPelamar" class="select-ui" onchange="this.form.submit()" title="Arah urutan">
                                <option value="asc" <?php echo (strtolower($sort_dir) === 'asc') ? 'selected' : ''; ?>>A → Z / Terlama</option>
                                <option value="desc" <?php echo (strtolower($sort_dir) === 'desc') ? 'selected' : ''; ?>>Z → A / Terbaru</option>
                            </select>

                            <div class="filter-actions-inline">
                                <button type="submit" form="formFilterPelamar" class="btn-filter-ui">🔍 Filter</button>
                                <?php if ($filter_cari !== '' || $sort_lamaran !== '' || $sort_by !== 'nama' || strtolower($sort_dir) !== 'asc'): ?>
                                    <a href="jadwal_seleksi.php" class="btn-reset-link">Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($rows_belum)): ?>
                            <label class="mobile-select-all-bar" for="checkAllMobile">
                                <span>Pilih Semua Pelamar Belum Diundang</span>
                                <input type="checkbox" id="checkAllMobile" onclick="toggleAllRows(this)">
                            </label>
                        <?php endif; ?>

                        <div class="table-fit-wrap">
                            <table class="pelamar-table">
                                <thead>
                                    <tr>
                                        <th style="width: 38px; text-align: center;">
                                            <input type="checkbox" id="checkAllRows" title="Pilih semua pelamar belum diundang" onclick="toggleAllRows(this)">
                                        </th>
                                        <th>
                                            <a class="sort-link" href="<?php echo htmlspecialchars(linkSort('nama', $sort_by, $sort_dir, $filter_cari, $sort_lamaran)); ?>">
                                                Pelamar <?php echo ikonSort('nama', $sort_by, $sort_dir); ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a class="sort-link" href="<?php echo htmlspecialchars(linkSort('posisi', $sort_by, $sort_dir, $filter_cari, $sort_lamaran)); ?>">
                                                Lamaran <?php echo ikonSort('posisi', $sort_by, $sort_dir); ?>
                                            </a>
                                        </th>
                                        <th>Status</th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows_belum)): ?>
                                        <?php foreach ($rows_belum as $row): ?>
                                            <?php
                                                $pid_row = intval($row['id_pelamar']);
                                                $hp_raw  = preg_replace('/[^0-9]/', '', $row['no_telepon'] ?? '');
                                                $hp_wa   = (substr($hp_raw, 0, 1) === '0') ? '62' . substr($hp_raw, 1) : $hp_raw;
                                                $pesan_wa = urlencode("Halo *{$row['nama_lengkap']}*, informasi seleksi dari HRD Kebab Monster.");

                                                $payload_detail = [
                                                    'id_pelamar'     => $pid_row,
                                                    'nama_lengkap'   => $row['nama_lengkap'] ?? '-',
                                                    'email'          => $row['email'] ?? '-',
                                                    'no_telepon'     => $row['no_telepon'] ?? '-',
                                                    'posisi_dilamar' => $row['posisi_dilamar'] ?: 'Belum memilih posisi',
                                                    'sudah_diundang' => false,
                                                    'jml_undangan'   => 0,
                                                    'riwayat'        => [],
                                                ];
                                            ?>
                                            <tr class="pelamar-row selectable-row" onclick="handleRowClick(event, this)">
                                                <td data-label="Pilih" style="text-align: center;" onclick="event.stopPropagation();">
                                                    <input
                                                        type="checkbox"
                                                        name="id_pelamar[]"
                                                        value="<?php echo $pid_row; ?>"
                                                        class="cb-pelamar-row"
                                                        onchange="syncRowHighlight()"
                                                    >
                                                </td>
                                                <td data-label="Pelamar">
                                                    <div>
                                                        <strong style="color: #0f172a;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong>
                                                        <span class="sub-info"><?php echo htmlspecialchars($row['email'] ?: '-'); ?></span>
                                                        <?php if (!empty($row['no_telepon'])): ?>
                                                            <span class="sub-info">📞 <?php echo htmlspecialchars($row['no_telepon']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="Lamaran">
                                                    <span style="font-weight: 600; color: #0369a1;">
                                                        <?php echo !empty($row['posisi_dilamar']) ? htmlspecialchars($row['posisi_dilamar']) : 'Belum memilih posisi'; ?>
                                                    </span>
                                                </td>
                                                <td data-label="Status">
                                                    <span class="badge-belum-diundang">⏳ Belum Diundang</span>
                                                </td>
                                                <td data-label="Aksi" style="text-align: center;" onclick="event.stopPropagation();">
                                                    <div class="aksi-stack">
                                                        <button
                                                            type="button"
                                                            class="btn-detail-sm"
                                                            data-detail="<?php echo htmlspecialchars(json_encode($payload_detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                                                            onclick="bukaModalDetail(this)"
                                                            title="Lihat Detail Pelamar"
                                                        >👁️ Detail</button>
                                                        <?php if (!empty($hp_wa)): ?>
                                                            <a href="https://wa.me/<?php echo htmlspecialchars($hp_wa); ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-wa-sm" title="Kirim WhatsApp">💬 WA</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 24px; color: #64748b;">
                                                Tidak ada pelamar yang belum diundang pada filter ini.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 2. TABEL TERPISAH: DAFTAR SUDAH DIKIRIM UNDANGAN -->
                <div class="panel-card">
                    <div class="panel-head-terkirim">
                        <h2><span>✉️</span> Daftar Sudah Dikirim Undangan</h2>
                        <span style="font-size: 12px; opacity: 0.92;"><?php echo intval($total_sudah); ?> pelamar sudah diundang</span>
                    </div>

                    <div class="panel-body">
                        <div class="table-fit-wrap">
                            <table class="pelamar-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <a class="sort-link" href="<?php echo htmlspecialchars(linkSort('nama', $sort_by, $sort_dir, $filter_cari, $sort_lamaran)); ?>">
                                                Pelamar <?php echo ikonSort('nama', $sort_by, $sort_dir); ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a class="sort-link" href="<?php echo htmlspecialchars(linkSort('posisi', $sort_by, $sort_dir, $filter_cari, $sort_lamaran)); ?>">
                                                Lamaran & Tahap <?php echo ikonSort('posisi', $sort_by, $sort_dir); ?>
                                            </a>
                                        </th>
                                        <th>
                                            <a class="sort-link" href="<?php echo htmlspecialchars(linkSort('jadwal', $sort_by, $sort_dir, $filter_cari, $sort_lamaran)); ?>">
                                                Jadwal & Email <?php echo ikonSort('jadwal', $sort_by, $sort_dir); ?>
                                            </a>
                                        </th>
                                        <th style="text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($rows_sudah)): ?>
                                        <?php foreach ($rows_sudah as $row): ?>
                                            <?php
                                                $pid_row        = intval($row['id_pelamar']);
                                                $tgl_jadwal_fmt = '-';
                                                if (!empty($row['jadwal_terdekat'])) {
                                                    $ts = strtotime($row['jadwal_terdekat']);
                                                    if ($ts) {
                                                        $tgl_jadwal_fmt = date('d M Y • H:i', $ts) . ' WIB';
                                                    }
                                                }
                                                $hp_raw   = preg_replace('/[^0-9]/', '', $row['no_telepon'] ?? '');
                                                $hp_wa    = (substr($hp_raw, 0, 1) === '0') ? '62' . substr($hp_raw, 1) : $hp_raw;
                                                $pesan_wa = urlencode("Halo *{$row['nama_lengkap']}*, pengingat undangan seleksi dari HRD Kebab Monster.");

                                                $payload_detail = [
                                                    'id_pelamar'     => $pid_row,
                                                    'nama_lengkap'   => $row['nama_lengkap'] ?? '-',
                                                    'email'          => $row['email'] ?? '-',
                                                    'no_telepon'     => $row['no_telepon'] ?? '-',
                                                    'posisi_dilamar' => $row['posisi_dilamar'] ?: 'Belum memilih posisi',
                                                    'sudah_diundang' => true,
                                                    'jml_undangan'   => $row['jml_undangan'],
                                                    'riwayat'        => $riwayat_by_pelamar[$pid_row] ?? [],
                                                ];
                                            ?>
                                            <tr>
                                                <td data-label="Pelamar">
                                                    <div>
                                                        <strong style="color: #0f172a;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong>
                                                        <span class="sub-info"><?php echo htmlspecialchars($row['email'] ?: '-'); ?></span>
                                                        <?php if (!empty($row['no_telepon'])): ?>
                                                            <span class="sub-info">📞 <?php echo htmlspecialchars($row['no_telepon']); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td data-label="Lamaran & Tahap">
                                                    <div>
                                                        <div style="font-weight: 600; color: #0369a1; margin-bottom: 3px;">
                                                            <?php echo !empty($row['posisi_dilamar']) ? htmlspecialchars($row['posisi_dilamar']) : 'Belum memilih posisi'; ?>
                                                        </div>
                                                        <span class="badge-diundang">✅ Terkirim (<?php echo intval($row['jml_undangan']); ?>x)</span>
                                                        <span class="sub-info">Tahap: <strong><?php echo htmlspecialchars($row['tahap_terakhir'] ?: '-'); ?></strong></span>
                                                    </div>
                                                </td>
                                                <td data-label="Jadwal & Email">
                                                    <div>
                                                        <strong style="font-size: 12px; color: #0f172a;"><?php echo htmlspecialchars($tgl_jadwal_fmt); ?></strong>
                                                        <span class="sub-info">Email: <?php echo htmlspecialchars($row['status_email_terakhir'] ?: 'Belum Dikirim'); ?></span>
                                                    </div>
                                                </td>
                                                <td data-label="Aksi" style="text-align: center;">
                                                    <div class="aksi-stack">
                                                        <button
                                                            type="button"
                                                            class="btn-detail-sm"
                                                            data-detail="<?php echo htmlspecialchars(json_encode($payload_detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
                                                            onclick="bukaModalDetail(this)"
                                                            title="Lihat Detail & Riwayat Jadwal"
                                                        >👁️ Detail</button>
                                                        <?php if (!empty($hp_wa)): ?>
                                                            <a href="https://wa.me/<?php echo htmlspecialchars($hp_wa); ?>?text=<?php echo $pesan_wa; ?>" target="_blank" class="btn-wa-sm" title="Kirim WhatsApp">💬 WA</a>
                                                        <?php endif; ?>
                                                        <a href="?hapus_jadwal_pelamar=<?php echo $pid_row; ?>" onclick="return confirm('Hapus jadwal undangan <?php echo htmlspecialchars(addslashes($row['nama_lengkap'])); ?> dan kembalikan ke Belum Diundang?');" class="btn-del-sm" title="Hapus Jadwal">🗑️ Hapus</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; padding: 24px; color: #64748b;">
                                                Belum ada pelamar yang dikirim undangannya.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: FORM PENGATURAN JADWAL & KIRIM UNDANGAN -->
            <div class="panel-card sticky-form-box">
                <div class="panel-head">
                    <h2><span>📨</span> Atur Jadwal & Undangan</h2>
                </div>
                <div class="panel-body">
                    <div class="selected-counter-banner">
                        <span>Pelamar Tercentang</span>
                        <span id="countSelectedForm" style="background: #1d4ed8; color: #fff; padding: 3px 10px; border-radius: 999px; font-size: 12px;">0 orang</span>
                    </div>

                    <div class="form-field">
                        <label for="jenisTesSelect">Tahap Seleksi *</label>
                        <select id="jenisTesSelect" name="jenis_tes" class="select-ui" required>
                            <option value="Psikotes">🧠 Psikotes</option>
                            <option value="Interview">🤝 Interview</option>
                        </select>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-field">
                            <label for="tglJadwalInput">Tanggal *</label>
                            <input id="tglJadwalInput" type="date" name="tanggal_jadwal" class="input-ui" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-field">
                            <label for="waktuJadwalInput">Jam (WIB) *</label>
                            <input id="waktuJadwalInput" type="time" name="waktu_jadwal" class="input-ui" required>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="metodeSelect">Metode Pelaksanaan *</label>
                        <select name="metode" id="metodeSelect" class="select-ui" onchange="ubahPlaceholderLokasi()">
                            <option value="Offline / Datang Langsung">🏢 Offline / Datang Langsung</option>
                            <option value="Online / Zoom / Meet">💻 Online / Zoom / Google Meet</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label id="labelLokasi" for="inputLokasi">Alamat Kantor / Cabang *</label>
                        <textarea name="lokasi_atau_link" id="inputLokasi" rows="2" class="textarea-ui" placeholder="Contoh: Office Kebab Monster Dramaga, Jl. Raya Dramaga..." required></textarea>
                    </div>

                    <div class="form-field">
                        <label for="catatanHrInput">Catatan Tambahan (Opsional)</label>
                        <textarea id="catatanHrInput" name="catatan_hr" rows="2" class="textarea-ui" placeholder="Contoh: Bawa KTP asli & alat tulis..."></textarea>
                    </div>

                    <label class="opt-box opt-green">
                        <input type="checkbox" name="sinkron_status" value="1" checked style="margin-top: 2px;">
                        <span>Update status lamaran pelamar terpilih menjadi tahap yang dijadwalkan.</span>
                    </label>

                    <label class="opt-box opt-blue">
                        <input type="checkbox" name="kirim_email_otomatis" value="1" checked style="margin-top: 2px;">
                        <span>Kirim email undangan otomatis ke seluruh pelamar terpilih.</span>
                    </label>

                    <button type="submit" class="btn-submit-jadwal">🚀 Simpan Jadwal & Kirim Undangan</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- MODAL DETAIL PELAMAR & RIWAYAT UNDANGAN -->
<div class="modal-backdrop-detail" id="modalDetailPelamar" onclick="tutupModalBackdrop(event)">
    <div class="modal-card-detail">
        <div class="modal-head-detail">
            <h3 id="modalDetailTitle">Detail Pelamar</h3>
            <button type="button" class="btn-close-modal" onclick="tutupModalDetail()">✕</button>
        </div>
        <div class="modal-body-detail" id="modalDetailBody"></div>
    </div>
</div>

<script>
function handleRowClick(event, rowElement) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'A' || event.target.tagName === 'BUTTON') {
        return;
    }
    const cb = rowElement.querySelector('.cb-pelamar-row');
    if (cb) {
        cb.checked = !cb.checked;
        syncRowHighlight();
    }
}

function toggleAllRows(source) {
    document.querySelectorAll('.cb-pelamar-row').forEach(cb => {
        cb.checked = source.checked;
    });
    syncRowHighlight();
}

function syncRowHighlight() {
    let selectedCount = 0;
    const allCbs = document.querySelectorAll('.cb-pelamar-row');

    document.querySelectorAll('.pelamar-row').forEach(row => {
        const cb = row.querySelector('.cb-pelamar-row');
        const isChecked = cb && cb.checked;
        row.classList.toggle('row-selected', isChecked);
        if (isChecked) selectedCount++;
    });

    const allChecked = (allCbs.length > 0 && selectedCount === allCbs.length);
    const masterCb = document.getElementById('checkAllRows');
    const mobileCb = document.getElementById('checkAllMobile');
    if (masterCb) masterCb.checked = allChecked;
    if (mobileCb) mobileCb.checked = allChecked;

    document.getElementById('countSelectedForm').textContent = selectedCount + ' orang';
}

function ubahPlaceholderLokasi() {
    const metode = document.getElementById('metodeSelect').value;
    const label  = document.getElementById('labelLokasi');
    const input  = document.getElementById('inputLokasi');
    if (metode.includes('Online')) {
        label.textContent = 'Link Zoom / Google Meet *';
        input.placeholder = 'Contoh: https://meet.google.com/xxx-yyyy-zzz';
    } else {
        label.textContent = 'Alamat Kantor / Cabang *';
        input.placeholder = 'Contoh: Office Kebab Monster Dramaga, Jl. Raya Dramaga...';
    }
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function bukaModalDetail(btnElement) {
    const raw = btnElement.getAttribute('data-detail');
    if (!raw) return;
    const d = JSON.parse(raw);

    document.getElementById('modalDetailTitle').textContent = 'Detail Pelamar — ' + (d.nama_lengkap || '');

    const statusBadge = d.sudah_diundang
        ? `<span class="badge-diundang">✅ Sudah Dikirim Undangan (${d.jml_undangan}x)</span>`
        : `<span class="badge-belum-diundang">⏳ Belum Diundang</span>`;

    let riwayatHtml = '';
    if (Array.isArray(d.riwayat) && d.riwayat.length > 0) {
        riwayatHtml = d.riwayat.map(item => `
            <div class="riwayat-item">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:5px;">
                    <strong>${escapeHtml(item.jenis_tes)}</strong>
                    <span style="font-size:12px; color:#0b6623; font-weight:700;">${escapeHtml(item.tanggal_jadwal)} • ${escapeHtml(item.waktu_jadwal)} WIB</span>
                </div>
                <div style="color:#475569;"><strong>Metode:</strong> ${escapeHtml(item.metode)}</div>
                <div style="color:#475569;"><strong>Lokasi / Link:</strong> ${escapeHtml(item.lokasi_atau_link)}</div>
                ${item.catatan_hr ? `<div style="color:#475569;"><strong>Catatan HR:</strong> ${escapeHtml(item.catatan_hr)}</div>` : ''}
                <div style="margin-top:5px; font-size:12px; color:#334155;">
                    <strong>Kehadiran:</strong> ${escapeHtml(item.status_kehadiran)} • <strong>Status Email:</strong> ${escapeHtml(item.status_email)}
                </div>
            </div>
        `).join('');
    } else {
        riwayatHtml = `<div style="padding:14px; text-align:center; background:#f8fafc; border-radius:9px; color:#64748b; font-size:12.5px;">Belum ada jadwal undangan yang tercatat untuk pelamar ini.</div>`;
    }

    document.getElementById('modalDetailBody').innerHTML = `
        <div class="detail-info-grid">
            <div><span class="detail-label">Nama Lengkap</span><div class="detail-val">${escapeHtml(d.nama_lengkap)}</div></div>
            <div><span class="detail-label">Status Undangan</span><div class="detail-val">${statusBadge}</div></div>
            <div><span class="detail-label">Email</span><div class="detail-val">${escapeHtml(d.email)}</div></div>
            <div><span class="detail-label">No. Telepon / WA</span><div class="detail-val">${escapeHtml(d.no_telepon)}</div></div>
            <div style="grid-column: 1 / -1;"><span class="detail-label">Posisi / Lamaran</span><div class="detail-val" style="color:#0369a1;">${escapeHtml(d.posisi_dilamar)}</div></div>
        </div>
        <h4 style="margin:0 0 10px; font-size:13.5px; color:#0f172a;">📅 Riwayat Jadwal Psikotes & Interview</h4>
        ${riwayatHtml}
    `;

    document.getElementById('modalDetailPelamar').classList.add('open');
}

function tutupModalDetail() {
    document.getElementById('modalDetailPelamar').classList.remove('open');
}

function tutupModalBackdrop(e) {
    if (e.target.id === 'modalDetailPelamar') {
        tutupModalDetail();
    }
}

document.getElementById('formJadwalTabel').addEventListener('submit', function(e) {
    const form = this;
    const checkedBoxes = Array.from(document.querySelectorAll('.cb-pelamar-row:checked'));
    const dipilih = checkedBoxes.length;
    if (dipilih === 0) {
        e.preventDefault();
        alert('Silakan centang minimal 1 pelamar pada tabel terlebih dahulu.');
        return;
    }

    if (!window.fetch) {
        return;
    }

    e.preventDefault();

    const fd = new FormData(form);
    const payload = {
        id_pelamar: checkedBoxes.map(cb => parseInt(cb.value, 10)).filter(id => id > 0),
        jenis_tes: String(fd.get('jenis_tes') || 'Psikotes'),
        tanggal_jadwal: String(fd.get('tanggal_jadwal') || ''),
        waktu_jadwal: String(fd.get('waktu_jadwal') || ''),
        metode: String(fd.get('metode') || 'Offline / Datang Langsung'),
        lokasi_atau_link: String(fd.get('lokasi_atau_link') || '').trim(),
        catatan_hr: String(fd.get('catatan_hr') || '').trim(),
        status_email: fd.get('kirim_email_otomatis') ? 'Terkirim' : 'Belum Dikirim'
    };

    const submitBtn = form.querySelector('.btn-submit-jadwal');
    if (submitBtn) submitBtn.disabled = true;

    const apiEndpoints = [
        '../api/admin/jadwal_seleksi.php',
        '../api/jadwal_seleksi.php'
    ];

    const postJadwalSeleksi = async () => {
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

    postJadwalSeleksi()
        .then(data => {
            if (!data || !data.success) {
                throw new Error((data && data.message) || 'Gagal menyimpan jadwal seleksi.');
            }
            const alertBox = document.getElementById('jadwalApiAlertBox');
            if (alertBox) {
                alertBox.innerHTML = '<div class="alert-ok">✅ ' +
                    escapeHtml(data.message || 'Jadwal seleksi berhasil disimpan.') +
                    '</div>';
            }
            window.location.reload();
        })
        .catch(() => {
            if (submitBtn) submitBtn.disabled = false;
            form.submit();
        });
});

syncRowHighlight();
</script>

<?php
mysqli_close($conn);
include __DIR__ . '/../includes/footer_admin.php';
?>