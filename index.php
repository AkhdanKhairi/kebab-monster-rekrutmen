<?php
include 'includes/header.php';
?>

<style>
    /* Banner Join Us - Full Width */
    .join-banner {
        background-color: #004d40;
        color: white;
        text-align: center;
        padding: 60px 20px;
        width: 100%;
    }
    .join-banner h1 {
        font-size: 32px;
        font-weight: 800;
        color: #ffb300;
        margin-bottom: 12px;
        letter-spacing: 1px;
    }
    .join-banner p {
        font-size: 15px;
        color: #e0f2f1;
        max-width: 800px;
        margin: 0 auto;
        line-height: 1.5;
    }

    /* Container Utama untuk Bagian Tengah */
    .content-wrapper {
        width: 90%;
        max-width: 800px;
        margin: 40px auto;
    }

    /* Warning Section */
    .fraud-warning-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }
    .fraud-box {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-top: 5px solid #d32f2f;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .fraud-box h3 {
        color: #d32f2f;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 15px;
    }
    .fraud-box p {
        font-size: 14px;
        color: #444;
        line-height: 1.7;
        margin-bottom: 12px;
    }
    
    .fraud-right-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        justify-content: center;
    }
    .fraud-tip-item {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        padding: 16px 22px;
        border-radius: 8px;
        font-size: 13.5px;
        color: #333;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        line-height: 1.5;
    }

    /* Be Part of the Change (Full Width, Hijau Transparan 15%) */
    .part-section {
        width: 100%;
        background: rgba(0, 77, 64, 0.15);
        padding: 70px 20px;
        text-align: center;
        margin-bottom: 50px;
    }
    .part-inner {
        max-width: 950px;
        margin: 0 auto;
    }
    .part-section h2 {
        font-size: 32px;
        color: #001a15;
        margin-bottom: 20px;
        font-weight: 800;
    }
    .part-subtitle {
        font-size: 13.5px;
        color: #004d40;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 25px;
    }
    .part-section p {
        font-size: 15px;
        color: #333;
        line-height: 1.8;
        margin-bottom: 20px;
    }

    /* Why Work With Us */
    .why-section-title {
        text-align: center;
        font-size: 24px;
        color: #004d40;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .why-subtitle {
        text-align: center;
        font-size: 14px;
        color: #666;
        margin-bottom: 30px;
    }
    .why-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }
    .why-card {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .why-icon {
        color: #004d40;
        font-size: 24px;
        margin-bottom: 15px;
    }
    .why-card h4 {
        font-size: 16px;
        color: #004d40;
        margin-bottom: 12px;
        font-weight: 700;
    }
    .why-card p {
        font-size: 13.5px;
        color: #555;
        line-height: 1.6;
    }

    /* We Are Expanding (Full Width, Hijau Transparan 15%) */
    .expanding-section {
        width: 100%;
        background: rgba(0, 77, 64, 0.15);
        padding: 60px 20px;
        text-align: center;
        margin-bottom: 50px;
    }
    .expanding-inner {
        max-width: 1100px;
        margin: 0 auto;
    }
    .expanding-section h2 {
        font-size: 26px;
        color: #004d40;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .expanding-section p {
        font-size: 14.5px;
        color: #333;
        margin-bottom: 30px;
    }
    
    .cities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 15px;
    }
    .city-card {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        padding: 20px 10px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        font-size: 20px;
        font-weight: 800;
        color: #004d40;
    }
    .city-card span {
        display: block;
        font-size: 13px;
        font-weight: normal;
        color: #666;
        margin-top: 4px;
    }

    /* Visi & Misi (Gaya Card Baru Sesuai Gambar) */
    .visimisi-section {
        text-align: center;
        margin-bottom: 60px;
    }
    .visimisi-title {
        font-size: 26px;
        color: #004d40;
        font-weight: 800;
        margin-bottom: 40px;
        letter-spacing: 0.5px;
    }
    .visimisi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 30px;
    }
    
    /* Card Container */
    .vm-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        overflow: hidden;
        border: 1px solid #e0e0e0;
        text-align: center;
    }
    
    /* Header Header Card (Visi & Misi) */
    .vm-header-visi {
        background-color: #00796b; /* Hijau Khas */
        color: white;
        padding: 20px;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 1px;
    }
    .vm-header-misi {
        background-color: #ffa000; /* Oranye Khas */
        color: white;
        padding: 20px;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: 1px;
    }
    
    /* Body Card (Isi Teks) */
    .vm-body {
        padding: 35px 30px;
        font-size: 14.5px;
        color: #444;
        line-height: 1.7;
    }

    /* Penyesuaian Layar HP */
    @media (max-width: 768px) {
        .join-banner {
            padding: 40px 16px;
        }
        .join-banner h1 {
            font-size: 26px;
        }
        .content-wrapper {
            width: 92%;
            margin: 25px auto;
        }
        .fraud-warning-container,
        .why-grid,
        .visimisi-grid {
            gap: 16px;
            margin-bottom: 35px;
        }
        .fraud-box,
        .why-card {
            padding: 20px;
        }
        .part-section,
        .expanding-section {
            padding: 45px 16px;
            margin-bottom: 35px;
        }
        .part-section h2,
        .visimisi-title {
            font-size: 24px;
        }
        .visimisi-title {
            margin-bottom: 24px;
        }
        .vm-body {
            padding: 24px 18px;
        }
    }

    @media (max-width: 576px) {
        .cities-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .city-card {
            font-size: 18px;
            padding: 16px 8px;
        }
    }
