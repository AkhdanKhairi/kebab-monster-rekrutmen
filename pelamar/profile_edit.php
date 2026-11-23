<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

// Daftar Opsi Dropdown
$opsi_jekel      = ['Laki-laki', 'Perempuan'];
$opsi_agama      = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
$opsi_nikah      = ['Belum Menikah', 'Menikah', 'Cerai Hidup', 'Cerai Mati'];
$opsi_pendidikan = ['SD', 'SMP', 'SMA / SMK', 'D1', 'D2', 'D3', 'D4 / S1', 'S2', 'S3'];
$opsi_hubungan   = ['Orang Tua (Ayah/Ibu)', 'Suami / Istri', 'Saudara Kandung', 'Paman / Bibi', 'Kerabat / Lainnya'];

$opsi_kota = [
    'Kota Bogor', 'Kabupaten Bogor', 'Kota Depok', 'Kota Tangerang',
    'Kota Tangerang Selatan', 'Kabupaten Tangerang', 'Kota Bekasi',
    'Kabupaten Bekasi', 'Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat',
    'Jakarta Pusat', 'Jakarta Utara', 'Kepulauan Seribu', 'Kota Bandung',
    'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kota Cimahi',
    'Kabupaten Sumedang'
];

$opsi_kecamatan = [
    'Andir', 'Antapani', 'Arcamanik', 'Astanaanyar', 'Babakan Ciparay', 'Bandung Kidul',
    'Bandung Kulon', 'Bandung Wetan', 'Batununggal', 'Bojongloa Kaler', 'Bojongloa Kidul', 'Buahbatu',
    'Cibeunying Kaler', 'Cibeunying Kidul', 'Cibiru', 'Cicendo', 'Cidadap', 'Cinambo',
    'Coblong', 'Gedebage', 'Kiaracondong', 'Lengkong', 'Mandalajati', 'Panyileukan',
    'Rancasari', 'Regol', 'Sukajadi', 'Sukasari', 'Sumur Bandung', 'Ujungberung',
    'Arjasari', 'Baleendah', 'Banjaran', 'Bojongsoang', 'Cangkuang', 'Cicalengka',
    'Cikancung', 'Cilengkrang', 'Cileunyi', 'Cimaung', 'Cimenyan', 'Ciparay',
    'Ciwidey', 'Dayeuhkolot', 'Ibun', 'Katapang', 'Kertasari', 'Kutawaringin',
    'Majalaya', 'Margaasih', 'Margahayu', 'Nagreg', 'Pacet', 'Pameungpeuk',
    'Pangalengan', 'Paseh', 'Pasirjambu', 'Rancabali', 'Rancaekek', 'Solokanjeruk',
    'Soreang', 'Batujajar', 'Cihampelas', 'Cikalongwetan', 'Cililin', 'Cipatat', 'Cipeundeuy',
    'Cipongkor', 'Cisarua', 'Gununghalu', 'Lembang', 'Ngamprah', 'Padalarang',
    'Parongpong', 'Rongga', 'Saguling', 'Sindangkerta', 'Cimahi Selatan', 'Cimahi Tengah', 'Cimahi Utara',
    'Buahdua', 'Cibugel', 'Cimalaka', 'Cimanggung', 'Cisarua', 'Cisitu',
    'Conggeang', 'Darmaraja', 'Ganeas', 'Jatigede', 'Jatinangor', 'Jatinunggal',
    'Pamulihan', 'Paseh', 'Rancakalong', 'Situraja', 'Sukasari', 'Sumedang Selatan',
    'Sumedang Utara', 'Surian', 'Tanjungkerta', 'Tanjungmedar', 'Tanjungsari', 'Tomo',
    'Ujungjaya', 'Wado', 'Kemang', 'Parung', 'Ciampea', 'Leuwiliang', 'Ciawi',
    'Cisarua', 'Megamendung', 'Caringin', 'Cijeruk', 'Tamansari', 'Tenjolaya',
    'Cibungbulang', 'Pamijahan', 'Rumpin', 'Gunung Sindur', 'Ciseeng', 'Babakan Madang', 'Sukaraja', 'Bogor Barat', 'Bogor Selatan', 'Bogor Tengah', 'Bogor Timur', 'Bogor Utara', 'Tanah Sareal',
    'Babakan Madang', 'Bojonggede', 'Caringin', 'Cariu', 'Ciampea', 'Ciawi',
    'Cibinong', 'Cibungbulang', 'Cigombong', 'Cigudeg', 'Cijeruk', 'Cileungsi',
    'Ciomas', 'Cisarua', 'Ciseeng', 'Citeureup', 'Dramaga', 'Gunung Putri',
    'Gunung Sindur', 'Jasinga', 'Jonggol', 'Kemang', 'Klapanunggal', 'Leuwiliang',
    'Leuwisadeng', 'Megamendung', 'Nanggung', 'Pamijahan', 'Parung', 'Parung Panjang',
    'Ranca Bungur', 'Rumpin', 'Sukajaya', 'Sukamakmur', 'Sukaraja', 'Tajurhalang',
    'Tamansari', 'Tanjungsari', 'Tenjo', 'Tenjolaya',
    'Beji', 'Bojongsari', 'Cilodong', 'Cimanggis', 'Cinere', 'Cipayung',
    'Limo', 'Pancoran Mas', 'Sawangan', 'Sukmajaya', 'Tapos',
    'Batuceper', 'Benda', 'Cibodas', 'Ciledug', 'Cipondoh', 'Jatiuwung',
    'Karangtengah', 'Karawaci', 'Larangan', 'Neglasari', 'Periuk', 'Pinang',
    'Tangerang',
    'Ciputat', 'Ciputat Timur', 'Pamulang', 'Pondok Aren', 'Serpong', 'Serpong Utara',
    'Setu',
    'Balaraja', 'Cikupa', 'Cisauk', 'Cisoka', 'Curug', 'Gunung Kaler',
    'Jambe', 'Jayanti', 'Kelapa Dua', 'Kemiri', 'Kosambi', 'Kresek',
    'Kronjo', 'Legok', 'Mauk', 'Mekar Baru', 'Pagedangan', 'Pakuhaji',
    'Panongan', 'Pasar Kemis', 'Rajeg', 'Sepatan', 'Sepatan Timur', 'Sindang Jaya',
    'Solear', 'Sukadiri', 'Sukamulya', 'Teluknaga', 'Tigaraksa',
    'Bantar Gebang', 'Bekasi Barat', 'Bekasi Selatan', 'Bekasi Timur', 'Bekasi Utara', 'Jatiasih',
    'Jatisampurna', 'Medan Satria', 'Mustika Jaya', 'Pondok Gede', 'Pondok Melati', 'Rawalumbu',
    'Babelan', 'Bojongmangu', 'Cabangbungin', 'Cibarusah', 'Cibitung', 'Cikarang Barat',
    'Cikarang Pusat', 'Cikarang Selatan', 'Cikarang Timur', 'Cikarang Utara', 'Karangbahagia', 'Kedungwaringin',
    'Muara Gembong', 'Pebayuran', 'Serang Baru', 'Setu', 'Sukakarya', 'Sukatani',
    'Sukawangi', 'Tambelang', 'Tambun Selatan', 'Tambun Utara', 'Tarumajaya',
    'Cilandak', 'Jagakarsa', 'Kebayoran Baru', 'Kebayoran Lama', 'Mampang Prapatan', 'Pancoran',
    'Pasar Minggu', 'Pesanggrahan', 'Setiabudi', 'Tebet',
    'Cakung', 'Cipayung', 'Ciracas', 'Duren Sawit', 'Jatinegara', 'Kramat Jati',
    'Makasar', 'Matraman', 'Pasar Rebo', 'Pulo Gadung',
    'Cengkareng', 'Grogol Petamburan', 'Kalideres', 'Kebon Jeruk', 'Kembangan', 'Palmerah',
    'Taman Sari', 'Tambora',
    'Cempaka Putih', 'Gambir', 'Johar Baru', 'Kemayoran', 'Menteng', 'Sawah Besar',
    'Senen', 'Tanah Abang',
    'Cilincing', 'Kelapa Gading', 'Koja', 'Pademangan', 'Penjaringan', 'Tanjung Priok',
    'Kepulauan Seribu Selatan', 'Kepulauan Seribu Utara', 'Kota Bogor', 'Kabupaten Bogor', 'Kota Depok', 'Kota Tangerang',
    'Kota Tangerang Selatan', 'Kabupaten Tangerang', 'Kota Bekasi',
    'Kabupaten Bekasi', 'Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat',
    'Jakarta Pusat', 'Jakarta Utara', 'Kepulauan Seribu', 'Kota Bandung',
    'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kota Cimahi',
    'Kabupaten Sumedang',
];

