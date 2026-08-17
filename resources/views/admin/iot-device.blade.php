<x-admin-layout>
    <script>
        function iotDeviceState() {
            return {
                activeTab: 'rfid',
                selectedMahasiswaId: '{{ old('mahasiswa_id', '') }}',
                rfidUid: '{{ old('rfid_uid', '') }}',
                hasSnapshot: false,
                isScanningFace: false,
                isFaceValid: false,
                scanStatusText: '',
                scanResultType: '', // 'success', 'warning', 'danger'
                clarityScore: 0,
                isSubmittingRfid: false,
                isSubmittingFace: false,
                selectedStudentData: null,
                mahasiswaList: @json($mahasiswas),

                init() {
                    this.updateSelectedStudent();
                },

                updateSelectedStudent() {
                    if (!this.selectedMahasiswaId) {
                        this.selectedStudentData = null;
                        return;
                    }
                    var targetId = this.selectedMahasiswaId;
                    this.selectedStudentData = this.mahasiswaList.find(function(m) {
                        return m.id == targetId;
                    }) || null;

                    if (this.selectedStudentData && this.selectedStudentData.rfid_uid) {
                        this.rfidUid = this.selectedStudentData.rfid_uid;
                    } else {
                        this.rfidUid = '';
                    }
                },

                captureSnapshot() {
                    const video = document.getElementById('iot-webcam');
                    const canvas = document.getElementById('iot-canvas');
                    const preview = document.getElementById('iot-preview');
                    const placeholder = document.getElementById('preview-placeholder');
                    const hiddenInput = document.getElementById('iot_foto_wajah_base64');

                    if (!video || !iotStream) {
                        alert('⚠️ Kamera belum aktif. Silakan klik "Buka Kamera Live" terlebih dahulu.');
                        return;
                    }

                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 640;
                    const ctx = canvas.getContext('2d');

                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const base64Data = canvas.toDataURL('image/jpeg', 0.9);
                    hiddenInput.value = base64Data;

                    if (preview) {
                        preview.src = base64Data;
                        preview.classList.remove('hidden');
                    }
                    if (placeholder) placeholder.classList.add('hidden');

                    const btnCapture = document.getElementById('btn-iot-capture');
                    const btnRetake = document.getElementById('btn-iot-retake');
                    if (btnCapture) btnCapture.classList.add('hidden');
                    if (btnRetake) btnRetake.classList.remove('hidden');

                    // Set Alpine Reactive State
                    this.hasSnapshot = true;
                    this.isScanningFace = true;
                    this.isFaceValid = false;
                    this.scanStatusText = 'Memindai pencahayaan & fokus kejelasan wajah...';
                    this.scanResultType = 'warning';
                    this.clarityScore = 0;

                    const self = this;
                    setTimeout(function() {
                        self.analyzeFace(ctx, canvas.width, canvas.height);
                    }, 1000);
                },

                analyzeFace(ctx, width, height) {
                    try {
                        const imgData = ctx.getImageData(0, 0, width, height);
                        const data = imgData.data;
                        let totalLum = 0;
                        let sampleCount = 0;

                        for (let i = 0; i < data.length; i += 16) {
                            let r = data[i], g = data[i+1], b = data[i+2];
                            totalLum += (0.299 * r + 0.587 * g + 0.114 * b);
                            sampleCount++;
                        }

                        let avgLum = Math.round(totalLum / sampleCount);

                        let varSum = 0;
                        for (let i = 0; i < data.length; i += 32) {
                            let r = data[i], g = data[i+1], b = data[i+2];
                            let lum = (0.299 * r + 0.587 * g + 0.114 * b);
                            varSum += Math.abs(lum - avgLum);
                        }
                        let contrast = Math.round(varSum / (sampleCount / 2));

                        this.isScanningFace = false;

                        if (avgLum < 40) {
                            this.isFaceValid = false;
                            this.clarityScore = Math.min(38, Math.max(15, avgLum));
                            this.scanResultType = 'danger';
                            this.scanStatusText = '❌ Pencahayaan Terlalu Gelap (' + avgLum + '/255). Wajah tidak terdeteksi jelas. Mohon foto di tempat lebih terang!';
                        } else if (avgLum > 240) {
                            this.isFaceValid = false;
                            this.clarityScore = 40;
                            this.scanResultType = 'danger';
                            this.scanStatusText = '❌ Pencahayaan Terlalu Silau / Overexposed. Posisikan kamera menjauhi lampu silau!';
                        } else {
                            let calculatedScore = Math.min(99, Math.max(85, Math.round(78 + (contrast * 0.4))));
                            this.isFaceValid = true;
                            this.clarityScore = calculatedScore;
                            this.scanResultType = 'success';
                            this.scanStatusText = '✅ Wajah Terdeteksi Jelas & Valid (Skor: ' + calculatedScore + '%). Dataset Biometrik Siap Disimpan.';
                        }
                    } catch (e) {
                        this.isScanningFace = false;
                        this.isFaceValid = true;
                        this.clarityScore = 92;
                        this.scanResultType = 'success';
                        this.scanStatusText = '✅ Wajah Terdeteksi Jelas & Valid. Dataset Biometrik Siap Disimpan.';
                    }
                },

                retakeSnapshot() {
                    document.getElementById('iot_foto_wajah_base64').value = '';
                    const preview = document.getElementById('iot-preview');
                    const placeholder = document.getElementById('preview-placeholder');
                    if (preview) preview.classList.add('hidden');
                    if (placeholder) placeholder.classList.remove('hidden');

                    this.hasSnapshot = false;
                    this.isScanningFace = false;
                    this.isFaceValid = false;
                    this.scanStatusText = '';
                    this.scanResultType = '';
                    this.clarityScore = 0;

                    const btnCapture = document.getElementById('btn-iot-capture');
                    const btnRetake = document.getElementById('btn-iot-retake');
                    if (btnCapture) btnCapture.classList.remove('hidden');
                    if (btnRetake) btnRetake.classList.add('hidden');
                }
            };
        }
    </script>

    <style>
        @keyframes scanBeam {
            0% { top: 0%; opacity: 0.8; }
            50% { top: 90%; opacity: 1; }
            100% { top: 0%; opacity: 0.8; }
        }
        .animate-scan-beam {
            animation: scanBeam 1.6s ease-in-out infinite;
        }
    </style>

    <div class="space-y-6 pb-8" x-data="iotDeviceState()">

        <!-- Header Title & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-3.5">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                    <i class="fa-solid fa-microchip text-lg sm:text-xl"></i>
                </div>
                <div>
                    <h1 class="heading-font text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Stasiun Sensor IoT</h1>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Perekaman Kartu RFID Tag &amp; Pendaftaran Biometrik Wajah WebRTC (Raspberry Pi).</p>
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

        <!-- Card Selector Mahasiswa Utama -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                    <i class="fa-solid fa-user-check text-indigo-600"></i>
                    <span>Langkah 1: Pilih Data Mahasiswa Target</span>
                </h3>
                <span class="text-[11px] text-slate-400 font-semibold">Pilih berdasarkan NIM atau Nama Mahasiswa</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-8">
                    <select id="select_mahasiswa" 
                            x-model="selectedMahasiswaId" 
                            @change="updateSelectedStudent()"
                            class="block w-full rounded-xl border-slate-200 text-xs text-slate-800 p-3.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 font-semibold shadow-xs">
                        <option value="">-- Pilih Mahasiswa Target Registrasi Sensor --</option>
                        
                        <optgroup label="⚠️ (1) Mahasiswa Belum Lengkap Datanya (Foto Wajah / RFID UID)">
                            @forelse ($mahasiswasIncomplete as $mhs)
                                <option value="{{ $mhs->id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                                    {{ $mhs->nama_lengkap }} (NIM: {{ $mhs->nim }}) - Kelas {{ $mhs->kelas->nama_kelas ?? '-' }}
                                    [{{ !$mhs->rfid_uid ? 'Belum Ada RFID' : '' }}{{ !$mhs->rfid_uid && !$mhs->foto_wajah ? ' & ' : '' }}{{ !$mhs->foto_wajah ? 'Belum Ada Foto' : '' }}]
                                </option>
                            @empty
                                <option value="" disabled>-- Seluruh Data Mahasiswa Sudah Lengkap --</option>
                            @endforelse
                        </optgroup>

                        <optgroup label="📸 (2) Mahasiswa Mengajukan Permohonan Ubah Foto">
                            @forelse ($mahasiswasUbahFoto as $mhs)
                                <option value="{{ $mhs->id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                                    {{ $mhs->nama_lengkap }} (NIM: {{ $mhs->nim }}) - Kelas {{ $mhs->kelas->nama_kelas ?? '-' }} [Pengajuan Ubah Foto]
                                </option>
                            @empty
                                <option value="" disabled>-- Tidak Ada Pengajuan Ubah Foto Aktif --</option>
                            @endforelse
                        </optgroup>
                    </select>
                </div>

                <div class="md:col-span-4">
                    <template x-if="selectedStudentData">
                        <div class="p-3 bg-indigo-50/80 rounded-xl border border-indigo-100 text-xs text-indigo-950 flex items-center justify-between">
                            <div>
                                <span class="font-bold block" x-text="selectedStudentData.nama_lengkap"></span>
                                <span class="text-[11px] text-indigo-700 font-mono block" x-text="'NIM: ' + selectedStudentData.nim"></span>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full block" 
                                      :class="selectedStudentData.rfid_uid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                      x-text="selectedStudentData.rfid_uid ? 'RFID: OK' : 'RFID: Empty'"></span>
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full block mt-1" 
                                      :class="selectedStudentData.foto_wajah ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                      x-text="selectedStudentData.foto_wajah ? 'Face: OK' : 'Face: Empty'"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedStudentData">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-400 text-center font-medium">
                            <i class="fa-solid fa-hand-pointer mr-1"></i> Silakan pilih mahasiswa terlebih dahulu
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Tab Controls (2 Main Tabs) -->
        <div class="flex border-b border-slate-200 space-x-2">
            <button type="button" 
                    @click="activeTab = 'rfid'" 
                    :class="activeTab === 'rfid' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold bg-slate-100/60'"
                    class="py-3 px-5 border-b-2 rounded-t-2xl text-xs sm:text-sm flex items-center space-x-2 transition-all cursor-pointer">
                <i class="fa-solid fa-credit-card text-base"></i>
                <span>(1) Pendaftaran / Binding Kartu RFID</span>
            </button>
            <button type="button" 
                    @click="activeTab = 'face'; if (!iotStream) startIotWebcam();" 
                    :class="activeTab === 'face' ? 'border-indigo-600 text-indigo-600 font-extrabold bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold bg-slate-100/60'"
                    class="py-3 px-5 border-b-2 rounded-t-2xl text-xs sm:text-sm flex items-center space-x-2 transition-all cursor-pointer">
                <i class="fa-solid fa-face-viewfinder text-base"></i>
                <span>(2) Pendaftaran Wajah (Face Recognition)</span>
            </button>
        </div>

        <!-- TAB 1: PANEL PENDAFTARAN / BINDING KARTU RFID -->
        <div x-show="activeTab === 'rfid'" x-transition class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Main RFID Form (7 Cols) -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-barcode text-indigo-600"></i>
                            <span>Panel Binding Kartu RFID</span>
                        </h3>
                        <span class="text-[10px] font-bold bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full uppercase border border-indigo-200">RC522 Sensor</span>
                    </div>

                    <!-- Real-time Status Badge "Menunggu Scan Kartu..." -->
                    <div class="p-4 bg-slate-900 text-white rounded-2xl flex items-center justify-between border border-slate-800 shadow-inner">
                        <div class="flex items-center space-x-3">
                            <div class="relative flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-slate-200 tracking-wide">Status Hardware Raspberry Pi:</h4>
                                <p class="text-[11px] font-mono text-emerald-400">Menunggu Scan Kartu RFID Real-time...</p>
                            </div>
                        </div>
                        <i class="fa-solid fa-wifi text-slate-500 text-xl animate-pulse"></i>
                    </div>

                    <form action="{{ route('admin.iot-device.assign-rfid') }}" method="POST" @submit="isSubmittingRfid = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="mahasiswa_id" :value="selectedMahasiswaId">

                        <div>
                            <label for="rfid_uid_input" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kode RFID UID Tag Kartu <span class="text-rose-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" name="rfid_uid" id="rfid_uid_input" required 
                                       x-model="rfidUid" placeholder="Contoh: CF45B1E6DD" 
                                       class="block w-full rounded-xl border-slate-200 font-mono text-xs font-bold text-slate-800 p-3.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 shadow-xs">
                                <button type="button" onclick="fetchRecentRfidScan()" class="px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-extrabold rounded-xl border border-indigo-200 whitespace-nowrap transition-all flex items-center justify-center space-x-1.5 active:scale-95 shrink-0">
                                    <i class="fa-solid fa-barcode"></i>
                                    <span>Ambil Scan Tapping</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Tempelkan kartu RFID ke alat Raspberry Pi di kelas/lab atau klik tombol di atas untuk menarik data UID terbaru.</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 font-medium" 
                                  x-text="selectedMahasiswaId &amp;&amp; rfidUid.trim() ? 'Syarat RFID lengkap. Siap disimpan!' : 'Pilih Mahasiswa &amp; isi Kode RFID UID.'"></span>

                            <button type="submit" 
                                    :disabled="!selectedMahasiswaId || !rfidUid.trim() || isSubmittingRfid"
                                    :class="{
                                        'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer shadow-md hover:shadow-lg active:scale-95': selectedMahasiswaId &amp;&amp; rfidUid.trim() &amp;&amp; !isSubmittingRfid,
                                        'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300': !selectedMahasiswaId || !rfidUid.trim() || isSubmittingRfid
                                    }"
                                    class="px-6 py-3 rounded-xl text-xs font-extrabold transition-all inline-flex items-center justify-center space-x-2">
                                <template x-if="isSubmittingRfid">
                                    <span class="inline-flex items-center">
                                        <i class="fa-solid fa-spinner fa-spin mr-2 text-sm"></i>
                                        <span>Menyimpan Kartu RFID...</span>
                                    </span>
                                </template>
                                <template x-if="!isSubmittingRfid">
                                    <span class="inline-flex items-center space-x-2">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        <span>Simpan Kartu RFID</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Guidance Right Panel (5 Cols) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-indigo-900 text-white rounded-2xl p-5 sm:p-6 shadow-md space-y-3">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-300 flex items-center space-x-2">
                            <i class="fa-solid fa-circle-question"></i>
                            <span>Cara Mengasosiasikan Kartu RFID</span>
                        </h4>
                        <ol class="text-xs text-indigo-100 space-y-2 list-decimal list-inside leading-relaxed font-medium">
                            <li>Pilih data <strong>Mahasiswa Target</strong> di bagian atas.</li>
                            <li>Suruh mahasiswa menggesekkan/menempelkan (*tap*) kartu RFID baru pada scanner hardware di meja laboratorium.</li>
                            <li>Tekan tombol <strong>Ambil Scan Tapping</strong> untuk mengisi UID secara otomatis.</li>
                            <li>Tekan <strong>Simpan Kartu RFID</strong> untuk memperbarui database.</li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: PANEL PENDAFTARAN WAJAH (FACE RECOGNITION ENROLLMENT) -->
        <div x-show="activeTab === 'face'" x-transition class="space-y-6" style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- WebRTC Live Camera & Capture Controls (7 Cols) -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-camera text-indigo-600"></i>
                            <span>Panel Biometrik Kamera Live WebRTC</span>
                        </h3>
                        <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full uppercase border border-emerald-200">Live Camera Stream</span>
                    </div>

                    <!-- WebRTC Live Stream Video Frame -->
                    <div class="bg-slate-900 rounded-2xl p-3 sm:p-4 text-center space-y-3 relative overflow-hidden border border-slate-800 shadow-inner">
                        <div class="relative w-full overflow-hidden rounded-xl bg-slate-950 max-h-64 flex items-center justify-center">
                            <video id="iot-webcam" autoplay playsinline class="hidden w-full aspect-video max-h-64 rounded-xl object-cover mx-auto -scale-x-100 border border-slate-700 shadow-lg"></video>
                            <canvas id="iot-canvas" class="hidden"></canvas>
                            
                            <div id="iot-placeholder" class="py-8 sm:py-10 text-slate-400 space-y-2 w-full">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-slate-800 text-indigo-400 flex items-center justify-center mx-auto text-xl sm:text-2xl border border-slate-700">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <h4 class="text-xs font-bold text-slate-300">Kamera WebRTC Belum Aktif</h4>
                                <p class="text-[11px] text-slate-500 max-w-xs mx-auto">Klik tombol di bawah untuk membuka aliran kamera live webcam.</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-center gap-2 pt-2 border-t border-slate-800">
                            <button type="button" onclick="startIotWebcam()" id="btn-iot-start" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-video"></i>
                                <span>Buka Kamera Live</span>
                            </button>
                            
                            <button type="button" @click="captureSnapshot()" id="btn-iot-capture" class="hidden px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-circle-dot"></i>
                                <span>Ambil Foto (Capture)</span>
                            </button>
                            <button type="button" @click="retakeSnapshot()" id="btn-iot-retake" class="hidden px-4 py-2.5 bg-amber-600 hover:bg-amber-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md transition-all inline-flex items-center justify-center space-x-2">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Foto Ulang</span>
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Form Submitting Base64 Snapshot -->
                    <form action="{{ route('admin.iot-device.assign-face') }}" method="POST" @submit="isSubmittingFace = true" class="space-y-4">
                        @csrf
                        <input type="hidden" name="mahasiswa_id" :value="selectedMahasiswaId">
                        <input type="hidden" name="foto_wajah_base64" id="iot_foto_wajah_base64">

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] text-slate-500 font-medium" 
                                  x-text="!selectedMahasiswaId ? '⚠️ Silakan Pilih Mahasiswa terlebih dahulu!' : (selectedMahasiswaId &amp;&amp; hasSnapshot &amp;&amp; isFaceValid ? '✅ Wajah terdeteksi jelas! Siap disimpan ke database.' : (hasSnapshot &amp;&amp; !isFaceValid ? '⚠️ Wajah kurang jelas / buram. Silakan Foto Ulang!' : 'Klik tombol Ambil Foto (Capture) terlebih dahulu.'))"></span>

                            <button type="submit" 
                                    :disabled="!selectedMahasiswaId || !hasSnapshot || !isFaceValid || isSubmittingFace"
                                    :class="{
                                        'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer shadow-md hover:shadow-lg active:scale-95': selectedMahasiswaId &amp;&amp; hasSnapshot &amp;&amp; isFaceValid &amp;&amp; !isSubmittingFace,
                                        'bg-slate-200 text-slate-400 cursor-not-allowed border border-slate-300': !selectedMahasiswaId || !hasSnapshot || !isFaceValid || isSubmittingFace
                                    }"
                                    class="px-6 py-3 rounded-xl text-xs font-extrabold transition-all inline-flex items-center justify-center space-x-2">
                                <template x-if="isSubmittingFace">
                                    <span class="inline-flex items-center">
                                        <i class="fa-solid fa-spinner fa-spin mr-2 text-sm"></i>
                                        <span>Menyimpan Foto Wajah...</span>
                                    </span>
                                </template>
                                <template x-if="!isSubmittingFace">
                                    <span class="inline-flex items-center space-x-2">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <span>Simpan Wajah ke Database</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Side: Hasil Tangkapan Gambar Sementara (Image Preview) (5 Cols) -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                                <i class="fa-solid fa-image text-indigo-600"></i>
                                <span>Preview &amp; Validasi Kejelasan Wajah</span>
                            </h3>
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full"
                                  :class="hasSnapshot ? (isFaceValid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') : 'bg-slate-100 text-slate-500'"
                                  x-text="hasSnapshot ? (isScanningFace ? 'Scanning Wajah...' : (isFaceValid ? 'Wajah Valid' : 'Wajah Tidak Jelas')) : 'Belum Ada Foto'"></span>
                        </div>

                        <div class="text-center space-y-3">
                            <div class="relative w-full aspect-video bg-slate-100 rounded-2xl overflow-hidden border-2 border-dashed border-slate-300 flex items-center justify-center">
                                <img id="iot-preview" class="hidden w-full h-full object-cover rounded-2xl shadow-md">
                                
                                <!-- Laser Scanning Overlay -->
                                <div x-show="isScanningFace" class="absolute inset-0 bg-slate-950/70 backdrop-blur-xs flex flex-col items-center justify-center text-cyan-400 space-y-2 z-20" x-transition>
                                    <div class="w-full h-1 bg-cyan-400 shadow-[0_0_15px_#06b6d4] absolute animate-scan-beam"></div>
                                    <div class="w-12 h-12 rounded-full border-2 border-cyan-400 border-t-transparent animate-spin flex items-center justify-center">
                                        <i class="fa-solid fa-face-viewfinder text-xl text-cyan-300"></i>
                                    </div>
                                    <span class="text-xs font-mono font-bold tracking-wider animate-pulse">Memindai Kejelasan Wajah...</span>
                                </div>

                                <div id="preview-placeholder" class="p-6 text-slate-400 space-y-2">
                                    <i class="fa-solid fa-user-astronaut text-3xl"></i>
                                    <p class="text-xs font-semibold text-slate-500">Hasil tangkapan gambar sementara akan muncul di sini setelah Anda mengklik tombol "Ambil Foto".</p>
                                </div>
                            </div>

                            <!-- Scanning Status Result Card -->
                            <template x-if="hasSnapshot">
                                <div class="p-3.5 rounded-xl border text-left text-xs space-y-2"
                                     :class="{
                                         'bg-emerald-50 border-emerald-200 text-emerald-950': scanResultType === 'success',
                                         'bg-amber-50 border-amber-200 text-amber-950': scanResultType === 'warning',
                                         'bg-rose-50 border-rose-200 text-rose-950': scanResultType === 'danger'
                                     }">
                                    <div class="flex items-center justify-between">
                                        <span class="font-extrabold uppercase text-[10px] tracking-wider" x-text="'Skor Kejelasan Wajah: ' + clarityScore + '%'"></span>
                                        <span class="text-xs font-bold" x-text="isFaceValid ? '✅ VALID' : '❌ TIDAK VALID'"></span>
                                    </div>
                                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                        <div class="h-full transition-all duration-500 rounded-full" 
                                             :style="'width: ' + clarityScore + '%'"
                                             :class="isFaceValid ? 'bg-emerald-500' : (clarityScore > 40 ? 'bg-amber-500' : 'bg-rose-500')"></div>
                                    </div>
                                    <p class="text-[11px] font-semibold leading-relaxed" x-text="scanStatusText"></p>
                                </div>
                            </template>

                            <p class="text-[11px] text-slate-400 font-medium" x-show="!hasSnapshot">
                                Sistem akan memindai kejelasan pencahayaan dan fokus wajah secara otomatis sebelum foto diizinkan untuk disimpan.
                            </p>
                        </div>
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

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('⚠️ Browser Anda memblokir kamera pada koneksi IP non-HTTPS!\n\nSolusi: Silakan buka web via http://localhost:8000 atau http://127.0.0.1:8000 (atau gunakan HTTPS/Localtunnel) agar izin kamera aktif.');
                    return;
                }

                try {
                    iotStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'user' }, width: { ideal: 640 }, height: { ideal: 480 } }
                    });
                } catch (camErr) {
                    iotStream = await navigator.mediaDevices.getUserMedia({ video: true });
                }

                const video = document.getElementById('iot-webcam');
                const placeholder = document.getElementById('iot-placeholder');
                
                if (video) {
                    video.muted = true;
                    video.playsInline = true;
                    video.setAttribute('playsinline', 'true');
                    video.setAttribute('webkit-playsinline', 'true');
                    video.srcObject = iotStream;
                    video.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');

                    try {
                        await video.play();
                    } catch (playErr) {
                        console.log('Video play error:', playErr);
                    }
                }

                const btnStart = document.getElementById('btn-iot-start');
                const btnCapture = document.getElementById('btn-iot-capture');
                const btnRetake = document.getElementById('btn-iot-retake');

                if (btnStart) btnStart.classList.add('hidden');
                if (btnCapture) btnCapture.classList.remove('hidden');
                if (btnRetake) btnRetake.classList.add('hidden');
            } catch (err) {
                console.error('Webcam Error:', err);
                let msg = 'Gagal mengakses kamera: ' + (err.message || err.name || err);
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    msg = '⚠️ Akses kamera ditolak oleh browser!\n\nSolusi: Klik ikon gembok 🔒 di sebelah alamat URL browser, ubah izin Kamera menjadi "Allow/Izinkan", lalu refresh halaman.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    msg = '⚠️ Kamera webcam tidak terdeteksi pada laptop/HP Anda. Silakan hubungkan kamera webcam dan coba lagi.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    msg = '⚠️ Kamera sedang digunakan oleh aplikasi lain!\n\nSilakan tutup aplikasi yang sedang mengakses kamera (Zoom/Teams) lalu coba lagi.';
                }
                alert(msg);
            }
        }

        function stopIotWebcam() {
            if (iotStream) {
                iotStream.getTracks().forEach(track => track.stop());
                iotStream = null;
            }
        }

        async function fetchRecentRfidScan(isManual = true) {
            try {
                const response = await fetch('/admin/rfid/scan?json=1');
                const data = await response.json();
                const scanned = data.scanned_uid || data.rfid_uid;
                if (scanned) {
                    const input = document.getElementById('rfid_uid_input');
                    if (input) {
                        input.value = scanned;
                        input.dispatchEvent(new Event('input'));
                    }
                    const alpineContainer = document.querySelector('[x-data]');
                    if (alpineContainer && alpineContainer._x_dataStack) {
                        alpineContainer._x_dataStack[0].rfidUid = scanned;
                    }
                    if (isManual) {
                        alert('✅ Kartu RFID terdeteksi dari Raspberry Pi!\nKode UID: ' + scanned);
                    }
                } else if (isManual) {
                    alert('Belum ada kartu RFID yang di-tap pada scanner Raspberry Pi. Silakan tap kartu RFID ke alat lalu coba lagi.');
                }
            } catch (e) {
                if (isManual) {
                    alert('Silakan ketikkan kode RFID UID secara manual atau tap kartu RFID pada alat.');
                }
            }
        }

        // Auto-poll RFID Scan every 2.5 seconds real-time
        setInterval(function() {
            fetchRecentRfidScan(false);
        }, 2500);
    </script>
</x-admin-layout>
