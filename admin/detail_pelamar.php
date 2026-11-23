<?php
// Nama File: admin/detail_pelamar.php
session_start();

// Cek sesi login HR / Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'hrd')) {
    echo "<script>alert('Silakan login terlebih dahulu sebagai HR/Admin!'); window.location='../login.php';</script>";
    exit();
}

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil ID pelamar dari GET; jika kosong, fallback ke pelamar pertama agar tidak error saat link tanpa ?id= diklik
$has_id_param = isset($_GET['id']) && $_GET['id'] !== '';
$id_pelamar   = $has_id_param ? intval($_GET['id']) : 0;

if ($has_id_param && $id_pelamar <= 0) {
    mysqli_close($conn);
    echo "<script>alert('ID Pelamar tidak valid!'); window.location='daftar_pelamar.php';</script>";
    exit();
}

if ($id_pelamar <= 0) {
    $q_first = mysqli_query($conn, "SELECT id_pelamar FROM pelamar ORDER BY id_pelamar ASC LIMIT 1");
    if ($q_first && $r_first = mysqli_fetch_assoc($q_first)) {
        $id_pelamar = intval($r_first['id_pelamar']);
    } else {
        mysqli_close($conn);
        echo "<script>alert('Belum ada data pelamar!'); window.location='daftar_pelamar.php';</script>";
        exit();
    }
}

// Ambil seluruh data pelamar
$query = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
if (!$query || mysqli_num_rows($query) == 0) {
    mysqli_close($conn);
    echo "<script>alert('Data pelamar tidak ditemukan!'); window.location='daftar_pelamar.php';</script>";
    exit();
}
$data = mysqli_fetch_assoc($query);

// Ambil riwayat lamaran pelamar (deteksi otomatis tabel lamaran & lowongan)
$riwayat_lamaran = [];
$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
while ($t = mysqli_fetch_row($q_tables)) {
    $all_tables[] = $t[0];
}

$tbl_lamaran  = "";
$cols_lamaran = [];
foreach ($all_tables as $tbl) {
    if ($tbl === 'pelamar' || $tbl === 'admin') continue;
    $cols = [];
    $q_c = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
    while ($c = mysqli_fetch_assoc($q_c)) {
        $cols[] = $c['Field'];
    }
    if (in_array('tgl_melamar', $cols) || (in_array('id_pelamar', $cols) && count($cols) <= 12)) {
        $tbl_lamaran  = $tbl;
        $cols_lamaran = $cols;
        if (in_array('tgl_melamar', $cols)) break;
    }
}

