<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{ scannedUid: '{{ $scannedUid ?? '' }}', checkInterval: null }" x-init="
        checkInterval = setInterval(() => {
            fetch('{{ route('admin.rfid.scan') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.rfid_uid) {
                    scannedUid = data.rfid_uid;
                } else {
                    scannedUid = '';
                }
            })
            .catch(err => console.error('RFID polling error:', err));
        }, 500);
    ">

        <!-- Title & Subtitle -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Registrasi & Validasi RFID IoT</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftarkan kartu RFID baru dengan mendekatkan kartu ke scanner IoT, lalu asosiasikan ke mahasiswa terdaftar.</p>
            </div>
            
            <a href="{{ route('admin.mahasiswa.index') }}" class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Data Mahasiswa
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column (4 cols): IoT Scanner Radar Card -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between text-center min-h-[420px]">
                
                <!-- Scanned State -->
                <div x-show="scannedUid" class="space-y-4 w-full my-auto" style="display: none;">
                    <div class="w-20 h-20 bg-indigo-100/80 rounded-full flex items-center justify-center text-indigo-600 mx-auto animate-bounce shadow-md">
                        <i class="fa-solid fa-credit-card text-3xl"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest block">KARTU TERDETEKSI!</span>
                        <h3 class="text-2xl font-extrabold text-slate-900 font-mono mt-1 tracking-wider" x-text="scannedUid"></h3>
                    </div>
                    <p class="text-xs text-slate-500 font-medium px-4">
                        Kartu di atas siap diasosiasikan ke mahasiswa di tabel sebelah kanan.
                    </p>
                    
                    <div class="pt-2">
                        <a href="{{ route('admin.rfid.clear') }}" class="inline-flex items-center text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors">
                            <i class="fa-solid fa-trash-can mr-1.5"></i> Clear Scanned UID
                        </a>
                    </div>
                </div>

                <!-- Waiting Scan State -->
                <div x-show="!scannedUid" class="space-y-5 my-auto">
                    <div class="relative w-28 h-28 mx-auto flex items-center justify-center">
                        <div class="absolute inset-0 rounded-full bg-indigo-500/20 animate-ping"></div>
                        <div class="absolute inset-3 rounded-full bg-indigo-500/30 animate-ping" style="animation-delay: 0.5s"></div>
                        <div class="w-20 h-20 bg-indigo-600 rounded-full flex items-center justify-center text-white relative shadow-lg">
                            <i class="fa-solid fa-wifi text-3xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Menunggu Tapping Kartu...</h3>
                        <p class="text-xs text-slate-500 font-medium mt-1 max-w-[220px] mx-auto">Dekatkan kartu RFID ke scanner Raspberry Pi IoT untuk membaca UID.</p>
                    </div>
                </div>

                <!-- IoT Simulation Tap Box -->
                <div class="mt-6 pt-5 border-t border-slate-100 text-left">
                    <h4 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mb-2.5">Simulasi Hardware Raspberry Pi</h4>
                    
                    <form action="{{ route('api.iot.log') }}" method="POST" target="_blank" class="space-y-2">
                        @csrf
                        <input type="hidden" name="tipe_log" value="RFID_TEMP_SCAN">
                        <input type="hidden" name="deskripsi" value="Simulasi Tap RFID Scanner">
                        
                        <div class="flex space-x-2">
                            <input type="text" name="rfid_uid" required placeholder="Simulasi UID (ex: AB12CD34)" value="9A:2B:3C:4D"
                                   class="block w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-mono font-semibold text-slate-800">
                            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold whitespace-nowrap shadow-sm">
                                Tap Card
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column (8 cols): Class Filter & Student List Table -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900 tracking-tight">Registrasi Batch Kartu RFID</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Pilih kelas untuk melihat daftar mahasiswa dan mengikat UID kartu.</p>
                    </div>

                    <!-- Filter Kelas Dropdown -->
                    <form action="{{ route('admin.rfid.scan') }}" method="GET" class="w-full sm:w-auto">
                        <select name="kelas_id" onchange="this.form.submit()" 
                                class="w-full sm:w-56 px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $k)
                                <option value="{{ $k->id }}" {{ $selectedKelasId == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if ($selectedKelasId)
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase text-[10px] tracking-wider bg-slate-50/50">
                                    <th class="py-3.5 px-5">NIM</th>
                                    <th class="py-3.5 px-5">Nama Lengkap</th>
                                    <th class="py-3.5 px-5 text-center">Status RFID Tag</th>
                                    <th class="py-3.5 px-5 text-right">Aksi Asosiasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse ($students as $mhs)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-5">
                                            <span class="bg-slate-100/90 text-slate-700 font-mono text-xs px-3 py-1 rounded-lg font-semibold inline-block">
                                                {{ $mhs->nim }}
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-5 font-extrabold text-slate-900 text-xs">
                                            {{ $mhs->nama_lengkap }}
                                        </td>

                                        <td class="py-3.5 px-5 text-center">
                                            @if ($mhs->rfid_uid)
                                                <span class="px-3 py-1 bg-blue-50 text-blue-600 font-bold text-xs rounded-full border border-blue-200/80 inline-flex items-center">
                                                    <i class="fa-solid fa-credit-card text-xs mr-1.5"></i> Linked: {{ substr($mhs->rfid_uid, 0, 8) }}
                                                </span>
                                            @else
                                                <span class="px-3 py-1 bg-slate-100 text-slate-500 font-bold text-xs rounded-full inline-flex items-center">
                                                    <i class="fa-solid fa-credit-card-slash text-xs mr-1.5"></i> Unlinked
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-3.5 px-5 text-right">
                                            <form action="{{ route('admin.rfid.assign') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="mahasiswa_id" value="{{ $mhs->id }}">
                                                <input type="hidden" name="rfid_uid" :value="scannedUid">
                                                
                                                <button type="submit" 
                                                        :disabled="!scannedUid"
                                                        :class="scannedUid ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-md' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all inline-flex items-center">
                                                    <i class="fa-solid fa-link mr-1.5"></i> Asosiasikan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 px-6 text-center text-slate-400">
                                            <i class="fa-solid fa-user-slash text-3xl mb-2 text-slate-300 block"></i>
                                            Tidak ada mahasiswa pada kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-16 px-6 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50 space-y-2">
                        <i class="fa-solid fa-hand-pointer text-4xl text-indigo-400 mb-2 block"></i>
                        <h4 class="font-extrabold text-sm text-slate-800">Silakan Pilih Kelas</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">Pilih salah satu kelas di dropdown atas untuk melihat daftar mahasiswa dan mengosiasikan kartu RFID.</p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-admin-layout>
