<?php
// Nama File: admin/detail_koreksi_essay.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_api_request = (
    (isset($_GET['api']) && $_GET['api'] == '1') ||
    (isset($_POST['api']) && $_POST['api'] == '1') ||
    (isset($_GET['format']) && strtolower((string)$_GET['format']) === 'json') ||
    (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false && !isset($_POST['simpan_koreksi_modul']) && empty($_GET))
);

// Cek sesi login HR / Admin
$role_sess = strtolower(trim((string)($_SESSION['role'] ?? '')));
if (!isset($_SESSION['user_id']) || !in_array($role_sess, ['admin', 'hr', 'hrd'], true)) {
    if ($is_api_request) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu sebagai HR/Admin!']);
        exit();
    }
    echo "<script>alert('Silakan login terlebih dahulu sebagai HR/Admin!'); window.location='../login.php';</script>";
    exit();
}

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    if ($is_api_request) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
        exit();
    }
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id_pelamar      = intval($_GET['id_pelamar'] ?? ($_POST['id_pelamar'] ?? 0));
$id_hasil        = intval($_GET['id_hasil'] ?? ($_POST['id_hasil'] ?? 0));
$id_modul_pilih  = intval($_GET['id_modul'] ?? ($_POST['id_modul'] ?? 0));

if ($id_pelamar <= 0) {
    if ($is_api_request) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data pelamar tidak ditemukan!']);
        exit();
    }
    echo "<script>alert('Data pelamar tidak ditemukan!'); window.location='koreksi_hasil.php';</script>";
    exit();
}

function deteksiKategoriDariTeks(string $teks): string {
    $v = strtolower(trim($teks));
    if ($v === '' || in_array($v, ['all', 'semua', 'umum', 'semua kategori', 'all kategori'], true)) {
        return 'semua';
    }
    if (preg_match('/\b(crew|kru|outlet|kitchen|dapur|kasir|cashier|barista|cook|server|pelayan|pramusaji|store)\b/i', $v)) {
        return 'crew';
    }
    if (preg_match('/\b(staff|staf|admin|administrasi|office|finance|akuntansi|accounting|hr|hrd|marketing|spv|supervisor|manager|manajer|it|desain|design|purchasing|gudang|warehouse|bi|qa)\b/i', $v)) {
        return 'staff';
    }
    return 'semua';
}

$pesan_sukses = "";
$pesan_error  = "";

// --- 1. DETEKSI OTOMATIS STRUKTUR TABEL DATABASE ---
$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
if ($q_tables) {
    while ($t = mysqli_fetch_row($q_tables)) {
        $all_tables[] = $t[0];
    }
}

// A. Tabel Hasil Psikotes
$tbl_hasil = "hasil_psikotes";
foreach ($all_tables as $tbl) {
    if (preg_match('/(hasil_psikotes|hasil_ujian|nilai_psikotes|hasil_tes)/i', $tbl)) {
        $tbl_hasil = $tbl;
        break;
    }
}
$cols_hasil = [];
$q_ch = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_hasil`");
if ($q_ch) while ($c = mysqli_fetch_assoc($q_ch)) $cols_hasil[] = $c['Field'];
$pk_hasil   = $cols_hasil[0] ?? 'id_hasil';
$fk_h_pel   = in_array('id_pelamar', $cols_hasil) ? 'id_pelamar' : 'pelamar_id';
$col_h_skor = in_array('skor_akhir', $cols_hasil) ? 'skor_akhir' : (in_array('nilai', $cols_hasil) ? 'nilai' : 'skor');
$col_h_stat = in_array('status_lulus', $cols_hasil) ? 'status_lulus' : (in_array('status', $cols_hasil) ? 'status' : '');

// B. Tabel Modul (`modul_ujian`)
$tbl_modul      = in_array('modul_ujian', $all_tables) ? 'modul_ujian' : '';
$pk_modul       = 'id_modul';
$col_nama_modul = 'nama_modul';
$col_desc_modul = '';
$cols_modul     = [];
if (!empty($tbl_modul)) {
    $q_cm = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_modul`");
    while ($cm = mysqli_fetch_assoc($q_cm)) {
        $cols_modul[] = $cm['Field'];
    }
    if (!empty($cols_modul)) {
        $pk_modul = $cols_modul[0];
    }
    // Prioritaskan nama kolom judul/nama modul yang persis agar tidak tertimpa kolom lain
    foreach (['nama_modul', 'judul_modul', 'nama', 'judul', 'title', 'topik', 'sub_judul'] as $cand_nm) {
        if (in_array($cand_nm, $cols_modul, true)) {
            $col_nama_modul = $cand_nm;
            break;
        }
    }
    // Deteksi kolom deskripsi / keterangan / materi modul jika ada
    foreach (['deskripsi', 'keterangan', 'materi', 'topik', 'sub_judul', 'catatan'] as $cand_desc) {
        if ($cand_desc !== $col_nama_modul && in_array($cand_desc, $cols_modul, true)) {
            $col_desc_modul = $cand_desc;
            break;
        }
    }
}