if (!empty($tbl_lamaran)) {
    $fk_pelamar = in_array('id_pelamar', $cols_lamaran) ? 'id_pelamar' : 'pelamar_id';
    $tgl_col    = in_array('tgl_melamar', $cols_lamaran) ? 'tgl_melamar' : (in_array('tgl_daftar', $cols_lamaran) ? 'tgl_daftar' : '');
    $status_col = in_array('status', $cols_lamaran) ? 'status' : (in_array('status_lamaran', $cols_lamaran) ? 'status_lamaran' : '');

    // Cek apakah nama posisi langsung di tabel lamaran atau join ke tabel lowongan
    $direct_pos = "";
    foreach ($cols_lamaran as $cl) {
        if (preg_match('/(posisi|lowongan|jabatan|judul)/i', $cl) && !preg_match('/^id_/i', $cl)) {
            $direct_pos = $cl;
            break;
        }
    }

    if (!empty($direct_pos)) {
        $q_lam = mysqli_query($conn, "SELECT * FROM `$tbl_lamaran` WHERE `$fk_pelamar` = $id_pelamar");
        while ($rl = mysqli_fetch_assoc($q_lam)) {
            $riwayat_lamaran[] = [
                'posisi' => $rl[$direct_pos] ?? '-',
                'tanggal' => !empty($tgl_col) ? ($rl[$tgl_col] ?? '-') : '-',
                'status' => !empty($status_col) ? ($rl[$status_col] ?? 'Diproses') : 'Diproses'
            ];
        }
    } else {
        $fk_low = "";
        foreach ($cols_lamaran as $cl) {
            if ($cl !== $fk_pelamar && preg_match('/(lowongan|loker|posisi)/i', $cl)) {
                $fk_low = $cl;
                break;
            }
        }
        if (!empty($fk_low)) {
            foreach ($all_tables as $tbl) {
                if ($tbl === 'pelamar' || $tbl === 'admin' || $tbl === $tbl_lamaran) continue;
                $cols_low = [];
                $q_clow = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
                while ($c = mysqli_fetch_assoc($q_clow)) {
                    $cols_low[] = $c['Field'];
                }
                $pk_low = $cols_low[0] ?? '';
                if ($pk_low === $fk_low || in_array($fk_low, $cols_low)) {
                    $nama_col = "";
                    foreach ($cols_low as $cn) {
                        if (preg_match('/(nama|judul|posisi|jabatan|title)/i', $cn) && !preg_match('/^id_/i', $cn)) {
                            $nama_col = $cn;
                            break;
                        }
                    }
                    if (!empty($nama_col)) {
                        $sql_rl = "SELECT l.*, low.`$nama_col` AS nama_posisi_low
                                   FROM `$tbl_lamaran` l
                                   LEFT JOIN `$tbl` low ON l.`$fk_low` = low.`$pk_low`
                                   WHERE l.`$fk_pelamar` = $id_pelamar";
                        $q_lam = mysqli_query($conn, $sql_rl);
                        while ($rl = mysqli_fetch_assoc($q_lam)) {
                            $riwayat_lamaran[] = [
                                'posisi'  => $rl['nama_posisi_low'] ?? '-',
                                'tanggal' => !empty($tgl_col) ? ($rl[$tgl_col] ?? '-') : '-',
                                'status'  => !empty($status_col) ? ($rl[$status_col] ?? 'Diproses') : 'Diproses'
                            ];
                        }
                        break;
                    }
                }
            }
        }
    }
}

$api_detail_url = file_exists(__DIR__ . '/../api/detail_pelamar.php')
    ? '../api/detail_pelamar.php'
    : (file_exists(__DIR__ . '/api/detail_pelamar.php') ? 'api/detail_pelamar.php' : '');

mysqli_close($conn);

// Helper format tanggal Indonesia
function tglIndo($tgl) {
    if (empty($tgl) || $tgl == '0000-00-00') return '-';
    return date('d M Y', strtotime($tgl));
}

// Helper tampil teks aman
function val($str) {
    return (!empty($str) || $str === '0') ? htmlspecialchars($str) : '-';
}

// Include Header Admin/HR
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';
?>

