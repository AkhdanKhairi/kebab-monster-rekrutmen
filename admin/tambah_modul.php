<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';
?>

<style>
    .card-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #e0e0e0;
        max-width: 700px;
        margin: 0 auto;
    }
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 15px;
    }
    .page-title {
        font-size: 20px;
        font-weight: 800;
        color: #004d40;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block; 
        font-size: 14px; 
        font-weight: 700; 
        color: #333; 
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%; 
        padding: 12px 15px; 
        border: 1px solid #ccc; 
        border-radius: 8px; 
        font-size: 14px; 
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus { 
        border-color: #1b5e20; 
        box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
    }
    .btn-submit {
        background: #1b5e20; 
        color: #fff; 
        padding: 12px 25px; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 15px;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover { 
        background: #00332c; 
    }
    .btn-back {
        background: #f1f3f5;
        color: #333;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-back:hover {
        background: #e2e6ea;
    }
    .alert { display: none; padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; font-weight: 600; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title">
            <span>📝</span> Tambah Modul Ujian Baru
        </div>
        <a href="master_ujian.php" class="btn-back">← Kembali</a>
    </div>

    <div class="alert error" id="tambahModulError"></div>

    <form id="formTambahModul">
        <div class="form-group">
            <label for="nama_modul">Nama Modul Ujian</label>
            <input type="text" id="nama_modul" name="nama_modul" class="form-control" placeholder="Contoh: Tes Logika & Penalaran Dasar" required>
        </div>

        <div class="form-group">
            <label for="kategori_posisi">Kategori Posisi Target</label>
            <select id="kategori_posisi" name="kategori_posisi" class="form-control" required>
                <option value="Semua">Semua Posisi (Umum)</option>
                <option value="Staff">Staff</option>
                <option value="Crew">Crew / Store</option>
            </select>
        </div>

        <div class="form-group">
            <label for="durasi_menit">Durasi Pengerjaan (Menit)</label>
            <input type="number" id="durasi_menit" name="durasi_menit" class="form-control" placeholder="Contoh: 20" min="1" required>
        </div>

        <div class="form-group">
            <label for="jenis_modul">Jenis Modul / Soal</label>
            <select id="jenis_modul" name="jenis_modul" class="form-control" required>
                <option value="pilihan_ganda">Pilihan Ganda</option>
                <option value="benar_salah">Benar / Salah (Ya / Tidak)</option>
                <option value="essay">Essay / Uraian</option>
            </select>
        </div>

        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="master_ujian.php" class="btn-back">Batal</a>
            <button type="submit" class="btn-submit" id="btnSimpanModul">💾 Simpan Modul</button>
        </div>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('formTambahModul');
    const errBox = document.getElementById('tambahModulError');
    const btnSave = document.getElementById('btnSimpanModul');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        errBox.style.display = 'none';
        btnSave.disabled = true;

        const payload = {
            nama_modul: document.getElementById('nama_modul').value.trim(),
            kategori_posisi: document.getElementById('kategori_posisi').value,
            durasi_menit: parseInt(document.getElementById('durasi_menit').value, 10),
            jenis_modul: document.getElementById('jenis_modul').value
        };

        try {
            const res = await fetch('../api/admin/modul_ujian.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok && result.success) {
                alert(result.message || 'Modul ujian baru berhasil ditambahkan!');
                window.location.href = 'master_ujian.php';
            } else {
                errBox.style.display = 'block';
                errBox.textContent = '❌ ' + (result.message || 'Gagal menambahkan modul.');
                btnSave.disabled = false;
            }
        } catch (err) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ Terjadi kesalahan koneksi saat menyimpan modul.';
            btnSave.disabled = false;
        }
    });
})();
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>