<x-admin-layout>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm 1.5cm;
            }
            .no-print, header, aside, .sidebar-wrapper, nav, button, form, .no-print * {
                display: none !important;
            }
            body, html, main {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                width: 100% !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .print-tab-active {
                display: block !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #333 !important;
                padding: 6px 10px !important;
                color: black !important;
                font-size: 11px !important;
            }
            th {
                background-color: #f1f5f9 !important;
                font-weight: bold !important;
            }
            .pdf-show {
                display: block !important;
            }
        }
        .pdf-show {
            display: none;
        }
    </style>

    <div class="space-y-6 pb-6" x-data="{
        selectedKelas: '{{ $selectedKelas->nama_kelas ?? 'Teknologi Informasi 3A' }}',
        rekapTab: 'mingguan',
        openModal: false,
        editModalOpen: false,
        editMhsId: '',
        editMhsNama: '',
        editTanggal: '',
        editStatus: 'H'
    }">
        
        <!-- Printable Kop Surat Header for PDF Export -->
        <div class="pdf-show text-center border-b-2 border-slate-900 pb-3 mb-5">
            <h2 class="text-base font-bold uppercase tracking-wider text-black">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h1 class="text-lg font-extrabold uppercase tracking-wider text-black mt-0.5">POLITEKNIK NEGERI PADANG</h1>
            <p class="text-xs text-slate-700 font-medium">Kampus Limau Manis, Pauh, Kota Padang, Sumatera Barat 25163 &bull; Telepon: (0751) 72590</p>
            <div class="border-t border-slate-900 mt-2 pt-2">
                <h3 class="text-sm font-extrabold uppercase underline text-black" x-text="rekapTab === 'mingguan' ? 'LAPORAN REKAPITULASI PRESENSI MINGGUAN (SENIN - SABTU)' : 'LAPORAN REKAPITULASI TOTAL PRESENSI MAHASISWA'"></h3>
                <p class="text-xs font-semibold text-slate-800 mt-0.5">Kelas: {{ $selectedKelas->nama_kelas ?? 'TI-3A' }} &bull; Periode: {{ $monthsList[$bulan] ?? '' }} {{ $tahun }} (Minggu ke-{{ $minggu }}) &bull; Semester: Ganjil</p>
            </div>
        </div>

        <!-- Header Title & Subtitle (Screen Only) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 no-print">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Kehadiran</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Rekapitulasi Presensi Mingguan &amp; Bulanan Mahasiswa berbasis IoT Sensor.</p>
            </div>
        </div>

        <!-- Filter Bar Card (4 Dropdowns: Kelas, Minggu, Bulan, Tahun) (Screen Only) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 no-print">
            <form action="{{ route('admin.laporan.rekap') }}" method="GET" id="rekap-filter-form" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                
                <!-- KELAS -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">KELAS</label>
                    <select name="kelas_id" id="kelas_id" onchange="this.form.submit()" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- MINGGU KE- -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MINGGU KE-</label>
                    <select name="minggu" id="minggu" onchange="this.form.submit()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="1" {{ (int)$minggu === 1 ? 'selected' : '' }}>Minggu ke-1 (Tgl 01-07)</option>
                        <option value="2" {{ (int)$minggu === 2 ? 'selected' : '' }}>Minggu ke-2 (Tgl 08-14)</option>
                        <option value="3" {{ (int)$minggu === 3 ? 'selected' : '' }}>Minggu ke-3 (Tgl 15-21)</option>
                        <option value="4" {{ (int)$minggu === 4 ? 'selected' : '' }}>Minggu ke-4 (Tgl 22-28)</option>
                    </select>
                </div>

                <!-- BULAN -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">BULAN</label>
                    <select name="bulan" id="bulan" onchange="this.form.submit()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        @foreach ($monthsList as $num => $name)
                            <option value="{{ $num }}" {{ (int)$bulan === (int)$num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- TAHUN -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">TAHUN</label>
                    <select name="tahun" id="tahun" onchange="this.form.submit()"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        @foreach ($yearsList as $y)
                            <option value="{{ $y }}" {{ (int)$tahun === (int)$y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Preview Laporan Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-4 print-card">
            
            <!-- Card Header: Title & Export Buttons -->
            <div class="p-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 no-print">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Preview Laporan Presensi</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        <span>{{ $selectedKelas->nama_kelas ?? 'TI-3A' }}</span> - Periode {{ $monthsList[$bulan] ?? '' }} {{ $tahun }}
                    </p>
                </div>

                <!-- Action Buttons: Ubah Status Absensi (Sakit/Izin) & Download Direct PDF -->
                <div class="flex flex-wrap items-center gap-2.5 no-print">
                    <button type="button" @click="openModal = true" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-sm hover:shadow-md transition-all inline-flex items-center">
                        <i class="fa-solid fa-file-medical mr-2 text-sm"></i> Ubah Status Absensi (Sakit / Izin)
                    </button>
                    <!-- Preview PDF (Stream di Tab Baru Browser) -->
                    <a :href="'{{ route('admin.laporan.rekap.download-pdf') }}?kelas_id={{ $selectedKelasId }}&mata_kuliah_id={{ $selectedMatkulId }}&bulan={{ $bulan }}&tahun={{ $tahun }}&minggu={{ $minggu }}&type=' + rekapTab" 
                       target="_blank"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg transition-all inline-flex items-center">
                        <i class="fa-solid fa-eye mr-2 text-sm"></i> Preview PDF
                    </a>
                    <!-- Download Direct PDF File -->
                    <a :href="'{{ route('admin.laporan.rekap.download-pdf') }}?kelas_id={{ $selectedKelasId }}&mata_kuliah_id={{ $selectedMatkulId }}&bulan={{ $bulan }}&tahun={{ $tahun }}&minggu={{ $minggu }}&type=' + rekapTab + '&download=1'" 
                       target="_blank"
                       class="px-4 py-2 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md hover:shadow-lg transition-all inline-flex items-center">
                        <i class="fa-solid fa-download mr-2 text-sm"></i> Download PDF
                    </a>
                </div>
            </div>

            <!-- TAB MODE SWITCHER (Mingguan vs Rincian Jam 1-10 vs Bulanan Total) -->
            <div class="flex border-b border-slate-100 space-x-2 px-6 no-print overflow-x-auto">
                <button type="button" 
                        @click="rekapTab = 'mingguan'" 
                        :class="rekapTab === 'mingguan' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="py-3 px-4 border-b-2 text-xs flex items-center space-x-2 transition-all cursor-pointer rounded-t-xl whitespace-nowrap">
                    <i class="fa-solid fa-calendar-week"></i>
                    <span>(1) Rekap Harian (Senin - Sabtu)</span>
                </button>

                <button type="button" 
                        @click="rekapTab = 'jam'" 
                        :class="rekapTab === 'jam' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="py-3 px-4 border-b-2 text-xs flex items-center space-x-2 transition-all cursor-pointer rounded-t-xl whitespace-nowrap">
                    <i class="fa-solid fa-clock"></i>
                    <span>(2) Rincian Per Jam Matkul (Jam 1 s/d 10)</span>
                </button>

                <button type="button" 
                        @click="rekapTab = 'bulanan'" 
                        :class="rekapTab === 'bulanan' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold'"
                        class="py-3 px-4 border-b-2 text-xs flex items-center space-x-2 transition-all cursor-pointer rounded-t-xl whitespace-nowrap">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>(3) Rekap Total Bulanan &amp; Persentase (%)</span>
                </button>
            </div>

            <!-- MODE 1: TABEL REKAP ABSEN MINGGUAN (Senin s/d Sabtu) -->
            <div x-show="rekapTab === 'mingguan'" :class="rekapTab === 'mingguan' ? 'print-tab-active' : ''" x-transition class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">NIM</th>
                            <th class="py-3.5 px-6">NAMA MAHASISWA</th>
                            @foreach ($daysOfWeek as $dayName => $dateVal)
                                <th class="py-3.5 px-4 text-center min-w-[70px]">
                                    <div class="text-slate-700 font-extrabold">{{ strtoupper($dayName) }}</div>
                                    <div class="text-[9px] text-slate-400 font-normal font-mono">{{ date('d/m', strtotime($dateVal)) }}</div>
                                </th>
                            @endforeach
                            <th class="py-3.5 px-4 text-center bg-purple-50/60 text-purple-700">S</th>
                            <th class="py-3.5 px-4 text-center bg-sky-50/60 text-sky-700">I</th>
                            <th class="py-3.5 px-4 text-center bg-rose-50/60 text-rose-700">A</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($students as $mhs)
                            @php
                                $sakitW = $weeklyTotals[$mhs->id]['S'] ?? 0;
                                $izinW = $weeklyTotals[$mhs->id]['I'] ?? 0;
                                $alpaW = $weeklyTotals[$mhs->id]['A'] ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-mono text-slate-500 text-xs">{{ $mhs->nim }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        @if ($mhs->foto_wajah)
                                            <img src="{{ asset('storage/' . $mhs->foto_wajah) }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shadow-sm no-print">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-[10px] flex items-center justify-center no-print">
                                                {{ strtoupper(substr($mhs->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="font-bold text-slate-800 text-xs">{{ $mhs->nama_lengkap }}</span>
                                    </div>
                                </td>

                                @foreach ($daysOfWeek as $dayName => $dateVal)
                                    @php
                                        $status = null;
                                        $jamsList = [];
                                        if (isset($weeklyAbsensi[$mhs->id][$dateVal])) {
                                            foreach ($weeklyAbsensi[$mhs->id][$dateVal] as $jam => $st) {
                                                $status = $st;
                                                $jamsList[] = $jam;
                                            }
                                        }
                                        $jamText = !empty($jamsList) ? 'Jam ' . min($jamsList) . (count($jamsList) > 1 ? '-' . max($jamsList) : '') : '';
                                    @endphp
                                    <td class="py-4 px-4 text-center">
                                        <button type="button" @click="editMhsId = '{{ $mhs->id }}'; editMhsNama = '{{ $mhs->nama_lengkap }}'; editTanggal = '{{ $dateVal }}'; editStatus = '{{ $status ?: 'H' }}'; openModal = true"
                                                class="focus:outline-none transform hover:scale-110 transition-transform cursor-pointer" title="Klik untuk ubah status presensi {{ $mhs->nama_lengkap }} ({{ $dayName }} - {{ date('d/m/Y', strtotime($dateVal)) }}) {{ $jamText }}">
                                            @if ($status === 'H')
                                                <span class="px-2 py-1 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-[10px] font-extrabold rounded-md shadow-xs">H {{ $jamText ? "($jamText)" : '' }}</span>
                                            @elseif ($status === 'T')
                                                <span class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 text-[10px] font-extrabold rounded-md shadow-xs">T {{ $jamText ? "($jamText)" : '' }}</span>
                                            @elseif ($status === 'I')
                                                <span class="px-2 py-1 bg-sky-100 hover:bg-sky-200 text-sky-800 text-[10px] font-extrabold rounded-md shadow-xs">I {{ $jamText ? "($jamText)" : '' }}</span>
                                            @elseif ($status === 'S')
                                                <span class="px-2 py-1 bg-purple-100 hover:bg-purple-200 text-purple-800 text-[10px] font-extrabold rounded-md shadow-xs">S {{ $jamText ? "($jamText)" : '' }}</span>
                                            @elseif ($status === 'A')
                                                <span class="px-2 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 text-[10px] font-extrabold rounded-md shadow-xs">A {{ $jamText ? "($jamText)" : '' }}</span>
                                            @else
                                                <span class="text-slate-300 hover:text-indigo-600 font-mono text-[10px]">-</span>
                                            @endif
                                        </button>
                                    </td>
                                @endforeach

                                <td class="py-4 px-4 text-center text-slate-500 font-bold text-xs bg-purple-50/20">{{ $sakitW }}</td>
                                <td class="py-4 px-4 text-center text-slate-500 font-bold text-xs bg-sky-50/20">{{ $izinW }}</td>
                                <td class="py-4 px-4 text-center font-bold {{ $alpaW > 0 ? 'text-rose-600 font-extrabold' : 'text-slate-500' }} text-xs bg-rose-50/20">{{ $alpaW }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-3xl mb-2 text-slate-300 block"></i>
                                    Tidak ada data mahasiswa atau jadwal kuliah pada kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- MODE 2: TABEL RINCIAN PRESENSI PER JAM MATKUL (JAM 1 S/D 10) -->
            @php
                $englishDaysMap = [
                    'Monday'    => 'Senin',
                    'Tuesday'   => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday'  => 'Kamis',
                    'Friday'    => 'Jumat',
                    'Saturday'  => 'Sabtu',
                    'Sunday'    => 'Senin',
                ];
                $todayEnglishName = date('l');
                $todayIndoName = $englishDaysMap[$todayEnglishName] ?? 'Senin';
                $defaultDayDate = $daysOfWeek[$todayIndoName] ?? reset($daysOfWeek);
            @endphp
            <div x-show="rekapTab === 'jam'" 
                 :class="rekapTab === 'jam' ? 'print-tab-active' : ''" 
                 x-data="{ selectedDayDate: '{{ $defaultDayDate }}' }" 
                 x-transition 
                 class="overflow-x-auto" 
                 style="display: none;">
                
                @php
                    $jamTimes = [
                        1  => '07:30', 2  => '08:20', 3  => '09:10', 4  => '10:10', 5  => '11:00',
                        6  => '11:50', 7  => '13:30', 8  => '14:20', 9  => '15:10', 10 => '16:00',
                    ];
                @endphp

                <!-- SUB-HEADER BAR: SUB-TAB FILTER PILIH HARI (SENIN S/D SABTU) -->
                <div class="flex items-center space-x-2 p-4 bg-slate-50 border-b border-slate-100 overflow-x-auto no-print">
                    <span class="text-xs font-extrabold text-slate-600 uppercase tracking-wider mr-2 flex items-center whitespace-nowrap">
                        <i class="fa-solid fa-calendar-day text-indigo-600 mr-2 text-sm"></i> PILIH HARI MATKUL:
                    </span>
                    @foreach ($daysOfWeek as $dayName => $dateVal)
                        <button type="button" 
                                @click="selectedDayDate = '{{ $dateVal }}'" 
                                :class="selectedDayDate === '{{ $dateVal }}' ? 'bg-indigo-600 text-white font-extrabold shadow-md scale-105' : 'bg-white text-slate-700 hover:bg-slate-100 font-bold border border-slate-200'"
                                class="px-4 py-2 rounded-xl text-xs transition-all flex items-center cursor-pointer whitespace-nowrap">
                            <span>{{ $dayName }}</span>
                            <span class="ml-1.5 text-[10px] font-mono opacity-80">({{ date('d/m', strtotime($dateVal)) }})</span>
                        </button>
                    @endforeach
                </div>

                <!-- TABLE CONTENT PER DAY -->
                @foreach ($daysOfWeek as $dayName => $dateVal)
                    <div x-show="selectedDayDate === '{{ $dateVal }}'">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                                    <th class="py-3.5 px-6">NIM</th>
                                    <th class="py-3.5 px-6">NAMA MAHASISWA (HARI: {{ strtoupper($dayName) }} - {{ date('d/m/Y', strtotime($dateVal)) }})</th>
                                    @foreach ($jamTimes as $jNum => $jTime)
                                        <th class="py-3.5 px-2 text-center min-w-[55px]">
                                            <div class="text-indigo-700 font-extrabold">JAM {{ $jNum }}</div>
                                            <div class="text-[9px] text-slate-400 font-mono font-normal">{{ $jTime }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse ($students as $mhs)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-4 px-6 font-mono text-slate-500 text-xs">{{ $mhs->nim }}</td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                @if ($mhs->foto_wajah)
                                                    <img src="{{ asset('storage/' . $mhs->foto_wajah) }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shadow-sm no-print">
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-[10px] flex items-center justify-center no-print">
                                                        {{ strtoupper(substr($mhs->nama_lengkap, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <span class="font-bold text-slate-800 text-xs">{{ $mhs->nama_lengkap }}</span>
                                            </div>
                                        </td>

                                        @foreach ($jamTimes as $jNum => $jTime)
                                            @php
                                                $jamStatus = $weeklyAbsensi[$mhs->id][$dateVal][$jNum] ?? null;
                                            @endphp
                                            <td class="py-4 px-2 text-center">
                                                @if ($jamStatus === 'H')
                                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded-md shadow-xs" title="{{ $dayName }} Jam {{ $jNum }}: Hadir">H</span>
                                                @elseif ($jamStatus === 'T')
                                                    <span class="px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md shadow-xs" title="{{ $dayName }} Jam {{ $jNum }}: Terlambat">T</span>
                                                @elseif ($jamStatus === 'I')
                                                    <span class="px-2 py-1 bg-sky-100 text-sky-800 text-[10px] font-extrabold rounded-md shadow-xs" title="{{ $dayName }} Jam {{ $jNum }}: Izin">I</span>
                                                @elseif ($jamStatus === 'S')
                                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 text-[10px] font-extrabold rounded-md shadow-xs" title="{{ $dayName }} Jam {{ $jNum }}: Sakit">S</span>
                                                @elseif ($jamStatus === 'A')
                                                    <span class="px-2 py-1 bg-rose-100 text-rose-800 text-[10px] font-extrabold rounded-md shadow-xs" title="{{ $dayName }} Jam {{ $jNum }}: Alpa">A</span>
                                                @else
                                                    <span class="text-slate-300 font-mono text-[10px]">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="py-12 px-6 text-center text-slate-400">
                                            <i class="fa-solid fa-calendar-xmark text-3xl mb-2 text-slate-300 block"></i>
                                            Tidak ada data rincian jam matkul untuk hari {{ $dayName }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            <!-- MODE 3: TABEL REKAP BULANAN TOTAL & PERSENTASE -->
            <div x-show="rekapTab === 'bulanan'" :class="rekapTab === 'bulanan' ? 'print-tab-active' : ''" x-transition class="overflow-x-auto" style="display: none;">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">NIM</th>
                            <th class="py-3.5 px-6">NAMA MAHASISWA</th>
                            <th class="py-3.5 px-4 text-center">HADIR (H)</th>
                            <th class="py-3.5 px-4 text-center">IZIN (I)</th>
                            <th class="py-3.5 px-4 text-center">SAKIT (S)</th>
                            <th class="py-3.5 px-4 text-center">ALPA (A)</th>
                            <th class="py-3.5 px-6">KEHADIRAN (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($students as $mhs)
                            @php
                                $sakit = $monthlyTotals[$mhs->id]['S'] ?? 0;
                                $izin = $monthlyTotals[$mhs->id]['I'] ?? 0;
                                $alpa = $monthlyTotals[$mhs->id]['A'] ?? 0;
                                $hadir = 14 - ($sakit + $izin + $alpa);
                                if ($hadir < 0) $hadir = 0;
                                $percentage = round(($hadir / 14) * 100);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 font-mono text-slate-500 text-xs">{{ $mhs->nim }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        @if ($mhs->foto_wajah)
                                            <img src="{{ asset('storage/' . $mhs->foto_wajah) }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover shadow-sm no-print">
                                        @else
                                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 font-extrabold text-[10px] flex items-center justify-center no-print">
                                                {{ strtoupper(substr($mhs->nama_lengkap, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="font-bold text-slate-800 text-xs">{{ $mhs->nama_lengkap }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-extrabold text-sky-600 text-xs">{{ $hadir }}</td>
                                <td class="py-4 px-4 text-center text-slate-500 text-xs">{{ $izin }}</td>
                                <td class="py-4 px-4 text-center text-slate-500 text-xs">{{ $sakit }}</td>
                                <td class="py-4 px-4 text-center font-bold {{ $alpa > 0 ? 'text-rose-600 font-extrabold' : 'text-slate-500' }} text-xs">{{ $alpa }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-extrabold {{ $percentage < 75 ? 'text-rose-600' : 'text-sky-600' }} text-xs w-10">{{ $percentage }}%</span>
                                        <div class="w-32 bg-slate-100 h-2 rounded-full overflow-hidden no-print">
                                            <div class="{{ $percentage < 75 ? 'bg-rose-500' : 'bg-sky-400' }} h-full rounded-full" style="width: {{ $percentage }}%;"></div>
                                        </div>
                                        @if ($alpa >= 4)
                                            <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-extrabold rounded-md uppercase tracking-wider">SP1</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300 block"></i>
                                    Tidak ada data mahasiswa pada kelas ini di MySQL database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination (Screen Only) -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium no-print">
                <div>
                    Menampilkan {{ count($students) }} dari {{ count($students) }} Mahasiswa
                </div>
            </div>
        </div>

        <!-- Printable Signature Footer for PDF Export -->
        <div class="pdf-show pt-8">
            <div class="flex justify-between items-end text-xs text-black">
                <div class="text-center w-64">
                    <p>Mengetahui,</p>
                    <p class="font-bold mt-1">Ketua Jurusan Teknologi Informasi</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline">Dr. Eng. Erwadi, M.T.</p>
                    <p>NIP. 197203151998021001</p>
                </div>
                <div class="text-center w-64">
                    <p>Padang, {{ date('d F Y') }}</p>
                    <p class="font-bold mt-1">Dosen Pengampu / Admin</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline">Admin EduAttend IoT</p>
                    <p>NIP/NIDN. System Generated</p>
                </div>
            </div>
        </div>

        <!-- MODAL UBAH STATUS ABSENSI MAHASISWA (SURAT SAKIT / IZIN) -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="openModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div x-show="openModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full p-6 space-y-5 border border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-file-medical text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Ubah Status Absensi (Surat Izin/Sakit)</h3>
                                <p class="text-xs text-slate-500 font-medium">Ubah status Alpa menjadi Sakit (S) atau Izin (I) berdasarkan surat resmi.</p>
                            </div>
                        </div>
                        <button type="button" @click="openModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.absensi.update-status') }}" method="POST" class="space-y-4">
                        @csrf
                        <!-- Pilih Mahasiswa -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Pilih Mahasiswa</label>
                            <select name="mahasiswa_id" required x-model="editMhsId" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                @foreach ($students as $mhs)
                                    <option value="{{ $mhs->id }}">{{ $mhs->nim }} - {{ $mhs->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Absensi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Tanggal Absensi</label>
                            <input type="date" name="tanggal" required x-model="editTanggal" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <!-- Status Baru -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Status Kehadiran Baru</label>
                            <select name="status" required x-model="editStatus" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="H">Hadir Tepat Waktu (H)</option>
                                <option value="T">Terlambat (T)</option>
                                <option value="S">Sakit (S) — Berdasarkan Surat Dokter</option>
                                <option value="I">Izin (I) — Berdasarkan Surat Izin Resmi</option>
                                <option value="A">Alpa (A)</option>
                                <option value="DELETE">❌ Hapus Status Absensi (-)</option>
                            </select>
                        </div>

                        <!-- Keterangan / Nomor Surat -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Keterangan / Nomor Surat (Opsional)</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Surat Dokter No. 123/SD/2023" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <!-- Modal Actions -->
                        <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button type="button" @click="openModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md transition-all">
                                Simpan Perubahan Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