<style>
    .detail-wrapper {
        max-width: 1200px;
        margin: 15px auto 45px auto;
        padding: 0 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .top-bar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .page-heading {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin: 0;
        text-transform: uppercase;
    }
    .btn-back {
        background-color: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }
    .btn-back:hover { background-color: #cbd5e1; }

    .detail-grid-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* Panel Kiri (Profil Ringkas & Berkas) */
    .left-sidebar-stack {
        display: flex;
        flex-direction: column;
        gap: 18px;
        position: sticky;
        top: 85px;
    }
    .profile-summary-card {
        background-color: #f4f5f9;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 24px 18px;
        text-align: center;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .avatar-large {
        width: 130px;
        height: 130px;
        margin: 0 auto 14px auto;
        border-radius: 50%;
        background-color: #e0f2f1;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 58px;
    }
    .avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pelamar-title-name {
        font-size: 15px;
        font-weight: 800;
        color: #0b6623;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .pelamar-sub-email {
        font-size: 12px;
        color: #555;
        margin-bottom: 14px;
        word-break: break-all;
    }
    .quick-pills {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 6px;
        margin-bottom: 18px;
    }
    .pill-item {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        padding: 4px 10px;
        border-radius: 14px;
        font-size: 11px;
        font-weight: 600;
        color: #333;
    }
    .btn-cetak-cv-full {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        background-color: #0b6623;
        color: #ffffff;
        text-decoration: none;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        box-sizing: border-box;
        transition: background 0.2s;
    }
    .btn-cetak-cv-full:hover { background-color: #084d1a; }

    /* Kartu Riwayat Lamaran di Sidebar Kiri */
    .lamaran-side-card {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 16px;
    }
    .lamaran-side-title {
        font-size: 13px;
        font-weight: 700;
        color: #0b6623;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 6px;
    }
    .lamaran-item-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 9px 11px;
        margin-bottom: 8px;
        font-size: 12px;
    }
    .lamaran-item-box:last-child { margin-bottom: 0; }

    /* Panel Kanan (Detail Section Cards) */
    .right-sections-stack {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .section-card-outer {
        background-color: #f4f5f9;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }
    .section-banner {
        background-color: #0b6623;
        color: #ffffff;
        border-radius: 25px;
        padding: 8px 18px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }
    .section-inner-white {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 18px 20px;
    }

    /* Grid Data Key-Value */
    .info-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 24px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        border-bottom: 1px dashed #e5e7eb;
        padding-bottom: 6px;
    }
    .info-item.full-span {
        grid-column: span 2;
    }
    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .info-val {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.4;
    }
    .sub-block-header {
        grid-column: span 2;
        font-size: 13px;
        font-weight: 700;
        color: #0b6623;
        background-color: #e8f5e9;
        padding: 6px 12px;
        border-radius: 6px;
        margin-top: 6px;
    }

    /* Grid Dokumen Lampiran */
    .docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 14px;
    }
    .doc-box-card {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        background-color: #f8fafc;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .doc-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .doc-preview-area {
        height: 115px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .doc-preview-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .btn-lihat-berkas {
        display: inline-block;
        background-color: #0b6623;
        color: #ffffff;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .btn-lihat-berkas:hover { background-color: #084d1a; }
    .badge-kosong {
        display: inline-block;
        background-color: #fee2e2;
        color: #991b1b;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    @media (max-width: 900px) {
        .detail-grid-layout { grid-template-columns: 1fr; }
        .left-sidebar-stack { position: static; }
        .info-grid-2col { grid-template-columns: 1fr; }
        .info-item.full-span, .sub-block-header { grid-column: span 1; }
    }
</style>

<div class="detail-wrapper">
<div class="detail-grid-layout">
        <!-- KOLOM KIRI: RINGKASAN PROFIL & POSISI DILAMAR -->
        <div class="left-sidebar-stack">
            <div class="profile-summary-card">
                <div class="avatar-large">
                    <?php if (!empty($data['pas_foto']) && file_exists('../uploads/' . $data['pas_foto'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($data['pas_foto']); ?>" alt="Pas Foto">
                    <?php else: ?>
                        👤
                    <?php endif; ?>
                </div>
                <div class="pelamar-title-name"><?php echo val($data['nama_lengkap']); ?></div>
                <div class="pelamar-sub-email"><?php echo val($data['email']); ?></div>

                <div class="quick-pills">
                    <span class="pill-item">📞 <?php echo val($data['no_telepon']); ?></span>
                    <span class="pill-item">📍 <?php echo val($data['kota']); ?></span>
                    <span class="pill-item">🎓 <?php echo val($data['pend_terakhir']); ?></span>
                </div>

                <a href="daftar_pelamar.php" class="btn-cetak-cv-full">
                    <span>📄</span> Kembali ke Daftar Pelamar
                </a>
            </div>

            <!-- Riwayat Lamaran yang Diajukan -->
            <div class="lamaran-side-card">
                <div class="lamaran-side-title">💼 Posisi yang Dilamar</div>
                <?php if (count($riwayat_lamaran) > 0): ?>
                    <?php foreach ($riwayat_lamaran as $lam): ?>
                        <div class="lamaran-item-box">
                            <div style="font-weight: 700; color: #0f172a; margin-bottom: 3px;">
                                <?php echo htmlspecialchars($lam['posisi']); ?>
                            </div>
                            <div style="font-size: 11px; color: #64748b;">
                                Tgl Melamar: <?php echo tglIndo($lam['tanggal']); ?>
                            </div>
                            <div style="margin-top: 4px;">
                                <span style="background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:10px; font-size:10.5px; font-weight:700;">
                                    <?php echo htmlspecialchars($lam['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="font-size: 12px; color: #64748b; font-style: italic;">
                        Pelamar belum mengajukan lamaran pada lowongan mana pun.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- KOLOM KANAN: SEMUA DETAIL DATA PELAMAR -->
        <div class="right-sections-stack">

            <!-- 1. DATA DIRI -->
            <div class="section-card-outer">
                <div class="section-banner"><span>👤</span> 1. Data Diri Pelamar</div>
                <div class="section-inner-white">
                    <div class="info-grid-2col">
                        <div class="info-item">
                            <span class="info-label">Nama Lengkap</span>
                            <span class="info-val"><?php echo val($data['nama_lengkap']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nomor KTP (NIK)</span>
                            <span class="info-val"><?php echo val($data['no_ktp']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tempat, Tanggal Lahir</span>
                            <span class="info-val"><?php echo val($data['tempat_lahir']) . ', ' . tglIndo($data['tanggal_lahir']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jenis Kelamin</span>
                            <span class="info-val"><?php echo val($data['jenis_kelamin']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Agama</span>
                            <span class="info-val"><?php echo val($data['agama']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kewarganegaraan</span>
                            <span class="info-val"><?php echo val($data['kewarganegaraan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status Pernikahan</span>
                            <span class="info-val"><?php echo val($data['status_nikah']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jumlah Anak</span>
                            <span class="info-val"><?php echo val($data['jumlah_anak']); ?> Orang</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tinggi / Berat Badan</span>
                            <span class="info-val"><?php echo val($data['tinggi_badan']) . ' / ' . val($data['berat_badan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No. Telepon / WhatsApp</span>
                            <span class="info-val"><?php echo val($data['no_telepon']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kota / Kabupaten</span>
                            <span class="info-val"><?php echo val($data['kota']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kecamatan & Kode Pos</span>
                            <span class="info-val"><?php echo val($data['kecamatan']) . ' (' . val($data['kode_pos']) . ')'; ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Alamat Lengkap Domisili</span>
                            <span class="info-val"><?php echo nl2br(val($data['alamat'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. KONTAK DARURAT -->
            <div class="section-card-outer">
                <div class="section-banner"><span>🚨</span> 2. Kontak Darurat (Keluarga)</div>
                <div class="section-inner-white">
                    <div class="info-grid-2col">
                        <div class="info-item">
                            <span class="info-label">Nama Kontak Darurat</span>
                            <span class="info-val"><?php echo val($data['darurat_nama']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Hubungan Keluarga</span>
                            <span class="info-val"><?php echo val($data['darurat_hubungan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No. Telepon 1</span>
                            <span class="info-val"><?php echo val($data['darurat_telp1']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No. Telepon 2</span>
                            <span class="info-val"><?php echo val($data['darurat_telp2']); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Alamat Kontak Darurat</span>
                            <span class="info-val"><?php echo nl2br(val($data['darurat_alamat'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. PENDIDIKAN TERAKHIR -->
            <div class="section-card-outer">
                <div class="section-banner"><span>🎓</span> 3. Pendidikan Terakhir</div>
                <div class="section-inner-white">
                    <div class="info-grid-2col">
                        <div class="info-item">
                            <span class="info-label">Jenjang Pendidikan</span>
                            <span class="info-val"><?php echo val($data['pend_terakhir']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nama Sekolah / Universitas</span>
                            <span class="info-val"><?php echo val($data['pend_institusi']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jurusan / Program Studi</span>
                            <span class="info-val"><?php echo val($data['pend_jurusan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Nilai Akhir / IPK</span>
                            <span class="info-val"><?php echo val($data['pend_nilai']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tanggal Mulai</span>
                            <span class="info-val"><?php echo tglIndo($data['pend_mulai']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tanggal Lulus / Selesai</span>
                            <span class="info-val"><?php echo tglIndo($data['pend_selesai']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. PENGALAMAN KERJA -->
            <div class="section-card-outer">
                <div class="section-banner"><span>💼</span> 4. Riwayat Pengalaman Kerja</div>
                <div class="section-inner-white">
                    <div class="info-grid-2col">
                        <div class="sub-block-header">Pengalaman Kerja 1 (Terakhir)</div>
                        <div class="info-item">
                            <span class="info-label">Nama Perusahaan</span>
                            <span class="info-val"><?php echo val($data['kerja_perusahaan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">No. Telepon Perusahaan</span>
                            <span class="info-val"><?php echo val($data['kerja_telp']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Periode Bekerja</span>
                            <span class="info-val"><?php echo tglIndo($data['kerja_mulai']) . ' s/d ' . tglIndo($data['kerja_akhir']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Jabatan</span>
                            <span class="info-val"><?php echo nl2br(val($data['kerja_jabatan'])); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Jobdesk / Deskripsi Pekerjaan</span>
                            <span class="info-val"><?php echo nl2br(val($data['kerja_jobdesk'])); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Alamat Perusahaan</span>
                            <span class="info-val"><?php echo nl2br(val($data['kerja_alamat'])); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Alasan Berhenti</span>
                            <span class="info-val"><?php echo nl2br(val($data['kerja_alasan'])); ?></span>
                        </div>

                        <?php if (!empty($data['kerja2_perusahaan'])): ?>
                            <div class="sub-block-header">Pengalaman Kerja 2 (Sebelumnya)</div>
                            <div class="info-item">
                                <span class="info-label">Nama Perusahaan</span>
                                <span class="info-val"><?php echo val($data['kerja2_perusahaan']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">No. Telepon Perusahaan</span>
                                <span class="info-val"><?php echo val($data['kerja2_telp']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Periode Bekerja</span>
                                <span class="info-val"><?php echo tglIndo($data['kerja2_mulai']) . ' s/d ' . tglIndo($data['kerja2_akhir']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Jabatan</span>
                                <span class="info-val"><?php echo nl2br(val($data['kerja2_jabatan'])); ?></span>
                            </div>
                            <div class="info-item full-span">
                                <span class="info-label">Jobdesk / Deskripsi Pekerjaan</span>
                                <span class="info-val"><?php echo nl2br(val($data['kerja2_jobdesk'])); ?></span>
                            </div>
                            <div class="info-item full-span">
                                <span class="info-label">Alamat Perusahaan</span>
                                <span class="info-val"><?php echo nl2br(val($data['kerja2_alamat'])); ?></span>
                            </div>
                            <div class="info-item full-span">
                                <span class="info-label">Alasan Berhenti</span>
                                <span class="info-val"><?php echo nl2br(val($data['kerja2_alasan'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 5. PENGALAMAN INTERNSHIP / MAGANG -->
            <div class="section-card-outer">
                <div class="section-banner"><span>📋</span> 5. Pengalaman Internship / Magang</div>
                <div class="section-inner-white">
                    <div class="info-grid-2col">
                        <div class="sub-block-header">Internship / Magang 1</div>
                        <div class="info-item">
                            <span class="info-label">Nama Instansi / Perusahaan</span>
                            <span class="info-val"><?php echo val($data['magang1_instansi']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Posisi / Peran</span>
                            <span class="info-val"><?php echo val($data['magang1_posisi']); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Periode Magang</span>
                            <span class="info-val"><?php echo tglIndo($data['magang1_mulai']) . ' s/d ' . tglIndo($data['magang1_akhir']); ?></span>
                        </div>
                        <div class="info-item full-span">
                            <span class="info-label">Jobdesk / Deskripsi Kegiatan</span>
                            <span class="info-val"><?php echo nl2br(val($data['magang1_jobdesk'])); ?></span>
                        </div>

                        <?php if (!empty($data['magang2_instansi'])): ?>
                            <div class="sub-block-header">Internship / Magang 2</div>
                            <div class="info-item">
                                <span class="info-label">Nama Instansi / Perusahaan</span>
                                <span class="info-val"><?php echo val($data['magang2_instansi']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Posisi / Peran</span>
                                <span class="info-val"><?php echo val($data['magang2_posisi']); ?></span>
                            </div>
                            <div class="info-item full-span">
                                <span class="info-label">Periode Magang</span>
                                <span class="info-val"><?php echo tglIndo($data['magang2_mulai']) . ' s/d ' . tglIndo($data['magang2_akhir']); ?></span>
                            </div>
                            <div class="info-item full-span">
                                <span class="info-label">Jobdesk / Deskripsi Kegiatan</span>
                                <span class="info-val"><?php echo nl2br(val($data['magang2_jobdesk'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 6. BERKAS LAMPIRAN PELAMAR -->
            <div class="section-card-outer">
                <div class="section-banner"><span>📁</span> 6. Berkas Lampiran & Dokumen Pelamar</div>
                <div class="section-inner-white">
                    <div class="docs-grid">
                        <?php
                        $dokumen_list = [
                            'pas_foto' => 'Pas Foto',
                            'ktp'      => 'Kartu Tanda Penduduk (KTP)',
                            'ijazah'   => 'Ijazah Terakhir',
                            'skck'     => 'SKCK',
                            'kk'       => 'Kartu Keluarga (KK)'
                        ];

                        foreach ($dokumen_list as $kolom => $judul_dok):
                            $nama_file = $data[$kolom] ?? '';
                            $path_file = '../uploads/' . $nama_file;
                            $ada_file  = (!empty($nama_file) && file_exists($path_file));
                            $ext       = $ada_file ? strtolower(pathinfo($nama_file, PATHINFO_EXTENSION)) : '';
                        ?>
                            <div class="doc-box-card">
                                <div class="doc-title"><?php echo $judul_dok; ?></div>
                                <div class="doc-preview-area">
                                    <?php if ($ada_file): ?>
                                        <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                                            <img src="<?php echo htmlspecialchars($path_file); ?>" alt="<?php echo $judul_dok; ?>">
                                        <?php elseif ($ext === 'pdf'): ?>
                                            <div style="color: #dc2626; font-weight: 700; font-size: 13px;">📕 Dokumen PDF</div>
                                        <?php else: ?>
                                            <div style="color: #334155; font-size: 12px;">📄 File Tersedia</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($ada_file): ?>
                                        <a href="<?php echo htmlspecialchars($path_file); ?>" target="_blank" class="btn-lihat-berkas">
                                            🔍 Buka / Unduh
                                        </a>
                                    <?php else: ?>
                                        <span class="badge-kosong">Belum Diunggah</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if (!empty($api_detail_url)): ?>
<script>
(function() {
    const idPelamar = <?php echo intval($id_pelamar); ?>;
    const apiUrl = <?php echo json_encode($api_detail_url); ?>;
    if (!window.fetch || !idPelamar || !apiUrl) return;

    fetch(apiUrl + '?id=' + encodeURIComponent(idPelamar))
        .then(res => res.ok ? res.json() : null)
        .then(json => {
            if (!json || !json.success || !json.pelamar) return;
            const p = json.pelamar;
            const titleName = document.querySelector('.pelamar-title-name');
            const subEmail  = document.querySelector('.pelamar-sub-email');
            if (titleName && p.nama_lengkap) titleName.textContent = p.nama_lengkap;
            if (subEmail && p.email) subEmail.textContent = p.email;
        })
        .catch(() => {});
})();
</script>
<?php endif; ?>

<?php
include '../includes/footer_admin.php';
?>