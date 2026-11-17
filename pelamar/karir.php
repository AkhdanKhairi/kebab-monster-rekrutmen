<?php
session_start();

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data pencarian jika ada input dari user
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query mengambil data lowongan disesuaikan dengan kolom tabel Anda
$query_sql = "SELECT * FROM lowongan";
if (!empty($keyword)) {
    $query_sql .= " WHERE posisi_pekerjaan LIKE '%$keyword%' OR divisi LIKE '%$keyword%' OR lokasi_pekerjaan LIKE '%$keyword%'";
}
$result_lowongan = mysqli_query($conn, $query_sql);

include '../includes/header_pelamar.php';
?>

<style>
    .career-page-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Bagian Header Teks */
    .career-hero {
        text-align: center;
        margin-bottom: 35px;
    }
    .career-hero h1 {
        font-size: 32px;
        font-weight: 800;
        color: #222;
        margin-bottom: 12px;
    }
    .career-hero h1 span {
        color: #e65100; /* Warna oranye khas Kebab Monster */
    }
    .career-hero p {
        font-size: 14.5px;
        color: #555;
        max-width: 750px;
        margin: 0 auto 20px auto;
        line-height: 1.5;
    }

    /* Badge Info (Lokasi & Tanpa Biaya) */
    .badge-container {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-bottom: 30px;
    }
    .info-badge {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Kotak Pencarian */
    .search-bar-form {
        max-width: 750px;
        margin: 0 auto 40px auto;
        display: flex;
        background: #ffffff;
        border: 2px solid #1b5e20;
        border-radius: 35px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        padding: 4px;
    }
    .search-input {
        flex: 1;
        border: none;
        padding: 12px 20px;
        font-size: 14px;
        outline: none;
        color: #333;
    }
    .search-btn {
        background-color: #1b5e20;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
    }
    .search-btn:hover { background-color: #00332c; }

    /* Kartu Lowongan Pekerjaan */
    .job-card-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .job-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 22px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    }
    .job-info {
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
        font-size: 18px;
        font-weight: 800;
        color: #111;
        letter-spacing: 0.3px;
    }
    .job-dept {
        font-size: 13px;
        font-weight: 600;
        color: #666;
    }
    .job-company {
        font-size: 13px;
        font-weight: 700;
        color: #222;
    }
    .job-location {
        font-size: 12.5px;
        color: #555;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Bagian Kanan Kartu */
    .job-card-right {
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
        font-size: 11.5px;
        font-weight: 700;
    }
    .date-badge {
        background-color: #ffe0b2;
        color: #e65100;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .btn-detail {
        background-color: #ffe082;
        color: #5d4037;
        border: none;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }
    .btn-detail:hover {
        background-color: #ffd54f;
    }
    .no-data {
        text-align: center;
        padding: 30px;
        color: #666;
        font-style: italic;
    }

    @media (max-width: 800px) {
        .job-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .job-card-right {
            align-items: flex-start;
            width: 100%;
            justify-content: space-between;
            flex-direction: row;
        }
    }
</style>

<div class="career-page-container">

    <!-- HERO SECTION -->
    <div class="career-hero">
        <h1>Karier di <span>Kebab Monster</span></h1>
        <p>Di Kebab Monster, kami percaya setiap individu punya potensi luar biasa untuk berkembang bersama. Temukan posisi yang cocok dan raih karier cemerlang bersama kami.</p>
        
        <div class="badge-container">
            <div class="info-badge">📍 Bogor, Jawa Barat</div>
            <div class="info-badge">🛡️ Tanpa Biaya Rekrutmen</div>
        </div>
    </div>

    <!-- PENCARIAN -->
    <form action="" method="GET" class="search-bar-form">
        <input type="text" name="search" class="search-input" placeholder="cari posisi pendaftaran..." value="<?php echo htmlspecialchars($keyword); ?>">
        <button type="submit" class="search-btn">Cari</button>
    </form>

    <!-- DAFTAR LOWONGAN (Dinamis Sesuai Kolom Database Anda) -->
    <div class="job-card-list">
        <?php if (mysqli_num_rows($result_lowongan) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result_lowongan)): ?>
                <div class="job-card">
                    <div class="job-info">
                        <div class="job-title-row">
                            <span class="job-title"><?php echo htmlspecialchars($row['posisi_pekerjaan']); ?></span>
                            <span class="job-dept"><?php echo htmlspecialchars($row['divisi']); ?></span>
                        </div>
                        <div class="job-company">PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster</div>
                        <div class="job-location">📍 <?php echo htmlspecialchars($row['lokasi_pekerjaan']); ?></div>
                    </div>
<div class="job-card-right">
    <div class="job-meta-badges">
        <span class="time-badge"><?php echo htmlspecialchars($row['shift'] ?? 'Penuh Waktu'); ?></span>
        <!-- Sesuaikan tgl_buat dengan database -->
        <span class="date-badge"><?php echo htmlspecialchars(date('d-M-Y', strtotime($row['tgl_dibuat']))); ?></span>
    </div>
    <!-- Ubah tujuannya menjadi detail_karir.php -->
    <a href="detail_karir.php?id=<?php echo $row['id_lowongan']; ?>" class="btn-detail">Liat Detail Lamaran ➔</a>
</div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">Belum ada lowongan pekerjaan yang tersedia atau kata kunci pencarian tidak ditemukan.</div>
        <?php endif; ?>
    </div>

</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>