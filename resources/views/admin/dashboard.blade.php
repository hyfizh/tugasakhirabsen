<x-admin-layout>
    <div class="space-y-6 pb-6">

        {{-- ============================================================== --}}
        {{-- TOP 4 STAT CARDS GRID --}}
        {{-- ============================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- Card 1: TOTAL MAHASISWA -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between transition-all hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">TOTAL MAHASISWA</span>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ number_format($totalMahasiswa) }}</span>
                </div>
                <div class="w-12 h-12 rounded-full bg-violet-100/80 text-violet-600 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-user-group"></i>
                </div>
            </div>

            <!-- Card 2: TOTAL DOSEN -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between transition-all hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">TOTAL DOSEN</span>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ number_format($totalDosen) }}</span>
                </div>
                <div class="w-12 h-12 rounded-full bg-sky-100/80 text-sky-500 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
            </div>

            <!-- Card 3: TOTAL KELAS -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between transition-all hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">TOTAL KELAS</span>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ number_format($totalKelas) }}</span>
                </div>
                <div class="w-12 h-12 rounded-full bg-indigo-100/80 text-indigo-500 flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-solid fa-door-closed"></i>
                </div>
            </div>

            <!-- Card 4: TODAY ATTENDANCE (With Circular Gauge Ring) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between transition-all hover:shadow-md">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest block">TODAY ATTENDANCE</span>
                    <div class="flex items-baseline">
                        <span class="text-3xl font-extrabold text-slate-900 heading-font">{{ $attendanceRate }}</span>
                        <span class="text-lg font-bold text-slate-500 ml-0.5">%</span>
                    </div>
                </div>
                <!-- Circular Ring Gauge -->
                <div class="relative w-12 h-12 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-indigo-600" stroke-dasharray="{{ $attendanceRate }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                </div>
            </div>

        </div>

        {{-- ============================================================== --}}
        {{-- MIDDLE ROW: ACTIVE SANCTIONS & EDGE READER STATUS --}}
        {{-- ============================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Panel (7 cols): Active Discipline Sanctions -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Active Discipline Sanctions</h2>
                    <button class="text-slate-400 hover:text-slate-600 focus:outline-none">
                        <i class="fa-solid fa-ellipsis-vertical text-lg"></i>
                    </button>
                </div>

                <!-- 3 Soft Pink/Red Cards for SP1, SP2, SP3 -->
                <div class="grid grid-cols-3 gap-4">
                    <!-- SP1 -->
                    <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-5 text-center space-y-2">
                        <span class="text-3xl font-extrabold text-rose-800 block heading-font">{{ $totalSp1 }}</span>
                        <span class="inline-block px-3 py-0.5 bg-rose-200/80 text-rose-700 text-[10px] font-extrabold rounded-md uppercase tracking-wider">
                            SP1
                        </span>
                    </div>

                    <!-- SP2 -->
                    <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-5 text-center space-y-2">
                        <span class="text-3xl font-extrabold text-rose-800 block heading-font">{{ $totalSp2 }}</span>
                        <span class="inline-block px-3 py-0.5 bg-rose-200/80 text-rose-700 text-[10px] font-extrabold rounded-md uppercase tracking-wider">
                            SP2
                        </span>
                    </div>

                    <!-- SP3 -->
                    <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-5 text-center space-y-2">
                        <span class="text-3xl font-extrabold text-rose-800 block heading-font">{{ $totalSp3 }}</span>
                        <span class="inline-block px-3 py-0.5 bg-rose-300 text-rose-900 text-[10px] font-extrabold rounded-md uppercase tracking-wider">
                            SP3
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right Panel (5 cols): Edge Reader Status -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Edge Reader Status</h2>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[11px] font-extrabold rounded-full tracking-wide">
                        Live Monitor
                    </span>
                </div>

                <!-- Readers List -->
                <div class="space-y-4">
                    <!-- Reader 1 -->
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="flex items-center space-x-3.5">
                            <span class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-wifi"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Reader-01 (Main Gate)</h4>
                                <p class="text-xs text-slate-400 font-mono">IP: 192.168.1.101</p>
                            </div>
                        </div>
                        <span class="flex items-center text-xs font-semibold text-slate-600">
                            Online <span class="w-2 h-2 rounded-full bg-indigo-600 ml-2"></span>
                        </span>
                    </div>

                    <!-- Reader 2 -->
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="flex items-center space-x-3.5">
                            <span class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-wifi"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Reader-02 (Library)</h4>
                                <p class="text-xs text-slate-400 font-mono">IP: 192.168.1.102</p>
                            </div>
                        </div>
                        <span class="flex items-center text-xs font-semibold text-slate-600">
                            Online <span class="w-2 h-2 rounded-full bg-indigo-600 ml-2"></span>
                        </span>
                    </div>

                    <!-- Reader 3 -->
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                        <div class="flex items-center space-x-3.5">
                            <span class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-wifi-slash"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Reader-03 (Lab A)</h4>
                                <p class="text-xs text-slate-400 font-mono">IP: 192.168.1.103</p>
                            </div>
                        </div>
                        <span class="flex items-center text-xs font-semibold text-slate-400">
                            Offline <span class="w-2 h-2 rounded-full bg-slate-400 ml-2"></span>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============================================================== --}}
        {{-- BOTTOM ROW: REAL-TIME LIVE ATTENDANCE & AUDIT LOG --}}
        {{-- ============================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Panel (7 cols): Real-time Live Attendance -->
            <div class="lg:col-span-7 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-4 flex flex-col">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Real-time Live Attendance</h2>
                    <a href="{{ route('admin.laporan.rekap') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                        View All
                    </a>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                                <th class="pb-3 pt-1 px-2">Student</th>
                                <th class="pb-3 pt-1 px-2">Class</th>
                                <th class="pb-3 pt-1 px-2">Time</th>
                                <th class="pb-3 pt-1 px-2">Method</th>
                                <th class="pb-3 pt-1 px-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 font-medium">
                            @if ($todayAbsensiList->isNotEmpty())
                                @foreach ($todayAbsensiList as $index => $abs)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3 px-2">
                                            <div class="flex items-center space-x-3">
                                                <img src="{{ $abs->mahasiswa->foto_wajah ? asset('storage/' . $abs->mahasiswa->foto_wajah) : 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=120' }}" 
                                                     alt="Student Avatar" class="w-8 h-8 rounded-full object-cover shadow-sm border border-slate-100">
                                                <div>
                                                    <span class="font-bold text-slate-800 block text-xs">{{ $abs->mahasiswa->nama_lengkap ?? 'Mahasiswa' }}</span>
                                                    <span class="text-[10px] text-slate-400 font-mono block">NIM: {{ $abs->mahasiswa->nim ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-2 text-slate-600 font-semibold">{{ $abs->mahasiswa->kelas->nama_kelas ?? 'CS-301' }}</td>
                                        <td class="py-3 px-2 text-slate-600 font-mono">{{ substr($abs->jam_masuk ?? '07:45:12', 0, 8) }}</td>
                                        <td class="py-3 px-2">
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-[10px] font-semibold border border-slate-200/60 inline-flex items-center">
                                                <i class="fa-solid fa-barcode text-indigo-500 mr-1.5"></i> RFID + Face
                                            </span>
                                        </td>
                                        <td class="py-3 px-2 text-center">
                                            <span class="px-3 py-1 bg-indigo-100/70 text-indigo-700 text-[11px] font-bold rounded-full inline-block">
                                                Hadir
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="py-8 px-4 text-center text-slate-400 text-xs">
                                        <i class="fa-solid fa-clock-rotate-left text-2xl mb-1 text-slate-300 block"></i>
                                        Belum ada catatan presensi hari ini di MySQL database.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel (5 cols): Audit Log -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 space-y-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-800 tracking-tight">Audit Log</h2>
                    <a href="{{ route('admin.audit-logs') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-clock-rotate-left text-base"></i>
                    </a>
                </div>

                <!-- Dynamic Timeline Items List from MySQL -->
                <div class="space-y-4">
                    @forelse ($recentLogs as $log)
                        @php
                            $isAlert = str_contains(strtoupper($log->tipe_log), 'DENIED') || str_contains(strtoupper($log->tipe_log), 'ALERT');
                            $isWarning = str_contains(strtoupper($log->tipe_log), 'WARN') || str_contains(strtoupper($log->tipe_log), 'FAIL');
                        @endphp
                        <div class="flex items-start space-x-3.5">
                            @if ($isAlert)
                                <span class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs flex-shrink-0 mt-0.5 shadow-sm">
                                    <i class="fa-solid fa-ban"></i>
                                </span>
                            @elseif ($isWarning)
                                <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-xs flex-shrink-0 mt-0.5 shadow-sm">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </span>
                            @else
                                <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs flex-shrink-0 mt-0.5 shadow-sm">
                                    <i class="fa-solid fa-microchip"></i>
                                </span>
                            @endif
                            <div class="flex-1 text-xs">
                                <p class="text-slate-700 font-semibold leading-tight">
                                    {{ $log->deskripsi }}
                                </p>
                                <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }} &bull; IP: {{ $log->ip_address ?: '127.0.0.1' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            <i class="fa-solid fa-folder-open text-2xl mb-1 text-slate-300 block"></i>
                            Belum ada log audit tersimpan di MySQL database.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
