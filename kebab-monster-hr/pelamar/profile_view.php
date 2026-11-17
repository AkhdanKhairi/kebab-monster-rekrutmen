<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

$query = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$data  = mysqli_fetch_assoc($query);

$_SESSION['email'] = $data['email'] ?? ''; 
$_SESSION['nama']  = $data['nama_lengkap'] ?? 'Pelamar';
mysqli_close($conn);

// --- HITUNG KELENGKAPAN DINAMIS ---
$fields_datadiri = ['nama_lengkap', 'no_telepon', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'status_nikah', 'kode_pos', 'kota', 'no_ktp', 'kewarganegaraan', 'berat_badan', 'tinggi_badan', 'alamat'];
$filled_datadiri = 0;
foreach ($fields_datadiri as $f) { if (!empty($data[$f])) $filled_datadiri++; }
$score_datadiri = round(($filled_datadiri / count($fields_datadiri)) * 100);

$fields_darurat = ['darurat_nama', 'darurat_hubungan', 'darurat_telp1', 'darurat_telp2', 'darurat_alamat'];
$filled_darurat = 0;
foreach ($fields_darurat as $f) { if (!empty($data[$f])) $filled_darurat++; }
$score_darurat = round(($filled_darurat / count($fields_darurat)) * 100);

$fields_pendidikan = ['pend_terakhir', 'pend_institusi', 'pend_jurusan', 'pend_nilai', 'pend_mulai', 'pend_selesai'];
$filled_pendidikan = 0;
foreach ($fields_pendidikan as $f) { if (!empty($data[$f])) $filled_pendidikan++; }
$score_pendidikan = round(($filled_pendidikan / count($fields_pendidikan)) * 100);

$fields_kerja = ['kerja_perusahaan', 'kerja_telp', 'kerja_mulai', 'kerja_akhir', 'kerja_jabatan_awal', 'kerja_jabatan_akhir', 'kerja_jobdesk', 'kerja_alamat', 'kerja_alasan'];
$filled_kerja = 0;
foreach ($fields_kerja as $f) { if (!empty($data[$f])) $filled_kerja++; }
$score_kerja = round(($filled_kerja / count($fields_kerja)) * 100);

$fields_magang = ['magang1_instansi', 'magang1_posisi', 'magang1_jobdesk'];
$filled_magang = 0;
foreach ($fields_magang as $f) { if (!empty($data[$f])) $filled_magang++; }
$score_magang = round(($filled_magang / count($fields_magang)) * 100);

$fields_berkas = ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto'];
$filled_berkas = 0;
foreach ($fields_berkas as $f) { if (!empty($data[$f])) $filled_berkas++; }
$score_berkas = round(($filled_berkas / count($fields_berkas)) * 100);

$total_score = round(($score_datadiri + $score_darurat + $score_pendidikan + $score_kerja + $score_magang + $score_berkas) / 6);

include '../includes/header_pelamar.php';
?>

