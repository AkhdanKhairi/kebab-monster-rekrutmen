<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil ID lowongan dari URL (parameter ?id=...)
$id_lowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query ambil detail lowongan berdasarkan id_lowongan
$query = mysqli_query($conn, "SELECT * FROM lowongan WHERE id_lowongan = $id_lowongan");
$job = mysqli_fetch_assoc($query);

if (!$job) {
    echo "<script>alert('Lowongan tidak ditemukan!'); window.location='karir.php';</script>";
    exit();
}

include '../includes/header_pelamar.php';
?>

<style>
    .detail-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Tombol Kembali */
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: #f1f8e9;
        color: #1b5e20;
        border: 1px solid #c8e6c9;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 20px;
        transition: background 0.2s, transform 0.1s;
    }
    .btn-back:hover {
        background-color: #dcedc8;
        transform: translateX(-2px);
    }

    /* Kotak Header / Judul Lowongan */
    .job-header-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
        margin-bottom: 25px;
    }
    .job-header-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .job-title-row {
        display: flex;
        align-items: baseline;
        gap: 12px;
    }
    .job-title {
        font-size: 22px;
        font-weight: 800;
        color: #111;
    }
    .job-dept {
        font-size: 14px;
        font-weight: 600;
        color: #666;
    }
    .job-company {
        font-size: 14px;
        font-weight: 700;
        color: #222;
    }
    .job-location {
        font-size: 13px;
        color: #555;
    }

    /* Bagian Kanan Header */
    .job-header-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
    }
    .job-meta-badges {
        display: flex;
        gap: 8px;
        align-items: center;
    }
    .time-badge {
        background-color: #c8e6c9;
        color: #1b5e20;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .date-badge {
        background-color: #ffe0b2;
        color: #e65100;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .btn-apply {
        background-color: #ffe082;
        color: #5d4037;
        border: none;
        padding: 10px 22px;
        border-radius: 25px;
        font-size: 13.5px;
        font-weight: 800;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s, transform 0.1s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .btn-apply:hover {
        background-color: #ffd54f;
        transform: translateY(-1px);
    }

    /* Grid Layout Utama (Kiri: Deskripsi/Tugas/Kualifikasi, Kanan: Benefit) */
    .detail-grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        align-items: start;
    }

    .card-box {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 25px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
    }

    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: #111;
        margin-bottom: 12px;
        margin-top: 18px;
    }
    .section-title:first-child {
        margin-top: 0;
    }

    .content-text {
        font-size: 14px;
        color: #444;
        line-height: 1.6;
        margin-bottom: 15px;
    }

    /* Styling Kotak Benefit di Sebelah Kanan */
    .benefit-title {
        font-size: 18px;
        font-weight: 800;
        color: #1b5e20;
        margin-bottom: 15px;
        text-align: center;
        border-bottom: 2px solid #e8f5e9;
        padding-bottom: 10px;
    }

    @media (max-width: 900px) {
        .job-header-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .job-header-right {
            align-items: flex-start;
            width: 100%;
            justify-content: space-between;
            flex-direction: row;
        }
        .detail-grid-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="detail-container">

    <!-- HEADER KARTU LOWONGAN -->
    <div class="job-header-card">
        <div class="job-header-info">
            <div class="job-title-row">
                <span class="job-title"><?php echo htmlspecialchars($job['posisi_pekerjaan']); ?></span>
                <span class="job-dept"><?php echo htmlspecialchars($job['divisi']); ?></span>
            </div>
            <div class="job-company">PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster</div>
            <div class="job-location">📍 <?php echo htmlspecialchars($job['lokasi_pekerjaan']); ?></div>
        </div>
        <div class="job-header-right">
            <div class="job-meta-badges">
                <span class="time-badge"><?php echo htmlspecialchars($job['shift'] ?? 'Penuh Waktu'); ?></span>
                <span class="date-badge"><?php echo htmlspecialchars(date('d-M-Y', strtotime($job['tgl_dibuat']))); ?></span>
            </div>
            <!-- Tombol Kirim Lamar Sekarang menuju proses lamaran -->
            <a href="proses_lamar.php?id=<?php echo $job['id_lowongan']; ?>" class="btn-apply" onclick="return confirm('Yakin ingin mengirim lamaran untuk posisi ini?')">
                Kirim Lamar Sekarang ➔
            </a>
        </div>
    </div>

    <!-- KONTEN UTAMA (GRID 2 KOLOM) -->
    <div class="detail-grid-layout">

        <!-- KOLOM KIRI: Deskripsi, Tugas, Kualifikasi -->
        <div class="card-box">
            <div class="section-title">Deskripsi pekerjaan</div>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($job['deskripsi_pekerjaan'] ?? 'Kesempatan kerja terbuka untuk kamu yang siap berkembang di dunia F&B bareng Kebab Monster!')); ?>
            </div>

            <div class="section-title">Tugas & Tanggung Jawab</div>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($job['tugas_tanggung_jawab'])); ?>
            </div>

            <div class="section-title">Kualifikasi</div>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($job['kualifikasi'])); ?>
            </div>
        </div>

        <!-- KOLOM KANAN: Benefit -->
        <div class="card-box">
            <div class="benefit-title">Benefit</div>
            <div class="content-text">
                <?php echo nl2br(htmlspecialchars($job['benefit'])); ?>
            </div>
                  <!-- TOMBOL KEMBALI KE HALAMAN KARIR -->
                 <a href="karir.php" class="btn-back">
                 ← Kembali ke Daftar Karir
                 </a>
        </div>

    </div>

</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>