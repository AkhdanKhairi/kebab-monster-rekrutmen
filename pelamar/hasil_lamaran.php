<?php
session_start();



if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

// Koneksi Database
$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_kebab_monster';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 1. Ambil data lamaran terbaru
$query_lamaran = mysqli_query($conn, "
    SELECT l.*, lw.posisi_pekerjaan 
    FROM lamaran l 
    JOIN lowongan lw ON l.id_lowongan = lw.id_lowongan 
    WHERE l.id_pelamar = $id_pelamar 
    ORDER BY l.id_lamaran DESC LIMIT 1
");
$data_lamaran = mysqli_fetch_assoc($query_lamaran);

// 2. Ambil data profil pelamar
$query_pelamar = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$profile = mysqli_fetch_assoc($query_pelamar);

$posisi_dilamar = $data_lamaran['posisi_pekerjaan'] ?? 'Belum Melamar Posisi Apapun';
$tgl_daftar     = isset($data_lamaran['tgl_melamar']) ? date('d-m-Y', strtotime($data_lamaran['tgl_melamar'])) : '-';
$status_db      = $data_lamaran['status_lamaran'] ?? 'Belum Melamar';

// Tentukan Styling & Pesan Berdasarkan Hasil Akhir
$badge_status = "MENUNGGU PROSES";
$badge_class  = "badge-pending";
$judul_hasil  = "Evaluasi Seleksi Sedang Berlangsung";
$pesan_hasil  = "Seluruh tahapan seleksi Anda sedang ditinjau oleh tim manajemen dan HR Kebab Monster. Mohon pantau halaman ini secara berkala untuk mengetahui pengumuman hasil akhir.";

if ($status_db == 'Diterima' || $status_db == 'Selesai') {
    $badge_status = "SELAMAT! ANDA DITERIMA";
    $badge_class  = "badge-success";
    $judul_hasil  = "Penawaran Kerja (Offer Letter)";
    $pesan_hasil  = "Berdasarkan hasil keseluruhan seleksi mulai dari seleksi berkas, psikotes, hingga wawancara, kami dengan senang hati mengumumkan bahwa Anda dinyatakan **LULUS dan DITERIMA** di Kebab Monster untuk posisi <b>" . htmlspecialchars($posisi_dilamar) . "</b>.<br><br>Tim HR kami akan segera mengirimkan email resmi berisi rincian kontrak kerja, jadwal onboarding, serta instruksi penempatan lebih lanjut.";
} elseif ($status_db == 'Ditolak' || $status_db == 'Tidak Lolos') {
    $badge_status = "MOHON MAAF, BELUM BERUNTUNG";
    $badge_class  = "badge-danger";
    $judul_hasil  = "Hasil Seleksi Rekrutmen";
    $pesan_hasil  = "Terima kasih banyak atas antusiasme dan waktu yang telah Anda luangkan untuk mengikuti proses seleksi di Kebab Monster.<br><br>Setelah melalui pertimbangan yang matang dari tim penilai, dengan berat hati kami menyampaikan bahwa saat ini kami belum dapat melanjutkan proses rekrutmen Anda untuk posisi <b>" . htmlspecialchars($posisi_dilamar) . "</b>. Kami mendoakan kesuksesan yang terbaik untuk karier Anda di masa depan.";
}

include '../includes/header_pelamar.php';
?>

<style>
    .info-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }

    .profile-sidebar-card {
        width: 320px;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 25px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        height: fit-content;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: #ffe0b2;
        border-radius: 50%;
        margin: 0 auto 15px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        overflow: hidden;
        border: 2px solid #1b5e20;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 2px; }
    .profile-phone { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; }
    
    .lamaran-info-box { text-align: left; }
    .lamaran-info-box h4 { font-size: 14px; font-weight: 800; color: #1b5e20; margin-bottom: 10px; }
    .info-row { font-size: 12px; margin-bottom: 10px; color: #444; }
    .info-row span { font-weight: 700; color: #111; display: block; margin-top: 2px; }

    .result-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 35px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
    }
    .result-title { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 25px; }

    /* Badge Status Akhir */
    .status-badge-container {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 25px;
        letter-spacing: 0.5px;
    }
    .badge-success { background-color: #c8e6c9; color: #1b5e20; border: 1.5px solid #2e7d32; }
    .badge-danger { background-color: #ffcdd2; color: #b71c1c; border: 1.5px solid #c62828; }
    .badge-pending { background-color: #fff9c4; color: #f57f17; border: 1.5px solid #fbc02d; }

    /* Kartu Pengumuman Hasil */
    .result-card-box {
        background-color: #f9fbe7;
        border: 1.5px solid #c5e1a5;
        border-radius: 12px;
        padding: 30px;
        text-align: left;
    }
    .result-card-box h3 { font-size: 18px; font-weight: 800; color: #1b5e20; margin-bottom: 12px; }
    .result-card-box p { font-size: 14px; color: #444; line-height: 1.7; margin: 0; }

    .btn-action-container {
        margin-top: 25px;
        text-align: center;
    }
    .btn-back-career {
        display: inline-block;
        background-color: #1b5e20;
        color: #fff;
        padding: 10px 24px;
        border-radius: 25px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-back-career:hover {
        background-color: #2e7d32;
    }
</style>

<div class="info-page-container">

    <!-- SIDEBAR PROFIL -->
    <div class="profile-sidebar-card">
        <div class="profile-avatar">
            <?php if (!empty($profile['pas_foto'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile['pas_foto']); ?>" alt="Foto Profil">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($profile['nama_lengkap'] ?? 'Nama Pelamar'); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($profile['email'] ?? '-'); ?></div>
        <div class="profile-phone"><?php echo htmlspecialchars($profile['no_telepon'] ?? '-'); ?></div>

        <div class="lamaran-info-box">
            <h4>LAMARAN</h4>
            <div class="info-row">Posisi Dilamar: <span><?php echo htmlspecialchars($posisi_dilamar); ?></span></div>
            <div class="info-row">Tanggal Daftar: <span><?php echo $tgl_daftar; ?></span></div>
            <div class="info-row">Status Tahap: <span><?php echo htmlspecialchars($status_db); ?></span></div>
        </div>
    </div>

    <!-- KONTEN UTAMA HASIL LAMARAN -->
    <div class="result-main-content">
        <h2 class="result-title">PENGUMUMAN HASIL AKHIR SELEKSI</h2>

        <!-- Badge Indikator Status -->
        <div class="status-badge-container <?php echo $badge_class; ?>">
            <?php echo $badge_status; ?>
        </div>

        <!-- Kotak Informasi Hasil -->
        <div class="result-card-box">
            <h3><?php echo $judul_hasil; ?></h3>
            <p><?php echo $pesan_hasil; ?></p>
        </div>

        <div class="btn-action-container">
            <a href="karir.php" class="btn-back-career">← Kembali ke Menu Karir</a>
        </div>
    </div>

</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>