<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karier - Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body id="top" class="bg-gray-50 font-sans text-gray-800">

  <?php 
  include "navbarb4.php";
  ?>
  
    <!-- HEADER / BANNER KARIER -->
    <section class="bg-[#0a5c36] text-white py-16 px-6 text-center">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold tracking-wide mb-4">
                Karier di <span class="text-amber-400">Kebab Monster</span>
            </h1>
            <p class="text-sm md:text-base text-gray-200 mb-8 leading-relaxed">
                Di Kebab Monster, kami percaya setiap individu punya potensi luar biasa untuk berkembang bersama. Temukan posisi yang cocok dan raih karier cemerlang bersama kami.
            </p>
            <div class="inline-flex flex-wrap justify-center gap-4">
                <span class="bg-[#08482a] text-white text-xs font-medium px-4 py-2 rounded-full border border-emerald-600 flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-location-dot"></i> Bogor, Jawa Barat
                </span>
                <span class="bg-[#08482a] text-white text-xs font-medium px-4 py-2 rounded-full border border-emerald-600 flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-shield-halved"></i> Tanpa Biaya Rekrutmen
                </span>
            </div>
        </div>
    </section>

    <!-- DAFTAR LOWONGAN PEKERJAAN -->
    <main class="max-w-4xl mx-auto px-6 py-12 space-y-6">

        <!-- Lowongan 1: Crew Outlet -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        CREW OUTLET <span class="text-sm font-normal text-gray-500 ml-1">Operasional</span>
                    </h2>
                    <p class="text-xs md:text-sm font-semibold text-gray-800 mt-1">
                        PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-emerald-50 text-[#0a5c36] border border-emerald-200 text-xs font-semibold px-3 py-1 rounded-full">Penuh Waktu</span>
                    <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1 rounded-full">03-Nov-2026</span>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-3 border-t border-gray-100">
                <p class="text-xs md:text-sm text-gray-600 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-gray-400"></i> JABODETABEK (Jakarta, Depok, Bogor, Tangerang, Bekasi) & Bandung
                </p>
                <a href="detail_lamaran.php?id=1" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs md:text-sm font-semibold px-4 py-2 rounded-lg transition">
                    Liat Detail Lamaran <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Lowongan 2: Staff Business Intelligent -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        STAFF BUSINESS INTELEGENT <span class="text-sm font-normal text-gray-500 ml-1">BOD</span>
                    </h2>
                    <p class="text-xs md:text-sm font-semibold text-gray-800 mt-1">
                        PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-emerald-50 text-[#0a5c36] border border-emerald-200 text-xs font-semibold px-3 py-1 rounded-full">Penuh Waktu</span>
                    <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1 rounded-full">04-Nov-2026</span>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-3 border-t border-gray-100">
                <p class="text-xs md:text-sm text-gray-600 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-gray-400"></i> Jl. Bintang Mas No.23, Nanggewer, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16912
                </p>
                <a href="detail_lamaran.php?id=2" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs md:text-sm font-semibold px-4 py-2 rounded-lg transition">
                    Liat Detail Lamaran <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Lowongan 3: Supply Chain Management Staff -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md transition">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        SUPPLY CHAIN MANAGEMENT STAFF <span class="text-sm font-normal text-gray-500 ml-1">SCM</span>
                    </h2>
                    <p class="text-xs md:text-sm font-semibold text-gray-800 mt-1">
                        PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-emerald-50 text-[#0a5c36] border border-emerald-200 text-xs font-semibold px-3 py-1 rounded-full">Penuh Waktu</span>
                    <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-3 py-1 rounded-full">04-Nov-2026</span>
                </div>
            </div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pt-3 border-t border-gray-100">
                <p class="text-xs md:text-sm text-gray-600 flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-gray-400"></i> Jl. Bintang Mas No.23, Nanggewer, Kec. Cibinong, Kabupaten Bogor, Jawa Barat 16912
                </p>
                <a href="detail_lamaran.php?id=3" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs md:text-sm font-semibold px-4 py-2 rounded-lg transition">
                    Liat Detail Lamaran <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

    </main>

<?php 
  include "footerb4.php";
 ?>

</body>
</html>