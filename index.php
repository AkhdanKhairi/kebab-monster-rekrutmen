<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebab Monster - Home</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kebabGreen: '#0a5c36',
                        kebabGreenDark: '#074226',
                        kebabOrange: '#ff7700',
                        kebabYellow: '#ffaa00',
                        kebabLightBg: '#f2f7f4'
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

<?php 
  include "navbarb4.php";
?>

<!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-kebabGreen text-2xl focus:outline-none">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-gray-100 px-4 pt-2 pb-4 space-y-2 text-sm font-semibold text-kebabGreen">
            <a href="#homepage" class="block py-2 hover:bg-gray-50 px-3 rounded-lg">HOMEPAGE</a>
            <a href="#career" class="block py-2 hover:bg-gray-50 px-3 rounded-lg">CAREER</a>
            <a href="#information" class="block py-2 hover:bg-gray-50 px-3 rounded-lg">INFORMATION</a>
            <a href="#contact" class="block py-2 hover:bg-gray-50 px-3 rounded-lg">CONTACT US</a>
            <a href="#login2" class="block py-2 bg-kebabGreen text-white text-center rounded-lg mt-2">LOGIN/REGISTER</a>
        </div>
    </header>

    <!-- Main Content -->
    <main id="homepage">

        <!-- Section: Join Us & Warning Box -->
        <section class="bg-kebabGreen text-white py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto text-center space-y-4 mb-12">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-wider text-kebabYellow">JOIN US</h1>
                <p class="text-sm sm:text-base text-emerald-100 max-w-2xl mx-auto leading-relaxed">
                    Bangun karier bersama brand kebab 24 jam pertama di Indonesia. Make History with Kebab Monster.
                </p>
            </div>

            <!-- Warning Card Box -->
            <div class="max-w-5xl mx-auto bg-white text-gray-800 rounded-3xl p-6 sm:p-8 shadow-xl grid grid-cols-1 md:grid-cols-2 gap-8 items-center border border-emerald-900/10">
                <div class="space-y-4">
                    <h3 class="text-red-600 font-extrabold text-lg sm:text-xl tracking-wide flex items-center space-x-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>HATI-HATI PENIPUAN !!!</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed text-justify">
                        Manajemen Kebab Monster dengan ini mengimbau kepada seluruh masyarakat dan para pencari kerja agar selalu waspada terhadap segala bentuk modus penipuan lowongan kerja yang mengatasnamakan Kebab Monster.
                    </p>
                </div>
                <div class="space-y-3">
                    <div class="bg-gray-50 border border-gray-100 p-3.5 rounded-2xl text-xs text-gray-600 shadow-sm">
                        Kebab Monster tidak pernah memungut biaya apa pun dari pelamar selama proses seleksi berlangsung (biaya administrasi, akomodasi, dll).
                    </div>
                    <div class="bg-gray-50 border border-gray-100 p-3.5 rounded-2xl text-xs text-gray-600 shadow-sm">
                        Seluruh informasi lowongan dan tahapan rekrutmen hanya disampaikan melalui kanal resmi perusahaan.
                    </div>
                    <div class="bg-gray-50 border border-gray-100 p-3.5 rounded-2xl text-xs text-gray-600 shadow-sm">
                        Imbauan untuk segera mengabaikan dan tidak merespons jika ada pihak tak dikenal yang meminta transfer uang atau mengatasnamakan perusahaan.
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Be Part of the Change -->
        <section id="career" class="py-16 px-4 sm:px-6 lg:px-8 bg-kebabLightBg text-center">
            <div class="max-w-4xl mx-auto space-y-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Be Part of the Change</h2>
                <p class="text-xs sm:text-sm uppercase tracking-widest text-kebabGreen font-semibold">Tujuan kami sederhana — menjadi brand kebab terdepan di Indonesia.</p>
                <p class="text-sm sm:text-base text-gray-600 leading-relaxed max-w-3xl mx-auto">
                    Lebih dari sekadar misi, ini kesempatan bagi setiap orang yang bergabung untuk mengubah cara masyarakat menikmati kebab, porsi monster, rasa premium, tersedia 24 jam, dan terjangkau untuk semua.<br><br>
                    Baik di operasional outlet, central kitchen, logistik, marketing, finance, maupun HR — setiap posisi punya peran penting. Semua orang punya peluang untuk berkembang dan berkontribusi.
                </p>
            </div>
        </section>

        <!-- Section: Visi & Misi -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-5xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-center text-gray-900 mb-12">VISI & MISI</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Visi Box -->
                    <div class="bg-white rounded-3xl overflow-hidden shadow-md border border-gray-100 flex flex-col justify-between">
                        <div class="bg-gradient-to-r from-kebabGreen to-emerald-600 py-6 text-center text-white">
                            <h3 class="font-extrabold text-xl tracking-wider">VISI</h3>
                        </div>
                        <div class="p-8 text-center text-sm sm:text-base text-gray-700 leading-relaxed">
                            Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang, saling ekspansi brand.
                        </div>
                    </div>

                    <!-- Misi Box -->
                    <div class="bg-white rounded-3xl overflow-hidden shadow-md border border-gray-100 flex flex-col justify-between">
                        <div class="bg-gradient-to-r from-kebabOrange to-kebabYellow py-6 text-center text-white">
                            <h3 class="font-extrabold text-xl tracking-wider">MISI</h3>
                        </div>
                        <div class="p-8 text-center text-sm sm:text-base text-gray-700 leading-relaxed">
                            Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang, saling ekspansi brand.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: We Are Expanding -->
        <section id="information" class="py-16 px-4 sm:px-6 lg:px-8 bg-kebabLightBg text-center">
            <div class="max-w-5xl mx-auto space-y-6">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">We Are Expanding</h2>
                <p class="text-xs sm:text-sm text-gray-600 max-w-xl mx-auto">Mari besar butuh talenta dari banyak kota. Temukan peluang di area operasional kami:</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 pt-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">JKT</h4>
                        <p class="text-xs text-gray-500 mt-1">Jakarta</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">DPK</h4>
                        <p class="text-xs text-gray-500 mt-1">Depok</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">BGR</h4>
                        <p class="text-xs text-gray-500 mt-1">Bogor</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">TGR</h4>
                        <p class="text-xs text-gray-500 mt-1">Tangerang</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">BKS</h4>
                        <p class="text-xs text-gray-500 mt-1">Bekasi</p>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h4 class="font-extrabold text-xl text-kebabGreen">BDG</h4>
                        <p class="text-xs text-gray-500 mt-1">Bandung</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Why Work With Us? -->
        <section class="py-16 px-4 sm:px-6 lg:px-8 bg-white">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-center text-gray-900 mb-4">Why Work With Us?</h2>
                <p class="text-center text-xs sm:text-sm text-gray-500 mb-12">Di Kebab Monster, kami membangun budaya kerja yang dinamis, suportif, dan penuh kesempatan berkembang di setiap brand.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hover:shadow-md transition space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-kebabGreen flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-forward-fast"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg">Pertumbuhan Cepat</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            70+ outlet dan terus ekspansi! Bergabung di fase growth berarti banyak peluang naik jabatan dan tanggung jawab baru.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hover:shadow-md transition space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-kebabGreen flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-users-viewfinder"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg">Tim Supportif</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Lingkungan kerja ramah, no drama, dan saling bantu. Kami menghargai kejujuran, disiplin, dan semangat #KerenDanBener.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-sm hover:shadow-md transition space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-kebabGreen flex items-center justify-center text-xl font-bold">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg">Jenjang Karier</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Dari crew outlet hingga supervisor, area manager, hingga posisi kantor pusat — jalur pengembangan jelas dan terbuka.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </main>

        <?php 
        include "footerb4.php";
        ?>

    <!-- JavaScript for Mobile Menu Toggle -->
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>