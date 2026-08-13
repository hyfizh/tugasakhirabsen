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
        selectedPeriode: 'Oktober 2023',
        openModal: false
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

        <!-- Filter Bar Card (3 Dropdowns + Auto Submit) (Screen Only) -->
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

                <!-- MATA KULIAH -->
                <div class="sm:col-span-4">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MATA KULIAH</label>
                    <select name="mata_kuliah_id" id="mata_kuliah_id" onchange="this.form.submit()"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">-- Semua Mata Kuliah --</option>
                        @foreach ($mataKuliahList as $mk)
                            <option value="{{ $mk->id }}" {{ (string)$selectedMatkulId === (string)$mk->id ? 'selected' : '' }}>
                                {{ $mk->kode_mk }} - {{ $mk->nama_mk }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BULAN -->
                <div class="sm:col-span-2">
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
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Preview Laporan</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        <span>{{ $selectedKelas->nama_kelas ?? 'TI-3A' }}</span> - Periode {{ $monthsList[$bulan] ?? '' }} {{ $tahun }}
                    </p>
                </div>

                <!-- Action Buttons: Ubah Status Absensi (Sakit/Izin) & Export PDF -->
                <div class="flex flex-wrap items-center gap-2.5 no-print">
                    <button type="button" @click="openModal = true" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-sm hover:shadow-md transition-all inline-flex items-center">
                        <i class="fa-solid fa-file-medical mr-2 text-sm"></i> Ubah Status Absensi (Sakit / Izin)
                    </button>
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
        <!-- MODAL UBAH STATUS ABSENSI MAHASISWA (SURAT SAKIT / IZIN) -->
        <div x-show="openModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                            <select name="mahasiswa_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                @foreach ($students as $mhs)
                                    <option value="{{ $mhs->id }}">{{ $mhs->nim }} - {{ $mhs->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Absensi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Tanggal Absensi</label>
                            <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <!-- Status Baru -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Status Kehadiran Baru</label>
                            <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="S">Sakit (S) — Berdasarkan Surat Dokter</option>
                                <option value="I">Izin (I) — Berdasarkan Surat Izin Resmi</option>
                                <option value="H">Hadir Tepat Waktu (H)</option>
                                <option value="T">Terlambat (T)</option>
                                <option value="A">Alpa (A)</option>
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
