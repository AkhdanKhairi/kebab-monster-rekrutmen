<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama    = htmlspecialchars($_POST['nama']);
    $telepon = htmlspecialchars($_POST['telepon']);
    $email   = htmlspecialchars($_POST['email']);
    $subjek  = htmlspecialchars($_POST['subjek']);
    $pesan   = htmlspecialchars($_POST['pesan']);

    // Email tujuan HR
    $tujuan = "hrd@kebabmonster.id";

    // Subjek email yang diterima HR
    $email_subjek = "Pesan Website Kebab Monster: " . $subjek;

    // Isi pesan email
    $email_isi = "Anda menerima pesan baru dari website Kebab Monster:\n\n";
    $email_isi .= "Nama    : " . $nama . "\n";
    $email_isi .= "Telepon : " . $telepon . "\n";
    $email_isi .= "Email   : " . $email . "\n\n";
    $email_isi .= "Pesan :\n" . $pesan . "\n";

    // Konfigurasi Header Email
    $headers = "From: " . $email . "\r\n" .
               "Reply-To: " . $email . "\r\n" .
               "X-Mailer: PHP/" . phpversion();

    // Kirim email menggunakan fungsi mail() PHP
    if (mail($tujuan, $email_subjek, $email_isi, $headers)) {
        // Jika berhasil terkirim
        echo "<script>
                alert('Pesan berhasil dikirim ke HR!');
                window.location.href='contact.php';
              </script>";
    } else {
        // Jika gagal (biasanya karena server lokal XAMPP belum dikonfigurasi SMTP)
        echo "<script>
                alert('Gagal mengirim pesan. Silakan coba lagi.');
                window.location.href='contact.php';
              </script>";
    }
} else {
    header("Location: contact.php");
}
?>