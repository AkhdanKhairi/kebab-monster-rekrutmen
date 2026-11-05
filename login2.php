<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Penerimaan Karyawan Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center font-sans">

    <!-- KOTAK UTAMA LOGIN -->
    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-8 relative">
        
        <!-- TAB SWITCH (Login / Register) -->
        <div class="flex justify-center mb-8">
            <div class="bg-gray-200 p-1 rounded-full flex relative w-52 h-10 items-center shadow-inner select-none">
                <!-- Latar hijau di sebelah kiri (Login aktif) -->
                <div class="absolute w-1/2 h-8 bg-[#0a5c36] rounded-full shadow-sm left-1 transition-all duration-300 ease-in-out"></div>
                
                <a href="login2.php" class="w-1/2 text-center text-xs font-bold text-white z-10 py-1">Login</a>
                <a href="daftar_pelamar.php" class="w-1/2 text-center text-xs font-semibold text-gray-600 hover:text-gray-900 z-10 py-1 transition">Register</a>
            </div>
        </div>

        <!-- LOGO & JUDUL -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black tracking-widest text-[#0a5c36] mb-1">K A R I R</h1>
            <p class="text-[11px] font-semibold text-gray-600 tracking-wide uppercase">Sistem Penerimaan Karyawan Kebab Monster</p>
            <p class="text-xs text-gray-500 mt-4">Untuk mengakses<br>dipersilahkan untuk login terlebih dahulu.</p>
        </div>

        <!-- FORM LOGIN -->
        <form action="proses_login.php" method="POST" class="space-y-4">
            
            <div class="relative">
                <input type="text" name="username" placeholder="Username / No. Penduduk" required
                    class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                    <i class="fa-solid fa-user text-base"></i>
                </div>
            </div>

            <div class="relative">
                <input type="password" name="password" placeholder="********" required
                    class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                    <i class="fa-solid fa-lock text-base"></i>
                </div>
            </div>

            <!-- CAPTCHA SIMULASI -->
            <div class="flex flex-col items-center justify-center bg-gray-50 border border-gray-200 rounded-lg p-3 space-y-2">
                <div class="bg-gray-300 px-6 py-2 rounded tracking-widest font-mono font-bold text-gray-700 text-lg select-none border border-gray-400 shadow-inner bg-opacity-60" style="letter-spacing: 6px;">
                    7 Y Z 1
                </div>
            </div>

            <div class="relative">
                <input type="text" name="captcha" placeholder="Masukan Kode Diatas" required
                    class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-[#0a5c36]">
                    <i class="fa-solid fa-key text-base"></i>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#0a5c36] hover:bg-opacity-90 text-white font-semibold py-3 rounded-lg transition text-sm shadow-sm">
                    Login
                </button>
            </div>

            <div class="text-center pt-2">
                <a href="lupa_password.php" class="text-xs text-blue-500 hover:underline font-medium">Lupa Password ?</a>
            </div>

        </form>

    </div>

</body>
</html>