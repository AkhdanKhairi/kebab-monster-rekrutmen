<?php
// File: admin/ucapan_hasil_akhir.php (Form Keputusan & Ucapan + Pratinjau Ucapan)
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'hrd'], true)) {
    echo "<script>alert('Silakan login terlebih dahulu sebagai HR/Admin!'); window.location='../login.php';</script>";
    exit();
}

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Opsional: load PHPMailer jika sudah terpasang
$phpmailer_tersedia = false;
if (file_exists(__DIR__ . '/../phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../phpmailer/src/Exception.php';
    require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../phpmailer/src/SMTP.php';
    $phpmailer_tersedia = true;
}

// Konfigurasi SMTP jika ingin sekaligus kirim email
$smtp_host = 'smtp.gmail.com';
$smtp_user = 'email_kamu@gmail.com';
$smtp_pass = 'app_password_16_huruf';
$smtp_port = 587;

// Pastikan kolom hasil_akhir & pesan_hasil_akhir ada di tabel pelamar
$kolom_pelamar = [];
$q_cols = mysqli_query($conn, "SHOW COLUMNS FROM `pelamar`");
if ($q_cols) {
    while ($c = mysqli_fetch_assoc($q_cols)) {
        $kolom_pelamar[] = $c['Field'];
    }
}
if (!in_array('hasil_akhir', $kolom_pelamar, true)) {
    @mysqli_query($conn, "ALTER TABLE `pelamar` ADD COLUMN `hasil_akhir` VARCHAR(30) NULL");
    $kolom_pelamar[] = 'hasil_akhir';
}
if (!in_array('pesan_hasil_akhir', $kolom_pelamar, true)) {
    @mysqli_query($conn, "ALTER TABLE `pelamar` ADD COLUMN `pesan_hasil_akhir` TEXT NULL");
    $kolom_pelamar[] = 'pesan_hasil_akhir';
}

$notifikasi = "";
$error_msg  = "";

// Proses simpan keputusan akhir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_hasil_akhir'])) {
    $id_pelamar   = intval($_POST['id_pelamar'] ?? 0);
    $status_akhir = trim($_POST['status_akhir'] ?? '');
    $ucapan       = trim($_POST['ucapan'] ?? '');
    $kirim_email  = isset($_POST['kirim_email']);

    if ($id_pelamar <= 0) {
        $error_msg = "Pilih kandidat terlebih dahulu.";
    } elseif (!in_array($status_akhir, ['Diterima', 'Tidak Diterima'], true)) {
        $error_msg = "Pilih keputusan akhir: Diterima atau Tidak Diterima.";
    } elseif ($ucapan === '') {
        $error_msg = "Pesan ucapan tidak boleh kosong.";
    } else {
        $safe_status = mysqli_real_escape_string($conn, $status_akhir);
        $safe_ucapan = mysqli_real_escape_string($conn, $ucapan);

        $sql_upd = "UPDATE `pelamar`
                    SET `hasil_akhir` = '$safe_status', `pesan_hasil_akhir` = '$safe_ucapan'
                    WHERE `id_pelamar` = $id_pelamar";

        if (mysqli_query($conn, $sql_upd)) {
            $notifikasi = "Keputusan akhir berhasil disimpan sebagai <strong>" . htmlspecialchars($status_akhir) . "</strong>.";

            $q_k = mysqli_query($conn, "SELECT nama_lengkap, email FROM `pelamar` WHERE `id_pelamar` = $id_pelamar LIMIT 1");
            $kandidat_db = $q_k ? mysqli_fetch_assoc($q_k) : null;

            if ($kirim_email && $kandidat_db && !empty($kandidat_db['email'])) {
                if (!$phpmailer_tersedia) {
                    $notifikasi .= " (Email belum dikirim: folder <code>phpmailer/src/</code> belum ada).";
                } else {
                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host       = $smtp_host;
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $smtp_user;
                        $mail->Password   = $smtp_pass;
                        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = $smtp_port;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom($smtp_user, 'HRD Kebab Monster');
                        $mail->addAddress($kandidat_db['email'], $kandidat_db['nama_lengkap'] ?? 'Kandidat');

                        $mail->isHTML(true);
                        $mail->Subject = ($status_akhir === 'Diterima')
                            ? 'Ucapan Selamat Bergabung - Kebab Monster'
                            : 'Informasi Hasil Seleksi Karyawan - Kebab Monster';
                        $mail->Body    = nl2br(htmlspecialchars($ucapan));
                        $mail->AltBody = $ucapan;

                        $mail->send();
                        $notifikasi .= " Email ucapan juga terkirim ke <strong>" . htmlspecialchars($kandidat_db['email']) . "</strong>.";
                    } catch (Exception $e) {
                        $notifikasi .= " (Gagal kirim email: " . htmlspecialchars($mail->ErrorInfo) . ")";
                    }
                }
            }
        } else {
            $error_msg = "Gagal menyimpan ke database: " . mysqli_error($conn);
        }
    }
}

