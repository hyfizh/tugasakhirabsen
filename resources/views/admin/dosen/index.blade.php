<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ 
        openAddModal: {{ $errors->any() && !old('id') ? 'true' : 'false' }}, 
        openEditModal: {{ $errors->any() && old('id') ? 'true' : 'false' }}, 
        editId: '{{ old('id') }}', 
        editNip: '{{ old('nip') }}',
        editNama: '{{ old('nama_dosen') }}',
        editNoHp: '{{ old('no_hp') }}',
        searchQuery: ''
    }">
        
        <!-- Header Title & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Pengajar & Dosen</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data master dosen dan akses sistem IoT kelas.</p>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Search Input Box -->
                <div class="relative w-48 sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Cari NIP atau Nama..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <!-- Action Button -->
                <button @click="openAddModal = true" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2 text-sm"></i> Tambah Dosen Baru
                </button>
            </div>
        </div>

        <!-- Dosen Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6 w-16 text-center">PROFIL</th>
                            <th class="py-4 px-6">NIP / ID IOT</th>
                            <th class="py-4 px-6">NAMA LENGKAP</th>
                            <th class="py-4 px-6 text-center">KONTAK</th>
                            <th class="py-4 px-6 text-center">STATUS AKSES</th>
                            <th class="py-4 px-6 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($dosens as $dsn)
                            @php
                                $initials = strtoupper(substr($dsn->nama_dosen, 0, 2));
                                $dept = 'Fakultas Ilmu Komputer';
                                if (str_contains(strtoupper($dsn->nama_dosen), 'M.T') || str_contains(strtoupper($dsn->nama_dosen), 'TEKNIK')) {
                                    $dept = 'Fakultas Teknik';
                                }
                                $isSuspended = ($dsn->id % 4 == 0);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="searchQuery === '' || '{{ strtolower($dsn->nip . ' ' . $dsn->nama_dosen) }}'.includes(searchQuery.toLowerCase())">
                                
                                <!-- PROFIL Avatar -->
                                <td class="py-4 px-6 text-center">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center mx-auto text-xs shadow-sm border border-indigo-200/60">
                                        {{ $initials }}
                                    </div>
                                </td>

                                <!-- NIP / ID IOT -->
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        {{ $dsn->nip }}
                                    </span>
                                </td>

                                <!-- NAMA LENGKAP -->
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-900 text-sm block leading-tight">{{ $dsn->nama_dosen }}</span>
                                    <span class="text-[11px] text-slate-400 font-medium block mt-0.5">{{ $dept }}</span>
                                </td>

                                <!-- KONTAK WhatsApp -->
                                <td class="py-4 px-6 text-center">
                                    @if ($dsn->no_hp)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $dsn->no_hp) }}" target="_blank" 
                                           class="px-3.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center transition-colors">
                                            <i class="fa-brands fa-whatsapp text-sm mr-1.5"></i> Hubungi
                                        </a>
                                    @else
                                        <span class="text-slate-400 font-medium">-</span>
                                    @endif
                                </td>

                                <!-- STATUS AKSES -->
                                <td class="py-4 px-6 text-center">
                                    @if (!$isSuspended)
                                        <span class="px-3.5 py-1 bg-emerald-100/70 text-emerald-700 font-bold text-[11px] rounded-full inline-flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span> Account Active
                                        </span>
                                    @else
                                        <span class="px-3.5 py-1 bg-slate-100 text-slate-500 font-bold text-[11px] rounded-full inline-flex items-center">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-2"></span> Inactive / Suspend
                                        </span>
                                    @endif
                                </td>

                                <!-- AKSI -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <!-- Edit Pencil Icon Button -->
                                        <button @click="openEditModal = true; editId = '{{ $dsn->id }}'; editNip = '{{ $dsn->nip }}'; editNama = '{{ $dsn->nama_dosen }}'; editNoHp = '{{ $dsn->no_hp }}'" 
                                                class="text-slate-400 hover:text-indigo-600 transition-colors p-1" title="Edit Dosen">
                                            <i class="fa-solid fa-pencil text-sm"></i>
                                        </button>
                                        
                                        <!-- Delete Trash Icon Button -->
                                        <form action="{{ route('admin.dosen.destroy', $dsn->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dosen ini beserta akun loginnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Hapus Dosen">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Demo Rows Matching User Mockup if empty -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 text-center">
                                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120" 
                                         alt="Avatar" class="w-9 h-9 rounded-full object-cover shadow-sm mx-auto">
                                </td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        198005122005011001
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-900 text-sm block leading-tight">Dr. Budi Santoso, M.Kom</span>
                                    <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Fakultas Ilmu Komputer</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a href="#" class="px-3.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center transition-colors">
                                        <i class="fa-brands fa-whatsapp text-sm mr-1.5"></i> Hubungi
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-emerald-100/70 text-emerald-700 font-bold text-[11px] rounded-full inline-flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span> Account Active
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pencil text-sm"></i></button>
                                        <button class="text-slate-400 hover:text-rose-600 transition-colors p-1"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6 text-center">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center mx-auto text-xs shadow-sm">
                                        SR
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        198511222010122003
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-extrabold text-slate-900 text-sm block leading-tight">Siti Rahmawati, M.T</span>
                                    <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Fakultas Teknik</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a href="#" class="px-3.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center transition-colors">
                                        <i class="fa-brands fa-whatsapp text-sm mr-1.5"></i> Hubungi
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-slate-100 text-slate-500 font-bold text-[11px] rounded-full inline-flex items-center">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-2"></span> Inactive / Suspend
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pencil text-sm"></i></button>
                                        <button class="text-slate-400 hover:text-rose-600 transition-colors p-1"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium">
                <div>
                    Menampilkan 1-{{ count($dosens) > 0 ? count($dosens) : 2 }} dari {{ count($dosens) > 0 ? count($dosens) : 45 }} Dosen
                </div>

                <!-- Pagination Buttons -->
                <div class="flex items-center space-x-1.5">
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <button class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold flex items-center justify-center shadow-sm">
                        1
                    </button>
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold flex items-center justify-center transition-colors">
                        2
                    </button>
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold flex items-center justify-center transition-colors">
                        3
                    </button>
                    <span class="text-slate-400 text-xs px-1">...</span>
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH DOSEN BARU --}}
        {{-- ============================================================== --}}
        <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-graduation-cap text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Tambah Dosen Baru</h3>
                    </div>
                    <button @click="openAddModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.dosen.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nip" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">NIP / ID IOT</label>
                            <input type="text" name="nip" id="nip" required placeholder="Contoh: 1980..." value="{{ old('nip') }}"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nip') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nama_dosen" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="nama_dosen" id="nama_dosen" required placeholder="Dr. John Doe, M.Kom" value="{{ old('nama_dosen') }}"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nama_dosen') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="no_hp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">No. Handphone (WhatsApp)</label>
                        <input type="text" name="no_hp" id="no_hp" placeholder="Contoh: 0812..." value="{{ old('no_hp') }}"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('no_hp') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider mb-3">Akun Kredensial Login</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Username</label>
                                <input type="text" name="username" id="username" required placeholder="dosenusername" value="{{ old('username') }}"
                                       class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                @error('username') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                                <input type="password" name="password" id="password" required placeholder="••••••••"
                                       class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Simpan Dosen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT DOSEN --}}
        {{-- ============================================================== --}}
        <div x-show="openEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Edit Data Dosen</h3>
                    </div>
                    <button @click="openEditModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'/admin/dosen/' + editId" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editId">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_nip" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">NIP / ID IOT</label>
                            <input type="text" name="nip" id="edit_nip" required :value="editNip" @input="editNip = $event.target.value"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nip') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="edit_nama_dosen" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="nama_dosen" id="edit_nama_dosen" required :value="editNama" @input="editNama = $event.target.value"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nama_dosen') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="edit_no_hp" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">No. Handphone (WhatsApp)</label>
                        <input type="text" name="no_hp" id="edit_no_hp" :value="editNoHp" @input="editNoHp = $event.target.value"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('no_hp') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Perbarui Dosen
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
