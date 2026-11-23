<?php
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .card-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #e0e0e0;
    }
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 14px;
        gap: 12px;
        flex-wrap: wrap;
    }
    .page-title {
        font-size: 19px;
        font-weight: 800;
        color: #004d40;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-tambah {
        background-color: #ffb300;
        color: #000;
        border: none;
        min-height: 38px;
        padding: 9px 16px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-tambah:hover {
        background-color: #ffa000;
    }

    .table-responsive {
        overflow-x: auto;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13.5px;
    }
    .table-custom th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 2px solid #e0e0e0;
        white-space: nowrap;
    }
    .table-custom td {
        padding: 12px 14px;
        border-bottom: 1px solid #eee;
        color: #444;
        vertical-align: middle;
    }
    .table-custom tbody tr:hover {
        background-color: #fafafa;
    }

    .badge-kategori {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e8f5e9;
        color: #1b5e20;
    }
    .badge-jenis {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e3f2fd;
        color: #0d47a1;
    }

    .aksi-modul-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .btn-action {
        min-height: 34px;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        transition: opacity 0.2s;
    }
    .btn-tatacara { background-color: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }
    .btn-soal { background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
    .btn-edit { background-color: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
    .btn-delete { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .btn-action:hover { opacity: 0.85; }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
        padding: 14px;
    }
    .modal-box {
        background: #fff;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.18);
        position: relative;
    }
    .modal-header {
        font-size: 17px;
        font-weight: 800;
        color: #004d40;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .close-btn {
        background: #f1f5f9;
        border: none;
        border-radius: 7px;
        min-width: 32px;
        min-height: 32px;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        color: #555;
    }
    .form-group {
        margin-bottom: 14px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #333;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        min-height: 38px;
        padding: 9px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        background: #fff;
    }
    .form-control:focus { border-color: #1b5e20; }
    .btn-submit-modal {
        background: #1b5e20;
        color: #fff;
        width: 100%;
        min-height: 42px;
        padding: 11px;
        border: none;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 8px;
        transition: background 0.2s;
    }
    .btn-submit-modal:hover { background: #00332c; }
    .alert { display: none; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; font-weight: 600; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

    @media (max-width: 768px) {
        .card-container {
            padding: 14px;
            border-radius: 10px;
        }
        .page-header-flex {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 14px;
            padding-bottom: 12px;
        }
        .page-title {
            font-size: 16.5px;
        }
        .btn-tambah {
            width: 100%;
            min-height: 42px;
            font-size: 14px;
        }

        .table-custom {
            display: block;
        }
        .table-custom thead {
            display: none;
        }
        .table-custom tbody {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .table-custom tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .table-custom td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: right;
            font-size: 13px;
        }
        .table-custom td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .table-custom td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #004d40;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .table-custom td[data-label="Nama Modul"] {
            align-items: flex-start;
        }
        .aksi-modul-group {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .btn-action {
            min-height: 38px;
            font-size: 12.5px;
        }

        .modal-box {
            padding: 18px 16px;
        }
        .form-control {
            min-height: 42px;
            font-size: 16px;
        }
        .btn-submit-modal {
            min-height: 44px;
            font-size: 14px;
        }
    }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title">
            <span>📝</span> Master Modul & Ujian Psikotes
        </div>
        <a href="tambah_modul.php" class="btn-tambah">
            + Tambah Modul Tes
        </a>
    </div>

    <div class="alert error" id="masterUjianError"></div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Nama Modul Ujian</th>
                    <th>Kategori Posisi</th>
                    <th>Durasi</th>
                    <th>Jenis Tes</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodyModulUjian">
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #777;">
                        Memuat daftar modul ujian...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL POPUP EDIT MODUL -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <span>Edit Modul Ujian & Urutan</span>
            <button type="button" class="close-btn" id="btnCloseEditModal" aria-label="Tutup modal">&times;</button>
        </div>

        <form id="formEditModul">
            <input type="hidden" id="edit_id_modul" name="id_modul">

            <div class="form-group">
                <label for="edit_nama_modul">Nama Modul Ujian</label>
                <input type="text" id="edit_nama_modul" name="nama_modul" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="edit_urutan">Nomor Urutan Tampil</label>
                <input type="number" id="edit_urutan" name="urutan" class="form-control" min="1" required>
                <small style="color: #666; font-size: 11.5px;">*Mengubah nomor urut akan otomatis menggeser modul lain di sekitarnya.</small>
            </div>

            <div class="form-group">
                <label for="edit_kategori_posisi">Kategori Posisi Target</label>
                <select id="edit_kategori_posisi" name="kategori_posisi" class="form-control" required>
                    <option value="Semua">Semua Posisi (Umum)</option>
                    <option value="Staff">Staff</option>
                    <option value="Crew">Crew / Store</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit_durasi_menit">Durasi Pengerjaan (Menit)</label>
                <input type="number" id="edit_durasi_menit" name="durasi_menit" class="form-control" min="1" required>
            </div>

            <div class="form-group">
                <label for="edit_jenis_modul">Jenis Modul / Soal</label>
                <select id="edit_jenis_modul" name="jenis_modul" class="form-control" required>
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="benar_salah">Benar / Salah (Ya / Tidak)</option>
                    <option value="essay">Essay / Uraian</option>
                </select>
            </div>

            <button type="submit" class="btn-submit-modal" id="btnSimpanEditModul">Simpan Perubahan & Urutkan</button>
        </form>
    </div>
</div>

<script>
(function() {
    const tbody = document.getElementById('tbodyModulUjian');
    const errBox = document.getElementById('masterUjianError');
    const modal = document.getElementById('modalEdit');
    const formEdit = document.getElementById('formEditModul');
    const btnCloseModal = document.getElementById('btnCloseEditModal');

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatJenis(jenis) {
        return String(jenis ?? '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, ch => ch.toUpperCase());
    }

    function showError(msg) {
        errBox.style.display = 'block';
        errBox.textContent = '❌ ' + msg;
    }

    function openEditModal(item) {
        document.getElementById('edit_id_modul').value = item.id_modul;
        document.getElementById('edit_nama_modul').value = item.nama_modul || '';
        document.getElementById('edit_kategori_posisi').value = item.kategori_posisi || 'Semua';
        document.getElementById('edit_durasi_menit').value = item.durasi_menit || 1;
        document.getElementById('edit_jenis_modul').value = item.jenis_modul || 'pilihan_ganda';
        document.getElementById('edit_urutan').value = item.urutan || 1;
        modal.style.display = 'flex';
    }

    function closeEditModal() {
        modal.style.display = 'none';
    }

    btnCloseModal.addEventListener('click', closeEditModal);
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeEditModal();
        }
    });

    function renderModulList(list) {
        if (!Array.isArray(list) || list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 30px; color: #777;">Belum ada modul ujian yang terdaftar di database. Silakan klik tombol 'Tambah Modul Tes' di atas.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(row => {
            const idM = parseInt(row.id_modul, 10);
            const urutanM = parseInt(row.urutan, 10) || 0;
            const durasiM = parseInt(row.durasi_menit, 10) || 0;
            const jsonData = encodeURIComponent(JSON.stringify({
                id_modul: idM,
                nama_modul: row.nama_modul || '',
                kategori_posisi: row.kategori_posisi || 'Semua',
                durasi_menit: durasiM,
                jenis_modul: row.jenis_modul || 'pilihan_ganda',
                urutan: urutanM
            }));

            return `
                <tr>
                    <td data-label="Urutan"><strong>#${urutanM}</strong></td>
                    <td data-label="Nama Modul"><strong>${escapeHtml(row.nama_modul || '')}</strong></td>
                    <td data-label="Kategori"><span class="badge-kategori">${escapeHtml(row.kategori_posisi || '')}</span></td>
                    <td data-label="Durasi">${durasiM} Menit</td>
                    <td data-label="Jenis Tes"><span class="badge-jenis">${escapeHtml(formatJenis(row.jenis_modul || ''))}</span></td>
                    <td data-label="Aksi">
                        <div class="aksi-modul-group">
                            <a href="tata_cara_ujian.php?id_modul=${idM}" class="btn-action btn-tatacara" title="Atur Tata Cara Ujian">📋 Tata Cara</a>
                            <a href="tambah_soal.php?id_modul=${idM}" class="btn-action btn-soal" title="Kelola Daftar Soal">❓ Kelola Soal</a>
                            <button type="button" class="btn-action btn-edit" data-edit-modul="${jsonData}">✏️ Edit</button>
                            <button type="button" class="btn-action btn-delete" data-delete-modul="${idM}">🗑️ Hapus</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadModul() {
        try {
            const res = await fetch('../api/admin/modul_ujian.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu sebagai HR/Admin!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Gagal memuat modul ujian');
            }

            renderModulList(result.data.modul_list || []);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 30px; color: #c62828;">Gagal memuat daftar modul ujian.</td></tr>`;
        }
    }

    tbody.addEventListener('click', async function(e) {
        const editBtn = e.target.closest('[data-edit-modul]');
        if (editBtn) {
            try {
                const parsed = JSON.parse(decodeURIComponent(editBtn.getAttribute('data-edit-modul')));
                openEditModal(parsed);
            } catch (_) {}
            return;
        }

        const delBtn = e.target.closest('[data-delete-modul]');
        if (delBtn) {
            const idModul = parseInt(delBtn.getAttribute('data-delete-modul'), 10);
            if (!confirm('Yakin ingin menghapus modul ini? Urutan modul di bawahnya akan otomatis dirapikan.')) return;

            try {
                const res = await fetch('../api/admin/modul_ujian.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'delete', id_modul: idModul })
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    alert(result.message || 'Modul ujian berhasil dihapus dan urutan dirapikan!');
                    loadModul();
                } else {
                    showError(result.message || 'Gagal menghapus modul.');
                }
            } catch (err) {
                showError('Terjadi kesalahan koneksi saat menghapus modul.');
            }
        }
    });

    formEdit.addEventListener('submit', async function(e) {
        e.preventDefault();
        errBox.style.display = 'none';

        const payload = {
            action: 'update',
            id_modul: parseInt(document.getElementById('edit_id_modul').value, 10),
            nama_modul: document.getElementById('edit_nama_modul').value.trim(),
            urutan: parseInt(document.getElementById('edit_urutan').value, 10),
            kategori_posisi: document.getElementById('edit_kategori_posisi').value,
            durasi_menit: parseInt(document.getElementById('edit_durasi_menit').value, 10),
            jenis_modul: document.getElementById('edit_jenis_modul').value
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
                closeEditModal();
                alert(result.message || 'Modul ujian berhasil diperbarui dan diurutkan ulang!');
                loadModul();
            } else {
                showError(result.message || 'Gagal memperbarui modul.');
            }
        } catch (err) {
            showError('Terjadi kesalahan koneksi saat menyimpan perubahan modul.');
        }
    });

    loadModul();
})();
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>