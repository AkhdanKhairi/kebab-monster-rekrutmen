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

// 3. CEK KELENGKAPAN DATA DIRI SECARA KETAT BERDASARKAN TABEL PELAMAR
// Profil dianggap lengkap HANYA JIKA kolom-kolom penting berikut terisi semua di database
$is_profil_lengkap = false;
if ($profile) {
    if (!empty($profile['nama_lengkap']) && 
        !empty($profile['email']) && 
        !empty($profile['no_telepon']) && 
        !empty($profile['jenis_kelamin']) && 
        !empty($profile['tanggal_lahir']) && 
        !empty($profile['alamat']) && 
        !empty($profile['no_ktp']) && 
        !empty($profile['pas_foto'])) {
        $is_profil_lengkap = true;
    }
}

// Atur Variabel Tampilan Berdasarkan Kondisi
if ($data_lamaran) {
    $posisi_dilamar = $data_lamaran['posisi_pekerjaan'];
    $tgl_daftar     = date('d-m-Y', strtotime($data_lamaran['tgl_melamar']));
    $status_db      = $data_lamaran['status_lamaran'] ?? 'Pending';
    
    $tgl_verifikasi = isset($data_lamaran['tgl_verifikasi']) ? date('d-m-Y', strtotime($data_lamaran['tgl_verifikasi'])) : $tgl_daftar;
    $tgl_screening  = isset($data_lamaran['tgl_screening']) ? date('d-m-Y', strtotime($data_lamaran['tgl_screening'])) : 'Menunggu Jadwal';
    $tgl_psikotes   = isset($data_lamaran['tgl_psikotes']) ? date('d-m-Y', strtotime($data_lamaran['tgl_psikotes'])) : 'Menunggu Jadwal';
    $tgl_interview  = isset($data_lamaran['tgl_interview']) ? date('d-m-Y', strtotime($data_lamaran['tgl_interview'])) : 'Menunggu Jadwal';
    $tgl_hasil      = isset($data_lamaran['tgl_hasil']) ? date('d-m-Y', strtotime($data_lamaran['tgl_hasil'])) : 'Menunggu Jadwal';

    // Jika sudah melamar TAPI profil belum lengkap, status berkas jadi Pending
    if (!$is_profil_lengkap) {
        $status_berkas_teks = 'Belum Lengkap (Pending)';
    } else {
        $status_berkas_teks = 'Berkas Sudah Terverifikasi (' . $tgl_verifikasi . ')';
    }

} else {
    $posisi_dilamar     = 'POSISI DILAMAR';
    $tgl_daftar         = '-';
    $status_berkas_teks = 'Belum Melamar';
    $tgl_screening      = 'Menunggu Jadwal';
    $tgl_psikotes       = 'Menunggu Jadwal';
    $tgl_interview      = 'Menunggu Jadwal';
    $tgl_hasil          = 'Menunggu Jadwal';
    $status_db          = 'Belum Melamar';
}

// Inisialisasi Tampilan Stepper & Detail Keterangan
$status_tahap1 = 'gray';
$status_tahap2 = 'gray';
$status_tahap3 = 'gray';
$status_tahap4 = 'gray';
$status_tahap5 = 'gray';

$judul_detail = "INFORMASI STATUS LAMARAN";
$teks_detail  = "Anda belum mengirimkan lamaran ke posisi apa pun. Silakan buka menu 'Karir' untuk memilih lowongan dan mengirimkan lamaran Anda.";