$pelamar_list = [];
$q_pelamar = mysqli_query($conn, "SELECT id_pelamar, nama_lengkap, email, hasil_akhir, pesan_hasil_akhir FROM `pelamar` ORDER BY nama_lengkap ASC");
if ($q_pelamar) {
    while ($row = mysqli_fetch_assoc($q_pelamar)) {
        $pelamar_list[] = $row;
    }
}

$selected_id = intval($_POST['id_pelamar'] ?? ($_GET['id_pelamar'] ?? ($pelamar_list[0]['id_pelamar'] ?? 0)));
$selected_data = null;
foreach ($pelamar_list as $p) {
    if (intval($p['id_pelamar']) === $selected_id) {
        $selected_data = $p;
        break;
    }
}

$cur_nama   = $selected_data['nama_lengkap'] ?? 'Kandidat';
$cur_email  = $selected_data['email'] ?? '-';
$cur_status = $_POST['status_akhir'] ?? ($selected_data['hasil_akhir'] ?? 'Diterima');
if (!in_array($cur_status, ['Diterima', 'Tidak Diterima'], true)) {
    $cur_status = 'Diterima';
}

$default_terima = "Selamat {$cur_nama},\n\nAnda dinyatakan DITERIMA sebagai karyawan Kebab Monster.\nTerima kasih atas usaha dan proses seleksi yang telah Anda jalani. Selamat bergabung bersama tim kami!";
$default_tolak  = "Halo {$cur_nama},\n\nTerima kasih telah mengikuti proses seleksi karyawan di Kebab Monster. Setelah pertimbangan, saat ini Anda belum dinyatakan DITERIMA untuk bergabung.\n\nSemoga sukses selalu untuk langkah karier Anda berikutnya.";

$cur_ucapan = $_POST['ucapan'] ?? ($selected_data['pesan_hasil_akhir'] ?? '');
if (trim($cur_ucapan) === '') {
    $cur_ucapan = ($cur_status === 'Diterima') ? $default_terima : $default_tolak;
}

// Deteksi otomatis lokasi file API ucapan (ucapan_hasl_akhir.php atau ucapan_hasil_akhir.php)
$api_candidates = [];
foreach (['ucapan_hasl_akhir.php', 'ucapan_hasil_akhir.php'] as $api_file) {
    if (file_exists(__DIR__ . '/../api/' . $api_file)) {
        $api_candidates[] = '../api/' . $api_file;
    }
    if (file_exists(__DIR__ . '/../api/admin/' . $api_file)) {
        $api_candidates[] = '../api/admin/' . $api_file;
    }
}
if (empty($api_candidates)) {
    $api_candidates = [
        '../api/ucapan_hasl_akhir.php',
        '../api/admin/ucapan_hasl_akhir.php',
        '../api/ucapan_hasil_akhir.php',
        '../api/admin/ucapan_hasil_akhir.php'
    ];
}

