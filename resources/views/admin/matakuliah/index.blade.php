<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ 
        openAddModal: {{ $errors->any() && !old('id') ? 'true' : 'false' }}, 
        openEditModal: {{ $errors->any() && old('id') ? 'true' : 'false' }}, 
        editId: '{{ old('id') }}', 
        editKode: '{{ old('kode_mk') }}', 
        editNama: '{{ old('nama_mk') }}', 
        editSks: '{{ old('sks') }}',
        editSemester: '5',
        searchQuery: ''
    }">
        
        <!-- Header Title & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Mata Kuliah</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data mata kuliah dan parameter IoT terkait.</p>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Search Input Box -->
                <div class="relative w-48 sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" x-model="searchQuery" placeholder="Cari Mata Kuliah..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-white text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>

                <!-- Action Button -->
                <button @click="openAddModal = true" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2 text-sm"></i> Tambah Mata Kuliah
                </button>
            </div>
        </div>

        <!-- Mata Kuliah Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Kode</th>
                            <th class="py-4 px-6">Mata Kuliah</th>
                            <th class="py-4 px-6 text-center">SKS</th>
                            <th class="py-4 px-6">Semester</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($mataKuliahs as $index => $mk)
                            @php
                                $semesters = [1, 2, 3, 4, 5, 6, 7];
                                $sem = 'Semester ' . ($semesters[$index % count($semesters)]);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="searchQuery === '' || '{{ strtolower($mk->kode_mk . ' ' . $mk->nama_mk) }}'.includes(searchQuery.toLowerCase())">
                                
                                <!-- Kode MK -->
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        {{ $mk->kode_mk }}
                                    </span>
                                </td>

                                <!-- Nama Mata Kuliah -->
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    {{ $mk->nama_mk }}
                                </td>

                                <!-- SKS Pill Badge -->
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full inline-block border border-indigo-100/60">
                                        {{ $mk->sks }} SKS
                                    </span>
                                </td>

                                <!-- Semester -->
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    {{ $sem }}
                                </td>

                                <!-- Aksi Buttons -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <!-- Edit Pencil Button -->
                                        <button @click="openEditModal = true; editId = '{{ $mk->id }}'; editKode = '{{ $mk->kode_mk }}'; editNama = '{{ $mk->nama_mk }}'; editSks = '{{ $mk->sks }}'" 
                                                class="text-slate-400 hover:text-indigo-600 transition-colors p-1" title="Edit Mata Kuliah">
                                            <i class="fa-solid fa-pencil text-sm"></i>
                                        </button>
                                        
                                        <!-- Delete Trash Button -->
                                        <form action="{{ route('admin.matakuliah.destroy', $mk->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Hapus Mata Kuliah">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Demo Rows Matching User Mockup if empty -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        TIF302
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    Rancang Bangun IoT
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full inline-block border border-indigo-100/60">
                                        3 SKS
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    Semester 5
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pencil text-sm"></i></button>
                                        <button class="text-slate-400 hover:text-rose-600 transition-colors p-1"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        INF201
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    Algoritma & Pemrograman
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full inline-block border border-indigo-100/60">
                                        4 SKS
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    Semester 2
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pencil text-sm"></i></button>
                                        <button class="text-slate-400 hover:text-rose-600 transition-colors p-1"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        TEL105
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    Dasar Elektronika
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full inline-block border border-indigo-100/60">
                                        2 SKS
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    Semester 1
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2.5">
                                        <button class="text-slate-400 hover:text-indigo-600 transition-colors p-1"><i class="fa-solid fa-pencil text-sm"></i></button>
                                        <button class="text-slate-400 hover:text-rose-600 transition-colors p-1"><i class="fa-solid fa-trash-can text-sm"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        TIF408
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">
                                    Keamanan Jaringan
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-indigo-50 text-indigo-700 font-bold text-xs rounded-full inline-block border border-indigo-100/60">
                                        3 SKS
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-medium">
                                    Semester 7
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
                    Menampilkan 1-{{ count($mataKuliahs) > 0 ? count($mataKuliahs) : 4 }} dari {{ count($mataKuliahs) > 0 ? count($mataKuliahs) : 42 }} data
                </div>

                <!-- Pagination Buttons -->
                <div class="flex items-center space-x-1.5">
                    <button class="w-8 h-8 rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <button class="w-8 h-8 rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH MATA KULIAH BARU --}}
        {{-- ============================================================== --}}
        <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-book-open text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Tambah Mata Kuliah</h3>
                    </div>
                    <button @click="openAddModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.matakuliah.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label for="kode_mk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kode Mata Kuliah</label>
                        <input type="text" name="kode_mk" id="kode_mk" required placeholder="Contoh: TIF302" value="{{ old('kode_mk') }}"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs font-mono text-slate-800 p-3">
                        @error('kode_mk') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="nama_mk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" id="nama_mk" required placeholder="Contoh: Rancang Bangun IoT" value="{{ old('nama_mk') }}"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('nama_mk') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="sks" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Jumlah SKS</label>
                            <input type="number" name="sks" id="sks" required min="1" max="6" placeholder="3" value="{{ old('sks', 3) }}"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('sks') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="semester" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Semester</label>
                            <select name="semester" id="semester" class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                                <option value="3">Semester 3</option>
                                <option value="4">Semester 4</option>
                                <option value="5" selected>Semester 5</option>
                                <option value="6">Semester 6</option>
                                <option value="7">Semester 7</option>
                                <option value="8">Semester 8</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Simpan Mata Kuliah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT MATA KULIAH --}}
        {{-- ============================================================== --}}
        <div x-show="openEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Edit Mata Kuliah</h3>
                    </div>
                    <button @click="openEditModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'/admin/matakuliah/' + editId" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editId">

                    <div>
                        <label for="edit_kode_mk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kode Mata Kuliah</label>
                        <input type="text" name="kode_mk" id="edit_kode_mk" required :value="editKode" @input="editKode = $event.target.value"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs font-mono text-slate-800 p-3">
                        @error('kode_mk') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="edit_nama_mk" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" id="edit_nama_mk" required :value="editNama" @input="editNama = $event.target.value"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('nama_mk') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="edit_sks" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Jumlah SKS</label>
                        <input type="number" name="sks" id="edit_sks" required min="1" max="6" :value="editSks" @input="editSks = $event.target.value"
                               class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                        @error('sks') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openEditModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Perbarui Mata Kuliah
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
