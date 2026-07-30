<x-admin-layout>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm 1.5cm;
            }
            .no-print, header, aside, .sidebar-wrapper, nav, button, form {
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
        selectedMatkul: 'Rancang Bangun IoT',
        selectedPeriode: 'Oktober 2023'
    }">
        
        <!-- Printable Kop Surat Header for PDF Export -->
        <div class="pdf-show text-center border-b-2 border-slate-900 pb-3 mb-5">
            <h2 class="text-base font-bold uppercase tracking-wider text-black">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h1 class="text-lg font-extrabold uppercase tracking-wider text-black mt-0.5">POLITEKNIK NEGERI PADANG</h1>
            <p class="text-xs text-slate-700 font-medium">Kampus Limau Manis, Pauh, Kota Padang, Sumatera Barat 25163 &bull; Telepon: (0751) 72590</p>
            <div class="border-t border-slate-900 mt-2 pt-2">
                <h3 class="text-sm font-extrabold uppercase underline text-black">LAPORAN REKAPITULASI PRESENSI MAHASISWA</h3>
                <p class="text-xs font-semibold text-slate-800 mt-0.5">Kelas: {{ $selectedKelas->nama_kelas ?? 'TI-3A' }} &bull; Semester: Ganjil &bull; Tahun Akademik 2023/2024</p>
            </div>
        </div>

        <!-- Header Title & Subtitle (Screen Only) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 no-print">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Laporan Kehadiran</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Generate and export attendance reports based on IoT sensor data.</p>
            </div>
        </div>

        <!-- Filter Bar Card (3 Dropdowns + Filter Button) (Screen Only) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 no-print">
            <form action="{{ route('admin.laporan.rekap') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                
                <!-- KELAS -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">KELAS</label>
                    <select name="kelas_id" id="kelas_id" x-model="selectedKelas" 
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- MATA KULIAH -->
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MATA KULIAH</label>
                    <select name="mata_kuliah_id" x-model="selectedMatkul"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="Rancang Bangun IoT">Rancang Bangun IoT</option>
                        <option value="Algoritma & Pemrograman">Algoritma & Pemrograman</option>
                        <option value="Dasar Elektronika">Dasar Elektronika</option>
                        <option value="Keamanan Jaringan">Keamanan Jaringan</option>
                    </select>
                </div>

                <!-- PERIODE BULAN -->
                <div class="sm:col-span-3">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PERIODE BULAN</label>
                    <select name="periode" x-model="selectedPeriode"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="Oktober 2023">Oktober 2023</option>
                        <option value="November 2023">November 2023</option>
                        <option value="Desember 2023">Desember 2023</option>
                        <option value="Januari 2024">Januari 2024</option>
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="sm:col-span-2">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center justify-center">
                        <i class="fa-solid fa-filter mr-2 text-xs"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Laporan Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-4 print-card">
            
            <!-- Card Header: Title & Export Buttons -->
            <div class="p-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 no-print">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Preview Laporan</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        <span x-text="selectedKelas">{{ $selectedKelas->nama_kelas ?? 'TI-3A' }}</span> - <span x-text="selectedMatkul">Rancang Bangun IoT</span> - <span x-text="selectedPeriode">Oktober 2023</span>
                    </p>
                </div>

                <!-- Action Buttons Export PDF & Print Report -->
                <div class="flex items-center space-x-2.5 no-print">
                    <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition-all inline-flex items-center shadow-sm">
                        <i class="fa-solid fa-file-pdf text-rose-500 mr-2 text-sm"></i> Export PDF
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition-all inline-flex items-center shadow-sm">
                        <i class="fa-solid fa-print text-slate-500 mr-2 text-sm"></i> Print Report
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">NIM</th>
                            <th class="py-3.5 px-6">NAMA MAHASISWA</th>
                            <th class="py-3.5 px-4 text-center">H</th>
                            <th class="py-3.5 px-4 text-center">I</th>
                            <th class="py-3.5 px-4 text-center">S</th>
                            <th class="py-3.5 px-4 text-center">A</th>
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

    </div>
</x-admin-layout>
