<x-admin-layout>
    <div class="space-y-6 pb-6" x-data="{
        selectedMahasiswaId: '{{ old('mahasiswa_id', '') }}',
        rfidUid: '{{ old('rfid_uid', '') }}',
        hasSnapshot: false,
        isSubmitting: false,
        get isValid() {
            return this.selectedMahasiswaId !== '' && this.rfidUid.trim() !== '' && this.hasSnapshot;
        }
    }">
        
        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                    <i class="fa-solid fa-microchip text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="heading-font text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Stasiun Registrasi Sensor IoT</h1>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Registrasi fisik RFID Tag Scanner &amp; Snapshot Biometrik Wajah WebRTC (Raspberry Pi).</p>
                </div>
            </div>
            <a href="{{ route('admin.mahasiswa.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all inline-flex items-center justify-center space-x-2 shrink-0">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Data Mahasiswa</span>
            </a>
        </div>

        <!-- Flash Messages Alert -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start sm:items-center justify-between shadow-xs gap-3" x-data="{ show: true }" x-show="show">
                <div class="flex items-center space-x-3 text-xs font-bold leading-relaxed">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 shrink-0 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start sm:items-center justify-between shadow-xs gap-3" x-data="{ show: true }" x-show="show">
                <div class="flex items-center space-x-3 text-xs font-bold leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700 shrink-0 p-1"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        <!-- Main Grid Container (Responsive Layout) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Side: Registration Form (7 Cols on Desktop, Full on Mobile) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                                <i class="fa-solid fa-id-card-clip text-base"></i>
                            </span>
                            <h3 class="font-bold text-sm text-white">Form Registrasi Sensor &amp; Biometrik</h3>
                        </div>
                        <span class="text-[10px] font-bold bg-indigo-500/30 text-indigo-300 px-2.5 py-1 rounded-full uppercase tracking-wider hidden sm:inline-block">Raspberry Pi Station</span>
                    </div>

                    <form action="{{ route('admin.iot-device.assign') }}" method="POST" @submit="isSubmitting = true" class="p-5 sm:p-6 space-y-5">
                        @csrf
                        
                        <!-- 1. Dropdown Mahasiswa -->
                        <div>
                            <label for="mahasiswa_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                1. Pilih Mahasiswa Terdaftar <span class="text-rose-500">*</span>
                            </label>
                            <select name="mahasiswa_id" id="mahasiswa_id" required 
                                    x-model="selectedMahasiswaId"
                                    class="block w-full rounded-xl border-slate-200 text-xs text-slate-800 p-3.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 font-semibold shadow-xs">
                                <option value="">-- Pilih Mahasiswa (Yang Belum Punya Foto/RFID) --</option>
                                
                                @if (count($mahasiswasPending) > 0)
                                    <optgroup label="⚠️ Mahasiswa Belum Lengkap (Perlu Registrasi Sensor)">
                                        @foreach ($mahasiswasPending as $mhs)
                                            <option value="{{ $mhs->id }}">
                                                {{ $mhs->nama_lengkap }} (NIM: {{ $mhs->nim }}) - Kelas {{ $mhs->kelas->nama_kelas ?? '-' }}
                                                [{{ !$mhs->foto_wajah ? 'Belum Ada Foto' : '' }}{{ !$mhs->foto_wajah && !$mhs->rfid_uid ? ' & ' : '' }}{{ !$mhs->rfid_uid ? 'Belum Ada RFID' : '' }}]
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                <optgroup label="📋 Seluruh Mahasiswa Terdaftar">
                                    @foreach ($mahasiswas as $mhs)
                                        <option value="{{ $mhs->id }}">
                                            {{ $mhs->nama_lengkap }} (NIM: {{ $mhs->nim }}) - {{ $mhs->kelas->nama_kelas ?? '-' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Pilih mahasiswa yang akan didaftarkan fisik kartu RFID dan foto biometrik wajahnya.</p>
                        </div>

                        <!-- 2. Input RFID UID Tag -->
                        <div>
                            <label for="rfid_uid" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                2. Kode RFID UID Tag Kartu <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" name="rfid_uid" id="rfid_uid" required 
                                       x-model="rfidUid" placeholder="Contoh: CF45B1E6DD" 
                                       class="block w-full rounded-xl border-slate-200 font-mono text-xs font-bold text-slate-800 p-3.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-xs">
                                <button type="button" onclick="fetchRecentRfidScan()" class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-extrabold rounded-xl border border-indigo-200 whitespace-nowrap transition-all flex items-center justify-center space-x-1.5 active:scale-95 shrink-0">
                                    <i class="fa-solid fa-barcode"></i>
                                    <span>Ambil Scan Tapping</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Tempelkan kartu RFID pada scanner Raspberry Pi atau klik tombol untuk mengambil hasil tap terbaru.</p>
                        </div>

                        <!-- 3. WebRTC Live Camera Capture (Responsive Scaling) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                3. Foto Biometrik Wajah Live (WebRTC Camera) <span class="text-rose-500">*</span>
                            </label>
                            
                            <div class="bg-slate-900 rounded-2xl p-3 sm:p-4 text-center space-y-3 relative overflow-hidden border border-slate-800 shadow-inner">
                                <!-- Proportional Scaling Video Container -->
                                <div class="relative w-full overflow-hidden rounded-xl bg-slate-950 max-h-64 flex items-center justify-center">
                                    <video id="iot-webcam" autoplay playsinline class="hidden w-full aspect-video max-h-64 rounded-xl object-cover mx-auto -scale-x-100 border border-slate-700 shadow-lg"></video>
                                    <canvas id="iot-canvas" class="hidden"></canvas>
                                    <img id="iot-preview" class="hidden w-full aspect-video max-h-64 rounded-xl object-cover mx-auto border-2 border-emerald-500 shadow-lg">
                                    
                                    <div id="iot-placeholder" class="py-8 sm:py-10 text-slate-400 space-y-2 w-full">
                                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-slate-800 text-indigo-400 flex items-center justify-center mx-auto text-xl sm:text-2xl border border-slate-700">
                                            <i class="fa-solid fa-camera"></i>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-300">Kamera WebRTC Belum Aktif</h4>
                                        <p class="text-[11px] text-slate-500 max-w-xs mx-auto">Klik tombol di bawah untuk mengaktifkan aliran kamera live webcam.</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-center gap-2 pt-2 border-t border-slate-800">
                                    <button type="button" onclick="startIotWebcam()" id="btn-iot-start" class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                        <i class="fa-solid fa-video"></i>
                                        <span>Buka Kamera Live</span>
                                    </button>
                                    <button type="button" onclick="captureIotSnapshot()" id="btn-iot-capture" class="hidden w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                        <i class="fa-solid fa-circle-dot"></i>
                                        <span>Ambil Snapshot Foto</span>
                                    </button>
                                    <button type="button" onclick="retakeIotSnapshot()" id="btn-iot-retake" class="hidden w-full sm:w-auto px-4 py-2.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        <span>Foto Ulang</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Hidden Base64 Input -->
                            <input type="hidden" name="foto_wajah_base64" id="iot_foto_wajah_base64">
                        </div>

                        <!-- 4. Submit Button (Disabled until form is valid + Loading State on Submit) -->
                        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-[11px] text-slate-500 flex items-center space-x-2 w-full sm:w-auto justify-center sm:justify-start">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" :class="isValid ? 'bg-emerald-500 animate-ping' : 'bg-amber-400'"></span>
                                <span class="font-medium" x-text="isValid ? 'Syarat validasi lengkap. Siap disimpan!' : 'Lengkapi Mahasiswa, RFID UID, & Snapshot Foto untuk menyimpan.'"></span>
                            </div>

                            <button type="submit" 
                                    :disabled="!isValid || isSubmitting"
                                    :class="{
                                        'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-md hover:shadow-lg active:scale-95': isValid &amp;&amp; !isSubmitting,
                                        'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300': !isValid || isSubmitting
                                    }"
                                    class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-extrabold transition-all inline-flex items-center justify-center space-x-2 shrink-0">
                                <template x-if="isSubmitting">
                                    <span class="inline-flex items-center">
                                        <i class="fa-solid fa-spinner fa-spin mr-2 text-sm"></i>
                                        <span>Menyimpan Registrasi IoT...</span>
                                    </span>
                                </template>
                                <template x-if="!isSubmitting">
                                    <span class="inline-flex items-center space-x-2">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Registrasi IoT &amp; Biometrik</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Side: Instructions & Pending Students List (5 Cols on Desktop, Full on Mobile) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Card Petunjuk Operasional Raspberry Pi -->
                <div class="bg-indigo-900 text-white rounded-2xl p-5 sm:p-6 shadow-md relative overflow-hidden space-y-4">
                    <div class="flex items-center space-x-3">
                        <span class="w-9 h-9 rounded-xl bg-indigo-500/30 text-indigo-300 flex items-center justify-center text-lg shrink-0">
                            <i class="fa-solid fa-circle-info"></i>
                        </span>
                        <h3 class="font-bold text-sm text-white">Panduan Operasional Raspberry Pi IoT</h3>
                    </div>

                    <ol class="text-xs text-indigo-100 space-y-2.5 list-decimal list-inside font-medium leading-relaxed">
                        <li>Pilih <strong>Mahasiswa</strong> yang baru dibuat secara administratif.</li>
                        <li>Tempelkan kartu RFID pada alat atau ketik <strong>Kode RFID UID</strong>.</li>
                        <li>Klik <strong>Buka Kamera Live</strong> dan posisikan wajah mahasiswa di depan webcam.</li>
                        <li>Klik <strong>Ambil Snapshot Foto</strong> untuk mengambil foto snapshot biometrik.</li>
                        <li>Tombol <strong>Simpan Registrasi</strong> akan aktif secara otomatis setelah semua syarat terpenuhi.</li>
                    </ol>
                </div>

                <!-- Card Mahasiswa Belum Lengkap Sensor -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
                            <span>Mahasiswa Menunggu Registrasi</span>
                        </h3>
                        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 font-extrabold text-[10px] rounded-full border border-amber-200">
                            {{ count($mahasiswasPending) }} Mahasiswa
                        </span>
                    </div>

                    <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1 no-scrollbar">
                        @forelse ($mahasiswasPending as $mhs)
                            <div @click="selectedMahasiswaId = '{{ $mhs->id }}'" class="p-3 bg-slate-50 hover:bg-indigo-50/50 rounded-xl border border-slate-200/60 cursor-pointer transition-all flex items-center justify-between group">
                                <div>
                                    <span class="font-bold text-xs text-slate-800 group-hover:text-indigo-600 block">{{ $mhs->nama_lengkap }}</span>
                                    <span class="text-[11px] font-mono text-slate-400 block mt-0.5">NIM: {{ $mhs->nim }} • {{ $mhs->kelas->nama_kelas ?? '-' }}</span>
                                </div>
                                <div class="flex items-center space-x-1 shrink-0">
                                    @if (!$mhs->foto_wajah)
                                        <span class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-bold rounded border border-rose-200">No Face</span>
                                    @endif
                                    @if (!$mhs->rfid_uid)
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[10px] font-bold rounded border border-amber-200">No RFID</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs space-y-2">
                                <i class="fa-solid fa-circle-check text-2xl text-emerald-500"></i>
                                <p class="font-bold text-slate-700">Seluruh Mahasiswa Terdaftar!</p>
                                <p class="text-[11px]">Seluruh mahasiswa telah memiliki sensor RFID dan foto biometrik terikat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- WebRTC Camera & RFID Fetch Script -->
    <script>
        let iotStream = null;

        async function startIotWebcam() {
            try {
                stopIotWebcam();
                iotStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' }
                });
                const video = document.getElementById('iot-webcam');
                const placeholder = document.getElementById('iot-placeholder');
                const preview = document.getElementById('iot-preview');
                
                video.srcObject = iotStream;
                video.classList.remove('hidden');
                placeholder.classList.add('hidden');
                preview.classList.add('hidden');

                document.getElementById('btn-iot-start').classList.add('hidden');
                document.getElementById('btn-iot-capture').classList.remove('hidden');
                document.getElementById('btn-iot-retake').classList.add('hidden');
            } catch (err) {
                let msg = 'Gagal mengakses kamera: ' + err.message;
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    msg = '⚠️ Akses kamera ditolak oleh browser! Tolong izinkan akses kamera pada pengaturan browser (HP/Desktop) Anda agar fitur snapshot biometrik dapat digunakan.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    msg = '⚠️ Perangkat webcam tidak ditemukan pada HP/Desktop Anda. Silakan hubungkan kamera terlebih dahulu.';
                }
                alert(msg);
            }
        }

        function captureIotSnapshot() {
            const video = document.getElementById('iot-webcam');
            const canvas = document.getElementById('iot-canvas');
            const preview = document.getElementById('iot-preview');
            const hiddenInput = document.getElementById('iot_foto_wajah_base64');

            if (!video || !iotStream) return;

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

            document.getElementById('btn-iot-capture').classList.add('hidden');
            document.getElementById('btn-iot-retake').classList.remove('hidden');

            // Trigger Alpine reactive variable
            const alpineContainer = document.querySelector('[x-data]');
            if (alpineContainer &amp;&amp; alpineContainer._x_dataStack) {
                alpineContainer._x_dataStack[0].hasSnapshot = true;
            }

            stopIotWebcam();
        }

        function retakeIotSnapshot() {
            document.getElementById('iot_foto_wajah_base64').value = '';
            const alpineContainer = document.querySelector('[x-data]');
            if (alpineContainer &amp;&amp; alpineContainer._x_dataStack) {
                alpineContainer._x_dataStack[0].hasSnapshot = false;
            }
            startIotWebcam();
        }

        function stopIotWebcam() {
            if (iotStream) {
                iotStream.getTracks().forEach(track => track.stop());
                iotStream = null;
            }
        }

        async function fetchRecentRfidScan() {
            try {
                const response = await fetch('/admin/rfid/scan?json=1');
                const data = await response.json();
                if (data &amp;&amp; data.scanned_uid) {
                    const input = document.getElementById('rfid_uid');
                    input.value = data.scanned_uid;
                    input.dispatchEvent(new Event('input'));
                } else {
                    alert('Belum ada kartu RFID yang di-tap pada scanner Raspberry Pi. Silakan tap kartu RFID ke alat lalu coba lagi.');
                }
            } catch (e) {
                alert('Silakan ketikkan kode RFID UID secara manual atau tap kartu RFID pada alat.');
            }
        }
    </script>
</x-admin-layout>
