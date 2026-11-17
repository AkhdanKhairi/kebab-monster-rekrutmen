<?php
// Pastikan file ini dipanggil dari index.php yang memiliki session
session_start();
?>
<div class="space-y-6 max-w-4xl mx-auto">
    
    <!-- BREADCRUMB & JUDUL -->
    <div class="mb-4">
        <p class="text-xs text-gray-500 mb-1">Profile / <span class="text-gray-700 font-semibold">Ganti Password</span></p>
        <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-key text-[#0a5c36]"></i> GANTI PASSWORD
        </h1>
    </div>

    <!-- KOTAK UTAMA FORM GANTI PASSWORD -->
    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm space-y-6">
        
        <!-- KOTAK NOTE -->
        <div class="bg-gray-600 text-white px-4 py-3 rounded-lg text-xs flex flex-col space-y-0.5 shadow-inner">
            <span class="font-bold">Note *</span>
            <span class="text-gray-200">Harap isi Password lama dan masukan password baru anda</span>
        </div>

        <!-- FORM INPUT -->
        <form action="proses_ganti_password.php" method="POST" class="space-y-5">
            
            <!-- Password Lama -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <label class="md:col-span-3 text-xs font-bold text-gray-700">
                    Password Lama <span class="text-red-500">*</span>
                </label>
                <div class="md:col-span-9 relative">
                    <input type="password" name="password_lama" placeholder="Masukan password" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-xs text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12 shadow-inner">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-emerald-700">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                </div>
            </div>

            <!-- Password Baru -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <label class="md:col-span-3 text-xs font-bold text-gray-700">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="md:col-span-9 relative">
                    <input type="password" name="password_baru" placeholder="Masukan password" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-xs text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12 shadow-inner">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-emerald-700">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                </div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <label class="md:col-span-3 text-xs font-bold text-gray-700">
                    Konfirmasi<br>Password Baru <span class="text-red-500">*</span>
                </label>
                <div class="md:col-span-9 relative">
                    <input type="password" name="konfirmasi_password" placeholder="Masukan password" required
                        class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-3 text-xs text-gray-700 focus:outline-none focus:border-[#0a5c36] pr-12 shadow-inner">
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-emerald-700">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                </div>
            </div>

            <!-- TOMBOL AKSI -->
            <div class="pt-4 flex justify-center">
                <button type="submit" class="bg-[#0a5c36] hover:bg-opacity-90 text-white font-semibold text-xs px-8 py-3 rounded-lg shadow-sm transition">
                    Lanjutkan
                </button>
            </div>

        </form>

    </div>

</div>