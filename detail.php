<?php
// Sertakan file header (navbar sticky otomatis aktif)
include 'includes/header.php';

// Ambil ID lowongan dari URL (contoh: detail.php?id=1)
$id_lowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Hubungkan ke Database MySQL
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data lowongan berdasarkan ID (termasuk kolom tugas & kualifikasi)
$query = "SELECT * FROM lowongan WHERE id_lowongan = $id_lowongan AND view = 'aktif'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $formatted_date = date('d-M-Y', strtotime($row['tgl_dibuat']));
} else {
    echo "<div style='text-align:center; padding:80px; font-family:sans-serif;'>
            <h2 style='color:#004d40; margin-bottom:10px;'>Lowongan Tidak Ditemukan</h2>
            <p style='color:#666; margin-bottom:20px;'>Lowongan yang Anda cari mungkin sudah ditutup atau tidak tersedia.</p>
            <a href='career.php' style='background:#ffb300; color:#111; padding:10px 20px; border-radius:6px; font-weight:bold; text-decoration:none;'>Kembali ke Halaman Karir</a>
          </div>";
    include 'includes/footer.php';
    exit();
}
mysqli_close($conn);
?>

<style>
    /* Banner Halaman Detail Lamaran */
    .detail-banner {
        background-color: #004d40;
        color: white;
        text-align: center;
        padding: 45px 20px 30px 20px;
        width: 100%;
        margin-bottom: 20px;
    }
    .detail-banner h1 {
        font-size: 30px;
        font-weight: 800;
        color: #ffffff;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }

    /* Catatan Panduan di Bawah Judul Detail */
    .banner-notice {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #e0f2f1;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 13.5px;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.5;
    }
    .banner-notice a {
        color: #ffb300;
        font-weight: 700;
        text-decoration: underline;
    }

    /* Container Utama */
    .detail-container {
        width: 90%;
        max-width: 1100px;
        margin: 30px auto 70px auto;
    }

    /* Kartu Header Posisi */
    .position-header-card {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 25px 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .position-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
    }
    .position-title-area {
        display: flex;
        align-items: baseline;
        gap: 12px;
    }
    .position-title-area h2 {
        font-size: 20px;
        font-weight: 800;
        color: #111;
    }
    .position-divisi {
        font-size: 13.5px;
        color: #666;
        font-weight: 600;
    }

    /* Badge Tanggal & Shift di Kanan Atas */
    .position-meta-badges {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 6px;
    }
    .badge-date-top {
        background-color: #fff8e1;
        color: #f57f17;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
    }
    .badge-shift-top {
        background-color: #e0f2f1;
        color: #00796b;
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
    }

    .position-company {
        font-size: 13px;
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .position-divider {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 12px 0;
    }

    .position-bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .position-location {
        font-size: 13px;
        color: #555;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Tombol Lamar Sekarang */
    .btn-apply-now {
        background-color: #ffb300;
        color: #111;
        padding: 9px 20px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 13px;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        transition: background 0.2s;
        display: inline-block;
    }
    .btn-apply-now:hover {
        background-color: #ffa000;
    }

    /* Layout 2 Kolom */
    .detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }

    /* Kolom Kiri: Deskripsi, Tugas, Kualifikasi */
    .card-left {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 35px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .card-left h3 {
        font-size: 17px;
        font-weight: 800;
        color: #004d40;
        margin-top: 25px;
        margin-bottom: 12px;
        border-bottom: 2px solid #e0f2f1;
        padding-bottom: 6px;
    }
    .card-left h3:first-child {
        margin-top: 0;
    }
    .card-left p {
        font-size: 13.5px;
        color: #444;
        line-height: 1.7;
    }

    /* Kolom Kanan: Benefit & Tombol Kembali */
    .card-right {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        height: fit-content;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .card-right h3 {
        font-size: 17px;
        font-weight: 800;
        color: #004d40;
        margin-bottom: 5px;
        border-bottom: 2px solid #e0f2f1;
        padding-bottom: 6px;
        text-align: center;
    }
    .benefit-content {
        font-size: 13.5px;
        color: #444;
        line-height: 1.7;
    }

    /* Tombol Kembali ke Menu Karir di Bawah Benefit */
    .btn-back-career {
        background-color: #f1f3f5;
        color: #333;
        padding: 11px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
        text-decoration: none;
        display: block;
        transition: background 0.2s, color 0.2s;
        border: 1px solid #dcdcdc;
        margin-top: 5px;
    }
    .btn-back-career:hover {
        background-color: #004d40;
        color: #ffffff;
        border-color: #004d40;
    }

    @media (max-width: 900px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Banner Header dengan Catatan Panduan -->
<div class="detail-banner">
    <h1>DETAIL LAMARAN</h1>
    <div class="banner-notice">
        ℹ️ Catatan: Untuk mengirimkan (submit) lamaran, Anda diwajibkan untuk <a href="login.php">Login</a> terlebih dahulu. Jika belum memiliki akun, silakan melakukan <a href="login.php">Registrasi</a> melalui halaman login.
    </div>
</div>

<div class="detail-container">

    <!-- Header Kartu Posisi -->
    <div class="position-header-card">
        <div class="position-top-row">
            <div class="position-title-area">
                <h2><?php echo htmlspecialchars($row['posisi_pekerjaan']); ?></h2>
                <span class="position-divisi"><?php echo htmlspecialchars($row['divisi']); ?></span>
            </div>
            <div class="position-meta-badges">
                <span class="badge-date-top"><?php echo $formatted_date; ?></span>
                <span class="badge-shift-top"><?php echo htmlspecialchars($row['shift']); ?></span>
            </div>
        </div>

        <div class="position-company">
            PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
        </div>

        <hr class="position-divider">

        <div class="position-bottom-row">
            <div class="position-location">
                <span>📍</span> <span><?php echo htmlspecialchars($row['lokasi_pekerjaan']); ?></span>
            </div>
        </div>
    </div>

    <!-- Layout Grid 2 Kolom Dinamis -->
    <div class="detail-grid">
        
        <!-- Kolom Kiri: Deskripsi, Tugas, & Kualifikasi (Dari Database) -->
        <div class="card-left">
            <h3>Deskripsi pekerjaan</h3>
            <p><?php echo nl2br(htmlspecialchars($row['deskripsi_pekerjaan'])); ?></p>

            <h3>Tugas & Tanggung Jawab</h3>
            <p><?php echo nl2br(htmlspecialchars($row['tugas_tanggung_jawab'])); ?></p>

            <h3>Kualifikasi</h3>
            <p><?php echo nl2br(htmlspecialchars($row['kualifikasi'])); ?></p>
        </div>

        <!-- Kolom Kanan: Benefit & Tombol Kembali di Bawahnya -->
        <div class="card-right">
            <div>
                <h3>Benefit</h3>
                <div class="benefit-content">
                    <?php echo nl2br(htmlspecialchars($row['benefit'])); ?>
                </div>
            </div>

            <!-- Tombol Kembali ke Menu Karir Dipindah ke Bawah Benefit -->
            <div>
                <a href="career.php" class="btn-back-career">
                    &larr; Kembali ke Menu Karir
                </a>
            </div>
        </div>

    </div>

</div>

<?php
// Sertakan footer
include 'includes/footer.php';
?>