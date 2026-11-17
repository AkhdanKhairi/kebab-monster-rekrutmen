<?php
// Sertakan file header (navbar sticky otomatis aktif)
include 'includes/header.php';

$status_pesan = "";

// Logika sederhana saat form dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama    = trim($_POST['nama']);
    $email   = trim($_POST['email']);
    $subjek  = trim($_POST['subjek']);
    $pesan   = trim($_POST['pesan']);

    if (!empty($nama) && !empty($email) && !empty($pesan)) {
        // Simulasi pengiriman email ke HRD Kebab Monster
        $tujuan      = "recruitment@kebabmonster.id";
        $email_subjek = "Pesan Kontak Website: " . $subjek;
        $isi_email   = "Nama: $nama\nEmail: $email\n\nPesan:\n$pesan";
        $headers     = "From: $email";

        // Menggunakan fungsi mail bawaan PHP (atau bisa disimpan ke database jika diperlukan)
        @mail($tujuan, $email_subjek, $isi_email, $headers);

        $status_pesan = "<div style='background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:600; text-align:center;'>Terima kasih! Pesan Anda berhasil dikirim ke tim HRD Kebab Monster. Kami akan segera meresponsnya.</div>";
    } else {
        $status_pesan = "<div style='background:#ffebee; color:#c62828; padding:15px; border-radius:6px; margin-bottom:20px; font-weight:600; text-align:center;'>Mohon isi semua kolom yang wajib diisi!</div>";
    }
}
?>

<style>
    /* Banner Halaman Contact */
    .contact-banner {
        background-color: #004d40;
        color: white;
        text-align: center;
        padding: 60px 20px 50px 20px;
        width: 100%;
    }
    .contact-banner h1 {
        font-size: 32px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 15px;
    }
    .contact-banner h1 span {
        color: #ffb300;
    }
    .contact-banner p {
        font-size: 14.5px;
        color: #e0f2f1;
        max-width: 750px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Container Utama Contact */
    .contact-container {
        width: 90%;
        max-width: 1100px;
        margin: 40px auto 60px auto;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 35px;
    }

    /* Kolom Informasi Kontak & WhatsApp */
    .contact-info-col {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .info-box-contact {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .info-box-contact h3 {
        color: #004d40;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 15px;
    }
    .info-box-contact p {
        font-size: 13.5px;
        color: #555;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    /* Tombol WhatsApp Langsung */
    .btn-whatsapp {
        background-color: #25d366;
        color: white;
        padding: 14px 20px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: background-color 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        margin-top: 15px;
    }
    .btn-whatsapp:hover {
        background-color: #20ba5a;
    }

    /* Kolom Form Kirim Pesan */
    .contact-form-col {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-top: 5px solid #004d40;
        border-radius: 8px;
        padding: 35px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .contact-form-col h3 {
        color: #004d40;
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        color: #333;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #004d40;
    }
    textarea.form-control {
        resize: vertical;
        height: 120px;
    }

    .btn-submit {
        background-color: #ffb300;
        color: #111;
        border: none;
        width: 100%;
        padding: 13px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 15px;
        cursor: pointer;
        transition: background-color 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .btn-submit:hover {
        background-color: #ffa000;
    }

    @media (max-width: 768px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Banner Contact -->
<div class="contact-banner">
    <h1>Hubungi <span>Tim Kami</span></h1>
    <p>Punya pertanyaan seputar lowongan pekerjaan, proses seleksi, atau kemitraan? Jangan ragu untuk menghubungi kami melalui form atau WhatsApp di bawah ini.</p>
</div>

<div class="contact-container">

    <?php echo $status_pesan; ?>

    <div class="contact-grid">
        
        <!-- Kolom Kiri: Informasi & Tombol WhatsApp -->
        <div class="contact-info-col">
            <div class="info-box-contact">
                <h3>📍 Kantor Pusat Kebab Monster</h3>
                <p><strong>Alamat:</strong> Jl. Bintang Mas No.23, Nanggewer, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16912</p>
                <p><strong>Email HRD:</strong> hrd@kebabmonster.id</p>
                <p><strong>Jam Operasional:</strong> Senin - Jumat (08.30 - 17.30 WIB)</p>
            </div>

            <div class="info-box-contact" style="border-top: 5px solid #25d366;">
                <h3>💬 Chat Cepat via WhatsApp</h3>
                <p>Butuh respons cepat atau ada pertanyaan mendesak terkait jadwal interview dan pendaftaran? Hubungi admin rekrutmen kami langsung via WhatsApp.</p>
                
                <!-- Tombol Direct ke WhatsApp -->
                <a href="https://wa.me/6281234567890?text=Halo%20Tim%20HRD%20Kebab%20Monster,%20saya%20ingin%20bertanya%20mengenai%20lowongan%20pekerjaan." target="_blank" class="btn-whatsapp">
                    <span style="font-size: 18px;">📱</span> Pesan Via WhatsApp
                </a>
            </div>
        </div>

        <!-- Kolom Kanan: Form Kirim Pesan Email -->
        <div class="contact-form-col">
            <h3>Ada Pertanyaan?</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda" required>
                </div>

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="contoh@email.com" required>
                </div>

                <div class="form-group">
                    <label for="subjek">Subjek Pesan</label>
                    <input type="text" id="subjek" name="subjek" class="form-control" placeholder="Misal: Pertanyaan Seputar Lowongan Crew Outlet" required>
                </div>

                <div class="form-group">
                    <label for="pesan">Pesan / Pertanyaan</label>
                    <textarea id="pesan" name="pesan" class="form-control" placeholder="Tuliskan pesan atau pertanyaan Anda di sini..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">Kirim Pesan Sekarang</button>
            </form>
        </div>

    </div>

</div>

<?php
// Sertakan footer
include 'includes/footer.php';
?>