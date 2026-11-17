<?php
// Sertakan file header (navbar sticky otomatis aktif)
include 'includes/header.php';
?>

<style>
    /* Banner Halaman Information */
    .info-banner {
        background-color: #004d40;
        color: white;
        text-align: center;
        padding: 60px 20px 50px 20px;
        width: 100%;
    }
    .info-banner h1 {
        font-size: 32px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 15px;
    }
    .info-banner h1 span {
        color: #ffb300;
    }
    .info-banner p {
        font-size: 14.5px;
        color: #e0f2f1;
        max-width: 750px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Container Utama Information */
    .info-container {
        width: 90%;
        max-width: 1200px;
        margin: 40px auto 60px auto;
    }

    /* Judul Bagian */
    .section-main-title {
        font-size: 24px;
        color: #004d40;
        font-weight: 800;
        text-align: center;
        margin-bottom: 30px;
        letter-spacing: 0.5px;
    }

    /* Grid 3 Kartu Tahapan Seleksi (Persis Gambar) */
    .steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 60px;
    }

    .step-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        overflow: hidden;
        border: 1px solid #e0e0e0;
        display: flex;
        flex-direction: column;
    }

    .step-header {
        background-color: #ffa000; /* Oranye khas */
        color: white;
        padding: 20px;
        font-size: 15px;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.5px;
    }

    .step-body {
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        flex: 1;
    }

    .step-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .step-badge {
        background-color: #fff8e1;
        color: #f57f17;
        border: 2px solid #ffe082;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }

    .step-text h4 {
        font-size: 14px;
        font-weight: 700;
        color: #111;
        margin-bottom: 3px;
    }

    .step-text p {
        font-size: 12.5px;
        color: #555;
        line-height: 1.5;
    }

    /* Grid Informasi Tambahan (Anti Penipuan) */
    .info-bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }

    .info-box {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-top: 5px solid #004d40;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .info-box h3 {
        color: #004d40;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .info-box p, .info-box li {
        font-size: 13.5px;
        color: #444;
        line-height: 1.7;
    }
    .info-box ul {
        padding-left: 20px;
        margin-top: 10px;
    }
    .info-box li {
        margin-bottom: 8px;
    }

    /* FAQ Section */
    .faq-section {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 35px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .faq-section h2 {
        font-size: 22px;
        color: #004d40;
        font-weight: 800;
        margin-bottom: 25px;
        text-align: center;
    }
    .faq-item {
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 15px;
    }
    .faq-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .faq-question {
        font-size: 14.5px;
        font-weight: 700;
        color: #111;
        margin-bottom: 6px;
    }
    .faq-answer {
        font-size: 13.5px;
        color: #555;
        line-height: 1.6;
    }

    /* Responsif untuk Layar Kecil */
    @media (max-width: 992px) {
        .steps-grid {
            grid-template-columns: 1fr;
        }
        .info-bottom-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Banner Information -->
<div class="info-banner">
    <h1>Informasi & <span>Ketentuan Rekrutmen</span></h1>
    <p>Pelajari panduan lengkap mengenai tahapan seleksi, aturan penting, serta jawaban atas pertanyaan umum seputar proses rekrutmen di Kebab Monster.</p>
</div>

<div class="info-container">

    <!-- Judul Tahapan Seleksi -->
    <h2 class="section-main-title">TAHAPAN SELEKSI KEBAB MONSTER</h2>

    <!-- Grid 3 Kartu Tahapan Seleksi (Persis Sesuai Gambar) -->
    <div class="steps-grid">
        
        <!-- Kartu Tahap 1 -->
        <div class="step-card">
            <div class="step-header">Tahap 1 Pembuatan Akun & Login</div>
            <div class="step-body">
                <div class="step-item">
                    <div class="step-badge">1</div>
                    <div class="step-text">
                        <h4>Akses Portal (Masuk)</h4>
                        <p>Klik "Login/Registrasi" yang ada di pojok atas kanan.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">2</div>
                    <div class="step-text">
                        <h4>Registrasi Akun Baru</h4>
                        <p>Klik "Register" yang ada di halaman login.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">3</div>
                    <div class="step-text">
                        <h4>Isi Data Akun</h4>
                        <p>Masukan Username & Password, lalu klik "Login".</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">4</div>
                    <div class="step-text">
                        <h4>Masuk (Login)</h4>
                        <p>Masukan Username & Password.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Tahap 2 -->
        <div class="step-card">
            <div class="step-header">Tahap 2: Lihat Lowongan & Mengisi Berkas</div>
            <div class="step-body">
                <div class="step-item">
                    <div class="step-badge">5</div>
                    <div class="step-text">
                        <h4>Buka Menu Karir</h4>
                        <p>Klik "Career" di bagian atas lalu pilih "Lihat Detail Lamaran".</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">6</div>
                    <div class="step-text">
                        <h4>Mulai Pendaftaran</h4>
                        <p>Klik tombol "Daftar".</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">7</div>
                    <div class="step-text">
                        <h4>Lengkapi Informasi Pribadi</h4>
                        <p>Isi seluruh data diri yang diperlukan.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">8</div>
                    <div class="step-text">
                        <h4>Unggah Dokumen (Upload)</h4>
                        <p>Unggah semua berkas yang diminta sesuai dengan ketentuan yang tertulis.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Tahap 3 -->
        <div class="step-card">
            <div class="step-header">Tahap 3: Seleksi, Tes, & Wawancara</div>
            <div class="step-body">
                <div class="step-item">
                    <div class="step-badge">9</div>
                    <div class="step-text">
                        <h4>Undangan Tes</h4>
                        <p>Cek email untuk undangan tes online.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">10</div>
                    <div class="step-text">
                        <h4>Tes & Zoom</h4>
                        <p>Ikuti tes di "Sistem Penerimaan" dan join Zoom. (Link-nya akan dikirim melalui email).</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">11</div>
                    <div class="step-text">
                        <h4>Wawancara</h4>
                        <p>Tunggu notifikasi panggilan wawancara.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-badge">12</div>
                    <div class="step-text">
                        <h4>Hasil Akhir</h4>
                        <p>Tunggu hasil di email.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Informasi Tambahan (Anti Penipuan & Ketentuan) -->
    <div class="info-bottom-grid">
        <div class="info-box">
            <h3>🛡️ Komitmen Anti-Penipuan</h3>
            <p>Manajemen Kebab Monster berkomitmen menjaga proses rekrutmen yang bersih dan akuntabel:</p>
            <ul>
                <li>Kebab Monster <strong>tidak pernah</strong> memungut biaya apa pun selama proses seleksi (100% Gratis).</li>
                <li>Seluruh pengumuman resmi hanya dikirim melalui sistem atau email resmi perusahaan.</li>
                <li>Mohon abaikan pihak tidak bertanggung jawab yang menjanjikan kelulusan dengan meminta transfer uang.</li>
            </ul>
        </div>

        <div class="info-box">
            <h3>📌 Ketentuan Umum Pelamar</h3>
            <p>Sebelum melamar, pastikan Anda memenuhi persyaratan dasar berikut:</p>
            <ul>
                <li>Memiliki akun aktif di Sistem Penerimaan Karyawan Kebab Monster.</li>
                <li>Melampirkan berkas lamaran, CV, dan dokumen pendukung dengan lengkap dan valid.</li>
                <li>Bersedia mengikuti seluruh tahapan seleksi sesuai jadwal yang ditentukan oleh tim HRD.</li>
            </ul>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h2>Frequently Asked Questions (FAQ)</h2>

        <div class="faq-item">
            <div class="faq-question">1. Bagaimana cara melamar pekerjaan di Kebab Monster?</div>
            <div class="faq-answer">Ikuti Tahap 1 dan Tahap 2 di atas. Buat akun terlebih dahulu, lalu pilih lowongan di menu <a href="career.php" style="color: #004d40; font-weight: bold;">Career</a> dan lengkapi berkas pendaftaran Anda.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">2. Apakah fresh graduate boleh melamar?</div>
            <div class="faq-answer">Ya, sebagian besar posisi operasional kami terbuka untuk lulusan baru (SMA/SMK maupun Sederajat) dengan training intensif dari perusahaan.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">3. Di mana lokasi kantor pusat Kebab Monster?</div>
            <div class="faq-answer">Kantor pusat kami berlokasi di Jl. Bintang Mas No.23, Nanggewer, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16912.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">4. Bagaimana jika saya mengalami kendala saat login atau upload dokumen?</div>
            <div class="faq-answer">Anda dapat menghubungi tim HRD melalui <a href="contact.php" style="color: #004d40; font-weight: bold;">Contact</a> atau melalui kontak resmi yang tercantum pada bagian bawah halaman.</div>
        </div>
    </div>

</div>

<?php
// Sertakan footer
include 'includes/footer.php';
?>