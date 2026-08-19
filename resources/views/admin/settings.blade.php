<x-admin-layout>
    <div class="space-y-6 pb-6">
        
        <!-- Header Title & Subtitle -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem IoT</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Konfigurasi parameter umum institusi, batas toleransi presensi, dan opsi notifikasi otomatis.</p>
            </div>
        </div>

        <!-- Settings Container Grid (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column (8 cols): General & IoT Settings Form -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center">
                            <i class="fa-solid fa-university text-indigo-600 mr-2 text-sm"></i> Profil Kampus & Institusi
                        </h3>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Informasi identitas universitas pada cetakan laporan dan header portal.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Institusi / Kampus</label>
                            <input type="text" name="nama_institusi" value="Politeknik Negeri Padang" required
                                   class="w-full rounded-xl border border-slate-200 shadow-sm text-xs font-semibold text-slate-800 p-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Notifikasi Sistem</label>
                            <input type="email" name="email_notifikasi" value="admin@pnp.ac.id" required
                                   class="w-full rounded-xl border border-slate-200 shadow-sm text-xs font-semibold text-slate-800 p-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <hr class="border-slate-100 my-4">

                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center">
                            <i class="fa-solid fa-microchip text-indigo-600 mr-2 text-sm"></i> Parameter Presensi & Hardware IoT
                        </h3>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Aturan keterlambatan dan ambang batas pencatatan presensi biometrik.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Toleransi Keterlambatan (Menit)</label>
                            <input type="number" name="toleransi_menit" value="15" required min="0" max="60"
                                   class="w-full rounded-xl border border-slate-200 shadow-sm text-xs font-bold text-slate-800 p-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ambang Akurasi Wajah AI (%)</label>
                            <input type="number" name="threshold_wajah" value="65" required min="50" max="95"
                                   class="w-full rounded-xl border border-slate-200 shadow-sm text-xs font-bold text-slate-800 p-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>

                <hr class="border-slate-100 my-6">

                <!-- FORM KONFIGURASI THRESHOLD SP OTOMATIS -->
                <form action="{{ route('admin.sp-thresholds.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight flex items-center">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-2 text-sm"></i> Konfigurasi Threshold Surat Peringatan (SP) Otomatis
                        </h3>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">Tentukan batas minimum jam Alpa untuk memicu pembuatan dan pengiriman email SP secara otomatis.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($thresholds as $th)
                            <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-slate-800">SP Level {{ $th->sp_level }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-100 text-amber-800">SP {{ $th->sp_level }}</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Minimum Alpa (Jam)</label>
                                    <input type="number" name="thresholds[{{ $th->sp_level }}][min_alpha]" value="{{ $th->min_alpha }}" required min="1" max="200"
                                           class="w-full rounded-xl border border-slate-300 shadow-sm text-xs font-bold text-slate-900 p-2.5 focus:ring-2 focus:ring-amber-500 outline-none">
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium">Jika total Alpa &ge; {{ $th->min_alpha }} jam ➔ Pemicu SP {{ $th->sp_level }} Email</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center">
                            <i class="fa-solid fa-bell mr-2"></i> Simpan Threshold SP
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column (4 cols): System Status & Security Badge -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Status Server IoT Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Status Server & Database</h3>
                    
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-slate-600 font-semibold">Koneksi Database</span>
                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded-full text-[10px]">Connected</span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-slate-600 font-semibold">Versi Laravel Framework</span>
                            <span class="font-mono font-bold text-slate-800 text-[11px]">v11.x</span>
                        </div>

                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-slate-600 font-semibold">Status Deep Learning Python</span>
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 font-bold rounded-full text-[10px]">Active (dlib)</span>
                        </div>
                    </div>
                </div>

                <!-- Backup & Maintenance Card -->
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Pemeliharaan Data</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">
                        Lakukan backup rutin basis data MySQL untuk menjaga keamanan data kehadiran mahasiswa dan log IoT.
                    </p>
                    <button class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center">
                        <i class="fa-solid fa-database mr-2"></i> Backup Database Sekarang
                    </button>
                </div>

            </div>

        </div>

    </div>
</x-admin-layout>
