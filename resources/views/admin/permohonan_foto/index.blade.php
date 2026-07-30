<x-admin-layout>
    <div class="space-y-6">
        
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-100/80">
            <div>
                <div class="flex items-center space-x-3">
                    <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl font-bold">
                        <i class="fa-solid fa-camera-retro text-lg"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Permohonan Pergantian Foto Profil Mahasiswa</h1>
                        <p class="text-xs font-medium text-slate-500 mt-0.5">Kelola dan setujui izin perubahan foto dataset wajah biometrik (Aturan 1x per 30 Hari)</p>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                    <i class="fa-solid fa-clock mr-1.5"></i> {{ $permohonans->where('status', 'pending')->count() }} Menunggu Persetujuan
                </span>
            </div>
        </div>

        <!-- Table Container Card -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100/80 space-y-4">
            
            <div class="overflow-x-hidden rounded-xl border border-slate-200/80">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-[11px] font-extrabold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                            <th class="py-3.5 px-4">No</th>
                            <th class="py-3.5 px-4">Mahasiswa</th>
                            <th class="py-3.5 px-4">NIM & Kelas</th>
                            <th class="py-3.5 px-4">Tanggal Pengajuan</th>
                            <th class="py-3.5 px-4">Alasan Permohonan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi / Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                        @forelse ($permohonans as $index => $req)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-3">
                                        @if ($req->mahasiswa->foto_wajah)
                                            <img src="{{ asset('storage/' . $req->mahasiswa->foto_wajah) }}" alt="Foto" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-xs">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 font-bold text-xs flex items-center justify-center border border-indigo-100">
                                                MH
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-extrabold text-slate-900 block leading-tight">{{ $req->mahasiswa->nama_lengkap ?? 'Mahasiswa' }}</span>
                                            <span class="text-[10px] text-slate-400 block">{{ $req->mahasiswa->email ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold">
                                    <span class="block text-slate-800">{{ $req->mahasiswa->nim ?? '-' }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 inline-block mt-0.5">
                                        {{ $req->mahasiswa->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold block text-slate-800">{{ $req->created_at->format('d M Y, H:i') }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $req->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-xs text-slate-600 italic leading-snug">"{{ $req->alasan ?? 'Mengajukan pergantian foto biometrik.' }}"</p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($req->status === 'pending')
                                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 animate-pulse inline-block">
                                            <i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu Konfirmasi
                                        </span>
                                    @elseif ($req->status === 'approved')
                                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 inline-block">
                                            <i class="fa-solid fa-circle-check mr-1"></i> Disetujui
                                        </span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 inline-block">
                                            <i class="fa-solid fa-circle-xmark mr-1"></i> Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($req->status === 'pending')
                                        <div class="flex items-center justify-center space-x-2">
                                            <form method="POST" action="{{ route('admin.permohonan-foto.approve', $req->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center space-x-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>Setujui</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.permohonan-foto.reject', $req->id) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold transition-all border border-rose-200/60 flex items-center space-x-1">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span>Tolak</span>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs font-bold italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                                    <span>Belum ada permohonan pergantian foto dari mahasiswa.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-admin-layout>
