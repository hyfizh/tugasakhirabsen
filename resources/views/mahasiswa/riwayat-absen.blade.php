<x-mahasiswa-layout>
    <div class="space-y-6">
        
        <!-- Header -->
        <div>
            <h1 class="heading-font text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Absensi</h1>
            <p class="text-slate-500 mt-1">Daftar kehadiran harian Anda yang terekam secara otomatis oleh sistem absensi IoT terintegrasi.</p>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-4 px-6">Tanggal</th>
                            <th class="py-4 px-6">Mata Kuliah</th>
                            <th class="py-4 px-6">Waktu Tap RFID</th>
                            <th class="py-4 px-6">Waktu Verifikasi Wajah</th>
                            <th class="py-4 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse ($absensis as $abs)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <!-- Tanggal -->
                                <td class="py-4 px-6 font-semibold text-slate-800">
                                    {{ $abs->tanggal ? $abs->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') : '-' }}
                                </td>
                                
                                <!-- Mata Kuliah -->
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-800">{{ $abs->jadwal->mataKuliah->nama_mk ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-medium">
                                        Jam Pelajaran: {{ substr($abs->jadwal->jam_mulai, 0, 5) }} - {{ substr($abs->jadwal->jam_selesai, 0, 5) }} &bull; Dosen: {{ $abs->jadwal->dosen->nama_dosen ?? '-' }}
                                    </div>
                                </td>
                                
                                <!-- Waktu Tap RFID -->
                                <td class="py-4 px-6 font-mono text-slate-600">
                                    @if($abs->waktu_tap_rfid)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded bg-indigo-50 text-indigo-750 font-bold border border-indigo-100">
                                            <i class="fa-solid fa-id-card mr-1 text-indigo-400"></i> {{ $abs->waktu_tap_rfid->format('H:i:s') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">Belum Tap</span>
                                    @endif
                                </td>
                                
                                <!-- Waktu Verifikasi Wajah -->
                                <td class="py-4 px-6">
                                    @if ($abs->waktu_verifikasi_wajah)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded bg-green-50 text-green-750 font-bold border border-green-100">
                                            <i class="fa-solid fa-face-smile-beam mr-1 text-green-400"></i> Verified ({{ $abs->waktu_verifikasi_wajah->format('H:i:s') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-50 text-slate-400 border border-slate-200">
                                            Belum Verifikasi
                                        </span>
                                    @endif
                                </td>
                                
                                <!-- Status -->
                                <td class="py-4 px-6">
                                    @if ($abs->status === 'H')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">Hadir</span>
                                    @elseif ($abs->status === 'S')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-850 border border-amber-200">Sakit</span>
                                    @elseif ($abs->status === 'I')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-850 border border-indigo-200">Izin</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">Alpa</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 px-6 text-center text-slate-400">
                                    <div class="space-y-2">
                                        <i class="fa-solid fa-folder-open text-4xl text-slate-350"></i>
                                        <p class="text-sm font-medium">Belum ada riwayat absensi terdaftar untuk Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-mahasiswa-layout>
