<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ 
        openAddModal: {{ $errors->any() && !old('id') ? 'true' : 'false' }}, 
        openEditModal: {{ $errors->any() && old('id') ? 'true' : 'false' }}, 
        editId: '{{ old('id') }}', 
        editKelasId: '{{ old('kelas_id') }}', 
        editMkId: '{{ old('mata_kuliah_id') }}', 
        editDosenId: '{{ old('dosen_id') }}', 
        editHari: '{{ old('hari', 'Selasa') }}', 
        editMulai: '{{ old('jam_mulai', '08:00') }}', 
        editSelesai: '{{ old('jam_selesai', '10:30') }}', 
        viewMode: 'matrix'
    }">
        
        <!-- Header Title & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Jadwal Kuliah & Validasi IoT</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola jadwal perkuliahan, alokasi ruangan, dan monitor status validasi presensi RFID secara real-time.</p>
            </div>

            <!-- Action Buttons Bar -->
            <div class="flex items-center space-x-3">
                <!-- View Mode Switcher -->
                <div class="bg-slate-200/70 p-1 rounded-xl flex items-center space-x-1">
                    <button @click="viewMode = 'matrix'" 
                            :class="viewMode === 'matrix' ? 'bg-white text-indigo-600 shadow-sm font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center">
                        <i class="fa-solid fa-table-cells mr-1.5 text-xs"></i> Matriks Jadwal
                    </button>
                    <button @click="viewMode = 'table'" 
                            :class="viewMode === 'table' ? 'bg-white text-indigo-600 shadow-sm font-extrabold' : 'text-slate-600 hover:text-slate-900 font-semibold'" 
                            class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center">
                        <i class="fa-solid fa-list-check mr-1.5 text-xs"></i> Tabel List
                    </button>
                </div>

                <!-- Add Schedule Button -->
                <button @click="openAddModal = true" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2 text-sm"></i> Buat Jadwal Baru
                </button>
            </div>
        </div>

        <!-- Filter Bar Card for Select Class -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form action="{{ route('admin.jadwal.index') }}" method="GET" class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-slate-500 whitespace-nowrap">PILIH KELAS:</label>
                    <select name="kelas_id" onchange="this.form.submit()" 
                            class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-extrabold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition-all inline-flex items-center shadow-sm">
                    <i class="fa-solid fa-print text-slate-500 mr-2 text-sm"></i> Cetak Jadwal
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- VIEW MODE 1: MATRIKS JADWAL MINGGUAN (MATCHING USER IMAGE) -->
        <!-- ============================================================== -->
        <div x-show="viewMode === 'matrix'" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4 overflow-x-auto">
            
            <!-- Document Header Title (Matching Image Kop) -->
            <div class="text-center space-y-1 pb-2">
                <h2 class="text-base font-extrabold uppercase tracking-wider text-slate-900">JURUSAN TEKNOLOGI INFORMASI</h2>
                <h3 class="text-sm font-bold uppercase text-indigo-700 tracking-wide">{{ $selectedKelasName }} - REGULER Grup Otomat</h3>
            </div>

            <!-- Timetable Grid Matrix Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-center border-collapse border border-slate-300 text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-900 font-extrabold border-b border-slate-300">
                            <th class="py-3 px-3 border border-slate-300 text-[11px] w-28">WAKTU</th>
                            @foreach ($days as $day)
                                <th class="py-3 px-3 border border-slate-300 text-[11px] font-extrabold uppercase {{ in_array($day, ['Sabtu', 'Minggu']) ? 'bg-slate-200/70 text-slate-600' : '' }}">
                                    {{ $day }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300">
                        @foreach ($timeSlots as $slotIndex => $slotLabel)
                            <tr class="border-b border-slate-300">
                                <!-- Time Label Cell -->
                                <td class="py-3 px-2 border border-slate-300 font-mono font-bold text-[11px] text-slate-800 bg-slate-50/80 whitespace-nowrap">
                                    {{ $slotLabel }}
                                </td>

                                <!-- Day Columns -->
                                @foreach ($days as $day)
                                    @php
                                        $cell = $grid[$slotIndex][$day] ?? ['status' => 'empty'];
                                    @endphp

                                    @if ($cell['status'] === 'occupied')
                                        @php
                                            $jdw = $cell['data'];
                                        @endphp
                                        <td rowspan="{{ $cell['rowspan'] }}" class="py-3 px-3 border border-slate-300 bg-white align-middle transition-colors hover:bg-indigo-50/40">
                                            <div class="space-y-1 py-1">
                                                <!-- Kelas -->
                                                <div class="font-extrabold text-[11px] text-slate-900 tracking-tight uppercase">
                                                    {{ $jdw->kelas->nama_kelas ?? $selectedKelasName }}
                                                </div>

                                                <!-- Kode & Nama Mata Kuliah -->
                                                <div class="font-bold text-xs text-indigo-950 leading-tight">
                                                    {{ $jdw->mataKuliah->kode_mk ?? '' }} {{ $jdw->mataKuliah->nama_mk ?? 'Mata Kuliah' }} PRAKTEK
                                                </div>

                                                <!-- Dosen Pengampu -->
                                                <div class="text-[11px] font-semibold text-slate-700">
                                                    {{ $jdw->dosen->nama_dosen ?? 'Dosen Pengampu' }}
                                                </div>

                                                <!-- Ruangan -->
                                                <div class="text-[10px] font-bold font-mono text-slate-500 uppercase pt-0.5">
                                                    E306-LABOR JARINGAN 2
                                                </div>
                                            </div>
                                        </td>
                                    @elseif ($cell['status'] === 'span')
                                        {{-- Cell covered by rowspan, do not render --}}
                                    @else
                                        <td class="py-3 px-2 border border-slate-300 font-mono text-slate-300 text-xs text-center bg-white">
                                            ---
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ============================================================== -->
        <!-- VIEW MODE 2: TABEL LIST DATA -->
        <!-- ============================================================== -->
        <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">HARI & WAKTU</th>
                            <th class="py-4 px-6">MATA KULIAH</th>
                            <th class="py-4 px-6">DOSEN PENGAMPU</th>
                            <th class="py-4 px-6 text-center">KELAS</th>
                            <th class="py-4 px-6 text-center">STATUS IOT</th>
                            <th class="py-4 px-6 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($jadwals as $j)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- HARI & WAKTU -->
                                <td class="py-4 px-6">
                                    <div class="space-y-1">
                                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                            <i class="fa-solid fa-calendar-day mr-1.5 text-indigo-500"></i> {{ $j->hari }}
                                        </span>
                                        <div class="text-xs font-mono font-bold text-slate-700 block">
                                            Slot: {{ $j->jam_mulai }} - {{ $j->jam_selesai }}
                                        </div>
                                    </div>
                                </td>

                                <!-- MATA KULIAH -->
                                <td class="py-4 px-6">
                                    <div class="space-y-0.5">
                                        <span class="font-extrabold text-slate-900 text-xs block leading-tight">{{ $j->mataKuliah->nama_mk ?? 'Mata Kuliah' }}</span>
                                        <span class="text-[10px] font-mono text-slate-400 block">Kode MK: {{ $j->mataKuliah->kode_mk ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- DOSEN PENGAMPU -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-extrabold text-xs flex items-center justify-center flex-shrink-0 border border-slate-200">
                                            {{ strtoupper(substr($j->dosen->nama_dosen ?? 'D', 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-800 text-xs block leading-tight">{{ $j->dosen->nama_dosen ?? 'Dosen Pengampu' }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block mt-0.5">NIP: {{ $j->dosen->nip ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- KELAS -->
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-800 font-extrabold text-xs rounded-xl border border-slate-200/80 inline-block">
                                        {{ $j->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>

                                <!-- STATUS IOT -->
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-[11px] rounded-full inline-flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Connected IoT
                                    </span>
                                </td>

                                <!-- AKSI -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="openEditModal = true; editId = '{{ $j->id }}'; editKelasId = '{{ $j->kelas_id }}'; editMkId = '{{ $j->mata_kuliah_id }}'; editDosenId = '{{ $j->dosen_id }}'; editHari = '{{ $j->hari }}'; editMulai = '{{ $j->jam_mulai }}'; editSelesai = '{{ $j->jam_selesai }}';" 
                                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all inline-flex items-center">
                                            <i class="fa-solid fa-pencil mr-1 text-xs"></i> Edit
                                        </button>
                                        <a href="{{ route('admin.laporan.rekap') }}" 
                                           class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all inline-flex items-center">
                                            <i class="fa-regular fa-eye mr-1.5 text-xs"></i> Presensi
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-4xl mb-2 text-slate-300 block"></i>
                                    Belum ada data jadwal perkuliahan tersimpan di MySQL database.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH JADWAL BARU --}}
        {{-- ============================================================== --}}
        <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-calendar-plus text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Buat Jadwal Perkuliahan Baru</h3>
                    </div>
                    <button @click="openAddModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.jadwal.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kelas_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas</label>
                            <select name="kelas_id" id="kelas_id" required class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="mata_kuliah_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mata Kuliah</label>
                            <select name="mata_kuliah_id" id="mata_kuliah_id" required class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                <option value="">-- Pilih Mata Kuliah --</option>
                                @foreach ($mataKuliahs as $mk)
                                    <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="dosen_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Dosen Pengampu</label>
                        <select name="dosen_id" id="dosen_id" required class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            <option value="">-- Pilih Dosen Pengampu --</option>
                            @foreach ($dosens as $d)
                                <option value="{{ $d->id }}">{{ $d->nama_dosen }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="hari" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Hari</label>
                            <select name="hari" id="hari" required class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                <option value="Senin">Senin</option>
                                <option value="Selasa" selected>Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>

                        <div>
                            <label for="jam_mulai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Slot Mulai (1-10)</label>
                            <input type="number" min="1" max="10" name="jam_mulai" id="jam_mulai" required placeholder="1" value="1"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs font-mono text-slate-800 p-3">
                        </div>

                        <div>
                            <label for="jam_selesai" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Slot Selesai (1-10)</label>
                            <input type="number" min="1" max="10" name="jam_selesai" id="jam_selesai" required placeholder="3" value="3"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs font-mono text-slate-800 p-3">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
