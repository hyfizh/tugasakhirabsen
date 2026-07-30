<x-mahasiswa-layout>
    <div class="space-y-6">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-indigo-800 to-indigo-600 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
            <div class="relative z-10 space-y-2">
                <span class="px-2.5 py-1 rounded-full bg-white/20 text-xs font-bold uppercase tracking-wider">Mahasiswa Portal</span>
                <h1 class="heading-font text-3xl font-extrabold tracking-tight">Selamat Datang, {{ $mahasiswa->nama_lengkap }}!</h1>
                <p class="text-indigo-100 max-w-md text-sm">Akses data presensi IoT Anda, lengkapi foto pengenalan wajah, dan lihat status SP di dashboard.</p>
            </div>
            <!-- Decorative Icon -->
            <div class="absolute right-6 bottom-0 text-white/10 text-[10rem] font-bold select-none pointer-events-none translate-y-8">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
        </div>

        <!-- Warning Alert for SP (Active) -->
        @if ($spActive)
            <div class="bg-rose-50 border-l-4 border-rose-500 rounded-r-lg p-5 shadow-sm flex items-start animate-pulse">
                <i class="fa-solid fa-triangle-exclamation text-rose-500 text-2xl mr-4 mt-0.5"></i>
                <div class="space-y-1">
                    <h3 class="text-sm font-bold text-rose-900">PERINGATAN KERAS: STATUS SURAT PERINGATAN (SP {{ $spActive->tingkat_sp }}) AKTIF!</h3>
                    <p class="text-xs text-rose-700">Total jam alpa Anda telah mencapai <strong class="font-bold">{{ $spActive->total_jam_alpa }} Jam Pelajaran</strong>. Silakan hubungi Kaprodi atau Dosen Pembina Akademik untuk memproses kompensasi sebelum denda berlipat ganda!</p>
                </div>
            </div>
        @endif

        <!-- Grid Status (Overview cards) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Completeness Score Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Kelengkapan Profil</span>
                    <span class="text-sm font-bold text-indigo-600 font-mono">{{ $completenessScore }}%</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $completenessScore }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400">Pastikan biodata, email, RFID UID, dan foto wajah Anda telah lengkap 100%.</p>
            </div>

            <!-- Attendance Rate Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Persentase Kehadiran</span>
                    <span class="text-sm font-bold text-emerald-600 font-mono">{{ $attendanceRate }}%</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $attendanceRate }}%"></div>
                </div>
                <p class="text-[11px] text-slate-400">Target kehadiran minimal perkuliahan adalah 80% untuk kelulusan.</p>
            </div>

            <!-- Profile Info Snapshot -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center space-x-4">
                @if ($mahasiswa->foto_wajah)
                    <img src="{{ asset('storage/' . $mahasiswa->foto_wajah) }}" alt="Wajah" class="w-16 h-16 rounded-full border border-slate-200 object-cover shadow-md">
                @else
                    <div class="w-16 h-16 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-400 flex items-center justify-center font-bold text-lg shadow-sm">
                        N/A
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $mahasiswa->nama_lengkap }}</h3>
                    <span class="text-xs text-slate-500 block mt-0.5">NIM: {{ $mahasiswa->nim }}</span>
                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 mt-1 inline-block">Kelas {{ $mahasiswa->kelas->nama_kelas ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Hadir -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center space-x-4">
                <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Hadir</span>
                    <span class="text-2xl font-black text-slate-800 font-mono mt-0.5 block">{{ $hadirCount }}</span>
                </div>
            </div>
            
            <!-- Sakit/Izin -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center space-x-4">
                <div class="p-3 rounded-lg bg-amber-50 text-amber-500">
                    <i class="fa-solid fa-envelope-open-text text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sakit / Izin</span>
                    <span class="text-2xl font-black text-slate-800 font-mono mt-0.5 block">{{ $sakitIzinCount }}</span>
                </div>
            </div>

            <!-- Alpa -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center space-x-4">
                <div class="p-3 rounded-lg bg-rose-50 text-rose-600">
                    <i class="fa-solid fa-circle-xmark text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alpa</span>
                    <span class="text-2xl font-black text-slate-800 font-mono mt-0.5 block">{{ $alpaCount }}</span>
                </div>
            </div>

            <!-- Jam Kompen -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex items-center space-x-4">
                <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jam Kompen</span>
                    <span class="text-2xl font-black text-slate-800 font-mono mt-0.5 block">{{ $alpaCount * 4 }} Jam</span>
                </div>
            </div>
        </div>

        <!-- Today's Schedule Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="heading-font font-bold text-lg text-slate-900">Jadwal Kuliah Hari Ini ({{ $hariIni }})</h3>
                <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Mata Kuliah</span>
            </div>
            
            @if($todaySchedules->isEmpty())
                <div class="py-8 text-center text-slate-400 space-y-2">
                    <i class="fa-solid fa-calendar-xmark text-3xl text-slate-300"></i>
                    <p class="text-sm font-medium">Tidak ada jadwal kuliah untuk hari ini.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                                <th class="py-2.5 px-4">Waktu</th>
                                <th class="py-2.5 px-4">Mata Kuliah</th>
                                <th class="py-2.5 px-4">Dosen Pengampu</th>
                                <th class="py-2.5 px-4 text-center">Toleransi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @foreach($todaySchedules as $jdw)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 font-mono text-slate-700 font-bold">
                                        {{ substr($jdw->jam_mulai, 0, 5) }} - {{ substr($jdw->jam_selesai, 0, 5) }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-800">
                                        {{ $jdw->mataKuliah->nama_mk ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 italic font-medium">
                                        {{ $jdw->dosen->nama_dosen ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                            {{ $jdw->toleransi_keterlambatan }} Menit
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Quick Actions Panel -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
            <h3 class="heading-font font-bold text-lg text-slate-900">Aksi Cepat Presensi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="{{ route('mahasiswa.profile.form') }}" class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/20 hover:bg-indigo-50 text-slate-800 transition-all flex items-center space-x-3">
                    <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-address-card text-lg"></i>
                    </span>
                    <div>
                        <span class="font-bold text-sm block">Biodata</span>
                        <span class="text-[11px] text-slate-500 block">Lengkapi biodata profil</span>
                    </div>
                </a>

                <a href="{{ route('mahasiswa.face.form') }}" class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/20 hover:bg-indigo-50 text-slate-800 transition-all flex items-center space-x-3">
                    <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-camera text-lg"></i>
                    </span>
                    <div>
                        <span class="font-bold text-sm block">Upload Wajah</span>
                        <span class="text-[11px] text-slate-500 block">Set foto verifikasi AI</span>
                    </div>
                </a>

                <a href="{{ route('mahasiswa.riwayat') }}" class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/20 hover:bg-indigo-50 text-slate-800 transition-all flex items-center space-x-3">
                    <span class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-clipboard-user text-lg"></i>
                    </span>
                    <div>
                        <span class="font-bold text-sm block">Riwayat Absensi</span>
                        <span class="text-[11px] text-slate-500 block">Cek riwayat kehadiran</span>
                    </div>
                </a>
            </div>
        </div>

    </div>
</x-mahasiswa-layout>
