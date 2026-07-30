<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ 
        openAddModal: {{ $errors->any() && !old('id') ? 'true' : 'false' }}, 
        openEditModal: {{ $errors->any() && old('id') ? 'true' : 'false' }}, 
        editId: '{{ old('id') }}', 
        editName: '{{ old('nama_kelas') }}',
        searchQuery: '',
        selectedDepartment: ''
    }">
        
        <!-- Header Title & Action Button -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Kelas</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data kelas, jadwal, dan alokasi ruangan terhubung IoT.</p>
            </div>
            <button @click="openAddModal = true" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus mr-2 text-sm"></i> Tambah Kelas Baru
            </button>
        </div>

        <!-- Search & Filter Card -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                <!-- Search Input Box -->
                <div class="relative w-full sm:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Cari nama kelas..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
                </div>

                <!-- Dropdown Jurusan / Departemen Filter -->
                <div class="w-full sm:w-auto">
                    <select x-model="selectedDepartment" class="w-full sm:w-48 px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">Semua Jurusan</option>
                        <option value="Teknologi Informasi">Teknologi Informasi</option>
                        <option value="Manajemen Informatika">Manajemen Informatika</option>
                        <option value="Teknik Elektro">Teknik Elektro</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Class Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Kelas</th>
                            <th class="py-4 px-6">Tahun Akademik</th>
                            <th class="py-4 px-6">Departemen</th>
                            <th class="py-4 px-6 text-center">Jumlah Mahasiswa</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($kelas as $kls)
                            @php
                                $dept = 'Teknologi Informasi';
                                if (str_contains(strtoupper($kls->nama_kelas), 'MI')) {
                                    $dept = 'Manajemen Informatika';
                                } elseif (str_contains(strtoupper($kls->nama_kelas), 'EE') || str_contains(strtoupper($kls->nama_kelas), 'EL')) {
                                    $dept = 'Teknik Elektro';
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="(searchQuery === '' || '{{ strtolower($kls->nama_kelas) }}'.includes(searchQuery.toLowerCase())) && (selectedDepartment === '' || '{{ $dept }}' === selectedDepartment)">
                                
                                <!-- Nama Kelas -->
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    {{ $kls->nama_kelas }}
                                </td>

                                <!-- Tahun Akademik -->
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    2023/2024
                                </td>

                                <!-- Departemen -->
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    {{ $dept }}
                                </td>

                                <!-- Jumlah Mahasiswa Badge -->
                                <td class="py-4 px-6 text-center">
                                    @if ($kls->mahasiswas_count > 0)
                                        <span class="inline-block px-3.5 py-1 rounded-full bg-indigo-100/70 text-indigo-700 font-bold text-xs">
                                            {{ $kls->mahasiswas_count }} Mahasiswa
                                        </span>
                                    @else
                                        <span class="inline-block px-3.5 py-1 rounded-full bg-slate-100 text-slate-400 font-bold text-xs">
                                            0 Mahasiswa
                                        </span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <!-- Edit Pencil Icon Button -->
                                        <button @click="openEditModal = true; editId = '{{ $kls->id }}'; editName = '{{ $kls->nama_kelas }}'" 
                                                class="text-slate-400 hover:text-indigo-600 transition-colors p-1" title="Edit Kelas">
                                            <i class="fa-solid fa-pencil text-sm"></i>
                                        </button>
                                        
                                        <!-- Delete Trash Icon Button -->
                                        <form action="{{ route('admin.kelas.destroy', $kls->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Hapus Kelas">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>

                                        <!-- Info Circle Icon Button -->
                                        @if ($kls->mahasiswas_count > 0)
                                            <button class="text-rose-400 hover:text-rose-600 transition-colors p-1" title="Info Detail">
                                                <i class="fa-regular fa-circle-question text-sm"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 px-6 text-center text-slate-400">
                                    <i class="fa-solid fa-door-closed text-4xl mb-2 text-slate-300 block"></i>
                                    <p class="text-sm font-semibold">Belum ada data kelas terdaftar</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Tambah Kelas Baru" untuk menambahkan data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-slate-500 font-medium">
                <div>
                    Menampilkan 1-{{ count($kelas) }} dari {{ count($kelas) }} kelas
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
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH KELAS BARU --}}
        {{-- ============================================================== --}}
        <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-door-closed text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Tambah Kelas Baru</h3>
                    </div>
                    <button @click="openAddModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.kelas.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="nama_kelas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="nama_kelas" required placeholder="Contoh: TI-3A, MI-2B, EE-205" value="{{ old('nama_kelas') }}"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('nama_kelas')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Simpan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT KELAS --}}
        {{-- ============================================================== --}}
        <div x-show="openEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Edit Data Kelas</h3>
                    </div>
                    <button @click="openEditModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'/admin/kelas/' + editId" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editId">

                    <div>
                        <label for="edit_nama_kelas" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Kelas</label>
                        <input type="text" name="nama_kelas" id="edit_nama_kelas" required :value="editName" @input="editName = $event.target.value"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('nama_kelas')
                            <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Perbarui Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
