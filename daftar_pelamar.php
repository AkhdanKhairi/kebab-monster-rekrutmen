<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pelamar - Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center font-sans relative">

    <!-- TOMBOL HOME DI POJOK KIRI ATAS -->
    <div class="absolute top-6 left-6">
        <a href="index.php" class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold px-4 py-2 rounded-full shadow-sm border border-gray-300 transition">
            <i class="fa-solid fa-house text-[#0a5c36]"></i> Beranda
        </a>
    </div>

    <!-- KOTAK UTAMA REGISTER -->
    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-8 relative mt-12 md:mt-0">
        
        <!-- TAB SWITCH (Login / Register) -->
        <div class="flex justify-center mb-8">
            <div class="bg-gray-200 p-1 rounded-full flex relative w-52 h-10 items-center shadow-inner select-none">
                <!-- Latar hijau bergeser ke sebelah kanan (Register aktif) -->
                <div class="absolute w-1/2 h-8 bg-[#0a5c36] rounded-full shadow-sm right-1 transition-all duration-300 ease-in-out"></div>
                
                <a href="login2.php" class="w-1/2 text-center text-xs font-semibold text-gray-600 hover:text-gray-900 z-10 py-1 transition">Login</a>
                <a href="daftar_pelamar.php" class="w-1/2 text-center text-xs font-bold text-white z-10 py-1">Register</a>
            </div>
        </div>

        <!-- LOGO & JUDUL -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-black tracking-widest text-[#0a5c36] mb-1">K A R I R</h1>
            <p class="text-[11px] font-semibold text-gray-600 tracking-wide uppercase">Sistem Penerimaan Karyawan Kebab Monster</p>
            <div class="mt-4">
                <h2 class="text-sm font-bold text-gray-800">Form Registrasi</h2>
                <p class="text-[11px] font-red-500 font-medium">*semua form wajib di isi</p>
            </div>
        </div>

        <!-- FORM REGISTRASI -->
        <form action="proses_register.php" method="POST" class="space-y-4">
            
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Email ID</label>
                <div class="relative">
                    <input type="email" name="email" placeholder="Enter email id" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                        <i class="fa-solid fa-user text-base"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Create Password</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="Enter password" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="confirm_password" placeholder="Enter password" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                        <i class="fa-solid fa-key text-base"></i>
                    </div>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-[#0a5c36] hover:bg-opacity-90 text-white font-semibold py-3 rounded-lg transition text-sm shadow-sm">
                    Register
                </button>
            </div>

        </form>

    </div>

</body>
</html>