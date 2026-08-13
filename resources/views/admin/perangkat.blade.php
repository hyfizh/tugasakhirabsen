<x-admin-layout>
    <div class="space-y-6 pb-6">
        
        <!-- Header Title & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Perangkat IoT</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola dan pantau status operasional Raspberry Pi, Camera Node, dan RFID Scanner di seluruh lokasi lab &amp; kelas.</p>
            </div>

            <button class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                <i class="fa-solid fa-plus mr-2 text-xs"></i> Tambah Perangkat IoT
            </button>
        </div>

        <!-- 4 Top Hardware Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">8 Perangkat</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Total Hardware Terpasang</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-emerald-600">7 Online</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Aktif Presensi</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-plug-circle-xmark"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-rose-600">1 Offline</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Perlu Pemeliharaan</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">4 Node AI</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Raspberry Pi Face Recog</span>
                </div>
            </div>

        </div>

        <!-- Devices List Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-4">
            
            <div class="p-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Daftar Perangkat Hardware IoT Kampus</h3>

                <div class="flex items-center space-x-2">
                    <input type="text" placeholder="Cari nama / IP / lokasi..." class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 outline-none w-56">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">KODE &amp; NAMA PERANGKAT</th>
                            <th class="py-4 px-6">TIPE HARDWARE</th>
                            <th class="py-4 px-6">LOKASI PENEMPATAN</th>
                            <th class="py-4 px-6">ALAMAT IP &amp; MAC</th>
                            <th class="py-4 px-6 text-center">STATUS KONEKSI</th>
                            <th class="py-4 px-6 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        
                        <!-- Device 1: Raspberry Pi RFID Gate 1 -->
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                        <i class="fa-solid fa-credit-card"></i>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-900 text-xs block leading-tight">RASPBERRY-RFID-01</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">SN: RPI-2026-RFID1</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-700">RC522 RFID Reader</td>
                            <td class="py-4 px-6 text-slate-600">Lab IoT Pintu Utama</td>
                            <td class="py-4 px-6 font-mono text-slate-600 text-xs">
                                <span class="block">192.168.1.101</span>
                                <span class="text-[10px] text-slate-400 block">MAC: 24:62:AB:E1:90:01</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all mr-1">Reboot</button>
                                <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                            </td>
                        </tr>

                        <!-- Device 2: Raspberry Pi Cam Lab AI -->
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xs font-bold">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-900 text-xs block leading-tight">RASPBERRY-CAM-01</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">SN: RPI-2026-AI01</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-700">Pi Camera Face Scanner</td>
                            <td class="py-4 px-6 text-slate-600">Lab Komputer 3A</td>
                            <td class="py-4 px-6 font-mono text-slate-600 text-xs">
                                <span class="block">192.168.1.104</span>
                                <span class="text-[10px] text-slate-400 block">MAC: 24:62:AB:E1:90:04</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all mr-1">Reboot</button>
                                <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                            </td>
                        </tr>

                        <!-- Device 3: Raspberry Pi RFID Lab 2B (Offline) -->
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold">
                                        <i class="fa-solid fa-plug-circle-xmark"></i>
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-900 text-xs block leading-tight">RASPBERRY-RFID-02</span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">SN: RPI-2026-RFID2</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-700">RC522 RFID Reader</td>
                            <td class="py-4 px-6 text-slate-600">Ruang Kelas EE-205</td>
                            <td class="py-4 px-6 font-mono text-slate-600 text-xs">
                                <span class="block">192.168.1.108</span>
                                <span class="text-[10px] text-slate-400 block">MAC: 24:62:AB:E1:90:08</span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 bg-rose-100 text-rose-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Offline
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition-all mr-1">Hubungkan</button>
                                <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pen-to-square text-sm"></i></button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-admin-layout>
