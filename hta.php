<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Information - Panduan Pendaftaran Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 font-sans text-gray-800 flex flex-col min-h-screen">

<?php 
  include "navbarb4.php";
?>

    <!-- BANNER UTAMA (HIJAU) -->
    <section class="bg-[#0a5c36] text-white py-16 px-6 text-center">
        <div class="max-w-4xl mx-auto space-y-4">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-wide">
                Panduan Pendaftaran Karir<br>di Kebab Monster
            </h1>
            <p class="text-sm md:text-base text-gray-200 max-w-2xl mx-auto leading-relaxed">
                Selamat datang di Portal Rekrutmen Resmi Kebab Monster! Ingin bergabung dan bertumbuh bersama tim kami? Ikuti panduan langkah demi langkah di bawah ini untuk menyelesaikan proses pendaftaran Anda:
            </p>
        </div>
    </section>

    <!-- KONTEN UTAMA: 3 TAHAPAN (GRID) -->
    <main class="flex-1 max-w-7xl mx-auto px-6 py-12 -mt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
            
            <!-- TAHAP 1 -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-amber-600 to-amber-500 text-white p-5 text-center font-bold text-sm tracking-wide shadow-sm">
                    Tahap 1 Pembuatan Akun & Login
                </div>
                <div class="p-6 space-y-6 flex-1 text-xs">
                    <!-- Langkah 1 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">1</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Akses Portal (Masuk)</p>
                            <p class="text-gray-600 leading-relaxed">Klik "Login/Registrasi" yang ada di pojok atas kanan.</p>
                        </div>
                    </div>
                    <!-- Langkah 2 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">2</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Registrasi Akun Baru</p>
                            <p class="text-gray-600 leading-relaxed">Klik "Register" yang ada di halaman login.</p>
                        </div>
                    </div>
                    <!-- Langkah 3 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">3</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Isi Data Akun</p>
                            <p class="text-gray-600 leading-relaxed">Masukan Username & Password, lalu klik "Login".</p>
                        </div>
                    </div>
                    <!-- Langkah 4 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">4</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Masuk (Login)</p>
                            <p class="text-gray-600 leading-relaxed">Masukan Username & Password.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAHAP 2 -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-amber-600 to-amber-500 text-white p-5 text-center font-bold text-sm tracking-wide shadow-sm">
                    Tahap 2: Memilih Lowongan & Pengisian Berkas
                </div>
                <div class="p-6 space-y-6 flex-1 text-xs">
                    <!-- Langkah 5 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">5</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Buka Menu Karir</p>
                            <p class="text-gray-600 leading-relaxed">Klik "Career" di bagian atas lalu pilih "Lihat Detail Lamaran".</p>
                        </div>
                    </div>
                    <!-- Langkah 6 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">6</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Mulai Pendaftaran</p>
                            <p class="text-gray-600 leading-relaxed">Klik tombol "Daftar".</p>
                        </div>
                    </div>
                    <!-- Langkah 7 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">7</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Lengkapi Informasi Pribadi</p>
                            <p class="text-gray-600 leading-relaxed">Isi seluruh data diri yang diperlukan.</p>
                        </div>
                    </div>
                    <!-- Langkah 8 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">8</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Unggah Dokumen (Upload)</p>
                            <p class="text-gray-600 leading-relaxed">Unggah semua berkas yang diminta sesuai dengan ketentuan yang tertulis.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAHAP 3 -->
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="bg-gradient-to-r from-amber-600 to-amber-500 text-white p-5 text-center font-bold text-sm tracking-wide shadow-sm">
                    Tahap 3: Seleksi, Tes, & Wawancara
                </div>
                <div class="p-6 space-y-6 flex-1 text-xs">
                    <!-- Langkah 9 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">9</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Undangan Tes</p>
                            <p class="text-gray-600 leading-relaxed">Cek email untuk undangan tes online.</p>
                        </div>
                    </div>
                    <!-- Langkah 10 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">10</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Tes & Zoom</p>
                            <p class="text-gray-600 leading-relaxed">Ikuti tes di "Sistem Penerimaan" dan join Zoom. (Link nya akan dikirim melalui email).</p>
                        </div>
                    </div>
                    <!-- Langkah 11 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">11</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Wawancara</p>
                            <p class="text-gray-600 leading-relaxed">Tunggu notifikasi panggilan wawancara.</p>
                        </div>
                    </div>
                    <!-- Langkah 12 -->
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-extrabold flex items-center justify-center shrink-0 text-sm border border-amber-300">12</div>
                        <div>
                            <p class="font-bold text-gray-900 mb-0.5">Hasil Akhir</p>
                            <p class="text-gray-600 leading-relaxed">Tunggu hasil di email.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

        <?php 
        include "footerb4.php";
        ?>

</body>
</html>