include '../includes/header_pelamar.php';
?>

<!-- Select2 CSS & JS untuk Dropdown yang Bisa Dicari / Diketik -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .profile-page-layout { max-width: 1150px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 25px; align-items: start; }
    .profile-card-container { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 90px; }
    .profile-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .profile-avatar-wrapper { width: 80px; height: 80px; margin: 0 auto 12px auto; background: #e0f2f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #b2dfdb; overflow: hidden; }
    .profile-avatar-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .card-username { font-size: 13px; font-weight: 800; color: #004d40; text-transform: uppercase; margin-bottom: 4px; line-height: 1.3; }
    .card-email { font-size: 11px; color: #666; margin-bottom: 15px; word-break: break-all; }
    .btn-save-card { background-color: #1b5e20; color: white; border: none; width: 100%; padding: 10px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
    .btn-save-card:hover { background-color: #00332c; }
    .btn-save-card:disabled { opacity: 0.7; cursor: wait; }

    .tab-navigation-bar { background: #e0e0e0; border-radius: 30px; padding: 6px; display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 5px; }
    .tab-btn { background: transparent; border: none; padding: 10px 12px; border-radius: 25px; font-size: 11.5px; font-weight: 700; color: #555; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px; flex: 1; justify-content: center; white-space: nowrap; }
    .tab-btn.active { background-color: #1b5e20; color: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }

    .form-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .form-section-content { display: none; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-section-content.active { display: grid; }
    .form-field-group { display: flex; flex-direction: column; gap: 5px; }
    .form-field-group.full-width { grid-column: span 2; }
    .form-field-group label { font-size: 12px; font-weight: 700; color: #333; }
    .form-control { background-color: #ffffff; border: 1px solid #dcdcdc; padding: 9px 12px; border-radius: 6px; font-size: 13px; color: #333; width: 100%; box-sizing: border-box; }
    .form-control.field-invalid { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
    .sub-section-title { grid-column: span 2; font-size: 14px; font-weight: 800; color: #004d40; border-bottom: 2px solid #b2dfdb; padding-bottom: 5px; margin-top: 10px; }
    .section-note-box { grid-column: span 2; background-color: #fff8e1; border: 1px solid #ffe082; color: #795548; padding: 10px 14px; border-radius: 8px; font-size: 12.5px; line-height: 1.5; }
    .notice-top-banner { background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; padding: 10px 15px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; line-height: 1.5; }
    .alert-success { background-color: #e8f5e9; color: #2e7d32; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-weight: 600; font-size: 13px; }
    .alert-error { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-weight: 600; font-size: 13px; line-height: 1.5; }

    /* Styling Select2 agar menyatu dengan .form-control */
    .select2-container { width: 100% !important; }
    .select2-container .select2-selection--single {
        height: 37px !important;
        border: 1px solid #dcdcdc !important;
        border-radius: 6px !important;
        padding: 4px 8px !important;
        font-size: 13px !important;
        color: #333 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 27px !important;
        padding-left: 4px !important;
        color: #333 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 35px !important;
    }

    @media (max-width: 950px) {
        .profile-page-layout { grid-template-columns: 1fr; }
        .profile-card-container { position: static; }
        .form-section-content { grid-template-columns: 1fr; }
        .form-field-group.full-width { grid-column: span 1; }
        .sub-section-title { grid-column: span 1; }
        .section-note-box { grid-column: span 1; }
        .tab-navigation-bar { flex-direction: column; border-radius: 12px; }
    }
</style>

<div class="profile-page-layout">
    <form id="formUpdateProfile" action="" method="POST" enctype="multipart/form-data" novalidate style="display: contents;">
        <div class="profile-card-container">
            <div class="profile-card-box">
                <div id="edit-avatar-wrap" class="profile-avatar-wrapper">
                    <span style="font-size: 40px;">👤</span>
                </div>
                <div id="edit-username" class="card-username">Memuat...</div>
                <div id="edit-email" class="card-email">-</div>
                <button type="submit" id="btnSaveProfile" class="btn-save-card"><span>💾</span> Simpan Data</button>
            </div>
        </div>

        <div class="content-right-side">
            <div class="notice-top-banner">
                <strong>Catatan Wajib:</strong> <strong>Semua kolom pada seluruh tab wajib diisi</strong> sebelum dapat menyimpan profil (kecuali tanggal mulai kerja, tanggal akhir kerja, tanggal mulai magang, dan tanggal akhir magang). Jika belum memiliki pengalaman kerja atau magang, isi dengan <strong>"Tidak ada"</strong>.
            </div>

            <div id="alert-error-box" class="alert-error" style="display: none;"></div>
            <div id="alert-success-box" class="alert-success" style="display: none;"></div>

            <div class="tab-navigation-bar">
                <button type="button" class="tab-btn active" data-tab="tab-datadiri" onclick="switchTab(event, 'tab-datadiri')">👤 Data Diri</button>
                <button type="button" class="tab-btn" data-tab="tab-darurat" onclick="switchTab(event, 'tab-darurat')">🚨 Kontak Darurat</button>
                <button type="button" class="tab-btn" data-tab="tab-pendidikan" onclick="switchTab(event, 'tab-pendidikan')">🎓 Pendidikan</button>
                <button type="button" class="tab-btn" data-tab="tab-kerja" onclick="switchTab(event, 'tab-kerja')">💼 Pengalaman Kerja</button>
                <button type="button" class="tab-btn" data-tab="tab-magang" onclick="switchTab(event, 'tab-magang')">📋 Internship</button>
                <button type="button" class="tab-btn" data-tab="tab-berkas" onclick="switchTab(event, 'tab-berkas')">📁 Upload Berkas</button>
            </div>

            <div class="form-card-box">
                <!-- 1. TAB DATA DIRI -->
                <div id="tab-datadiri" class="form-section-content active">
                    <div class="form-field-group">
                        <label>Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" name="nama_lengkap" data-label="Nama Lengkap" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Email <span style="color: red;">*</span></label>
                        <input type="email" name="email" data-label="Email" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>No Handphone <span style="color: red;">*</span></label>
                        <input type="text" name="no_telepon" data-label="No Handphone" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Kode Pos <span style="color: red;">*</span></label>
                        <input type="text" name="kode_pos" data-label="Kode Pos" class="form-control wajib-isi" value="">
                    </div>

                    <div class="form-field-group">
                        <label>Jenis Kelamin <span style="color: red;">*</span></label>
                        <select name="jenis_kelamin" data-label="Jenis Kelamin" class="form-control select-cari wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_jekel as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>Kota / Kabupaten <span style="color: red;">*</span></label>
                        <select name="kota" data-label="Kota / Kabupaten" class="form-control select-cari-bebas wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_kota as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>Tempat Lahir <span style="color: red;">*</span></label>
                        <input type="text" name="tempat_lahir" data-label="Tempat Lahir" class="form-control wajib-isi" value="">
                    </div>

                    <div class="form-field-group">
                        <label>Kecamatan <span style="color: red;">*</span></label>
                        <select name="kecamatan" data-label="Kecamatan" class="form-control select-cari-bebas wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_kecamatan as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>Tanggal Lahir <span style="color: red;">*</span></label>
                        <input type="date" name="tanggal_lahir" data-label="Tanggal Lahir" class="form-control wajib-isi" value="">
                    </div>

                    <div class="form-field-group">
                        <label>Agama <span style="color: red;">*</span></label>
                        <select name="agama" data-label="Agama" class="form-control select-cari wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_agama as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>No KTP <span style="color: red;">*</span></label>
                        <input type="text" name="no_ktp" data-label="No KTP" class="form-control wajib-isi" value="">
                    </div>

                    <div class="form-field-group">
                        <label>Status Pernikahan <span style="color: red;">*</span></label>
                        <select name="status_nikah" data-label="Status Pernikahan" class="form-control select-cari wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_nikah as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>Kewarganegaraan <span style="color: red;">*</span></label>
                        <input type="text" name="kewarganegaraan" data-label="Kewarganegaraan" class="form-control wajib-isi" value="Indonesia">
                    </div>
                    <div class="form-field-group">
                        <label>Jumlah Anak <span style="color: red;">*</span></label>
                        <input type="number" name="jumlah_anak" data-label="Jumlah Anak" class="form-control wajib-isi" min="0" value="0">
                    </div>
                    <div class="form-field-group">
                        <label>Berat Badan <span style="color: red;">*</span></label>
                        <input type="text" name="berat_badan" data-label="Berat Badan" class="form-control wajib-isi" placeholder="Contoh: 60 kg" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Tinggi Badan <span style="color: red;">*</span></label>
                        <input type="text" name="tinggi_badan" data-label="Tinggi Badan" class="form-control wajib-isi" placeholder="Contoh: 170 cm" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Lengkap (Jalan / RT / RW / Kelurahan) <span style="color: red;">*</span></label>
                        <textarea name="alamat" data-label="Alamat Lengkap" class="form-control wajib-isi" rows="2"></textarea>
                    </div>
                </div>

                <!-- 2. TAB KONTAK DARURAT -->
                <div id="tab-darurat" class="form-section-content">
                    <div class="form-field-group">
                        <label>Nama Keluarga <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_nama" data-label="Nama Keluarga (Kontak Darurat)" class="form-control wajib-isi" value="">
                    </div>

                    <div class="form-field-group">
                        <label>Hubungan Keluarga <span style="color: red;">*</span></label>
                        <select name="darurat_hubungan" data-label="Hubungan Keluarga" class="form-control select-cari-bebas wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_hubungan as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>No Telepon 1 <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_telp1" data-label="No Telepon 1 (Kontak Darurat)" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>No Telepon 2 <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_telp2" data-label="No Telepon 2 (Kontak Darurat)" class="form-control wajib-isi" placeholder="Isi nomor kedua atau 'Tidak ada'" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Keluarga <span style="color: red;">*</span></label>
                        <textarea name="darurat_alamat" data-label="Alamat Keluarga (Kontak Darurat)" class="form-control wajib-isi" rows="2"></textarea>
                    </div>
                </div>

                <!-- 3. TAB PENDIDIKAN -->
                <div id="tab-pendidikan" class="form-section-content">
                    <div class="form-field-group">
                        <label>Pendidikan Terakhir <span style="color: red;">*</span></label>
                        <select name="pend_terakhir" data-label="Pendidikan Terakhir" class="form-control select-cari wajib-isi">
                            <option value=""></option>
                            <?php foreach ($opsi_pendidikan as $val): ?>
                                <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($val) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label>Nama Sekolah / Universitas <span style="color: red;">*</span></label>
                        <input type="text" name="pend_institusi" data-label="Nama Sekolah / Universitas" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Jurusan <span style="color: red;">*</span></label>
                        <input type="text" name="pend_jurusan" data-label="Jurusan" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Nilai / IPK <span style="color: red;">*</span></label>
                        <input type="text" name="pend_nilai" data-label="Nilai / IPK" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Tanggal <span style="color: red;">*</span></label>
                        <input type="date" name="pend_mulai" data-label="Mulai Tanggal Pendidikan" class="form-control wajib-isi" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Selesai Tanggal <span style="color: red;">*</span></label>
                        <input type="date" name="pend_selesai" data-label="Selesai Tanggal Pendidikan" class="form-control wajib-isi" value="">
                    </div>
                </div>

                <!-- 4. TAB PENGALAMAN KERJA -->
                <div id="tab-kerja" class="form-section-content">
                    <div class="section-note-box">
                        <strong>Catatan Pengalaman Kerja:</strong> Jika belum memiliki pengalaman kerja, <strong>jangan dikosongkan</strong>—isi kolom teks dengan <strong>"Tidak ada"</strong>. Kolom <em>Mulai Kerja</em> dan <em>Akhir Kerja</em> boleh dikosongkan.
                    </div>

                    <div class="sub-section-title">Pengalaman Kerja 1 (Terakhir)</div>
                    <div class="form-field-group">
                        <label>Nama Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja_perusahaan" data-label="Nama Perusahaan (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>No. Telepon Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja_telp" data-label="No. Telepon Perusahaan (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Kerja (Opsional)</label>
                        <input type="date" name="kerja_mulai" class="form-control" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Kerja (Opsional)</label>
                        <input type="date" name="kerja_akhir" class="form-control" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jabatan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja_jabatan" data-label="Jabatan (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Pekerjaan <span style="color: red;">*</span></label>
                        <textarea name="kerja_jobdesk" data-label="Jobdesk (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Perusahaan <span style="color: red;">*</span></label>
                        <textarea name="kerja_alamat" data-label="Alamat Perusahaan (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alasan Berhenti Bekerja <span style="color: red;">*</span></label>
                        <textarea name="kerja_alasan" data-label="Alasan Berhenti (Kerja 1)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>

                    <div class="sub-section-title">Pengalaman Kerja 2 (Sebelumnya)</div>
                    <div class="form-field-group">
                        <label>Nama Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja2_perusahaan" data-label="Nama Perusahaan (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>No. Telepon Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja2_telp" data-label="No. Telepon Perusahaan (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Kerja (Opsional)</label>
                        <input type="date" name="kerja2_mulai" class="form-control" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Kerja (Opsional)</label>
                        <input type="date" name="kerja2_akhir" class="form-control" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jabatan <span style="color: red;">*</span></label>
                        <input type="text" name="kerja2_jabatan" data-label="Jabatan (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Pekerjaan <span style="color: red;">*</span></label>
                        <textarea name="kerja2_jobdesk" data-label="Jobdesk (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Perusahaan <span style="color: red;">*</span></label>
                        <textarea name="kerja2_alamat" data-label="Alamat Perusahaan (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alasan Berhenti Bekerja <span style="color: red;">*</span></label>
                        <textarea name="kerja2_alasan" data-label="Alasan Berhenti (Kerja 2)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                </div>

                <!-- 5. TAB INTERNSHIP / MAGANG -->
                <div id="tab-magang" class="form-section-content">
                    <div class="section-note-box">
                        <strong>Catatan Internship / Magang:</strong> Jika belum memiliki pengalaman magang/internship, <strong>jangan dikosongkan</strong>—isi kolom teks dengan <strong>"Tidak ada"</strong>. Kolom <em>Mulai Magang</em> dan <em>Akhir Magang</em> boleh dikosongkan, serta upload dokumen/sertifikat magang bila tersedia (format JPG, JPEG, PNG, atau PDF).
                    </div>

                    <div class="sub-section-title">Pengalaman Internship / Magang 1</div>
                    <div class="form-field-group">
                        <label>Nama Instansi / Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="magang1_instansi" data-label="Nama Instansi (Magang 1)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Posisi / Peran <span style="color: red;">*</span></label>
                        <input type="text" name="magang1_posisi" data-label="Posisi / Peran (Magang 1)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Magang (Opsional)</label>
                        <input type="date" name="magang1_mulai" class="form-control" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Magang (Opsional)</label>
                        <input type="date" name="magang1_akhir" class="form-control" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Kegiatan <span style="color: red;">*</span></label>
                        <textarea name="magang1_jobdesk" data-label="Jobdesk (Magang 1)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Upload Dokumen / Sertifikat Magang 1 (Opsional)</label>
                        <input type="file" name="magang1_sertifikat" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <small style="color: #666;">Format: JPG, JPEG, PNG, atau PDF.</small>
                        <small id="status-file-magang1_sertifikat" style="color: green; display: none;"></small>
                    </div>

                    <div class="sub-section-title">Pengalaman Internship / Magang 2</div>
                    <div class="form-field-group">
                        <label>Nama Instansi / Perusahaan <span style="color: red;">*</span></label>
                        <input type="text" name="magang2_instansi" data-label="Nama Instansi (Magang 2)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Posisi / Peran <span style="color: red;">*</span></label>
                        <input type="text" name="magang2_posisi" data-label="Posisi / Peran (Magang 2)" data-default-tidak-ada="1" class="form-control wajib-isi" placeholder="Tidak ada" value="Tidak ada">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Magang (Opsional)</label>
                        <input type="date" name="magang2_mulai" class="form-control" value="">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Magang (Opsional)</label>
                        <input type="date" name="magang2_akhir" class="form-control" value="">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Kegiatan <span style="color: red;">*</span></label>
                        <textarea name="magang2_jobdesk" data-label="Jobdesk (Magang 2)" data-default-tidak-ada="1" class="form-control wajib-isi" rows="2" placeholder="Tidak ada">Tidak ada</textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Upload Dokumen / Sertifikat Magang 2 (Opsional)</label>
                        <input type="file" name="magang2_sertifikat" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <small style="color: #666;">Format: JPG, JPEG, PNG, atau PDF.</small>
                        <small id="status-file-magang2_sertifikat" style="color: green; display: none;"></small>
                    </div>
                </div>

                <!-- 6. TAB UPLOAD BERKAS -->
                <div id="tab-berkas" class="form-section-content">
                    <div class="form-field-group">
                        <label>Upload KTP <span style="color: red;">*</span></label>
                        <input type="file" name="ktp" data-label="Upload KTP" data-existing="0" class="form-control wajib-berkas" accept=".jpg, .jpeg, .png, .pdf">
                        <small id="status-file-ktp" style="color: green; display: none;"></small>
                    </div>
                    <div class="form-field-group">
                        <label>Upload Ijazah <span style="color: red;">*</span></label>
                        <input type="file" name="ijazah" data-label="Upload Ijazah" data-existing="0" class="form-control wajib-berkas" accept=".jpg, .jpeg, .png, .pdf">
                        <small id="status-file-ijazah" style="color: green; display: none;"></small>
                    </div>
                    <div class="form-field-group">
                        <label>Upload SKCK <span style="color: red;">*</span></label>
                        <input type="file" name="skck" data-label="Upload SKCK" data-existing="0" class="form-control wajib-berkas" accept=".jpg, .jpeg, .png, .pdf">
                        <small id="status-file-skck" style="color: green; display: none;"></small>
                    </div>
                    <div class="form-field-group">
                        <label>Upload Kartu Keluarga (KK) <span style="color: red;">*</span></label>
                        <input type="file" name="kk" data-label="Upload Kartu Keluarga (KK)" data-existing="0" class="form-control wajib-berkas" accept=".jpg, .jpeg, .png, .pdf">
                        <small id="status-file-kk" style="color: green; display: none;"></small>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Upload Pas Foto (Foto Profil) <span style="color: red;">*</span></label>
                        <input type="file" name="pas_foto" data-label="Upload Pas Foto" data-existing="0" class="form-control wajib-berkas" accept=".jpg, .jpeg, .png">
                        <small style="color: #666;">Format: JPG/PNG. Foto ini otomatis menjadi foto profil Anda.</small>
                        <small id="status-file-pas_foto" style="color: green; display: none;"></small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function bukaTabById(tabId) {
    let contents = document.getElementsByClassName("form-section-content");
    for (let i = 0; i < contents.length; i++) { contents[i].classList.remove("active"); }
    let buttons = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove("active");
        if (buttons[i].getAttribute("data-tab") === tabId) {
            buttons[i].classList.add("active");
        }
    }
    const target = document.getElementById(tabId);
    if (target) target.classList.add("active");
    initSelect2();
}

function switchTab(evt, tabId) {
    bukaTabById(tabId);
}

function initSelect2() {
    $('.select-cari').select2({ width: '100%' });
    $('.select-cari-bebas').select2({ width: '100%', tags: true });
}

function setSelectValue($select, value) {
    const val = (value ?? '').toString().trim();
    if (!val) {
        $select.val('').trigger('change');
        return;
    }
    let matchedVal = null;
    $select.find('option').each(function() {
        if ($(this).val().toLowerCase() === val.toLowerCase()) {
            matchedVal = $(this).val();
            return false;
        }
    });
    if (matchedVal !== null) {
        $select.val(matchedVal).trigger('change');
    } else {
        const newOption = new Option(val, val, true, true);
        $select.append(newOption).trigger('change');
    }
}

function populateFormFromApi(pelamar, files) {
    document.getElementById('edit-username').textContent = pelamar.nama_lengkap || 'Nama Pelamar';
    document.getElementById('edit-email').textContent = pelamar.email || '-';

    const pasFoto = files?.pas_foto;
    if (pasFoto && pasFoto.exists && pasFoto.url) {
        document.getElementById('edit-avatar-wrap').innerHTML =
            `<img src="${pasFoto.url}" alt="Foto Profil">`;
    }

    const form = document.getElementById('formUpdateProfile');
    form.querySelectorAll('input:not([type="file"]), textarea, select').forEach(el => {
        const name = el.getAttribute('name');
        if (!name || !(name in pelamar)) return;

        let val = pelamar[name];
        const strVal = (val ?? '').toString().trim();

        if (el.tagName === 'SELECT') {
            setSelectValue($(el), strVal);
        } else if (el.getAttribute('data-default-tidak-ada') === '1') {
            el.value = strVal !== '' ? strVal : 'Tidak ada';
        } else if (name === 'kewarganegaraan') {
            el.value = strVal !== '' ? strVal : 'Indonesia';
        } else if (name === 'jumlah_anak') {
            el.value = strVal !== '' ? strVal : '0';
        } else {
            el.value = strVal;
        }
    });

    // Perbarui status berkas tersimpan
    const allFileCols = ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto', 'magang1_sertifikat', 'magang2_sertifikat'];
    allFileCols.forEach(col => {
        const info = files?.[col];
        const filename = (info?.filename || pelamar[col] || '').trim();
        const inputEl = form.querySelector(`input[type="file"][name="${col}"]`);
        const statusEl = document.getElementById(`status-file-${col}`);

        if (inputEl && inputEl.classList.contains('wajib-berkas')) {
            inputEl.setAttribute('data-existing', filename !== '' ? '1' : '0');
        }
        if (statusEl) {
            if (filename !== '') {
                const prefix = col === 'pas_foto' ? 'Aktif: ' : 'Tersimpan: ';
                statusEl.textContent = prefix + filename;
                statusEl.style.display = 'block';
            } else {
                statusEl.style.display = 'none';
            }
        }
    });
}

async function loadInitialProfile() {
    try {
        const res = await fetch('../api/pelamar/profile.php', {
            credentials: 'same-origin'
        });
        const result = await res.json();

        if (!res.ok || !result.success || !result.data?.pelamar) {
            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu sebagai pelamar!');
                window.location.href = '../login.php';
                return;
            }
            const errBox = document.getElementById('alert-error-box');
            errBox.textContent = result.message || 'Gagal memuat data profil.';
            errBox.style.display = 'block';
            return;
        }

        populateFormFromApi(result.data.pelamar, result.data.files || {});
    } catch (err) {
        const errBox = document.getElementById('alert-error-box');
        errBox.textContent = 'Terjadi kesalahan koneksi saat memuat data profil.';
        errBox.style.display = 'block';
    }
}

$(document).ready(function() {
    initSelect2();
    loadInitialProfile();

    $('#formUpdateProfile').on('submit', async function(e) {
        e.preventDefault();

        const errBox = document.getElementById('alert-error-box');
        const okBox  = document.getElementById('alert-success-box');
        errBox.style.display = 'none';
        okBox.style.display  = 'none';

        let daftarKosong = [];
        let tabPertamaKosong = null;
        let elemenPertamaKosong = null;

        $('.field-invalid').removeClass('field-invalid');

        // 1. Cek semua input/select/textarea yang wajib diisi
        $('.wajib-isi').each(function() {
            let nilai = ($(this).val() || '').toString().trim();
            if (nilai === '') {
                let label = $(this).data('label') || $(this).attr('name');
                daftarKosong.push(label);
                $(this).addClass('field-invalid');

                if (!tabPertamaKosong) {
                    tabPertamaKosong = $(this).closest('.form-section-content').attr('id');
                    elemenPertamaKosong = $(this);
                }
            }
        });

        // 2. Cek 5 berkas wajib (harus sudah tersimpan di DB atau baru dipilih)
        $('.wajib-berkas').each(function() {
            let sudahAda = $(this).attr('data-existing') === '1';
            let baruPilih = this.files && this.files.length > 0;
            if (!sudahAda && !baruPilih) {
                let label = $(this).data('label') || $(this).attr('name');
                daftarKosong.push(label);
                $(this).addClass('field-invalid');

                if (!tabPertamaKosong) {
                    tabPertamaKosong = $(this).closest('.form-section-content').attr('id');
                    elemenPertamaKosong = $(this);
                }
            }
        });

        if (daftarKosong.length > 0) {
            if (tabPertamaKosong) {
                bukaTabById(tabPertamaKosong);
            }
            alert(
                "Profil belum bisa disimpan! Harap lengkapi semua data terlebih dahulu:\n\n- " +
                daftarKosong.join("\n- ") +
                "\n\nCatatan: Untuk pengalaman kerja/magang yang tidak dimiliki, isi dengan \"Tidak ada\"."
            );
            if (elemenPertamaKosong) {
                elemenPertamaKosong.focus();
            }
            return;
        }

        const btnSave = document.getElementById('btnSaveProfile');
        const originalBtnHtml = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = '<span>⏳</span> Menyimpan...';

        try {
            const formData = new FormData(this);
            const res = await fetch('../api/pelamar/profile.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (!res.ok || !result.success) {
                errBox.textContent = result.message || 'Gagal menyimpan data profil.';
                errBox.style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            if (result.data?.pelamar) {
                populateFormFromApi(result.data.pelamar, result.data.files || {});
            }

            // Kosongkan input file setelah berhasil diupload
            this.querySelectorAll('input[type="file"]').forEach(inp => { inp.value = ''; });

            okBox.textContent = result.message || 'Profil dan dokumen berhasil diperbarui!';
            okBox.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            errBox.textContent = 'Terjadi kesalahan saat mengirim data ke server.';
            errBox.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            btnSave.disabled = false;
            btnSave.innerHTML = originalBtnHtml;
        }
    });
});
</script>

<?php include '../includes/footer_pelamar.php'; ?>