</style>

<!-- Banner Join Us -->
<div class="join-banner">
    <h1>JOIN US</h1>
    <p>Bangun karier bersama brand kebab 24 jam pertama di Indonesia. Make History with Kebab Monster.</p>
</div>

<div class="content-wrapper">

    <!-- Warning & Tips Penipuan -->
    <div class="fraud-warning-container">
        <div class="fraud-box">
            <h3>HATI-HATI PENIPUAN !!!</h3>
            <p>Manajemen Kebab Monster dengan ini mengimbau kepada seluruh masyarakat dan para pencari kerja agar selalu waspada terhadap segala bentuk modus penipuan lowongan kerja yang mengatasnamakan Kebab Monster.</p>
            <p>Seluruh informasi resmi mengenai rekrutmen dan lowongan hanya disampaikan melalui kanal resmi perusahaan.</p>
        </div>
        <div class="fraud-right-list">
            <div class="fraud-tip-item">
                Kebab Monster tidak pernah memungut biaya apa pun dari pelamar selama proses seleksi berlangsung (biaya administrasi, tes, akomodasi, dll).
            </div>
            <div class="fraud-tip-item">
                Seluruh informasi lowongan dan tahapan resmi hanya disampaikan melalui kanal resmi perusahaan.
            </div>
            <div class="fraud-tip-item">
                Imbauan untuk segera mengabaikan dan tidak merespons jika ada pihak tak dikenal yang meminta transfer uang atau kewajiban pembayaran perkuliahan/pelatihan.
            </div>
        </div>
    </div>

</div>

<!-- Be Part of the Change -->
<div class="part-section">
    <div class="part-inner">
        <h2>Be Part of the Change</h2>
        <div class="part-subtitle">TUJUAN KAMI SEDERHANA — MENJADI BRAND KEBAB TERDEPAN DI INDONESIA.</div>
        <p>Lebih dari sekadar misi, ini kesempatan bagi setiap orang yang bergabung untuk mengubah cara masyarakat menikmati kebab, porsi monster, rasa premium, tersedia 24 jam, dan terjangkau untuk semua.</p>
        <p>Baik di operasional outlet, central kitchen, logistik, marketing, finance, maupun HR — setiap posisi punya peran penting. Semua orang punya peluang untuk berkembang dan berkontribusi.</p>
    </div>
</div>

<div class="content-wrapper">

    <!-- Why Work With Us -->
    <h3 class="why-section-title">Why Work With Us?</h3>
    <p class="why-subtitle">Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang setiap di brand.</p>
    
    <div class="why-grid">
        <div class="why-card">
            <div class="why-icon">⏩</div>
            <h4>Pertumbuhan Cepat</h4>
            <p>70+ outlet dan terus ekspansi. Bergabung di fase growth beri banyak peluang naik jabatan dan tanggung jawab besar.</p>
        </div>
        <div class="why-card">
            <div class="why-icon">👥</div>
            <h4>Tim Supportif</h4>
            <p>Lingkungan kerja ramah, no drama, dan saling bantu. Kami menghargai kejujuran, disiplin, dan semangat #KebabMonster.</p>
        </div>
        <div class="why-card">
            <div class="why-icon">📈</div>
            <h4>Jenjang Karier</h4>
            <p>Ikuti jalur crew hingga supervisor atau manager hingga level pusat - jalur pengembangan jelas dan terbuka.</p>
        </div>
    </div>

</div>

<!-- We Are Expanding -->
<div class="expanding-section">
    <div class="expanding-inner">
        <h2>We Are Expanding</h2>
        <p>Misi besar butuh talenta dari banyak kota. Temukan peluang di area operasional kami:</p>
        <div class="cities-grid">
            <div class="city-card">JKT <span>Jakarta</span></div>
            <div class="city-card">DPK <span>Depok</span></div>
            <div class="city-card">BGR <span>Bogor</span></div>
            <div class="city-card">TGR <span>Tangerang</span></div>
            <div class="city-card">BKS <span>Bekasi</span></div>
            <div class="city-card">BDG <span>Bandung</span></div>
        </div>
    </div>
</div>

<div class="content-wrapper">

    <!-- Visi & Misi (Gaya Card Baru Sesuai Gambar) -->
    <div class="visimisi-section">
        <h2 class="visimisi-title">VISI & MISI</h2>
        <div class="visimisi-grid">
            <!-- Card Visi -->
            <div class="vm-card">
                <div class="vm-header-visi">VISI</div>
                <div class="vm-body">
                    Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang, saling ekspansi brand.
                </div>
            </div>
            <!-- Card Misi -->
            <div class="vm-card">
                <div class="vm-header-misi">MISI</div>
                <div class="vm-body">
                    Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang, saling ekspansi brand.
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function() {
    if (!window.fetch) return;
    fetch('api/index.php')
        .then(res => res.ok ? res.json() : null)
        .catch(() => {});
})();
</script>

<?php
include 'includes/footer.php';
?>