<?php
// Sertakan header admin (sudah otomatis session_start, cek role, dan koneksi $conn)
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

// Koneksi database db_kebab_monster
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn_dash = mysqli_connect($host, $user, $pass, $db);

// Inisialisasi nilai hitung ringkasan operasional
$jml_screening = 0; 
$jml_psikotes = 0; 
$jml_hasil_psikotes = 0;
$jml_interview = 0; 
$jml_keputusan = 0;

$lowongan_list = [];

if ($conn_dash) {
    // 1. Hitung statistik berdasarkan status di tabel lamaran (jika ada kolom status)
    $check_table_lamaran = mysqli_query($conn_dash, "SHOW TABLES LIKE 'lamaran'");
    if ($check_table_lamaran && mysqli_num_rows($check_table_lamaran) > 0) {
        $check_col_status = mysqli_query($conn_dash, "SHOW COLUMNS FROM lamaran LIKE 'status'");
        if ($check_col_status && mysqli_num_rows($check_col_status) > 0) {
            $q1 = mysqli_query($conn_dash, "SELECT COUNT(*) as jml FROM lamaran WHERE status LIKE '%Screening%'");
            if($q1) $jml_screening = mysqli_fetch_assoc($q1)['jml'];

            $q2 = mysqli_query($conn_dash, "SELECT COUNT(*) as jml FROM lamaran WHERE status LIKE '%Psikotes%'");
            if($q2) $jml_psikotes = mysqli_fetch_assoc($q2)['jml'];

            $q3 = mysqli_query($conn_dash, "SELECT COUNT(*) as jml FROM lamaran WHERE status LIKE '%Dinilai%' OR status LIKE '%Hasil%'");
            if($q3) $jml_hasil_psikotes = mysqli_fetch_assoc($q3)['jml'];

            $q4 = mysqli_query($conn_dash, "SELECT COUNT(*) as jml FROM lamaran WHERE status LIKE '%Interview%'");
            if($q4) $jml_interview = mysqli_fetch_assoc($q4)['jml'];

            $q5 = mysqli_query($conn_dash, "SELECT COUNT(*) as jml FROM lamaran WHERE status LIKE '%Diterima%' OR status LIKE '%Ditolak%'");
            if($q5) $jml_keputusan = mysqli_fetch_assoc($q5)['jml'];
        }
    }

    // 2. Ambil data Lowongan secara DINAMIS (Mendeteksi kolom tabel secara otomatis tanpa takut error)
    $check_table_lowongan = mysqli_query($conn_dash, "SHOW TABLES LIKE 'lowongan'");
    if ($check_table_lowongan && mysqli_num_rows($check_table_lowongan) > 0) {
        // Ambil daftar kolom yang ada di tabel lowongan
        $cols_lowongan = [];
        $res_c = mysqli_query($conn_dash, "SHOW COLUMNS FROM lowongan");
        while ($rc = mysqli_fetch_assoc($res_c)) {
            $cols_lowongan[] = $rc['Field'];
        }

        // Cari tahu nama kolom untuk judul lowongan
        $title_col = $cols_lowongan[1] ?? $cols_lowongan[0]; 
        foreach (['nama_lowongan', 'posisi', 'judul', 'nama', 'nama_posisi', 'job_title'] as $opt) {
            if (in_array($opt, $cols_lowongan)) {
                $title_col = $opt;
                break;
            }
        }

        // Ambil seluruh data lowongan
        $res_low = mysqli_query($conn_dash, "SELECT * FROM lowongan");
        if ($res_low) {
            while ($row_l = mysqli_fetch_assoc($res_low)) {
                $nama_job = $row_l[$title_col] ?? 'Staff Kebab Monster';
                
                // Hitung jumlah pendaftar untuk lowongan ini
                $pendaftar_count = 0;
                // Cek apakah tabel lamaran punya relasi id_lowongan atau posisi
                $has_id_lowongan = in_array('id_lowongan', $cols_lowongan) && isset($row_l['id_lowongan']);
                
                if ($has_id_lowongan) {
                    $id_l = $row_l['id_lowongan'];
                    $q_count = mysqli_query($conn_dash, "SELECT COUNT(*) as total FROM lamaran WHERE id_lowongan = '$id_l'");
                    if ($q_count) $pendaftar_count = mysqli_fetch_assoc($q_count)['total'];
                }

                $lowongan_list[] = [
                    'nama' => $nama_job,
                    'total' => $pendaftar_count
                ];
            }
        }
    }
}
?>

<style>
    .dashboard-wrapper {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    /* Grid Ringkasan Operasional Harian */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #1b5e20;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .summary-card .card-label {
        font-size: 12px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    .summary-card .card-value {
        font-size: 30px;
        font-weight: 900;
        color: #1b5e20;
        margin-bottom: 4px;
    }
    .summary-card .card-desc {
        font-size: 11.5px;
        color: #666;
        font-weight: 600;
    }

    /* Kotak Lowongan Aktif */
    .section-card-box {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .section-title {
        font-size: 18px;
        font-weight: 800;
        color: #004d40;
        margin-bottom: 20px;
    }
    .job-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
        font-size: 15px;
        font-weight: 600;
        color: #333;
    }
    .job-list-item:last-child {
        border-bottom: none;
    }
    .job-pendaftar {
        font-size: 14px;
        color: #1b5e20;
        font-weight: 700;
        background: #e8f5e9;
        padding: 5px 12px;
        border-radius: 20px;
    }
</style>

<div class="dashboard-wrapper">
    
    <!-- RINGKASAN OPERASIONAL HARIAN -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="card-label">Berkas Masuk (Screening)</div>
            <div class="card-value"><?php echo $jml_screening; ?></div>
            <div class="card-desc">Perlu Screening Berkas</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Jadwal Psikotes (Antre)</div>
            <div class="card-value"><?php echo $jml_psikotes; ?></div>
            <div class="card-desc">Antre Penjadwalan</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Hasil Psikotes (Dinilai)</div>
            <div class="card-value"><?php echo $jml_hasil_psikotes; ?></div>
            <div class="card-desc">Perlu Screening Berkas</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Jadwal Interview (Antre)</div>
            <div class="card-value"><?php echo $jml_interview; ?></div>
            <div class="card-desc">Siap Dijadwalkan</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Perlu Keputusan</div>
            <div class="card-value"><?php echo $jml_keputusan; ?></div>
            <div class="card-desc">Keputusan Lamaran</div>
        </div>
    </div>

    <!-- LOWONGAN AKTIF (TERHUBUNG KE DATABASE SECARA OTOMATIS) -->
    <div class="section-card-box">
        <div class="section-title">LOWONGAN AKTIF & STATISTIK PENDAFTAR</div>
        
        <?php 
        if (!empty($lowongan_list)) {
            foreach ($lowongan_list as $job) {
        ?>
        <div class="job-list-item">
            <span><?php echo htmlspecialchars($job['nama']); ?></span>
            <span class="job-pendaftar"><?php echo $job['total']; ?> Pendaftar</span>
        </div>
        <?php 
            }
        } else {
            echo "<div style='text-align: center; padding: 20px; color: #777;'>Belum ada data lowongan di tabel 'lowongan' database Anda.</div>";
        }
        ?>
    </div>

</div>

<?php 
if(isset($conn_dash)) mysqli_close($conn_dash);
?>
        </main>
    </div>
</body>
</html>