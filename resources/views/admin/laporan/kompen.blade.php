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

    <div class="space-y-6 pb-6" x-data="{ searchQuery: '', selectedStatus: '' }">
        
        <!-- Printable Kop Surat Header for PDF Export -->
        <div class="pdf-show text-center border-b-2 border-slate-900 pb-3 mb-5">
            <h2 class="text-base font-bold uppercase tracking-wider text-black">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
            <h1 class="text-lg font-extrabold uppercase tracking-wider text-black mt-0.5">POLITEKNIK NEGERI PADANG</h1>
            <p class="text-xs text-slate-700 font-medium">Kampus Limau Manis, Pauh, Kota Padang, Sumatera Barat 25163 &bull; Telepon: (0751) 72590</p>
            <div class="border-t border-slate-900 mt-2 pt-2">
                <h3 class="text-sm font-extrabold uppercase underline text-black">LAPORAN REKAPITULASI KOMPENSASI & SURAT PERINGATAN (SP) MAHASISWA</h3>
                <p class="text-xs font-semibold text-slate-800 mt-0.5">Jurusan Teknologi Informasi &bull; Semester Ganjil TA 2023/2024</p>
            </div>
        </div>

        <!-- Header Title & Subtitle (Screen Only) -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 no-print">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kompensasi & SP</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-3xl">Pantau akumulasi jam absen mahasiswa, kelola kewajiban kompensasi, dan terbitkan Surat Peringatan (SP) secara otomatis berdasarkan data sensor IoT.</p>
            </div>
        </div>

        <!-- Top 4 KPI Stat Cards Grid (Screen Only) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 no-print">
            
            <!-- Card 1: Total Mahasiswa Alpha -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-4 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-indigo-100/80 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 font-bold text-[10px] rounded-md">
                        Bulan Ini
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ count($kompenData) }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">Total Mahasiswa SP / Kompen</span>
                </div>
            </div>

            <!-- Card 2: SP 1 Terbit -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-4 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-sky-100/80 text-sky-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 font-bold text-[10px] rounded-md">
                        Aktif
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ $kompenData->where('sp_level', 1)->count() }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">SP 1 Terbit</span>
                </div>
            </div>

            <!-- Card 3: SP 2 Terbit -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-4 transition-all hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-rose-100/80 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 font-bold text-[10px] rounded-md">
                        Aktif
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 heading-font block">{{ $kompenData->where('sp_level', 2)->count() }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-1">SP 2 Terbit</span>
                </div>
            </div>

            <!-- Card 4: SP 3 Kritis -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between space-y-4 transition-all hover:shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <span class="px-2.5 py-0.5 bg-rose-100 text-rose-700 font-extrabold text-[10px] rounded-md uppercase tracking-wider">
                        Kritis
                    </span>
                </div>
                <div>
                    <span class="text-3xl font-extrabold text-rose-700 heading-font block">{{ $kompenData->where('sp_level', 3)->count() }}</span>
                    <span class="text-[10px] font-bold text-rose-700 uppercase tracking-wider block mt-1">SP 3 Kritis</span>
                </div>
            </div>

        </div>

        <!-- Data Kompensasi & SP Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-4 print-card">
            
            <!-- Table Header Bar -->
            <div class="p-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 no-print">
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Data Kompensasi & SP</h3>

                <!-- Export & Print PDF Buttons -->
                <div class="flex items-center space-x-2.5 no-print">
                    <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition-all inline-flex items-center shadow-sm">
                        <i class="fa-solid fa-file-pdf text-rose-500 mr-2 text-sm"></i> Export PDF
                    </button>
                    <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all inline-flex items-center">
                        <i class="fa-solid fa-print mr-2 text-xs"></i> Cetak Laporan PDF
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-3.5 px-6">Mahasiswa</th>
                            <th class="py-3.5 px-6">NIM</th>
                            <th class="py-3.5 px-6">Kelas</th>
                            <th class="py-3.5 px-6 text-center">Jam Absen</th>
                            <th class="py-3.5 px-6 text-center">Jam Kompen</th>
                            <th class="py-3.5 px-6 text-center">Status SP</th>
                            <th class="py-3.5 px-6 text-right no-print">Aksi Surat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        
                        @if ($kompenData->isNotEmpty())
                            @foreach ($kompenData as $data)
                                @php
                                    $isSp3 = ($data->sp_level == 3);
                                    $isSp1 = ($data->sp_level == 1);
                                    $isSp2 = ($data->sp_level == 2);
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs no-print">
                                                {{ strtoupper(substr($data->nama_lengkap, 0, 2)) }}
                                            </div>
                                            <div>
                                                <span class="font-extrabold {{ $isSp3 ? 'text-rose-700' : 'text-slate-900' }} text-xs block leading-tight">{{ $data->nama_lengkap }}</span>
                                                <span class="text-[10px] {{ $isSp3 ? 'text-rose-400' : 'text-slate-400' }} block mt-0.5">Jurusan TI</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 font-mono {{ $isSp3 ? 'text-rose-600 font-bold' : 'text-slate-600' }} text-xs">
                                        {{ $data->nim }}
                                    </td>

                                    <td class="py-4 px-6 font-semibold {{ $isSp3 ? 'text-rose-700' : 'text-slate-600' }}">
                                        {{ $data->kelas }}
                                    </td>

                                    <td class="py-4 px-6 text-center font-bold {{ $isSp3 || $isSp2 || $isSp1 ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $data->total_alpa }} Jam
                                    </td>

                                    <td class="py-4 px-6 text-center font-bold {{ $isSp3 || $isSp2 || $isSp1 ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $data->kompen_jam }} Jam <span class="bg-slate-900 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded ml-1 no-print">2x</span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        @if ($data->sp_level == 1)
                                            <span class="px-3.5 py-1 bg-rose-100 text-rose-700 font-bold text-xs rounded-full inline-flex items-center">
                                                <i class="fa-solid fa-circle-info text-xs mr-1.5 no-print"></i> SP 1
                                            </span>
                                        @elseif ($data->sp_level == 2)
                                            <span class="px-3.5 py-1 bg-rose-200 text-rose-800 font-bold text-xs rounded-full inline-flex items-center">
                                                <i class="fa-solid fa-triangle-exclamation text-xs mr-1.5 no-print"></i> SP 2
                                            </span>
                                        @elseif ($data->sp_level == 3)
                                            <span class="px-3.5 py-1 bg-rose-800 text-white font-bold text-xs rounded-full inline-flex items-center shadow-sm">
                                                <i class="fa-solid fa-shield-halved text-xs mr-1.5 no-print"></i> SP 3
                                            </span>
                                        @else
                                            <span class="px-3.5 py-1 bg-slate-100 text-slate-600 font-bold text-xs rounded-full inline-flex items-center">
                                                • Normal
                                            </span>
                                        @endif
                                    </td>

                                     <td class="py-4 px-6 text-right no-print">
                                         <div class="flex items-center justify-end space-x-2">
                                             @if ($data->sp_level >= 1)
                                                 <a href="{{ route('admin.laporan.cetak-sp', $data->id) }}" target="_blank" 
                                                    class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold text-[11px] rounded-xl inline-flex items-center border border-indigo-200/80 transition-all">
                                                     <i class="fa-solid fa-eye mr-1"></i> Cetak Browser
                                                 </a>
                                                 <a href="{{ route('admin.laporan.download-sp-pdf', $data->id) }}" target="_blank" 
                                                    class="px-3 py-1.5 {{ $isSp3 ? 'bg-rose-800 hover:bg-rose-900 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' }} font-extrabold text-[11px] rounded-xl inline-flex items-center shadow-sm transition-all">
                                                     <i class="fa-solid fa-file-pdf mr-1"></i> Download PDF
                                                 </a>
                                             @endif
                                         </div>
                                     </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-check text-4xl mb-2 text-emerald-400 block"></i>
                                    <h4 class="font-bold text-sm text-slate-800">Tidak Ada Mahasiswa Terkena SP</h4>
                                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">Seluruh mahasiswa pada basis data MySQL saat ini dalam kondisi presensi aman dan memenuhi aturan kompensasi.</p>
                                </td>
                            </tr>
                        @endif

                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination (Screen Only) -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium no-print">
                <div>
                    Menampilkan {{ count($kompenData) }} Mahasiswa Terkena Sanksi / SP
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
                    <p class="font-bold mt-1">Kepala Laboratorium / Admin</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline">Admin EduAttend IoT</p>
                    <p>NIP/NIDN. System Generated</p>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