// C. Tabel Soal
$tbl_soal  = "";
$cols_soal = [];
foreach ($all_tables as $tbl) {
    if (preg_match('/(soal|pertanyaan|bank_soal)/i', $tbl) && !preg_match('/(jawaban|hasil|riwayat)/i', $tbl)) {
        $tbl_soal = $tbl;
        break;
    }
}
if (!empty($tbl_soal)) {
    $q_cs = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_soal`");
    while ($c = mysqli_fetch_assoc($q_cs)) $cols_soal[] = $c['Field'];
}

$pk_soal   = $cols_soal[0] ?? 'id_soal';
$col_tanya = 'pertanyaan';
foreach (['pertanyaan', 'soal', 'isi_soal', 'teks_soal'] as $cand) {
    if (in_array($cand, $cols_soal)) { $col_tanya = $cand; break; }
}
$col_tipe = '';
foreach (['tipe_soal', 'jenis_soal', 'tipe', 'jenis', 'kategori_soal', 'bentuk_soal'] as $cand) {
    if (in_array($cand, $cols_soal)) { $col_tipe = $cand; break; }
}
$col_kunci = '';
foreach (['kunci_jawaban', 'kunci', 'jawaban_benar', 'kunci_pg'] as $cand) {
    if (in_array($cand, $cols_soal)) { $col_kunci = $cand; break; }
}
$col_bobot = '';
foreach (['bobot_nilai', 'bobot', 'poin', 'skor_soal', 'nilai'] as $cand) {
    if (in_array($cand, $cols_soal)) { $col_bobot = $cand; break; }
}
$col_flag_alasan = '';
foreach ($cols_soal as $cs) {
    if (preg_match('/(alasan|penjelasan|uraian|essay)/i', $cs) && !preg_match('/^(id_|pertanyaan|soal)/i', $cs)) {
        $col_flag_alasan = $cs;
        break;
    }
}
$fk_modul_soal = in_array('id_modul', $cols_soal) ? 'id_modul' : '';

// D. Tabel Jawaban Pelamar
$tbl_jawaban  = "";
$cols_jawaban = [];
foreach ($all_tables as $tbl) {
    if ($tbl === $tbl_hasil || $tbl === $tbl_soal || $tbl === $tbl_modul || $tbl === 'riwayat_ujian_pelamar') continue;
    if (preg_match('/(jawaban|detail_ujian|detail_tes|respon)/i', $tbl)) {
        $cols_tmp = [];
        $q_tmp = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
        while ($c = mysqli_fetch_assoc($q_tmp)) $cols_tmp[] = $c['Field'];
        if ((in_array('id_soal', $cols_tmp) || in_array('soal_id', $cols_tmp)) &&
            (in_array('id_pelamar', $cols_tmp) || in_array('pelamar_id', $cols_tmp))) {
            $tbl_jawaban  = $tbl;
            $cols_jawaban = $cols_tmp;
            break;
        }
    }
}
if (empty($tbl_jawaban)) {
    foreach ($all_tables as $tbl) {
        if ($tbl === $tbl_hasil || $tbl === $tbl_soal) continue;
        $cols_tmp = [];
        $q_tmp = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
        while ($c = mysqli_fetch_assoc($q_tmp)) $cols_tmp[] = $c['Field'];
        if ((in_array('id_soal', $cols_tmp) || in_array('soal_id', $cols_tmp)) &&
            (in_array('id_pelamar', $cols_tmp) || in_array('pelamar_id', $cols_tmp))) {
            $tbl_jawaban  = $tbl;
            $cols_jawaban = $cols_tmp;
            break;
        }
    }
}

if (!empty($tbl_jawaban)) {
    if (!in_array('persentase_essay', $cols_jawaban)) {
        @mysqli_query($conn, "ALTER TABLE `$tbl_jawaban` ADD `persentase_essay` INT(3) DEFAULT 0");
        $cols_jawaban[] = 'persentase_essay';
    }
    if (!in_array('nilai_diperoleh', $cols_jawaban)) {
        @mysqli_query($conn, "ALTER TABLE `$tbl_jawaban` ADD `nilai_diperoleh` DECIMAL(5,2) DEFAULT 0.00");
        $cols_jawaban[] = 'nilai_diperoleh';
    }
    if (!in_array('catatan_koreksi', $cols_jawaban)) {
        @mysqli_query($conn, "ALTER TABLE `$tbl_jawaban` ADD `catatan_koreksi` VARCHAR(255) NULL");
        $cols_jawaban[] = 'catatan_koreksi';
    }
    if (!in_array('is_dikoreksi', $cols_jawaban)) {
        @mysqli_query($conn, "ALTER TABLE `$tbl_jawaban` ADD `is_dikoreksi` TINYINT(1) DEFAULT 0");
        $cols_jawaban[] = 'is_dikoreksi';
    }
}

$pk_jawaban   = $cols_jawaban[0] ?? 'id_jawaban';
$fk_j_pelamar = in_array('id_pelamar', $cols_jawaban) ? 'id_pelamar' : 'pelamar_id';
$fk_j_soal    = in_array('id_soal', $cols_jawaban) ? 'id_soal' : 'soal_id';

$kandidat_kolom_jawaban = [];
$col_alasan_jawaban     = '';
foreach ($cols_jawaban as $cj) {
    if (preg_match('/(alasan|penjelasan|keterangan_jawaban|argumen)/i', $cj) && !preg_match('/^(id_|catatan_koreksi)/i', $cj)) {
        $col_alasan_jawaban = $cj;
        continue;
    }
    if (preg_match('/(jawaban|pilihan|opsi|respon|isi|teks|essay)/i', $cj) && !preg_match('/^(id_|is_|status|catatan|persentase|nilai)/i', $cj)) {
        $kandidat_kolom_jawaban[] = "NULLIF(TRIM(j.`$cj`), '')";
    }
}
$expr_jawaban_pelamar = !empty($kandidat_kolom_jawaban)
    ? "COALESCE(" . implode(", ", $kandidat_kolom_jawaban) . ", '')"
    : "''";
$expr_alasan_pelamar = !empty($col_alasan_jawaban)
    ? "COALESCE(NULLIF(TRIM(j.`$col_alasan_jawaban`), ''), '')"
    : "''";

// Helper klasifikasi soal
function klasifikasiSoal($tipe_raw, $kunci_str, $jwb_user, $alasan_user, $flag_alasan) {
    $tipe_l  = strtolower(trim((string)$tipe_raw));
    $kunci_l = strtolower(trim((string)$kunci_str));
    $jwb_l   = strtolower(trim((string)$jwb_user));
    $flag_l  = strtolower(trim((string)$flag_alasan));

    $is_bs = (
        strpos($tipe_l, 'benar') !== false ||
        strpos($tipe_l, 'salah') !== false ||
        strpos($tipe_l, 'true') !== false ||
        $tipe_l === 'bs' || $tipe_l === 'b/s' ||
        in_array($kunci_l, ['benar', 'salah', 'true', 'false'])
    );

    $is_essay = (
        strpos($tipe_l, 'essay') !== false ||
        strpos($tipe_l, 'esai') !== false ||
        strpos($tipe_l, 'uraian') !== false ||
        strpos($tipe_l, 'isian') !== false ||
        strpos($tipe_l, 'studi') !== false ||
        strpos($tipe_l, 'kasus') !== false
    );

    if (!$is_bs && !$is_essay) {
        $k_up = strtoupper(trim((string)$kunci_str));
        if ($tipe_l === '' && !in_array($k_up, ['A', 'B', 'C', 'D', 'E', '1', '2', '3', '4', '5'])) {
            $is_essay = true;
        }
    }

    $bs_alasan = false;
    if ($is_bs) {
        if (trim((string)$alasan_user) !== '' || in_array($flag_l, ['1', 'ya', 'yes', 'true', 'wajib']) ||
            (trim((string)$jwb_user) !== '' && !in_array($jwb_l, ['benar', 'salah', 'true', 'false', 'b', 's']))) {
            $bs_alasan = true;
        }
    }

    return [
        'is_bs'        => $is_bs,
        'is_essay'     => $is_essay,
        'bs_alasan'    => $bs_alasan,
        'pakai_slider' => ($is_essay || ($is_bs && $bs_alasan)),
    ];
}

// --- 3. AMBIL DATA PROFIL PELAMAR & POSISI LAMARAN ---
$q_pel = mysqli_query($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = $id_pelamar LIMIT 1");
$pelamar = $q_pel ? (mysqli_fetch_assoc($q_pel) ?: ['nama_lengkap' => 'Pelamar', 'email' => '-']) : ['nama_lengkap' => 'Pelamar', 'email' => '-'];

$posisi_dilamar   = '';
$kategori_pelamar = 'semua';
$tbl_lowongan     = in_array('lowongan', $all_tables) ? 'lowongan' : (in_array('lowongan_kerja', $all_tables) ? 'lowongan_kerja' : '');

if (in_array('lamaran', $all_tables)) {
    $q_lam_raw = mysqli_query($conn, "SELECT * FROM `lamaran` WHERE `id_pelamar` = $id_pelamar ORDER BY 1 DESC LIMIT 1");
    if ($q_lam_raw && $row_lam = mysqli_fetch_assoc($q_lam_raw)) {
        foreach ($row_lam as $col_k => $col_v) {
            if (is_string($col_v) && !preg_match('/^(id_|status|tanggal|waktu|created|updated|file|cv|foto)/i', $col_k)) {
                $kat_cek = deteksiKategoriDariTeks($col_v);
                if ($kat_cek !== 'semua') {
                    $posisi_dilamar   = $col_v;
                    $kategori_pelamar = $kat_cek;
                    break;
                }
            }
        }
        $id_low = intval($row_lam['id_lowongan'] ?? 0);
        if ($id_low > 0 && !empty($tbl_lowongan)) {
            $q_low = mysqli_query($conn, "SELECT * FROM `$tbl_lowongan` WHERE `id_lowongan` = $id_low LIMIT 1");
            if ($q_low && $row_low = mysqli_fetch_assoc($q_low)) {
                $posisi_low = (string)($row_low['posisi_pekerjaan'] ?? ($row_low['posisi'] ?? ($row_low['judul_lowongan'] ?? ($row_low['nama_lowongan'] ?? ($row_low['nama_posisi'] ?? ($row_low['divisi'] ?? ($row_low['kategori'] ?? '')))))));
                if ($posisi_low !== '') {
                    $posisi_dilamar = $posisi_low;
                }
                $kat_low = deteksiKategoriDariTeks($posisi_low . ' ' . ($row_low['divisi'] ?? '') . ' ' . ($row_low['kategori'] ?? ''));
                if ($kat_low !== 'semua') {
                    $kategori_pelamar = $kat_low;
                }
            }
        }
    }
}

if ($kategori_pelamar === 'semua' && is_array($pelamar)) {
    foreach ($pelamar as $pk => $pv) {
        if (is_string($pv) && preg_match('/(posisi|jabatan|kategori|tipe|role|bagian)/i', $pk)) {
            $kat_p = deteksiKategoriDariTeks($pv);
            if ($kat_p !== 'semua') {
                if ($posisi_dilamar === '') {
                    $posisi_dilamar = $pv;
                }
                $kategori_pelamar = $kat_p;
                break;
            }
        }
    }
}

$where_h = ($id_hasil > 0) ? "`$pk_hasil` = $id_hasil" : "`$fk_h_pel` = $id_pelamar ORDER BY `$pk_hasil` DESC LIMIT 1";
$q_h_row = mysqli_query($conn, "SELECT * FROM `$tbl_hasil` WHERE $where_h");
$data_hasil = $q_h_row ? mysqli_fetch_assoc($q_h_row) : null;
if ($data_hasil && $id_hasil <= 0) {
    $id_hasil = intval($data_hasil[$pk_hasil]);
}

$skor_akhir_baru = floatval($data_hasil[$col_h_skor] ?? 0);

// --- 2. PROSES SIMPAN NILAI KOREKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['simpan_koreksi_modul']) || $is_api_request) && !empty($tbl_jawaban)) {
    $persen_arr  = $_POST['persentase'] ?? [];
    $bobot_arr   = $_POST['bobot_soal'] ?? [];
    $catatan_arr = $_POST['catatan_koreksi'] ?? [];
    $nama_mod_sv = $_POST['nama_modul_simpan'] ?? 'Modul';

    foreach ($persen_arr as $id_jwb_raw => $persen_val_raw) {
        $id_jwb = intval($id_jwb_raw);
        $persen = max(0, min(100, intval($persen_val_raw)));
        $bobot  = floatval($bobot_arr[$id_jwb] ?? 10);
        $cat_k  = mysqli_real_escape_string($conn, $catatan_arr[$id_jwb] ?? '');

        $poin_didapat = round(($persen / 100) * $bobot, 2);

        mysqli_query($conn, "UPDATE `$tbl_jawaban` 
                             SET `persentase_essay` = $persen, 
                                 `nilai_diperoleh` = $poin_didapat, 
                                 `catatan_koreksi` = '$cat_k',
                                 `is_dikoreksi` = 1
                             WHERE `$pk_jawaban` = $id_jwb");
    }

    $q_recalc = mysqli_query($conn, "
        SELECT j.*,
               $expr_jawaban_pelamar AS teks_jwb,
               $expr_alasan_pelamar AS teks_alasan,
               " . (!empty($col_tipe) ? "s.`$col_tipe` AS tipe_raw," : "'' AS tipe_raw,") . "
               " . (!empty($col_kunci) ? "s.`$col_kunci` AS kunci_jwb," : "'' AS kunci_jwb,") . "
               " . (!empty($col_flag_alasan) ? "s.`$col_flag_alasan` AS flag_alasan," : "'' AS flag_alasan,") . "
               " . (!empty($col_bobot) ? "COALESCE(NULLIF(s.`$col_bobot`, 0), 10) AS bobot_s" : "10 AS bobot_s") . "
        FROM `$tbl_jawaban` j
        INNER JOIN `$tbl_soal` s ON j.`$fk_j_soal` = s.`$pk_soal`
        WHERE j.`$fk_j_pelamar` = $id_pelamar
    ");

    $total_poin_semua  = 0;
    $total_bobot_semua = 0;

    if ($q_recalc) {
        while ($rc = mysqli_fetch_assoc($q_recalc)) {
            $bobot_rc = floatval($rc['bobot_s'] ?? 10);
            $total_bobot_semua += $bobot_rc;

            $klas = klasifikasiSoal($rc['tipe_raw'], $rc['kunci_jwb'], $rc['teks_jwb'], $rc['teks_alasan'], $rc['flag_alasan']);
            if ($klas['pakai_slider']) {
                $persen_rc = intval($rc['persentase_essay'] ?? 0);
                $total_poin_semua += round(($persen_rc / 100) * $bobot_rc, 2);
            } else {
                if (!empty($rc['kunci_jwb']) && strcasecmp(trim($rc['teks_jwb']), trim($rc['kunci_jwb'])) === 0) {
                    $total_poin_semua += $bobot_rc;
                }
            }
        }
    }

    $skor_akhir_baru = ($total_bobot_semua > 0) ? round(($total_poin_semua / $total_bobot_semua) * 100, 2) : 0;

    if ($id_hasil > 0) {
        $st_otomatis = ($skor_akhir_baru >= 70) ? 'Lulus' : 'Tidak Lulus';
        $upd_h = "`$col_h_skor` = $skor_akhir_baru";
        if (!empty($col_h_stat) && isset($_POST['auto_status_lulus'])) {
            $upd_h .= ", `$col_h_stat` = '$st_otomatis'";
        }
        mysqli_query($conn, "UPDATE `$tbl_hasil` SET $upd_h WHERE `$pk_hasil` = $id_hasil");
    }

    if (isset($_POST['kembali_daftar_modul'])) {
        $id_modul_pilih = 0;
    }

    $pesan_sukses = "Nilai koreksi <strong>" . htmlspecialchars($nama_mod_sv) . "</strong> berhasil disimpan! Skor akhir gabungan: <strong>" . number_format($skor_akhir_baru, 1) . "</strong>.";
}

// --- 4. AMBIL DAFTAR MODUL TERPISAH (ALL TAMPIL DI SEMUA; CREW VS STAFF DIFILTER KETAT) ---
$daftar_modul_kartu = [];
if (!empty($tbl_modul)) {
    $order_mod = in_array('urutan', $cols_modul) ? "ORDER BY `urutan` ASC, `$pk_modul` ASC" : "ORDER BY `$pk_modul` ASC";
    $q_all_mod = mysqli_query($conn, "SELECT * FROM `$tbl_modul` $order_mod");
    if ($q_all_mod) {
        while ($m = mysqli_fetch_assoc($q_all_mod)) {
            $idm = intval($m[$pk_modul]);

            $kat_mod = 'semua';
            foreach ($m as $m_col => $m_val) {
                if (!is_string($m_val) || preg_match('/^(id_|deskripsi|instruksi|created|updated)/i', $m_col)) {
                    continue;
                }
                $val_low = strtolower(trim($m_val));
                if (in_array($val_low, ['all', 'semua', 'umum'], true)) {
                    $kat_mod = 'semua';
                    break;
                }
                if (preg_match('/\b(crew|kru)\b/i', $val_low)) {
                    $kat_mod = 'crew';
                    break;
                }
                if (preg_match('/\b(staff|staf)\b/i', $val_low)) {
                    $kat_mod = 'staff';
                    break;
                }
            }

            $nama_mod_str = trim((string)($m[$col_nama_modul] ?? ''));
            $desc_mod_str = ($col_desc_modul !== '') ? trim((string)($m[$col_desc_modul] ?? '')) : '';
            $nama_mod_low = strtolower($nama_mod_str);

            if ($kat_mod === 'semua') {
                if (preg_match('/:\s*(all|semua|umum)\s*$/i', $nama_mod_low)) {
                    $kat_mod = 'semua';
                } elseif (preg_match('/(:\s*(crew|kru)\b|pengetahuan dasar f&b|situasi pelayanan pelanggan|pelayanan pelanggan|operasional outlet)/i', $nama_mod_low)) {
                    $kat_mod = 'crew';
                } elseif (preg_match('/(:\s*(staff|staf)\b|administrasi|ketelitian|komunikasi bisnis|manajerial|office)/i', $nama_mod_low)) {
                    $kat_mod = 'staff';
                }
            }

            // Jika kategori 'semua' (All), selalu tampil untuk semua pelamar.
            // Jika pelamar 'staff', sembunyikan modul khusus 'crew'.
            // Jika pelamar 'crew', sembunyikan modul khusus 'staff'.
            if ($kategori_pelamar === 'staff' && $kat_mod === 'crew') {
                continue;
            }
            if ($kategori_pelamar === 'crew' && $kat_mod === 'staff') {
                continue;
            }

            $status_pengerjaan = 'Belum Mulai';
            if (in_array('riwayat_ujian_pelamar', $all_tables)) {
                $q_riw = mysqli_query($conn, "SELECT status_pengerjaan FROM riwayat_ujian_pelamar WHERE id_pelamar = $id_pelamar AND id_modul = $idm LIMIT 1");
                if ($q_riw && $rw = mysqli_fetch_assoc($q_riw)) {
                    $status_pengerjaan = $rw['status_pengerjaan'] ?? 'Belum Mulai';
                }
            }

            $jml_jawaban_mod = 0;
            $jml_slider_mod  = 0;
            $jml_koreksi_mod = 0;
            $poin_mod        = 0;
            $bobot_mod       = 0;

            if (!empty($tbl_jawaban) && !empty($tbl_soal) && !empty($fk_modul_soal)) {
                $q_jm = mysqli_query($conn, "
                    SELECT j.*,
                           $expr_jawaban_pelamar AS teks_jwb,
                           $expr_alasan_pelamar AS teks_alasan,
                           " . (!empty($col_tipe) ? "s.`$col_tipe` AS tipe_raw," : "'' AS tipe_raw,") . "
                           " . (!empty($col_kunci) ? "s.`$col_kunci` AS kunci_jwb," : "'' AS kunci_jwb,") . "
                           " . (!empty($col_flag_alasan) ? "s.`$col_flag_alasan` AS flag_alasan," : "'' AS flag_alasan,") . "
                           " . (!empty($col_bobot) ? "COALESCE(NULLIF(s.`$col_bobot`, 0), 10) AS bobot_s" : "10 AS bobot_s") . "
                    FROM `$tbl_jawaban` j
                    INNER JOIN `$tbl_soal` s ON j.`$fk_j_soal` = s.`$pk_soal`
                    WHERE j.`$fk_j_pelamar` = $id_pelamar AND s.`$fk_modul_soal` = $idm
                ");
                if ($q_jm) {
                    while ($jm = mysqli_fetch_assoc($q_jm)) {
                        $jml_jawaban_mod++;
                        if ($status_pengerjaan === 'Belum Mulai') {
                            $status_pengerjaan = 'Selesai';
                        }
                        $b_s = floatval($jm['bobot_s'] ?? 10);
                        $bobot_mod += $b_s;

                        $klas = klasifikasiSoal($jm['tipe_raw'], $jm['kunci_jwb'], $jm['teks_jwb'], $jm['teks_alasan'], $jm['flag_alasan']);
                        if ($klas['pakai_slider']) {
                            $jml_slider_mod++;
                            if (intval($jm['is_dikoreksi'] ?? 0) === 1) {
                                $jml_koreksi_mod++;
                            }
                            $poin_mod += round((intval($jm['persentase_essay'] ?? 0) / 100) * $b_s, 2);
                        } else {
                            if (!empty($jm['kunci_jwb']) && strcasecmp(trim($jm['teks_jwb']), trim($jm['kunci_jwb'])) === 0) {
                                $poin_mod += $b_s;
                            }
                        }
                    }
                }
            }

            $skor_modul = ($bobot_mod > 0) ? round(($poin_mod / $bobot_mod) * 100, 1) : 0;

            $m['id_modul_val']      = $idm;
            $m['nama_modul_val']    = ($nama_mod_str !== '') ? $nama_mod_str : "Modul $idm";
            $m['desc_modul_val']    = $desc_mod_str;
            $m['kategori_modul']    = $kat_mod;
            $m['durasi_val']        = $m['durasi_menit'] ?? 0;
            $m['status_pengerjaan'] = $status_pengerjaan;
            $m['jml_jawaban']       = $jml_jawaban_mod;
            $m['jml_slider']        = $jml_slider_mod;
            $m['jml_koreksi']       = $jml_koreksi_mod;
            $m['skor_modul']        = $skor_modul;

            $daftar_modul_kartu[$idm] = $m;
        }
    }
}

// --- 5. JIKA MODUL SPESIFIK DIPILIH, AMBIL DAFTAR SOAL MODUL TERSEBUT SAJA ---
$soal_modul_terpilih = [];
$info_modul_aktif    = $daftar_modul_kartu[$id_modul_pilih] ?? null;

if ($id_modul_pilih > 0 && !empty($tbl_jawaban) && !empty($tbl_soal) && !empty($fk_modul_soal)) {
    $sql_jwb_mod = "SELECT j.*,
                           $expr_jawaban_pelamar AS teks_jawaban_pelamar,
                           $expr_alasan_pelamar AS teks_alasan_pelamar,
                           s.`$col_tanya` AS pertanyaan,
                           " . (!empty($col_tipe) ? "s.`$col_tipe` AS tipe_soal_raw," : "'' AS tipe_soal_raw,") . "
                           " . (!empty($col_kunci) ? "s.`$col_kunci` AS kunci_jawaban," : "'' AS kunci_jawaban,") . "
                           " . (!empty($col_flag_alasan) ? "s.`$col_flag_alasan` AS flag_alasan_soal," : "'' AS flag_alasan_soal,") . "
                           " . (!empty($col_bobot) ? "COALESCE(NULLIF(s.`$col_bobot`, 0), 10) AS bobot_soal" : "10 AS bobot_soal") . "
                    FROM `$tbl_jawaban` j
                    INNER JOIN `$tbl_soal` s ON j.`$fk_j_soal` = s.`$pk_soal`
                    WHERE j.`$fk_j_pelamar` = $id_pelamar AND s.`$fk_modul_soal` = $id_modul_pilih
                    ORDER BY s.`$pk_soal` ASC";

    $q_jwb_mod = mysqli_query($conn, $sql_jwb_mod);
    if ($q_jwb_mod) {
        while ($item = mysqli_fetch_assoc($q_jwb_mod)) {
            $klas = klasifikasiSoal(
                $item['tipe_soal_raw'] ?? '',
                $item['kunci_jawaban'] ?? '',
                $item['teks_jawaban_pelamar'] ?? '',
                $item['teks_alasan_pelamar'] ?? '',
                $item['flag_alasan_soal'] ?? ''
            );
            $item['pakai_slider']    = $klas['pakai_slider'];
            $item['bs_punya_alasan'] = $klas['bs_alasan'];

            if ($klas['is_essay']) {
                $item['label_tipe']  = '✍️ Essay / Uraian';
                $item['badge_class'] = 'tipe-essay';
            } elseif ($klas['is_bs'] && $klas['bs_alasan']) {
                $item['label_tipe']  = '⚖️ Benar / Salah + Alasan';
                $item['badge_class'] = 'tipe-bs';
            } elseif ($klas['is_bs']) {
                $item['label_tipe']  = '⚖️ Benar / Salah (Tanpa Alasan)';
                $item['badge_class'] = 'tipe-pg';
            } else {
                $item['label_tipe']  = '🔘 Pilihan Ganda';
                $item['badge_class'] = 'tipe-pg';
            }

            if (!$klas['pakai_slider']) {
                $item['auto_benar'] = (!empty($item['kunci_jawaban']) && strcasecmp(trim($item['teks_jawaban_pelamar']), trim($item['kunci_jawaban'])) === 0);
            }

            $soal_modul_terpilih[] = $item;
        }
    }
}

if ($is_api_request) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => empty($pesan_error),
        'message' => strip_tags($pesan_sukses ?: $pesan_error),
        'data' => [
            'id_pelamar' => $id_pelamar,
            'id_hasil' => $id_hasil,
            'id_modul' => $id_modul_pilih,
            'pelamar' => $pelamar,
            'posisi_dilamar' => $posisi_dilamar,
            'kategori_pelamar' => $kategori_pelamar,
            'skor_akhir_gabungan' => $skor_akhir_baru,
            'modul' => array_values($daftar_modul_kartu),
            'modul_aktif' => $info_modul_aktif,
            'soal_modul_terpilih' => $soal_modul_terpilih,
        ],
    ], JSON_UNESCAPED_UNICODE);
    mysqli_close($conn);
    exit();
}

include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .koreksi-wrapper { max-width: 1120px; margin: 15px auto 50px auto; padding: 0 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .top-nav-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; }
    .btn-back { background: #f1f5f9; color: #334155; text-decoration: none; padding: 8px 15px; border-radius: 8px; font-size: 13px; font-weight: 700; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 6px; min-height: 38px; }
    .btn-back:hover { background: #e2e8f0; }

    .candidate-summary-bar {
        background: linear-gradient(135deg, #0b6623, #15803d);
        color: #ffffff;
        border-radius: 14px;
        padding: 20px 25px;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(11, 102, 35, 0.18);
    }
    .live-score-box {
        background: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 12px 22px;
        border-radius: 12px;
        text-align: center;
        min-width: 155px;
    }
    .live-score-box span { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.92; }
    .live-score-box strong { font-size: 28px; font-weight: 900; line-height: 1.15; }

    .modul-list-container {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 28px 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .modul-list-title {
        text-align: center;
        font-size: 20px;
        font-weight: 800;
        color: #111;
        margin: 0 0 22px 0;
        text-transform: uppercase;
    }
    .modul-card-row {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 12px;
        padding: 18px 24px;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .modul-card-row:hover {
        box-shadow: 0 4px 12px rgba(27, 94, 32, 0.1);
    }
    .modul-card-info {
        flex: 1;
        min-width: 240px;
    }
    .modul-card-header-line {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 5px;
    }
    .modul-card-info h3 {
        font-size: 16px;
        font-weight: 800;
        color: #111;
        margin: 0;
    }
    .badge-target-modul {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
    .target-all   { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .target-staff { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .target-crew  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .modul-desc-text {
        font-size: 12.5px;
        color: #334155;
        margin: 0 0 6px 0;
        line-height: 1.45;
    }
    .modul-card-info p.modul-meta-line {
        font-size: 13px;
        color: #475569;
        margin: 0;
    }
    .badge-posisi-admin {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        margin-top: 4px;
    }
    .btn-koreksi-modul {
        background-color: #fff9c4;
        color: #f57f17;
        border: 1.5px solid #fbc02d;
        padding: 9px 20px;
        border-radius: 22px;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.2s;
        white-space: nowrap;
        min-height: 38px;
    }
    .btn-koreksi-modul:hover { background-color: #fff176; }
    .btn-koreksi-selesai { background-color: #dcfce7; color: #166534; border: 1.5px solid #2e7d32; }
    .btn-koreksi-selesai:hover { background-color: #bbf7d0; }
    .btn-modul-kosong {
        background-color: #f1f5f9;
        color: #94a3b8;
        border: 1.5px solid #cbd5e1;
        padding: 9px 20px;
        border-radius: 22px;
        font-size: 13px;
        font-weight: 700;
        cursor: not-allowed;
        min-height: 38px;
    }

    .modul-detail-card {
        background: #ffffff;
        border: 1.5px solid #0b6623;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }
    .modul-detail-header {
        background: #e8f5e9;
        color: #0b6623;
        padding: 16px 22px;
        font-size: 16px;
        font-weight: 800;
        border-bottom: 2px solid #c8e6c9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .soal-item-box { padding: 20px 22px; border-bottom: 1px solid #e5e7eb; }
    .soal-item-box:last-child { border-bottom: none; }

    .badge-tipe { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-bottom: 8px; }
    .tipe-essay { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .tipe-bs    { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
    .tipe-pg    { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

    .jawaban-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 13px 15px; margin: 10px 0; font-size: 13.5px; line-height: 1.6; color: #0f172a; }
    .alasan-box { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 10px; padding: 12px 15px; margin: 8px 0 12px 0; font-size: 13.5px; line-height: 1.6; color: #3b0764; }
    .kunci-ref-box { background: #f0fdf4; border-left: 4px solid #16a34a; padding: 9px 14px; border-radius: 6px; font-size: 12.5px; color: #166534; margin-bottom: 12px; }

    .slider-grading-panel { background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 15px 18px; }
    .slider-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px; }
    .persen-pill { background: #0b6623; color: #ffffff; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 800; }
    .range-slider-essay { width: 100%; height: 10px; border-radius: 6px; background: #cbd5e1; outline: none; accent-color: #0b6623; cursor: pointer; margin: 8px 0 12px 0; }
    .quick-presets { display: flex; gap: 6px; flex-wrap: wrap; }
    .btn-preset { background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 5px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; cursor: pointer; min-height: 32px; }
    .btn-preset:hover { background: #0b6623; color: #ffffff; border-color: #0b6623; }
    .input-catatan-soal { flex: 1; min-width: 220px; padding: 8px 11px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 12.5px; min-height: 36px; box-sizing: border-box; }

    .modul-footer-actions { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .btn-simpan-modul { background: #0b6623; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13.5px; font-weight: 800; cursor: pointer; min-height: 40px; }
    .btn-simpan-modul:hover { background: #084d1a; }
    .btn-simpan-back { background: #1d4ed8; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 13.5px; font-weight: 800; cursor: pointer; min-height: 40px; }
    .btn-simpan-back:hover { background: #1e40af; }

    .alert-msg-success { background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 11px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600; margin-bottom: 16px; }

    @media (max-width: 768px) {
        .koreksi-wrapper { margin: 8px auto 32px auto; padding: 0 8px; }
        .top-nav-row { flex-direction: column; align-items: stretch; gap: 8px; }
        .btn-back { width: 100%; justify-content: center; min-height: 42px; }
        .candidate-summary-bar { flex-direction: column; align-items: stretch; padding: 16px; border-radius: 12px; }
        .live-score-box { width: 100%; box-sizing: border-box; }
        .modul-list-container { padding: 16px 12px; border-radius: 12px; }
        .modul-list-title { font-size: 17px; margin-bottom: 16px; }
        .modul-card-row { flex-direction: column; align-items: stretch; padding: 14px; }
        .modul-card-row > div:last-child { width: 100%; }
        .btn-koreksi-modul, .btn-modul-kosong { width: 100%; min-height: 42px; white-space: normal; text-align: center; }
        .soal-item-box { padding: 15px 14px; }
        .slider-grading-panel { padding: 12px; }
        .input-catatan-soal { width: 100%; min-height: 42px; font-size: 16px; }
        .modul-footer-actions { flex-direction: column; align-items: stretch; padding: 14px; }
        .modul-footer-actions > div { display: flex; flex-direction: column; width: 100%; gap: 8px; }
        .btn-simpan-modul, .btn-simpan-back { width: 100%; min-height: 44px; }
    }
</style>

<div class="koreksi-wrapper">
    <div class="top-nav-row">
        <?php if ($id_modul_pilih > 0): ?>
            <a href="detail_koreksi_essay.php?id_pelamar=<?php echo $id_pelamar; ?>&id_hasil=<?php echo $id_hasil; ?>" class="btn-back">
                ← Kembali ke Daftar Modul Psikotes
            </a>
        <?php else: ?>
            <a href="koreksi_hasil.php" class="btn-back">
                ← Kembali ke Rekap Hasil Psikotes
            </a>
        <?php endif; ?>
    </div>

    <div id="apiAlertContainer">
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert-msg-success">✅ <?php echo $pesan_sukses; ?></div>
        <?php endif; ?>
    </div>

    <!-- BANNER INFO KANDIDAT -->
    <div class="candidate-summary-bar">
        <div>
            <div style="font-size:11.5px; text-transform:uppercase; opacity:0.85; font-weight:700;">Koreksi Jawaban Kandidat</div>
            <h1 style="margin:3px 0; font-size:21px; font-weight:800;"><?php echo htmlspecialchars($pelamar['nama_lengkap'] ?? 'Pelamar'); ?></h1>
            <div style="font-size:13px; opacity:0.95; display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                <span>✉️ <?php echo htmlspecialchars($pelamar['email'] ?? '-'); ?></span>
                <?php if (!empty($posisi_dilamar)): ?>
                    <span class="badge-posisi-admin">💼 Posisi: <?php echo htmlspecialchars($posisi_dilamar); ?> (Filter: <?php echo strtoupper($kategori_pelamar); ?>)</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="live-score-box">
            <span>Skor Akhir Gabungan</span>
            <strong id="skorAkhirGabunganText"><?php echo number_format(floatval($data_hasil[$col_h_skor] ?? 0), 1); ?></strong>
        </div>
    </div>

    <?php if ($id_modul_pilih > 0 && $info_modul_aktif): ?>
        <!-- MODE 2: HALAMAN KOREKSI 1 MODUL -->
        <div class="modul-detail-card">
            <div class="modul-detail-header">
                <div>
                    <div>🧩 <?php echo htmlspecialchars($info_modul_aktif['nama_modul_val']); ?></div>
                    <?php if (!empty($info_modul_aktif['desc_modul_val'])): ?>
                        <div style="font-size:12px; font-weight:500; color:#166534; margin-top:2px;">
                            <?php echo htmlspecialchars($info_modul_aktif['desc_modul_val']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="font-size:13px;">
                    Skor Modul Ini: <strong id="skorModulLive"><?php echo $info_modul_aktif['skor_modul']; ?></strong> / 100
                </div>
            </div>

            <?php if (count($soal_modul_terpilih) > 0): ?>
                <form id="formKoreksiModulApi" method="POST" action="detail_koreksi_essay.php?id_pelamar=<?php echo $id_pelamar; ?>&id_hasil=<?php echo $id_hasil; ?>&id_modul=<?php echo $id_modul_pilih; ?>">
                    <input type="hidden" name="simpan_koreksi_modul" value="1">
                    <input type="hidden" name="id_pelamar" value="<?php echo $id_pelamar; ?>">
                    <input type="hidden" name="id_hasil" value="<?php echo $id_hasil; ?>">
                    <input type="hidden" name="id_modul" value="<?php echo $id_modul_pilih; ?>">
                    <input type="hidden" name="nama_modul_simpan" value="<?php echo htmlspecialchars($info_modul_aktif['nama_modul_val']); ?>">

                    <?php $no_s = 1; foreach ($soal_modul_terpilih as $s): ?>
                        <?php
                            $id_jwb    = intval($s[$pk_jawaban]);
                            $bobot     = floatval($s['bobot_soal'] ?? 10);
                            $persen_db = intval($s['persentase_essay'] ?? 0);
                            $poin_awal = round(($persen_db / 100) * $bobot, 2);
                        ?>
                        <div class="soal-item-box">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                                <span class="badge-tipe <?php echo $s['badge_class']; ?>">
                                    <?php echo $s['label_tipe']; ?>
                                </span>
                                <span style="font-size:12px; font-weight:700; color:#64748b;">Bobot: <?php echo number_format($bobot, 0); ?> poin</span>
                            </div>

                            <div style="font-size:14.5px; font-weight:700; color:#111; margin-bottom:8px;">
                                <?php echo $no_s++ . ". " . nl2br(htmlspecialchars($s['pertanyaan'])); ?>
                            </div>

                            <div class="jawaban-box">
                                <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:4px;">Jawaban Pelamar:</div>
                                <?php if ($s['teks_jawaban_pelamar'] !== ''): ?>
                                    <?php echo nl2br(htmlspecialchars($s['teks_jawaban_pelamar'])); ?>
                                <?php else: ?>
                                    <em style="color:#94a3b8;">(Pelamar tidak mengisi jawaban)</em>
                                <?php endif; ?>
                            </div>

                            <?php if ($s['teks_alasan_pelamar'] !== ''): ?>
                                <div class="alasan-box">
                                    <div style="font-size:11px; font-weight:700; color:#7e22ce; text-transform:uppercase; margin-bottom:4px;">💬 Alasan / Penjelasan Pelamar:</div>
                                    <?php echo nl2br(htmlspecialchars($s['teks_alasan_pelamar'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($s['kunci_jawaban'])): ?>
                                <div class="kunci-ref-box">
                                    <strong>🔑 Kunci / Acuan Jawaban HR:</strong> <?php echo nl2br(htmlspecialchars($s['kunci_jawaban'])); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($s['pakai_slider']): ?>
                                <div class="slider-grading-panel">
                                    <input type="hidden" name="bobot_soal[<?php echo $id_jwb; ?>]" value="<?php echo $bobot; ?>">

                                    <div class="slider-header-row">
                                        <span style="font-size:13px; font-weight:800; color:#92400e;">
                                            🎚️ <?php echo $s['bs_punya_alasan'] ? 'Penilaian Ketepatan Pilihan & Alasan:' : 'Penilaian Ketepatan Jawaban Essay:'; ?>
                                        </span>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <span style="font-size:12.5px; font-weight:700; color:#334155;">
                                                Poin: <strong id="poinText_<?php echo $id_jwb; ?>" style="color:#0b6623;"><?php echo number_format($poin_awal, 1); ?></strong> / <?php echo number_format($bobot, 0); ?>
                                            </span>
                                            <span class="persen-pill" id="persenBadge_<?php echo $id_jwb; ?>"><?php echo $persen_db; ?>%</span>
                                        </div>
                                    </div>

                                    <input type="range" min="0" max="100" step="5"
                                           name="persentase[<?php echo $id_jwb; ?>]"
                                           id="slider_<?php echo $id_jwb; ?>"
                                           class="range-slider-essay slider-item-modul"
                                           data-bobot="<?php echo $bobot; ?>"
                                           value="<?php echo $persen_db; ?>"
                                           oninput="updateSliderItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, this.value)">

                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                        <div class="quick-presets">
                                            <button type="button" class="btn-preset" onclick="setPresetItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, 0)">0% (Salah)</button>
                                            <button type="button" class="btn-preset" onclick="setPresetItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, 25)">25%</button>
                                            <button type="button" class="btn-preset" onclick="setPresetItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, 50)">50% (Cukup)</button>
                                            <button type="button" class="btn-preset" onclick="setPresetItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, 75)">75% (Baik)</button>
                                            <button type="button" class="btn-preset" onclick="setPresetItem(<?php echo $id_jwb; ?>, <?php echo $bobot; ?>, 100)">100% (Tepat)</button>
                                        </div>
                                        <input type="text" name="catatan_koreksi[<?php echo $id_jwb; ?>]"
                                               class="input-catatan-soal"
                                               placeholder="Catatan koreksi soal ini (opsional)..."
                                               value="<?php echo htmlspecialchars($s['catatan_koreksi'] ?? ''); ?>">
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="auto-poin-item"
                                     data-poin="<?php echo $s['auto_benar'] ? $bobot : 0; ?>"
                                     data-bobot="<?php echo $bobot; ?>"
                                     style="background: <?php echo $s['auto_benar'] ? '#f0fdf4' : '#fef2f2'; ?>; border: 1px solid <?php echo $s['auto_benar'] ? '#bbf7d0' : '#fecaca'; ?>; padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 700; color: <?php echo $s['auto_benar'] ? '#166534' : '#dc2626'; ?>;">
                                    <?php echo $s['auto_benar']
                                        ? "✅ Auto-Koreksi: Jawaban Benar (+{$bobot} poin)"
                                        : "❌ Auto-Koreksi: Jawaban Salah (0 poin)"; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="modul-footer-actions">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#1e293b; cursor:pointer;">
                            <input type="checkbox" name="auto_status_lulus" value="1" checked>
                            <span>Otomatis perbarui status <strong>Lulus / Tidak Lulus</strong> (KKM 70)</span>
                        </label>

                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <button type="submit" name="aksi_simpan" value="stay" class="btn-simpan-modul">
                                💾 Simpan Nilai Modul Ini
                            </button>
                            <button type="submit" name="kembali_daftar_modul" value="1" class="btn-simpan-back">
                                Simpan & Kembali ke Daftar Modul ➔
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div style="padding:40px; text-align:center; color:#64748b;">
                    Belum ada jawaban pelamar pada modul ini.
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- MODE 1: DAFTAR KARTU MODUL (ALL TAMPIL DI SEMUA; CREW VS STAFF DIFILTER KETAT) -->
        <div class="modul-list-container">
            <h2 class="modul-list-title">DAFTAR MODUL PSIKOTES</h2>

            <?php if (count($daftar_modul_kartu) > 0): ?>
                <?php $no_mod = 1; foreach ($daftar_modul_kartu as $idm => $m): ?>
                    <?php
                        $ada_jawaban   = ($m['jml_jawaban'] > 0);
                        $sudah_koreksi = ($m['jml_slider'] === 0 || $m['jml_koreksi'] >= $m['jml_slider']);
                        $kat_m         = $m['kategori_modul'] ?? 'semua';
                        $badge_cls_m   = ($kat_m === 'staff') ? 'target-staff' : (($kat_m === 'crew') ? 'target-crew' : 'target-all');
                        $label_kat_m   = ($kat_m === 'staff') ? 'Target: Staff' : (($kat_m === 'crew') ? 'Target: Crew' : 'Target: All / Semua');
                    ?>
                    <div class="modul-card-row">
                        <div class="modul-card-info">
                            <div class="modul-card-header-line">
                                <h3>Modul <?php echo $no_mod++; ?>: <?php echo htmlspecialchars($m['nama_modul_val']); ?></h3>
                                <span class="badge-target-modul <?php echo $badge_cls_m; ?>"><?php echo $label_kat_m; ?></span>
                            </div>
                            <?php if (!empty($m['desc_modul_val'])): ?>
                                <p class="modul-desc-text"><?php echo htmlspecialchars($m['desc_modul_val']); ?></p>
                            <?php endif; ?>
                            <p class="modul-meta-line">
                                Status Pengerjaan: <strong><?php echo htmlspecialchars($m['status_pengerjaan']); ?></strong>
                                <?php if (!empty($m['durasi_val'])): ?>
                                    (Durasi: <?php echo intval($m['durasi_val']); ?> Menit)
                                <?php endif; ?>
                                <?php if ($ada_jawaban): ?>
                                    • Skor Modul: <strong style="color:#0b6623;"><?php echo $m['skor_modul']; ?></strong>
                                    <?php if ($m['jml_slider'] > 0): ?>
                                        • Koreksi Essay/Alasan: <strong><?php echo $m['jml_koreksi']; ?> / <?php echo $m['jml_slider']; ?></strong>
                                    <?php else: ?>
                                        • <em>(Full Auto-Koreksi PG)</em>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div>
                            <?php if ($ada_jawaban): ?>
                                <a href="detail_koreksi_essay.php?id_pelamar=<?php echo $id_pelamar; ?>&id_hasil=<?php echo $id_hasil; ?>&id_modul=<?php echo $idm; ?>"
                                   class="btn-koreksi-modul <?php echo $sudah_koreksi ? 'btn-koreksi-selesai' : ''; ?>">
                                    <?php echo $sudah_koreksi ? '✅ Lihat / Edit Koreksi ➔' : '✍️ Koreksi Modul ➔'; ?>
                                </a>
                            <?php else: ?>
                                <button class="btn-modul-kosong" disabled>Belum Dikerjakan</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center; padding:30px; color:#64748b;">
                    Belum ada modul psikotes yang sesuai dengan kategori posisi kandidat ini.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function updateSliderItem(idJwb, bobot, val) {
    const persen = parseInt(val) || 0;
    const poin   = (persen / 100) * parseFloat(bobot);

    document.getElementById('persenBadge_' + idJwb).textContent = persen + '%';
    document.getElementById('poinText_' + idJwb).textContent    = poin.toFixed(1);

    hitungLiveSkorModulIni();
}

function setPresetItem(idJwb, bobot, persenVal) {
    const slider = document.getElementById('slider_' + idJwb);
    slider.value = persenVal;
    updateSliderItem(idJwb, bobot, persenVal);
}

function hitungLiveSkorModulIni() {
    let totalPoin  = 0;
    let totalBobot = 0;

    document.querySelectorAll('.auto-poin-item').forEach(el => {
        totalPoin  += parseFloat(el.getAttribute('data-poin')) || 0;
        totalBobot += parseFloat(el.getAttribute('data-bobot')) || 0;
    });

    document.querySelectorAll('.slider-item-modul').forEach(sl => {
        const persen = parseFloat(sl.value) || 0;
        const bobot  = parseFloat(sl.getAttribute('data-bobot')) || 0;
        totalPoin  += (persen / 100) * bobot;
        totalBobot += bobot;
    });

    const skor = totalBobot > 0 ? (totalPoin / totalBobot) * 100 : 0;
    const elTarget = document.getElementById('skorModulLive');
    if (elTarget) {
        elTarget.textContent = skor.toFixed(1);
    }
}

(function bindDetailKoreksiApi() {
    const form = document.getElementById('formKoreksiModulApi');
    if (!form || !window.fetch) return;

    let lastClickedBtn = null;
    form.querySelectorAll('button[type="submit"]').forEach(btn => {
        btn.addEventListener('click', function () {
            lastClickedBtn = this;
        });
    });

    const apiEndpoints = [
        '../api/admin/detail_koreksi_essay.php',
        '../api/detail_koreksi_essay.php'
    ];

    const postToApi = async (payload) => {
        for (const url of apiEndpoints) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            if (res.status === 404) {
                continue;
            }
            return res.json();
        }
        throw new Error('Endpoint API tidak ditemukan (404).');
    };

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const fd = new FormData(form);
        const payload = {
            id_pelamar: parseInt(fd.get('id_pelamar') || '0', 10),
            id_hasil: parseInt(fd.get('id_hasil') || '0', 10),
            id_modul: parseInt(fd.get('id_modul') || '0', 10),
            auto_status_lulus: fd.get('auto_status_lulus') ? 1 : 0,
            persentase: {},
            bobot_soal: {},
            catatan_koreksi: {}
        };

        for (const [key, val] of fd.entries()) {
            let m;
            if ((m = key.match(/^persentase\[(\d+)\]$/))) {
                payload.persentase[m[1]] = val;
            } else if ((m = key.match(/^bobot_soal\[(\d+)\]$/))) {
                payload.bobot_soal[m[1]] = val;
            } else if ((m = key.match(/^catatan_koreksi\[(\d+)\]$/))) {
                payload.catatan_koreksi[m[1]] = val;
            }
        }

        const kembaliKeDaftar = !!(lastClickedBtn && lastClickedBtn.name === 'kembali_daftar_modul');
        const namaModul = fd.get('nama_modul_simpan') || 'Modul';

        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(b => { b.disabled = true; });

        postToApi(payload)
        .then(data => {
            if (!data || !data.success) {
                throw new Error((data && data.message) || 'Gagal menyimpan koreksi melalui API.');
            }

            const skorBaru = parseFloat(data.skor_akhir_baru || 0).toFixed(1);
            const skorBox = document.getElementById('skorAkhirGabunganText');
            if (skorBox) {
                skorBox.textContent = skorBaru;
            }

            if (kembaliKeDaftar) {
                window.location.href = 'detail_koreksi_essay.php?id_pelamar=' +
                    encodeURIComponent(payload.id_pelamar) + '&id_hasil=' + encodeURIComponent(payload.id_hasil);
                return;
            }

            const alertBox = document.getElementById('apiAlertContainer');
            if (alertBox) {
                alertBox.innerHTML = '<div class="alert-msg-success">✅ Nilai koreksi <strong>' +
                    String(namaModul).replace(/</g, '&lt;') +
                    '</strong> berhasil disimpan! Skor akhir gabungan: <strong>' + skorBaru + '</strong>.</div>';
            }
            submitButtons.forEach(b => { b.disabled = false; });
        })
        .catch(() => {
            submitButtons.forEach(b => { b.disabled = false; });
            if (kembaliKeDaftar && lastClickedBtn) {
                const hiddenBack = document.createElement('input');
                hiddenBack.type = 'hidden';
                hiddenBack.name = 'kembali_daftar_modul';
                hiddenBack.value = '1';
                form.appendChild(hiddenBack);
            }
            form.submit();
        });
    });
})();
</script>

<?php
mysqli_close($conn);
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>