<style>
    .profile-page-layout { max-width: 1150px; margin: 0 auto; display: grid; grid-template-columns: 280px 1fr; gap: 25px; align-items: start; }
    .profile-card-container { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 90px; }
    .profile-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .profile-avatar-wrapper { width: 75px; height: 75px; margin: 0 auto 12px auto; background: #e0f2f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #b2dfdb; overflow: hidden; }
    .card-username { font-size: 13px; font-weight: 800; color: #004d40; text-transform: uppercase; margin-bottom: 4px; line-height: 1.3; }
    .card-email { font-size: 11px; color: #666; margin-bottom: 15px; word-break: break-all; }
    .btn-pdf-page { background-color: #1b5e20; color: white; border: none; width: 100%; padding: 8px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: background 0.2s; }
    .btn-pdf-page:hover { background-color: #00332c; }

    .progress-detail-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .progress-header-title { font-size: 13px; font-weight: 800; color: #004d40; margin-bottom: 15px; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
    .progress-item { margin-bottom: 12px; }
    .progress-item:last-child { margin-bottom: 0; }
    .progress-label-wrap { display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 700; color: #444; margin-bottom: 5px; }
    .progress-bar-bg { width: 100%; height: 8px; background-color: #e0e0e0; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background-color: #1b5e20; border-radius: 4px; transition: width 0.4s ease; }

    .content-right-side { display: flex; flex-direction: column; gap: 20px; }
    .notice-banner { background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; padding: 12px 20px; border-radius: 6px; font-size: 13.5px; }
    .form-section-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .section-header-bar { background-color: #004d40; color: white; padding: 12px 20px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .section-body { padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .data-field-group { display: flex; flex-direction: column; gap: 4px; }
    .data-field-group.full-width { grid-column: span 2; }
    .data-field-group label { font-size: 12px; font-weight: 700; color: #333; }
    .data-value-box { background-color: #fcfcfc; border: 1px solid #dcdcdc; padding: 9px 12px; border-radius: 6px; font-size: 13px; color: #444; min-height: 18px; }
    .sub-section-title { grid-column: span 2; font-size: 13.5px; font-weight: 800; color: #004d40; border-bottom: 2px solid #b2dfdb; padding-bottom: 4px; margin-top: 10px; }

    @media (max-width: 950px) {
        .profile-page-layout { grid-template-columns: 1fr; }
        .profile-card-container { position: static; }
        .section-body { grid-template-columns: 1fr; }
        .data-field-group.full-width { grid-column: span 1; }
        .sub-section-title { grid-column: span 1; }
    }
</style>

<div class="profile-page-layout">
    <div class="profile-card-container">
        <div class="profile-card-box">
            <div class="profile-avatar-wrapper">
                <?php if (!empty($data['pas_foto']) && file_exists('../uploads/' . $data['pas_foto'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($data['pas_foto']); ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <span style="font-size: 40px;">👤</span>
                <?php endif; ?>
            </div>
            <div class="card-username"><?php echo htmlspecialchars($data['nama_lengkap'] ?? 'Nama Pelamar'); ?></div>
            <div class="card-email"><?php echo htmlspecialchars($data['email'] ?? '-'); ?></div>
            <a href="generate_cv.php" target="_blank" class="btn-pdf-page"><span>📄</span> Jadikan PDF / Cetak CV</a>
        </div>

        <div class="progress-detail-box">
            <div class="progress-header-title">Status Kelengkapan</div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Data Diri</span><span><?php echo $score_datadiri; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_datadiri; ?>%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Kontak Darurat</span><span><?php echo $score_darurat; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_darurat; ?>%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Pendidikan</span><span><?php echo $score_pendidikan; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_pendidikan; ?>%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Pengalaman Kerja</span><span><?php echo $score_kerja; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_kerja; ?>%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Internship / Magang</span><span><?php echo $score_magang; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_magang; ?>%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Upload Berkas</span><span><?php echo $score_berkas; ?>%</span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: <?php echo $score_berkas; ?>%;"></div></div>
            </div>
        </div>
    </div>

    <div class="content-right-side">
        <div class="notice-banner">
            <strong>Note*</strong><br>Harap isi <strong>Data Diri</strong> terlebih dahulu pada menu <strong>"Update Profile"</strong> sebelum mengirim lamaran Anda.
        </div>

        <!-- 1. DATA DIRI -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>👤</span> Data Diri Lengkap</div>
            <div class="section-body">
                <div class="data-field-group"><label>Nama Lengkap *</label><div class="data-value-box"><?php echo htmlspecialchars($data['nama_lengkap'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No Telepon *</label><div class="data-value-box"><?php echo htmlspecialchars($data['no_telepon'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jenis Kelamin *</label><div class="data-value-box"><?php echo htmlspecialchars($data['jenis_kelamin'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Tempat Lahir *</label><div class="data-value-box"><?php echo htmlspecialchars($data['tempat_lahir'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Tanggal Lahir *</label><div class="data-value-box"><?php echo htmlspecialchars($data['tanggal_lahir'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Agama *</label><div class="data-value-box"><?php echo htmlspecialchars($data['agama'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Status Nikah *</label><div class="data-value-box"><?php echo htmlspecialchars($data['status_nikah'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Kode Pos *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kode_pos'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Kota *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kota'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No KTP *</label><div class="data-value-box"><?php echo htmlspecialchars($data['no_ktp'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Kewarganegaraan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jumlah Anak</label><div class="data-value-box"><?php echo htmlspecialchars($data['jumlah_anak'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Berat Badan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['berat_badan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Tinggi Badan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['tinggi_badan'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Alamat *</label><div class="data-value-box"><?php echo htmlspecialchars($data['alamat'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 2. KONTAK DARURAT -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>🚨</span> Kontak Darurat Keluarga</div>
            <div class="section-body">
                <div class="data-field-group"><label>Nama Keluarga *</label><div class="data-value-box"><?php echo htmlspecialchars($data['darurat_nama'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Hubungan Keluarga *</label><div class="data-value-box"><?php echo htmlspecialchars($data['darurat_hubungan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No Telepon 1 *</label><div class="data-value-box"><?php echo htmlspecialchars($data['darurat_telp1'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No Telepon 2</label><div class="data-value-box"><?php echo htmlspecialchars($data['darurat_telp2'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Alamat Keluarga *</label><div class="data-value-box"><?php echo htmlspecialchars($data['darurat_alamat'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 3. PENDIDIKAN -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>🎓</span> Latar Belakang Pendidikan</div>
            <div class="section-body">
                <div class="data-field-group"><label>Pendidikan Terakhir *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_terakhir'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Nama Institusi / Universitas *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_institusi'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jurusan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_jurusan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Nilai / IPK *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_nilai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Mulai Tanggal *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_mulai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Selesai Tanggal *</label><div class="data-value-box"><?php echo htmlspecialchars($data['pend_selesai'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 4. PENGALAMAN KERJA (2 Kolom + Jobdesk) -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>💼</span> Pengalaman Kerja</div>
            <div class="section-body">
                <div class="sub-section-title">Pengalaman Kerja 1 (Terakhir)</div>
                <div class="data-field-group"><label>Nama Perusahaan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_perusahaan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No Telepon Perusahaan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_telp'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Mulai Kerja *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_mulai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Akhir Kerja *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jabatan Awal *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_jabatan_awal'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jabatan Akhir *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_jabatan_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Deskripsi Pekerjaan *</label><div class="data-value-box"><?php echo nl2br(htmlspecialchars($data['kerja_jobdesk'] ?? '-')); ?></div></div>
                <div class="data-field-group full-width"><label>Alamat Perusahaan *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_alamat'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Alasan Berhenti *</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja_alasan'] ?? '-'); ?></div></div>

                <div class="sub-section-title">Pengalaman Kerja 2 (Sebelumnya)</div>
                <div class="data-field-group"><label>Nama Perusahaan</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_perusahaan'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>No Telepon Perusahaan</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_telp'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Mulai Kerja</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_mulai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Akhir Kerja</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jabatan Awal</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_jabatan_awal'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Jabatan Akhir</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_jabatan_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Deskripsi Pekerjaan</label><div class="data-value-box"><?php echo nl2br(htmlspecialchars($data['kerja2_jobdesk'] ?? '-')); ?></div></div>
                <div class="data-field-group full-width"><label>Alamat Perusahaan</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_alamat'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Alasan Berhenti</label><div class="data-value-box"><?php echo htmlspecialchars($data['kerja2_alasan'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 5. INTERNSHIP / MAGANG -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>📋</span> Pengalaman Internship / Magang</div>
            <div class="section-body">
                <div class="sub-section-title">Magang 1</div>
                <div class="data-field-group"><label>Nama Instansi</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang1_instansi'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Posisi / Peran</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang1_posisi'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Mulai Magang</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang1_mulai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Akhir Magang</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang1_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Kegiatan</label><div class="data-value-box"><?php echo nl2br(htmlspecialchars($data['magang1_jobdesk'] ?? '-')); ?></div></div>

                <div class="sub-section-title">Magang 2</div>
                <div class="data-field-group"><label>Nama Instansi</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang2_instansi'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Posisi / Peran</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang2_posisi'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Mulai Magang</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang2_mulai'] ?? '-'); ?></div></div>
                <div class="data-field-group"><label>Akhir Magang</label><div class="data-value-box"><?php echo htmlspecialchars($data['magang2_akhir'] ?? '-'); ?></div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Kegiatan</label><div class="data-value-box"><?php echo nl2br(htmlspecialchars($data['magang2_jobdesk'] ?? '-')); ?></div></div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer_pelamar.php'; ?>