<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{
        search: '',
        openAddModal: false,
        openEditModal: false,
        editPerangkat: {
            id: '',
            kode: '',
            nama: '',
            sn: '',
            tipe: '',
            lokasi: '',
            ip_address: '',
            mac_address: '',
            icon: 'fa-microchip'
        }
    }">
        
        <!-- Header Title & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Perangkat IoT</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola dan pantau status operasional Raspberry Pi 3, Pi Camera Node, dan RFID Scanner di lokasi lab &amp; kelas.</p>
            </div>

            <button type="button" @click="openAddModal = true" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md transition-all cursor-pointer">
                <i class="fa-solid fa-plus mr-2 text-xs"></i> Tambah Perangkat IoT
            </button>
        </div>

        <!-- 3 Hardware Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-slate-900">{{ $totalPerangkat ?? count($perangkatList) }} Perangkat</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Total Hardware Terpasang</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wifi"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-emerald-600">{{ $totalOnline ?? 0 }} Online</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Aktif Presensi Realtime</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-plug-circle-xmark"></i>
                </div>
                <div>
                    <span class="text-2xl font-extrabold text-rose-600">{{ $totalOffline ?? 0 }} Offline</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mt-0.5">Tidak Terhubung ke Server</span>
                </div>
            </div>

        </div>

        <!-- Devices List Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden space-y-4">
            
            <div class="p-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100">
                <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Daftar Perangkat Hardware IoT Kampus</h3>

                <div class="flex items-center space-x-2">
                    <input type="text" x-model="search" placeholder="Cari kode / nama / IP / lokasi..." class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium text-slate-700 outline-none w-64 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">KODE &amp; NAMA PERANGKAT</th>
                            <th class="py-4 px-6">TIPE HARDWARE</th>
                            <th class="py-4 px-6">LOKASI PENEMPATAN</th>
                            <th class="py-4 px-6">ALAMAT IP &amp; MAC</th>
                            <th class="py-4 px-6 text-center">STATUS KONEKSI</th>
                            <th class="py-4 px-6 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($perangkatList as $p)
                            <tr class="hover:bg-slate-50/80 transition-colors" x-show="!search || '{{ strtolower($p->kode . ' ' . $p->nama . ' ' . $p->tipe . ' ' . $p->lokasi . ' ' . $p->ip_address) }}'.includes(search.toLowerCase())">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl {{ $p->is_online ? 'bg-indigo-100 text-indigo-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center text-xs font-bold shadow-xs">
                                            <i class="fa-solid {{ $p->icon }}"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 text-xs block leading-tight">{{ $p->kode }}</span>
                                            <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">{{ $p->nama }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-bold text-slate-700">{{ $p->tipe }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ $p->lokasi }}</td>
                                <td class="py-4 px-6 font-mono text-slate-600 text-xs">
                                    <span class="block font-bold text-slate-800">{{ $p->ip_address }}</span>
                                    <span class="text-[10px] text-slate-400 block">MAC: {{ $p->mac_address ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if ($p->is_online)
                                        <span class="px-3 py-1 bg-emerald-100 text-emerald-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Online
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-rose-100 text-rose-700 font-extrabold text-[10px] rounded-full inline-flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span> Offline
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="editPerangkat = {
                                                    id: '{{ $p->id }}',
                                                    kode: '{{ $p->kode }}',
                                                    nama: '{{ $p->nama }}',
                                                    sn: '{{ $p->sn }}',
                                                    tipe: '{{ $p->tipe }}',
                                                    lokasi: '{{ $p->lokasi }}',
                                                    ip_address: '{{ $p->ip_address }}',
                                                    mac_address: '{{ $p->mac_address }}',
                                                    icon: '{{ $p->icon }}'
                                                }; openEditModal = true" 
                                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center">
                                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                        </button>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.perangkat.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus perangkat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl transition-all flex items-center">
                                                <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-microchip text-3xl mb-2 text-slate-300 block"></i>
                                    Belum ada perangkat IoT terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- MODAL TAMBAH PERANGKAT IOT -->
        <div x-show="openAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openAddModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openAddModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="openAddModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full p-6 space-y-5 border border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-microchip text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Tambah Perangkat Hardware IoT</h3>
                                <p class="text-xs text-slate-500 font-medium">Daftarkan node sensor atau stasiun baru ke sistem.</p>
                            </div>
                        </div>
                        <button type="button" @click="openAddModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.perangkat.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Perangkat</label>
                                <input type="text" name="kode" required placeholder="Contoh: RASP-RFID-03" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Perangkat</label>
                                <input type="text" name="nama" required placeholder="Contoh: RFID Pintu Lab 2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Hardware</label>
                                <input type="text" name="tipe" required placeholder="Contoh: RC522 RFID Reader" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Serial Number (SN)</label>
                                <input type="text" name="sn" placeholder="Contoh: SN-RPI-2026-03" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Lokasi Penempatan</label>
                            <input type="text" name="lokasi" required placeholder="Contoh: Ruang Lab Komputer 3A" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat IP (Local Network)</label>
                                <input type="text" name="ip_address" required placeholder="192.168.1.23" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">MAC Address (Opsional)</label>
                                <input type="text" name="mac_address" placeholder="B8:27:EB:XX:XX:XX" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Ikon</label>
                            <select name="icon" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="fa-id-card">💳 RFID Reader (fa-id-card)</option>
                                <option value="fa-camera">📷 Pi Camera Node (fa-camera)</option>
                                <option value="fa-microchip">🎛️ Raspberry Pi Gateway (fa-microchip)</option>
                            </select>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button type="button" @click="openAddModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md transition-all">
                                Simpan Perangkat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL EDIT PERANGKAT IOT -->
        <div x-show="openEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openEditModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="openEditModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="openEditModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full p-6 space-y-5 border border-slate-100">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Edit Perangkat Hardware IoT</h3>
                                <p class="text-xs text-slate-500 font-medium">Perbarui rincian IP, lokasi, atau tipe perangkat.</p>
                            </div>
                        </div>
                        <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('/admin/perangkat') }}/' + editPerangkat.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Perangkat</label>
                                <input type="text" name="kode" x-model="editPerangkat.kode" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Perangkat</label>
                                <input type="text" name="nama" x-model="editPerangkat.nama" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Hardware</label>
                                <input type="text" name="tipe" x-model="editPerangkat.tipe" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Serial Number (SN)</label>
                                <input type="text" name="sn" x-model="editPerangkat.sn" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Lokasi Penempatan</label>
                            <input type="text" name="lokasi" x-model="editPerangkat.lokasi" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat IP (Local Network)</label>
                                <input type="text" name="ip_address" x-model="editPerangkat.ip_address" required class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">MAC Address (Opsional)</label>
                                <input type="text" name="mac_address" x-model="editPerangkat.mac_address" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipe Ikon</label>
                            <select name="icon" x-model="editPerangkat.icon" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                                <option value="fa-id-card">💳 RFID Reader (fa-id-card)</option>
                                <option value="fa-camera">📷 Pi Camera Node (fa-camera)</option>
                                <option value="fa-microchip">🎛️ Raspberry Pi Gateway (fa-microchip)</option>
                            </select>
                        </div>

                        <div class="pt-3 flex items-center justify-end space-x-3 border-t border-slate-100">
                            <button type="button" @click="openEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md transition-all">
                                Update Perangkat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
