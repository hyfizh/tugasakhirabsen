<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ 
        openAddModal: {{ $errors->any() && !old('id') ? 'true' : 'false' }}, 
        openEditModal: {{ $errors->any() && old('id') ? 'true' : 'false' }}, 
        editId: '{{ old('id') }}', 
        editNim: '{{ old('nim') }}', 
        editNama: '{{ old('nama_lengkap') }}', 
        editEmail: '{{ old('email') }}', 
        editNoHp: '{{ old('no_hp') }}', 
        editKelasId: '{{ old('kelas_id') }}', 
        editRfid: '{{ old('rfid_uid') }}',
        searchQuery: '{{ request('search') }}',
        selectedClass: '{{ request('kelas_id') ? ($kelas->firstWhere('id', request('kelas_id'))->nama_kelas ?? '') : '' }}',
        selectedBiometric: '',
        selectedPassword: '',
        addRfid: '',
        fetchRfidForAdd() {
            fetch('{{ route('admin.rfid.scan') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data.rfid_uid) {
                        this.addRfid = data.rfid_uid;
                        alert('RFID UID berhasil dideteksi: ' + data.rfid_uid);
                    } else {
                        alert('Belum ada kartu RFID yang di-tap pada scanner IoT.');
                    }
                });
        },
        fetchRfidForEdit() {
            fetch('{{ route('admin.rfid.scan') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data.rfid_uid) {
                        this.editRfid = data.rfid_uid;
                        alert('RFID UID berhasil dideteksi: ' + data.rfid_uid);
                    } else {
                        alert('Belum ada kartu RFID yang di-tap pada scanner IoT.');
                    }
                });
        }
    }">
        
        <!-- Header Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Data Mahasiswa</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola data master mahasiswa dan status biometrik kehadiran untuk sistem IoT universitas.</p>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Export Button -->
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-semibold rounded-xl transition-all">
                    <i class="fa-solid fa-download mr-2 text-xs text-slate-500"></i> Export
                </button>

                <!-- Add Student Button -->
                <button @click="openAddModal = true" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2 text-sm"></i> Tambah Mahasiswa
                </button>
            </div>
        </div>

        <!-- Filter Panel UI Card -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-filter text-indigo-600 text-sm"></i>
                    <h3 class="font-extrabold text-slate-800 text-sm">Filter &amp; Pencarian Mahasiswa</h3>
                </div>
                <button @click="searchQuery = ''; selectedClass = ''; selectedBiometric = ''; selectedPassword = ''" 
                        class="text-xs font-bold text-slate-500 hover:text-indigo-600 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Search Keyword -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Cari NIM / Nama / Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" x-model="searchQuery" placeholder="Ketik NIM atau Nama..." 
                               class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
                    </div>
                </div>

                <!-- Filter Kelas -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Filter Kelas</label>
                    <select x-model="selectedClass" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->nama_kelas }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Biometrik & RFID -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Biometrik &amp; RFID</label>
                    <select x-model="selectedBiometric" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">Semua Status Biometrik</option>
                        <option value="enrolled">Wajah Terdaftar (Face Enrolled)</option>
                        <option value="noface">Belum Ada Foto Wajah</option>
                        <option value="linked">Kartu RFID Terhubung</option>
                        <option value="unlinked">RFID Belum Terhubung</option>
                    </select>
                </div>

                <!-- Status Password ADM -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Status Password</label>
                    <select x-model="selectedPassword" class="w-full px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                        <option value="">Semua Status Password</option>
                        <option value="default">Default / Reset ADM</option>
                        <option value="changed">Sudah Diubah Mahasiswa</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Mahasiswa Table Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Student</th>
                            <th class="py-4 px-6">NIM</th>
                            <th class="py-4 px-6">Class</th>
                            <th class="py-4 px-6 text-center">Dataset AI</th>
                            <th class="py-4 px-6 text-center">RFID Status</th>
                            <th class="py-4 px-6 text-center">Status Pass</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($mahasiswas as $mhs)
                            @php
                                $initials = strtoupper(substr($mhs->nama_lengkap, 0, 2));
                                $hasFace = !empty($mhs->foto_wajah);
                                $hasRfid = !empty($mhs->rfid_uid);
                                $isPassChanged = ($mhs->user && $mhs->user->is_password_changed);
                                $className = $mhs->kelas->nama_kelas ?? 'TI-3A';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors"
                                x-show="(searchQuery === '' || '{{ strtolower($mhs->nim . ' ' . $mhs->nama_lengkap . ' ' . $mhs->email) }}'.includes(searchQuery.toLowerCase())) &amp;&amp; (selectedClass === '' || '{{ $className }}' === selectedClass) &amp;&amp; (selectedBiometric === '' || (selectedBiometric === 'enrolled' &amp;&amp; {{ $hasFace ? 'true' : 'false' }}) || (selectedBiometric === 'noface' &amp;&amp; {{ !$hasFace ? 'true' : 'false' }}) || (selectedBiometric === 'linked' &amp;&amp; {{ $hasRfid ? 'true' : 'false' }}) || (selectedBiometric === 'unlinked' &amp;&amp; {{ !$hasRfid ? 'true' : 'false' }})) &amp;&amp; (selectedPassword === '' || (selectedPassword === 'changed' &amp;&amp; {{ $isPassChanged ? 'true' : 'false' }}) || (selectedPassword === 'default' &amp;&amp; {{ !$isPassChanged ? 'true' : 'false' }}))">
                                
                                <!-- Student Avatar & Info -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3.5">
                                        @if ($hasFace)
                                            <img src="{{ asset('storage/' . $mhs->foto_wajah) }}" alt="Student Avatar" 
                                                 class="w-9 h-9 rounded-full object-cover shadow-sm border border-slate-200">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shadow-sm border border-indigo-200/60">
                                                {{ $initials }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-extrabold text-slate-900 text-sm block leading-tight">{{ $mhs->nama_lengkap }}</span>
                                            <span class="text-[11px] text-slate-400 font-medium block mt-0.5">{{ $mhs->email ?: strtolower(str_replace(' ', '.', $mhs->nama_lengkap)) . '@student.edu' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- NIM -->
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        {{ $mhs->nim }}
                                    </span>
                                </td>

                                <!-- Class -->
                                <td class="py-4 px-6 text-slate-600 font-semibold">
                                    {{ $className }}
                                </td>

                                <!-- Dataset AI Status -->
                                <td class="py-4 px-6 text-center">
                                    @if ($hasFace)
                                        <span class="px-3.5 py-1 bg-emerald-50 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center">
                                            <i class="fa-regular fa-circle-check text-sm mr-1.5"></i> Face Enrolled
                                        </span>
                                    @else
                                        <span class="px-3.5 py-1 bg-rose-50 text-rose-600 font-bold text-xs rounded-full border border-rose-200/80 inline-flex items-center">
                                            <i class="fa-regular fa-circle-xmark text-sm mr-1.5"></i> No Face
                                        </span>
                                    @endif
                                </td>

                                <!-- RFID Status -->
                                <td class="py-4 px-6 text-center">
                                    @if ($hasRfid)
                                        <span class="px-3.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-200/80 inline-flex items-center">
                                            <i class="fa-solid fa-credit-card text-xs mr-1.5"></i> Linked: {{ substr($mhs->rfid_uid, 0, 8) }}
                                        </span>
                                    @else
                                        <span class="px-3.5 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-full inline-flex items-center">
                                            <i class="fa-solid fa-credit-card-slash text-xs mr-1.5"></i> Unlinked
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Password ADM Reset -->
                                <td class="py-4 px-6 text-center">
                                    @if ($mhs->user && $mhs->user->is_password_changed)
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-[11px] rounded-full border border-emerald-200/80 inline-flex items-center" title="Mahasiswa sudah mengubah password pribadi">
                                            <i class="fa-solid fa-lock text-[10px] mr-1.5"></i> Sudah Diubah
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-amber-50 text-amber-700 font-extrabold text-[11px] rounded-full border border-amber-200/80 inline-flex items-center animate-pulse" title="Password masih default ('12345678'). Wajib ganti saat login.">
                                            <i class="fa-solid fa-key text-[10px] mr-1.5"></i> Default / Reset
                                        </span>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Reset Password ADM Button -->
                                        <form action="{{ route('admin.mahasiswa.reset-password', $mhs->id) }}" method="POST" class="inline-block" onsubmit="return confirm('PERMINTAAN RESET PASSWORD (ADM):\n\nApakah Anda yakin ingin mereset password {{ $mhs->nama_lengkap }} (NIM: {{ $mhs->nim }}) kembali ke password default (\'12345678\')?\n\nStatus password akan diubah menjadi BELUM UBAH dan mahasiswa wajib mengganti password saat login berikutnya.');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg border border-amber-200/80 transition-all font-extrabold text-[11px] inline-flex items-center space-x-1" title="Reset Password ke Default ('12345678') & Paksa Ganti Password Saat Login">
                                                <i class="fa-solid fa-rotate-left text-xs"></i>
                                                <span class="hidden sm:inline">Reset Pass</span>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <button @click="openEditModal = true; editId = '{{ $mhs->id }}'; editNim = '{{ $mhs->nim }}'; editNama = '{{ $mhs->nama_lengkap }}'; editEmail = '{{ $mhs->email }}'; editNoHp = '{{ $mhs->no_hp }}'; editKelasId = '{{ $mhs->kelas_id }}'; editRfid = '{{ $mhs->rfid_uid }}'" 
                                                class="text-slate-400 hover:text-indigo-600 transition-colors p-1.5" title="Edit Student">
                                            <i class="fa-solid fa-pencil text-sm"></i>
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.mahasiswa.destroy', $mhs->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mahasiswa ini beserta akun loginnya?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1.5" title="Delete Student">
                                                <i class="fa-solid fa-trash-can text-sm"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <!-- Demo Rows Matching User Mockup if Empty -->
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3.5">
                                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=120" 
                                             alt="Student Avatar" class="w-9 h-9 rounded-full object-cover shadow-sm border border-slate-200">
                                        <div>
                                            <span class="font-extrabold text-slate-900 text-sm block leading-tight">Budi Santoso</span>
                                            <span class="text-[11px] text-slate-400 font-medium block mt-0.5">budi.s@student.edu</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        2241720001
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">TI-3A</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-emerald-50 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center">
                                        <i class="fa-regular fa-circle-check text-sm mr-1.5"></i> Face Enrolled
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-200/80 inline-flex items-center">
                                        <i class="fa-solid fa-credit-card text-xs mr-1.5"></i> Linked: 9A:2B:3C
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
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3.5">
                                        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&q=80&w=120" 
                                             alt="Student Avatar" class="w-9 h-9 rounded-full object-cover shadow-sm border border-slate-200">
                                        <div>
                                            <span class="font-extrabold text-slate-900 text-sm block leading-tight">Siti Aminah</span>
                                            <span class="text-[11px] text-slate-400 font-medium block mt-0.5">siti.a@student.edu</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        2241720042
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">MI-2B</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-emerald-50 text-emerald-600 font-bold text-xs rounded-full border border-emerald-200/80 inline-flex items-center">
                                        <i class="fa-regular fa-circle-check text-sm mr-1.5"></i> Face Enrolled
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-full inline-flex items-center">
                                        <i class="fa-solid fa-credit-card-slash text-xs mr-1.5"></i> Unlinked
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
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-3.5">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs shadow-sm border border-indigo-200/60">
                                            HW
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-slate-900 text-sm block leading-tight">Hendra Wijaya</span>
                                            <span class="text-[11px] text-slate-400 font-medium block mt-0.5">hendra.w@student.edu</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1.5 rounded-lg font-semibold inline-block border border-slate-200/50">
                                        2341720115
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">SI-1C</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-rose-50 text-rose-600 font-bold text-xs rounded-full border border-rose-200/80 inline-flex items-center">
                                        <i class="fa-regular fa-circle-xmark text-sm mr-1.5"></i> No Face
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3.5 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-full inline-flex items-center">
                                        <i class="fa-solid fa-credit-card-slash text-xs mr-1.5"></i> Unlinked
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
                    Showing 1-{{ count($mahasiswas) > 0 ? count($mahasiswas) : 10 }} of {{ count($mahasiswas) > 0 ? count($mahasiswas) : 1240 }} students
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
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold flex items-center justify-center transition-colors">
                        124
                    </button>
                    <button class="w-7 h-7 rounded-lg border border-slate-200 text-slate-400 hover:bg-slate-50 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH MAHASISWA BARU --}}
        {{-- ============================================================== --}}
        <div x-show="openAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-user-plus text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Tambah Mahasiswa Baru</h3>
                    </div>
                    <button @click="openAddModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.mahasiswa.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nim" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">NIM (Nomor Induk)</label>
                            <input type="text" name="nim" id="nim" required placeholder="Contoh: 2241720001" value="{{ old('nim') }}"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nim') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="nama_lengkap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" required placeholder="Contoh: Budi Santoso" value="{{ old('nama_lengkap') }}"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nama_lengkap') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="kelas_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas Terdaftar</label>
                        <select name="kelas_id" id="kelas_id" required class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @error('kelas_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="p-3.5 bg-indigo-50/70 border border-indigo-100 rounded-xl text-xs text-indigo-900 flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-info text-indigo-500 text-sm mt-0.5"></i>
                        <div class="leading-relaxed">
                            <strong>Pendaftaran Administrasi Dasar:</strong> Setelah data disimpan, registrasi fisik <strong>RFID Tag</strong> &amp; <strong>Foto Biometrik Wajah</strong> dilakukan terpisah via menu <a href="{{ route('admin.iot-device.index') }}" class="font-bold underline text-indigo-700 hover:text-indigo-900">Stasiun Registrasi Sensor IoT</a>.
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openAddModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Simpan Data Administrasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT MAHASISWA --}}
        {{-- ============================================================== --}}
        <div x-show="openEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                            <i class="fa-solid fa-pen-to-square text-base"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Edit Data Mahasiswa</h3>
                    </div>
                    <button @click="openEditModal = false" class="text-slate-400 hover:text-white p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'/admin/mahasiswa/' + editId" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" :value="editId">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_nim" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">NIM</label>
                            <input type="text" name="nim" id="edit_nim" required :value="editNim" @input="editNim = $event.target.value"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nim') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="edit_nama_lengkap" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="edit_nama_lengkap" required :value="editNama" @input="editNama = $event.target.value"
                                   class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                            @error('nama_lengkap') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_kelas_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kelas</label>
                            <select name="kelas_id" id="edit_kelas_id" required :value="editKelasId" @change="editKelasId = $event.target.value"
                                    class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs text-slate-800 p-3">
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_rfid_uid" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">RFID UID Tag</label>
                            <div class="flex space-x-2">
                                <input type="text" name="rfid_uid" id="edit_rfid_uid" :value="editRfid" @input="editRfid = $event.target.value" placeholder="Contoh: CF45B1E6DD"
                                       class="mt-1.5 block w-full rounded-xl border border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-xs font-mono text-slate-800 p-3">
                                <button type="button" @click="fetchRfidForEdit()" class="mt-1.5 px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 text-xs font-bold rounded-xl border border-indigo-200 whitespace-nowrap" title="Scan RFID dari Scanner IoT">
                                    <i class="fa-solid fa-barcode"></i> Scan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- WebRTC Camera Capture Section for Admin Edit Mahasiswa -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fa-solid fa-camera text-indigo-500 mr-1"></i> Perbarui Foto Biometrik Wajah
                        </label>
                        
                        <div class="bg-slate-900 rounded-xl p-3 text-center space-y-2 relative overflow-hidden">
                            <video id="admin-edit-webcam" autoplay playsinline class="hidden w-full max-h-40 rounded-lg object-cover mx-auto -scale-x-100 border border-slate-700"></video>
                            <canvas id="admin-edit-canvas" class="hidden"></canvas>
                            <img id="admin-edit-preview" class="hidden w-full max-h-40 rounded-lg object-cover mx-auto border border-slate-700 shadow-md">
                            
                            <div id="admin-edit-placeholder" class="py-4 text-slate-400 space-y-1">
                                <i class="fa-solid fa-camera-retro text-2xl"></i>
                                <p class="text-[11px] font-semibold">Webcam Live Selfie Belum Aktif</p>
                            </div>

                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" onclick="startAdminEditWebcam()" id="btn-admin-edit-start" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow transition-all inline-flex items-center">
                                    <i class="fa-solid fa-video mr-1.5"></i> Buka Kamera Live
                                </button>
                                <button type="button" onclick="captureAdminEditSnapshot()" id="btn-admin-edit-capture" class="hidden px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow transition-all inline-flex items-center">
                                    <i class="fa-solid fa-circle-dot mr-1.5"></i> Ambil Foto Snapshot
                                </button>
                                <button type="button" onclick="retakeAdminEditSnapshot()" id="btn-admin-edit-retake" class="hidden px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow transition-all inline-flex items-center">
                                    <i class="fa-solid fa-rotate-left mr-1.5"></i> Foto Ulang
                                </button>
                            </div>
                        </div>

                        <!-- Hidden Base64 Input -->
                        <input type="hidden" name="foto_wajah_base64" id="admin_edit_foto_wajah_base64">

                        <!-- File Input Fallback -->
                        <div>
                            <label for="edit_foto_wajah" class="block text-[11px] font-medium text-slate-500 mb-1">Atau Unggah File Foto Baru (JPG/PNG):</label>
                            <input type="file" name="foto_wajah" id="edit_foto_wajah" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" @click="openEditModal = false; stopAdminEditWebcam();" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                            Perbarui Mahasiswa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- WebRTC JavaScript for Admin Webcam Selfie -->
        <script>
            let adminAddStream = null;
            let adminEditStream = null;

            async function startAdminAddWebcam() {
                try {
                    stopAdminAddWebcam();
                    adminAddStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' }
                    });
                    const video = document.getElementById('admin-add-webcam');
                    const placeholder = document.getElementById('admin-add-placeholder');
                    const preview = document.getElementById('admin-add-preview');
                    
                    video.srcObject = adminAddStream;
                    video.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    preview.classList.add('hidden');

                    document.getElementById('btn-admin-add-start').classList.add('hidden');
                    document.getElementById('btn-admin-add-capture').classList.remove('hidden');
                    document.getElementById('btn-admin-add-retake').classList.add('hidden');
                } catch (err) {
                    alert('Gagal mengakses kamera: ' + err.message + '. Pastikan izin kamera telah diberikan pada browser.');
                }
            }

            function captureAdminAddSnapshot() {
                const video = document.getElementById('admin-add-webcam');
                const canvas = document.getElementById('admin-add-canvas');
                const preview = document.getElementById('admin-add-preview');
                const hiddenInput = document.getElementById('admin_add_foto_wajah_base64');

                if (!video || !adminAddStream) return;

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 640;
                const ctx = canvas.getContext('2d');

                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const base64Data = canvas.toDataURL('image/jpeg', 0.9);
                hiddenInput.value = base64Data;

                preview.src = base64Data;
                preview.classList.remove('hidden');
                video.classList.add('hidden');

                document.getElementById('btn-admin-add-capture').classList.add('hidden');
                document.getElementById('btn-admin-add-retake').classList.remove('hidden');

                stopAdminAddWebcam();
            }

            function retakeAdminAddSnapshot() {
                document.getElementById('admin_add_foto_wajah_base64').value = '';
                startAdminAddWebcam();
            }

            function stopAdminAddWebcam() {
                if (adminAddStream) {
                    adminAddStream.getTracks().forEach(track => track.stop());
                    adminAddStream = null;
                }
            }

            async function startAdminEditWebcam() {
                try {
                    stopAdminEditWebcam();
                    adminEditStream = await navigator.mediaDevices.getUserMedia({
                        video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' }
                    });
                    const video = document.getElementById('admin-edit-webcam');
                    const placeholder = document.getElementById('admin-edit-placeholder');
                    const preview = document.getElementById('admin-edit-preview');
                    
                    video.srcObject = adminEditStream;
                    video.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                    preview.classList.add('hidden');

                    document.getElementById('btn-admin-edit-start').classList.add('hidden');
                    document.getElementById('btn-admin-edit-capture').classList.remove('hidden');
                    document.getElementById('btn-admin-edit-retake').classList.add('hidden');
                } catch (err) {
                    alert('Gagal mengakses kamera: ' + err.message + '. Pastikan izin kamera telah diberikan pada browser.');
                }
            }

            function captureAdminEditSnapshot() {
                const video = document.getElementById('admin-edit-webcam');
                const canvas = document.getElementById('admin-edit-canvas');
                const preview = document.getElementById('admin-edit-preview');
                const hiddenInput = document.getElementById('admin_edit_foto_wajah_base64');

                if (!video || !adminEditStream) return;

                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 640;
                const ctx = canvas.getContext('2d');

                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                const base64Data = canvas.toDataURL('image/jpeg', 0.9);
                hiddenInput.value = base64Data;

                preview.src = base64Data;
                preview.classList.remove('hidden');
                video.classList.add('hidden');

                document.getElementById('btn-admin-edit-capture').classList.add('hidden');
                document.getElementById('btn-admin-edit-retake').classList.remove('hidden');

                stopAdminEditWebcam();
            }

            function retakeAdminEditSnapshot() {
                document.getElementById('admin_edit_foto_wajah_base64').value = '';
                startAdminEditWebcam();
            }

            function stopAdminEditWebcam() {
                if (adminEditStream) {
                    adminEditStream.getTracks().forEach(track => track.stop());
                    adminEditStream = null;
                }
            }
        </script>

    </div>
</x-admin-layout>