// LOGIKA UTAMA: JIKA BELUM MELAMAR ATAU PROFIL BELUM LENGKAP
if ($status_db == 'Belum Melamar') {
    // Semua abu-abu
} 
elseif ($data_lamaran && !$is_profil_lengkap) {
    // KUNCI UTAMA: Jika sudah melamar tapi data profil ada yang kosong -> TAHAP 1 JADI KUNING (PENDING)
    $status_tahap1 = 'yellow'; 
    $judul_detail  = "PERHATIAN: LENGKAPI DATA DIRI ANDA";
    $teks_detail   = "Formulir pendaftaran Anda sudah masuk ke sistem, namun <b>data diri atau profil akun Anda belum lengkap</b> di database. Sistem menahan proses verifikasi ini. Mohon segera melengkapi informasi profil Anda pada menu 'Profil' agar status berubah menjadi hijau dan dapat diproses ke tahap selanjutnya.";
}
elseif ($status_db == 'Pending' || $status_db == 'Aplikasi Diterima') {
    // Baru jadi HIJAU jika sudah melamar DAN profil sudah lengkap
    $status_tahap1 = 'green'; 
    $status_tahap2 = 'yellow'; 
    $judul_detail  = "DETAIL TAHAP: 1. APLIKASI DITERIMA";
    $teks_detail   = "Selamat! Formulir pendaftaran dan berkas lamaran Anda telah berhasil masuk dan tercatat di dalam sistem database rekrutmen Kebab Monster untuk diverifikasi secara administratif.<br><br>Saat ini lamaran Anda sedang berada dalam status menunggu pengecekan berkas awal oleh tim HR. Mohon pastikan nomor telepon dan alamat email Anda selalu aktif dan rutin diperiksa agar Anda tidak ketinggalan informasi untuk tahapan berikutnya.";
} 
elseif ($status_db == 'Screening') {
    $status_tahap1 = 'green';
    $status_tahap2 = 'yellow';
    $judul_detail  = "DETAIL TAHAP: 2. SCREENING CV";
    $teks_detail   = "Tahap <i>Screening CV</i> adalah proses di mana tim HR dan manajemen Kebab Monster meninjau secara mendalam dan teliti riwayat pendidikan, latar belakang pengalaman kerja, serta keahlian yang tertera di dalam CV Anda guna disesuaikan dengan posisi yang dilamar.<br><br>Saat ini lamaran Anda sedang dalam antrean peninjauan oleh tim terkait, yang umumnya memakan waktu sekitar 1 hingga 3 hari kerja. Mohon bersabar menunggu hingga status Anda diperbarui secara berkala melalui sistem ini.";
} 
elseif ($status_db == 'Psikotes') {
    $status_tahap1 = 'green';
    $status_tahap2 = 'green';
    $status_tahap3 = 'yellow';
    $judul_detail  = "DETAIL TAHAP: 3. PSIKOTES ONLINE";
    $teks_detail   = "<i>Psikotes Online</i> adalah tahapan uji kompetensi kepribadian dan kemampuan dasar guna mengukur kesesuaian karakter serta potensi kerja Anda dengan standar perusahaan.<br><br>Jika Anda belum mengisi atau mengerjakan tes pada halaman \"Psikotes\", harap segera mengakses dan menyelesaikannya. Bagi yang sudah selesai mengerjakan, saat ini lembar jawaban Anda sedang dalam proses pemeriksaan oleh tim HR, jadi mohon tunggu hasilnya.";
} 
elseif ($status_db == 'Interview') {
    $status_tahap1 = 'green';
    $status_tahap2 = 'green';
    $status_tahap3 = 'green';
    $status_tahap4 = 'yellow';
    $judul_detail  = "DETAIL TAHAP: 4. INTERVIEW HR";
    $teks_detail   = "<i>Interview HR</i> adalah sesi wawancara awal bersama tim Human Resources Kebab Monster, baik secara daring maupun tatap muka, untuk membahas latar belakang profesional, motivasi kerja, kecocokan budaya, serta ekspektasi gaji Anda.<br><br>Saat ini status Anda sedang menunggu penjadwalan wawancara dari tim kami. Undangan resmi yang memuat jadwal dan tautan pertemuan akan segera dikirimkan langsung melalui email Anda jika giliran Anda telah tiba.";
} 
elseif ($status_db == 'Diterima' || $status_db == 'Selesai') {
    $status_tahap1 = 'green';
    $status_tahap2 = 'green';
    $status_tahap3 = 'green';
    $status_tahap4 = 'green';
    $status_tahap5 = 'green';
    $judul_detail  = "DETAIL TAHAP: 5. PENGUMUMAN AKHIR";
    $teks_detail   = "Pengumuman Akhir merupakan babak penutup dari seluruh rangkaian proses seleksi, di mana tim manajemen dan HR telah merampungkan penilaian keseluruhan mulai dari seleksi berkas, psikotes, hingga hasil wawancara.<br><br>Saat ini hasil akhir evaluasi Anda sedang diproses oleh tim penilai. Pengumuman resmi akan segera dipublikasikan melalui sistem dan dikirimkan ke email Anda, lengkap beserta informasi penawaran kerja jika Anda dinyatakan lulus.";
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
    .progress-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .progress-title { text-align: center; font-size: 22px; font-weight: 800; color: #111; margin-bottom: 30px; }
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    .step-item { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; }
    .step-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        transition: all 0.3s;
    }
    .step-circle.gray { background-color: #e0e0e0; color: #757575; border: 2px solid #bdbdbd; }
    .step-circle.yellow { background-color: #fff176; color: #f57f17; border: 3px solid #fbc02d; box-shadow: 0 0 10px rgba(253, 216, 53, 0.5); }
    .step-circle.green { background-color: #2e7d32; color: #ffffff; border: 3px solid #1b5e20; }
    .step-label { font-size: 12px; font-weight: 700; color: #333; margin-top: 8px; text-align: center; }
    .step-line { position: absolute; top: 30px; left: 50px; right: 50px; height: 4px; background-color: #e0e0e0; z-index: 1; }
    .detail-tahap-box {
        background-color: #fff9c4;
        border: 1px solid #fbc02d;
        border-radius: 12px;
        padding: 20px;
    }
    .detail-tahap-box h3 { font-size: 15px; font-weight: 800; color: #f57f17; margin-bottom: 10px; }
    .detail-tahap-box p { font-size: 13.5px; color: #444; line-height: 1.6; margin: 0; }
</style>

<div class="info-page-container">
    <div class="profile-sidebar-card">
        <div class="profile-avatar">
            <?php if (!empty($profile['pas_foto'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile['pas_foto']); ?>" alt="Foto Profil">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($profile['nama_lengkap'] ?? 'Nama Lengkap Pelamar'); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($profile['email'] ?? 'alamatemailpelamar@gmail.com'); ?></div>
        <div class="profile-phone"><?php echo htmlspecialchars($profile['no_telepon'] ?? '001040804'); ?></div>

        <div class="lamaran-info-box">
            <h4>LAMARAN</h4>
            <div class="info-row">Posisi Dilamar: <span><?php echo htmlspecialchars($posisi_dilamar); ?></span></div>
            <div class="info-row">Tanggal Daftar: <span><?php echo $tgl_daftar; ?></span></div>
            <div class="info-row">Status Berkas (tanggal verifikasi): <span><?php echo $status_berkas_teks; ?></span></div>
            <div class="info-row">Screening CV (tanggal update): <span><?php echo $tgl_screening; ?></span></div>
            <div class="info-row">Status Psikotes: <span><?php echo $tgl_psikotes; ?></span></div>
            <div class="info-row">Status interview: <span><?php echo $tgl_interview; ?></span></div>
            <div class="info-row">Status hasil penerimaan: <span><?php echo $tgl_hasil; ?></span></div>
        </div>
    </div>

    <div class="progress-main-content">
        <h2 class="progress-title">STATUS LAMARAN ANDA</h2>
        <div style="position: relative;">
            <div class="step-line"></div>
            <div class="stepper-container">
                <div class="step-item">
                    <div class="step-circle <?php echo $status_tahap1; ?>">✓</div>
                    <div class="step-label">Aplikasi Diterima</div>
                </div>
                <div class="step-item">
                    <div class="step-circle <?php echo $status_tahap2; ?>">📄</div>
                    <div class="step-label">Screening CV</div>
                </div>
                <div class="step-item">
                    <div class="step-circle <?php echo $status_tahap3; ?>">📝</div>
                    <div class="step-label">Psikotes</div>
                </div>
                <div class="step-item">
                    <div class="step-circle <?php echo $status_tahap4; ?>">👥</div>
                    <div class="step-label">Interview</div>
                </div>
                <div class="step-item">
                    <div class="step-circle <?php echo $status_tahap5; ?>">A+</div>
                    <div class="step-label">Interview</div>
                </div>
            </div>
        </div>

        <div class="detail-tahap-box">
            <h3><?php echo $judul_detail; ?></h3>
            <p><?php echo $teks_detail; ?></p>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>