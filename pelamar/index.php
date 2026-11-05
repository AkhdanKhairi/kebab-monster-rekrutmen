<?php
session_start();
// include 'koneksi.php'; // Hubungkan ke database jika diperlukan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Data Diri - Kebab Monster</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

    <!-- TOPBAR -->
    <header class="bg-[#0a5c36] text-white px-6 py-3 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <!-- Logo -->
            <img src="kebablogo.jpeg" alt="Logo Kebab Monster" class="h-10 w-auto object-contain bg-white rounded p-1">
            <span class="font-bold text-base tracking-wide">Sistem Penerimaan Karyawan <span class="text-amber-400">KEBAB MONSTER</span></span>
        </div>
        <div class="flex items-center space-x-3 text-sm">
            <div class="text-right">
                <p class="font-semibold text-amber-300">@username</p>
                <p class="text-xs text-gray-200">EmailPendaftar@gmail.com</p>
            </div>
            <div class="w-9 h-9 bg-amber-400 rounded-full flex items-center justify-center text-[#0a5c36] font-bold">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
    </header>

    <div class="flex min-h-screen">
        
        <!-- SIDEBAR -->
        <aside class="w-20 md:w-64 bg-white border-r border-gray-200 flex flex-col py-6 px-4 space-y-6">
            <div class="hidden md:block text-xs font-bold text-gray-400 tracking-wider uppercase">Menu Utama</div>
            <nav class="space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-emerald-50 text-[#0a5c36] font-semibold text-sm">
                    <i class="fa-solid fa-user text-base"></i>
                    <span class="hidden md:inline">Lihat Data Diri</span>
                </a>
                <a href="lowongan.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm transition">
                    <i class="fa-solid fa-briefcase text-base"></i>
                    <span class="hidden md:inline">Lowongan Tersedia</span>
                </a>
                <a href="status_lamaran.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm transition">
                    <i class="fa-solid fa-file-lines text-base"></i>
                    <span class="hidden md:inline">Status Lamaran</span>
                </a>
                <a href="logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 text-sm transition mt-8">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                    <span class="hidden md:inline">Psikotes</span>
                </a>
            </nav>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-1 p-6 md:p-8 overflow-y-auto">
            
            <!-- BREADCRUMB & JUDUL -->
            <div class="mb-6">
                <p class="text-xs text-gray-500 mb-1">Profile / <span class="text-gray-700 font-semibold">Lihat Data Diri</span></p>
                <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-[#0a5c36]"></i> LIHAT DATA DIRI
                </h1>
            </div>

            <!-- LAYOUT UTAMA: PROFIL DI KIRI, FORM DI KANAN -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- KARTU PROFIL DI KIRI[cite: 9] -->
                <div class="lg:col-span-3 bg-white border border-gray-200 rounded-2xl p-6 text-center shadow-sm">
                    <div class="w-28 h-28 bg-emerald-50 border-4 border-emerald-100 rounded-full mx-auto mb-4 overflow-hidden flex items-center justify-center">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=MuhammadRidho" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm">MUHAMMAD RIDHO FATHAN</h3>
                    <p class="text-xs text-gray-500 mb-4">ldhopatan.2@gmail.com</p>
                    
                    <a href="cetak_pdf.php" target="_blank" class="inline-flex items-center justify-center gap-2 w-full bg-[#0a5c36] hover:bg-opacity-90 text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-sm transition">
                        <i class="fa-solid fa-file-pdf"></i> Jadikan PDF
                    </a>
                </div>

                <!-- FORM DATA DIRI DI KANAN[cite: 9] -->
                <div class="lg:col-span-9 space-y-6">
                    
                    <!-- SECTION 1: DATA DIRI[cite: 9] -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="bg-[#0a5c36] text-white px-6 py-2.5 text-xs font-bold tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-user"></i> Data Diri
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Nama Lengkap *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Nama User</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Kode Pos *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">112041</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Jenis Kelamin *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Laki-laki</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Kota *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Bogor</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Tempat Lahir *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Bogor</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">No Handphone *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">088219497564</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Tanggal Lahir *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">04/04/2004</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">No KTP *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">1234567890</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Agama *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Islam</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Kewarganegaraan *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Indonesia</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Status *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Belum Menikah</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Jumlah Anak</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-500 font-medium mb-1">Alamat *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Jl. Pangeran Sagiri No. 404 Tanah Baru</div>
                                <p class="text-[10px] text-gray-400 mt-1">Max 60 Karakter</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: ANGGOTA KELUARGA DARURAT[cite: 9] -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="bg-[#0a5c36] text-white px-6 py-2.5 text-xs font-bold tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-users"></i> Anggota Keluarga yang dapat dihubungi dalam keadaan darurat
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Nama Keluarga *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Ibu user</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Hubungan Keluarga *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">orang tua</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">No Telepon 1 *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">088291435401</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">No Telepon 2 *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">09123123123</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-500 font-medium mb-1">Alamat Keluarga *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Jl. Pangeran Sagiri No. 404 Tanah Baru</div>
                                <p class="text-[10px] text-gray-400 mt-1">Max 60 Karakter</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: LATAR BELAKANG PENDIDIKAN[cite: 9] -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="bg-[#0a5c36] text-white px-6 py-2.5 text-xs font-bold tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-graduation-cap"></i> Latar Belakang Pendidikan
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Pendidikan Terakhir *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">S1</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Nama Sekolah/Universitas *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">universitas</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Jurusan *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">Elektro</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Nilai *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">38.00</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Mulai Tanggal *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">08/08/2022</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Selesai Tanggal *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">08/08/2026</div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: PENGALAMAN KERJA TERAKHIR[cite: 9] -->
                    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="bg-[#0a5c36] text-white px-6 py-2.5 text-xs font-bold tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-briefcase"></i> Pengalaman Kerja Terakhir
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Nama Perusahaan *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">No. Telepon *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Mulai Kerja *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Akhir Kerja *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Jabatan Awal *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div>
                                <label class="block text-gray-500 font-medium mb-1">Jabatan Akhir *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-500 font-medium mb-1">Alamat Perusahaan *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                                <p class="text-[10px] text-gray-400 mt-1">Max 60 Karakter</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-500 font-medium mb-1">Alasan Berhenti Bekerja *</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-800">-</div>
                                <p class="text-[10px] text-gray-400 mt-1">Max 60 Karakter</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

<?php 
  include "footerb4.php";
 ?>

</body>
</html>