mysqli_close($conn);
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';
?>

<style>
    .ha-page {
        max-width: 1120px;
        margin: 22px auto 50px auto;
        padding: 0 20px;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        color: #1e293b;
    }
    .ha-hero {
        background: linear-gradient(125deg, #064e3b 0%, #047857 55%, #10b981 100%);
        color: #ffffff;
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 20px;
        box-shadow: 0 10px 25px -5px rgba(4, 120, 87, 0.28);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .ha-hero h1 { margin: 0 0 6px 0; font-size: 22px; font-weight: 800; }
    .ha-hero p { margin: 0; font-size: 13.5px; color: #d1fae5; }
    .btn-back-rekap {
        text-decoration: none;
        background: rgba(255, 255, 255, 0.18);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 9px 15px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
    }
    .btn-back-rekap:hover { background: rgba(255, 255, 255, 0.28); }

    .ha-alert-ok {
        background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46;
        padding: 13px 16px; border-radius: 12px; margin-bottom: 18px; font-size: 13.5px;
    }
    .ha-alert-err {
        background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b;
        padding: 13px 16px; border-radius: 12px; margin-bottom: 18px; font-size: 13.5px;
    }

    .ha-grid {
        display: grid;
        grid-template-columns: 1.25fr 0.85fr;
        gap: 20px;
        align-items: start;
    }
    .ha-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .ha-card-title {
        margin: 0 0 18px 0;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        padding-bottom: 12px;
        border-bottom: 1px solid #f1f5f9;
    }

    .ha-field { margin-bottom: 16px; }
    .ha-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .ha-select, .ha-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 11px 13px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        background: #ffffff;
    }
    .ha-select:focus, .ha-textarea:focus {
        outline: none;
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .status-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .status-option {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 13px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        font-size: 13.5px;
        transition: all 0.2s ease;
        user-select: none;
    }
    .status-option input { display: none; }
    .status-option.terima.active {
        border-color: #10b981;
        background: #ecfdf5;
        color: #065f46;
    }
    .status-option.tolak.active {
        border-color: #ef4444;
        background: #fef2f2;
        color: #991b1b;
    }

    .email-check {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 11px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
    }
    .email-check input { width: 16px; height: 16px; accent-color: #059669; }

    .btn-ha-submit {
        width: 100%;
        background: linear-gradient(135deg, #047857, #059669);
        color: #ffffff;
        border: none;
        padding: 13px 20px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.25);
    }

    .preview-box {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }
    .preview-top {
        padding: 14px 18px;
        color: #ffffff;
        font-weight: 800;
        font-size: 13.5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .preview-top.is-terima { background: linear-gradient(135deg, #047857, #10b981); }
    .preview-top.is-tolak  { background: linear-gradient(135deg, #991b1b, #ef4444); }
    .preview-body {
        padding: 18px;
        font-size: 13.5px;
        line-height: 1.65;
        color: #1e293b;
        white-space: pre-line;
        background: #ffffff;
        min-height: 210px;
    }
    .preview-foot {
        padding: 11px 18px;
        background: #f1f5f9;
        font-size: 12px;
        color: #475569;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 960px) {
        .ha-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 560px) {
        .status-cards { grid-template-columns: 1fr; }
    }
</style>

<div class="ha-page">
    <div class="ha-hero">
        <div>
            <h1>Ucapan Hasil Akhir</h1>
            <p>Tetapkan status diterima atau tidak diterima dan susun ucapan untuk <strong><?php echo htmlspecialchars($cur_nama); ?></strong>.</p>
        </div>
        <a href="hasil_akhir.php" class="btn-back-rekap">← Kembali ke Rekap Status</a>
    </div>

    <div id="ucapanApiAlertBox">
        <?php if (!empty($notifikasi)): ?>
            <div class="ha-alert-ok"><?php echo $notifikasi; ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="ha-alert-err"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>
    </div>

    <div class="ha-grid">
        <div class="ha-card">
            <h2 class="ha-card-title">✍️ Form Keputusan & Ucapan</h2>

            <form method="POST" action="" id="formUcapanHasilAkhir">
                <div class="ha-field">
                    <label for="id_pelamar">Pilih Kandidat Pelamar</label>
                    <select name="id_pelamar" id="id_pelamar" class="ha-select" onchange="gantiPelamar(this.value)">
                        <?php foreach ($pelamar_list as $p): ?>
                            <option value="<?php echo intval($p['id_pelamar']); ?>" <?php echo intval($p['id_pelamar']) === $selected_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_lengkap'] . (!empty($p['email']) ? " — {$p['email']}" : "")); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ha-field">
                    <label>Keputusan Hasil Akhir</label>
                    <div class="status-cards">
                        <label class="status-option terima <?php echo $cur_status === 'Diterima' ? 'active' : ''; ?>" id="lbl_terima">
                            <input type="radio" name="status_akhir" value="Diterima" <?php echo $cur_status === 'Diterima' ? 'checked' : ''; ?> onchange="setKeputusanUI('Diterima')">
                            <span>🎉 Diterima</span>
                        </label>
                        <label class="status-option tolak <?php echo $cur_status === 'Tidak Diterima' ? 'active' : ''; ?>" id="lbl_tolak">
                            <input type="radio" name="status_akhir" value="Tidak Diterima" <?php echo $cur_status === 'Tidak Diterima' ? 'checked' : ''; ?> onchange="setKeputusanUI('Tidak Diterima')">
                            <span>🙏 Tidak Diterima</span>
                        </label>
                    </div>
                </div>

                <div class="ha-field">
                    <label for="ucapan">Pesan Ucapan Hasil Akhir</label>
                    <textarea name="ucapan" id="ucapan" rows="7" class="ha-textarea" oninput="updatePreview()" required><?php echo htmlspecialchars($cur_ucapan); ?></textarea>
                </div>

                <div class="ha-field">
                    <label class="email-check">
                        <input type="checkbox" name="kirim_email" value="1">
                        <span>Kirim juga ucapan ini ke email pelamar (<?php echo htmlspecialchars($cur_email); ?>)</span>
                    </label>
                </div>

                <button type="submit" name="simpan_hasil_akhir" class="btn-ha-submit">
                    💾 Simpan Keputusan & Ucapan
                </button>
            </form>
        </div>

        <div class="ha-card">
            <h2 class="ha-card-title">👀 Pratinjau Ucapan</h2>
            <div class="preview-box">
                <div class="preview-top <?php echo $cur_status === 'Diterima' ? 'is-terima' : 'is-tolak'; ?>" id="previewTop">
                    <span id="previewBadge"><?php echo $cur_status === 'Diterima' ? '🎉 DITERIMA SEBAGAI KARYAWAN' : '🙏 BELUM DITERIMA'; ?></span>
                    <span style="font-size:11.5px; opacity:0.9;">Kebab Monster</span>
                </div>
                <div class="preview-body" id="previewBody"><?php echo htmlspecialchars($cur_ucapan); ?></div>
                <div class="preview-foot">
                    Tujuan: <strong><?php echo htmlspecialchars($cur_nama); ?></strong> • <?php echo htmlspecialchars($cur_email); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const namaPelamarAktif = <?php echo json_encode($cur_nama); ?>;
const ucapanApiEndpoints = <?php echo json_encode(array_values(array_unique($api_candidates))); ?>;

function gantiPelamar(id) {
    window.location.href = '?id_pelamar=' + encodeURIComponent(id);
}

function setKeputusanUI(status) {
    const lblTerima = document.getElementById('lbl_terima');
    const lblTolak  = document.getElementById('lbl_tolak');
    const ta        = document.getElementById('ucapan');

    lblTerima.classList.remove('active');
    lblTolak.classList.remove('active');

    if (status === 'Diterima') {
        lblTerima.classList.add('active');
        ta.value = `Selamat ${namaPelamarAktif},\n\nAnda dinyatakan DITERIMA sebagai karyawan Kebab Monster.\nTerima kasih atas usaha dan proses seleksi yang telah Anda jalani. Selamat bergabung bersama tim kami!`;
    } else {
        lblTolak.classList.add('active');
        ta.value = `Halo ${namaPelamarAktif},\n\nTerima kasih telah mengikuti proses seleksi karyawan di Kebab Monster. Setelah pertimbangan, saat ini Anda belum dinyatakan DITERIMA untuk bergabung.\n\nSemoga sukses selalu untuk langkah karier Anda berikutnya.`;
    }
    updatePreview();
}

function updatePreview() {
    const isTerima = document.querySelector('input[name="status_akhir"]:checked')?.value === 'Diterima';
    const topEl    = document.getElementById('previewTop');
    const badgeEl  = document.getElementById('previewBadge');
    const bodyEl   = document.getElementById('previewBody');
    const taVal    = document.getElementById('ucapan').value;

    topEl.classList.remove('is-terima', 'is-tolak');
    if (isTerima) {
        topEl.classList.add('is-terima');
        badgeEl.textContent = '🎉 DITERIMA SEBAGAI KARYAWAN';
    } else {
        topEl.classList.add('is-tolak');
        badgeEl.textContent = '🙏 BELUM DITERIMA';
    }
    bodyEl.textContent = taVal;
}

function submitViaPhpFallback(form) {
    if (!form.querySelector('input[name="simpan_hasil_akhir"][type="hidden"]')) {
        const hiddenSubmit = document.createElement('input');
        hiddenSubmit.type = 'hidden';
        hiddenSubmit.name = 'simpan_hasil_akhir';
        hiddenSubmit.value = '1';
        form.appendChild(hiddenSubmit);
    }
    form.submit();
}

document.getElementById('formUcapanHasilAkhir').addEventListener('submit', async function(e) {
    const form = this;
    const kirimEmailChecked = !!form.querySelector('input[name="kirim_email"]')?.checked;

    if (!window.fetch || kirimEmailChecked) {
        return;
    }

    e.preventDefault();

    const idPelamar = parseInt(document.getElementById('id_pelamar')?.value || '0', 10);
    const statusAkhir = String(document.querySelector('input[name="status_akhir"]:checked')?.value || '');
    const ucapan = String(document.getElementById('ucapan')?.value || '').trim();

    const submitBtn = form.querySelector('.btn-ha-submit');
    if (submitBtn) submitBtn.disabled = true;

    try {
        let data = null;
        let foundEndpoint = false;

        for (const url of ucapanApiEndpoints) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_pelamar: idPelamar,
                    status_akhir: statusAkhir,
                    ucapan: ucapan
                })
            });
            if (res.status === 404) {
                continue;
            }
            foundEndpoint = true;
            data = await res.json();
            break;
        }

        if (!foundEndpoint) {
            submitViaPhpFallback(form);
            return;
        }

        if (!data || !data.success) {
            throw new Error((data && data.message) || 'Gagal menyimpan keputusan dan ucapan.');
        }

        const alertBox = document.getElementById('ucapanApiAlertBox');
        if (alertBox) {
            alertBox.innerHTML = '<div class="ha-alert-ok">' +
                String(data.message || 'Keputusan akhir berhasil disimpan.').replace(/</g, '&lt;') +
                '</div>';
        }
        if (submitBtn) submitBtn.disabled = false;
        updatePreview();
    } catch (err) {
        if (submitBtn) submitBtn.disabled = false;
        submitViaPhpFallback(form);
    }
});
</script>

<?php
include '../includes/footer_admin.php';
?>