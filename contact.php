<?php
// Panggil file koneksi jika nanti form ingin disimpan ke database
// include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, text-kebabYellow">
    <title>Hubungi Kami - Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body id="top" class="bg-gray-50 font-sans text-gray-800">

    <!-- NAVBAR -->
    <?php include 'navbarb4.php'; ?>

    <!-- HEADER / BANNER HUBUNGI KAMI -->
    <section class="bg-[#0a5c36] text-white py-16 px-6 text-center">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold tracking-wide mb-4">
                Hubungi Kami
            </h1>
            <p class="text-sm md:text-base text-gray-200 max-w-2xl mx-auto leading-relaxed">
                Jika Anda ingin mengetahui informasi lebih lanjut mengenai KEBAB MONSTER, silakan menghubungi kami
            </p>
        </div>
    </section>

    <!-- KONTEN UTAMA: INFO & FORM -->
    <main class="max-w-6xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- SISI KIRI: INFORMASI KONTAK -->
            <div class="lg:col-span-5 space-y-8">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 tracking-wide mb-6">
                        HUBUNGI KAMI
                    </h2>
                    
                    <div class="space-y-6 text-sm text-gray-700">
                        <div>
                            <p class="font-bold text-gray-900 tracking-wider text-xs mb-1">EMAIL KAMI</p>
                            <a href="mailto:hrd@kebabmonster.id" class="text-gray-600 hover:text-[#0a5c36]">hrd@kebabmonster.id</a>
                        </div>

                        <div>
                            <p class="font-bold text-gray-900 tracking-wider text-xs mb-1">WHATSAPPS KAMI</p>
                            <p class="text-gray-600">Pesan Via WhatsApp</p>
                        </div>

                        <div>
                            <p class="text-gray-600 leading-relaxed">
                                Jl. Bintang Mas No.23, Nanggewer, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16912
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SISI KANAN: FORM PESAN -->
            <div class="lg:col-span-7 bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <form action="proses_kontak.php" method="POST" class="space-y-5">
                    
                    <!-- Nama -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama</label>
                        <input type="text" name="nama" placeholder="Masukan Nama Anda..." required 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#0a5c36]">
                    </div>

                    <!-- Telepon & Email (Grid 2 Kolom) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Telepon</label>
                            <input type="text" name="telepon" placeholder="Masukan Nomor Telepon Anda..." required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#0a5c36]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" placeholder="Masukan Alamat Email Anda..." required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#0a5c36]">
                        </div>
                    </div>

                    <!-- Subjek -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Subjek</label>
                        <input type="text" name="subjek" placeholder="Masukan Subjek Anda..." required 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#0a5c36]">
                    </div>

                    <!-- Pesan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pesan</label>
                        <textarea name="pesan" rows="4" placeholder="Ceritakan Tentang Pesan Anda..." required 
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#0a5c36]"></textarea>
                    </div>

                    <!-- Rekaptcha Simulasi / Checkbox -->
                    <div class="flex items-center space-x-3 border border-gray-200 p-3 rounded-lg w-fit bg-gray-50">
                        <input type="checkbox" id="human" name="human" required class="w-4 h-4 accent-[#0a5c36] cursor-pointer">
                        <label for="human" class="text-xs font-medium text-gray-700 cursor-pointer">Aku Manusia</label>
                    </div>

                    <!-- Tombol Kirim -->
                    <div>
                        <button type="submit" class="inline-flex items-center gap-2 bg-[#0a5c36] hover:bg-opacity-90 text-white font-semibold text-xs px-6 py-3 rounded-lg transition shadow-sm">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Pesan
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>
    
<?php 
  include "footerb4.php";
 ?>
   
</body>
</html>