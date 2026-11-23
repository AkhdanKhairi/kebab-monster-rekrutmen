<?php
// api/auth/register.php
require_once __DIR__ . '/../_helper.php';

requireMethod('POST');

$input    = getRequestInput();
$nama     = trim((string) ($input['nama_lengkap'] ?? ''));
$email    = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');
$telepon  = trim((string) ($input['no_telepon'] ?? ''));

if ($nama === '' || $email === '' || $password === '' || $telepon === '') {
    jsonResponse(400, false, 'Nama lengkap, email, password, dan nomor telepon wajib diisi.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(400, false, 'Format email tidak valid.');
}

$conn = getDbConnection();

// Cek apakah email sudah terdaftar
$stmtCek = mysqli_prepare($conn, "SELECT id_pelamar FROM pelamar WHERE email = ? LIMIT 1");
if (!$stmtCek) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal memeriksa email.');
}

mysqli_stmt_bind_param($stmtCek, 's', $email);
mysqli_stmt_execute($stmtCek);
$resCek = mysqli_stmt_get_result($stmtCek);

if ($resCek && mysqli_num_rows($resCek) > 0) {
    mysqli_stmt_close($stmtCek);
    mysqli_close($conn);
    jsonResponse(409, false, 'Email sudah terdaftar, silakan gunakan email lain.');
}
mysqli_stmt_close($stmtCek);

// Simpan pelamar baru (mengikuti format MD5 agar kompatibel dengan login.php)
$passwordMd5 = md5($password);
$stmtInsert  = mysqli_prepare(
    $conn,
    "INSERT INTO pelamar (nama_lengkap, email, password, no_telepon) VALUES (?, ?, ?, ?)"
);

if (!$stmtInsert) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query registrasi.');
}

mysqli_stmt_bind_param($stmtInsert, 'ssss', $nama, $email, $passwordMd5, $telepon);

if (mysqli_stmt_execute($stmtInsert)) {
    $newId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmtInsert);
    mysqli_close($conn);

    jsonResponse(201, true, 'Registrasi berhasil. Silakan login.', [
        'pelamar' => [
            'id'           => $newId,
            'nama_lengkap' => $nama,
            'email'        => $email,
            'no_telepon'  => $telepon,
        ],
    ]);
}

mysqli_stmt_close($stmtInsert);
mysqli_close($conn);
jsonResponse(500, false, 'Terjadi kesalahan saat menyimpan data registrasi.');