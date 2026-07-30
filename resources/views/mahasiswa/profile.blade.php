<x-mahasiswa-layout>
    <!-- Include Cropper.js for Square Face Cropping -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

    <!-- Include tracking.js core and face data classifier from CDN (fallback only) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/tracking-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tracking.js/1.1.3/data/face-min.js"></script>

    <style>
        /* Real-time scanner animation effect */
        @keyframes scanLine {
            0% { top: 5%; opacity: 0.8; }
            50% { opacity: 1; }
            100% { top: 90%; opacity: 0.8; }
        }
        .scanner-line {
            position: absolute;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #10b981, #34d399, #10b981, transparent);
            box-shadow: 0 0 12px #10b981, 0 0 4px #34d399;
            animation: scanLine 1.5s infinite ease-in-out;
            z-index: 10;
        }

        /* Cropper custom styling */
        .cropper-view-box,
        .cropper-face {
            border-radius: 8px;
            outline: 2px solid #6366f1;
        }
    </style>

    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div>
            <h1 class="heading-font text-3xl font-extrabold text-slate-900 tracking-tight">Profil & Dataset Wajah</h1>
            <p class="text-slate-500 mt-1">Lengkapi biodata kontak Anda dan kelola dataset pengenalan wajah untuk verifikasi presensi IoT.</p>
        </div>

        <div class="grid grid-cols-1 gap-6">
            
            <!-- Card 1: Data Diri Mahasiswa -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center space-x-3">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <i class="fa-solid fa-address-card text-lg"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Biodata & Informasi Kontak</h3>
                        <p class="text-[10px] text-slate-400">NIM dan Nama bersifat permanen, hanya email & nomor handphone yang dapat diedit.</p>
                    </div>
                </div>

                <form action="{{ route('mahasiswa.profile.update') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- NIM & Nama (Read-only) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="nim" class="block text-sm font-semibold text-slate-400">Nomor Induk Mahasiswa (NIM)</label>
                            <input type="text" id="nim" value="{{ $mahasiswa->nim }}" readonly
                                   class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 shadow-sm cursor-not-allowed border focus:ring-0 focus:outline-none">
                        </div>
                        <div>
                            <label for="nama_lengkap" class="block text-sm font-semibold text-slate-400">Nama Lengkap</label>
                            <input type="text" id="nama_lengkap" value="{{ $mahasiswa->nama_lengkap }}" readonly
                                   class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 shadow-sm cursor-not-allowed border focus:ring-0 focus:outline-none">
                        </div>
                    </div>

                    <!-- Email & No HP (Editable) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $mahasiswa->email) }}" required
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-slate-800 @error('email') border-rose-500 focus:border-rose-500 focus:ring-rose-200 @enderror">
                            @error('email')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="no_hp" class="block text-sm font-semibold text-slate-700">Nomor Handphone (WhatsApp)</label>
                            <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $mahasiswa->no_hp) }}" placeholder="Contoh: 0812..."
                                   class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-slate-800 @error('no_hp') border-rose-500 focus:border-rose-500 focus:ring-rose-200 @enderror">
                            @error('no_hp')
                                <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Kelas & RFID (Read-only) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-400">Kelas Terdaftar</label>
                            <input type="text" value="{{ $mahasiswa->kelas->nama_kelas ?? '-' }}" readonly
                                   class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 shadow-sm cursor-not-allowed border focus:ring-0 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-400">RFID UID Tag Kartu</label>
                            <input type="text" value="{{ $mahasiswa->rfid_uid ?: 'Belum terikat' }}" readonly
                                   class="mt-1 block w-full rounded-lg border-slate-200 bg-slate-50 text-slate-500 shadow-sm cursor-not-allowed border focus:ring-0 focus:outline-none font-mono">
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="flex justify-end pt-4 border-t border-slate-100">
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Dataset Wajah (Face Recognition) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                            <i class="fa-solid fa-camera text-lg"></i>
                        </span>
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm">Dataset Wajah (Face Recognition)</h3>
                            <p class="text-[10px] text-slate-400">Maksimal 1 foto dataset. Pengujian AI dapat dilakukan dengan kamera laptop.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- ALWAYS VISIBLE AJUKAN GANTI FOTO BUTTON -->
                        <button type="button" onclick="openRequestModal()"
                                class="inline-flex items-center px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-lg shadow-sm transition-all">
                            <i class="fa-regular fa-paper-plane mr-1.5"></i> Ajukan Ganti Foto
                        </button>

                        <!-- ALWAYS VISIBLE UJI SCAN BUTTON -->
                        <button type="button" onclick="openMatchTestModal()"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-lg shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-bolt mr-2 text-amber-300 text-sm"></i> UJI AUTO SCAN KAMERA (PYTHON AI)
                        </button>

                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $mahasiswa->foto_wajah ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $mahasiswa->foto_wajah ? '1' : '0' }} / 1
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    @if ($mahasiswa->foto_wajah)
                        @php
                            $canChangePhoto = !$mahasiswa->last_photo_updated_at || $mahasiswa->last_photo_updated_at->diffInDays(now()) >= 30;
                            $daysRemaining = $mahasiswa->last_photo_updated_at ? max(1, 30 - $mahasiswa->last_photo_updated_at->diffInDays(now())) : 0;
                        @endphp

                        @if (!$canChangePhoto)
                            <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-4 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-amber-100 text-amber-700 rounded-xl flex-shrink-0">
                                        <i class="fa-solid fa-shield-halved text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-extrabold text-amber-900 uppercase tracking-wider">KEAMANAN BIOMETRIK: FOTO TERKUNCI (1x Per 30 Hari)</h4>
                                        <p class="text-[11px] text-amber-700 mt-0.5 font-medium leading-relaxed">
                                            Foto profil Anda terakhir diubah pada <strong>{{ $mahasiswa->last_photo_updated_at->format('d M Y') }}</strong>. 
                                            Dapat diubah secara mandiri dalam <strong>{{ $daysRemaining }} hari lagi</strong>. Jika butuh mengganti foto segera, silakan ajukan permohonan ke Admin.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" onclick="openRequestModal()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-extrabold shadow-sm transition-all flex items-center space-x-1.5 flex-shrink-0">
                                    <i class="fa-regular fa-paper-plane"></i>
                                    <span>Ajukan Ganti Foto Ke Admin</span>
                                </button>
                            </div>
                        @endif
                    @endif

                    {{-- ============================== --}}
                    {{-- TABEL DAFTAR FOTO --}}
                    {{-- ============================== --}}
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left">
                                    <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider w-12">#</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama File</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Format</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($mahasiswa->foto_wajah)
                                    <tr class="border-t border-slate-100 hover:bg-indigo-50/30 transition-colors">
                                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">1</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ asset('storage/' . $mahasiswa->foto_wajah) }}" id="uploaded-face-img" alt="Foto" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-sm">
                                                <span class="text-xs font-medium text-slate-700 truncate max-w-[180px]">{{ basename($mahasiswa->foto_wajah) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200">
                                                {{ strtoupper(pathinfo($mahasiswa->foto_wajah, PATHINFO_EXTENSION)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fa-solid fa-circle-check mr-1"></i> Terdaftar
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center gap-2">
                                                <!-- Auto Live Match Test Button -->
                                                <button type="button" onclick="openMatchTestModal()"
                                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow-sm transition-all">
                                                    <i class="fa-solid fa-bolt mr-1.5 text-amber-300"></i> Auto Scan Kamera
                                                </button>
                                                <!-- View Button -->
                                                <a href="{{ asset('storage/' . $mahasiswa->foto_wajah) }}" target="_blank"
                                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 hover:text-indigo-700 font-bold text-xs rounded-lg border border-indigo-200 hover:border-indigo-300 transition-all">
                                                    <i class="fa-solid fa-eye mr-1"></i> View
                                                </a>
                                                <!-- Delete Button -->
                                                <form action="{{ route('mahasiswa.face.delete') }}" method="POST" onsubmit="return confirmDelete()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-bold text-xs rounded-lg border border-rose-200 hover:border-rose-300 transition-all">
                                                        <i class="fa-solid fa-trash-can mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr class="border-t border-slate-100">
                                        <td colspan="5" class="px-4 py-8 text-center">
                                            <div class="text-slate-400 space-y-2">
                                                <i class="fa-solid fa-image text-3xl"></i>
                                                <p class="text-sm font-semibold">Belum ada foto wajah terdaftar</p>
                                                <p class="text-xs">Silakan unggah foto wajah menggunakan form di bawah, atau klik tombol <strong>"UJI AUTO SCAN KAMERA"</strong> di kanan atas.</p>
                                                <button type="button" onclick="openMatchTestModal()" class="mt-2 inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow">
                                                    <i class="fa-solid fa-bolt mr-2 text-amber-300"></i> Uji Auto Scan Kamera Sekarang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- ============================== --}}
                    {{-- FORM UPLOAD / KAMERA LIVE --}}
                    {{-- ============================== --}}
                    @if (!$mahasiswa->foto_wajah)
                        <div class="border-t border-slate-100 pt-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-slate-700 flex items-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-indigo-500 mr-2"></i> Unggah Foto Wajah Baru
                                </h4>
                                
                                <!-- Toggle WebCam Button -->
                                <button type="button" onclick="toggleWebcamModal()" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-semibold text-xs rounded-lg border border-indigo-200 transition-colors">
                                    <i class="fa-solid fa-video mr-1.5"></i> Ambil Pakai Kamera Laptop
                                </button>
                            </div>

                            <!-- WebCam Live Box (Hidden by default) -->
                            <div id="webcam-container" class="hidden border-2 border-indigo-300 rounded-xl p-4 bg-indigo-50/40 space-y-3 text-center">
                                <div class="relative w-full max-w-md mx-auto aspect-video bg-black rounded-lg overflow-hidden shadow-inner">
                                    <video id="webcam-video" autoplay playsinline class="w-full h-full object-cover"></video>
                                    <canvas id="webcam-canvas" class="hidden"></canvas>
                                </div>
                                <div class="flex justify-center gap-3">
                                    <button type="button" onclick="captureWebcamPhoto()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg shadow transition-colors">
                                        <i class="fa-solid fa-camera mr-1"></i> Tangkap Foto & Crop
                                    </button>
                                    <button type="button" onclick="stopWebcam()" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-lg transition-colors">
                                        <i class="fa-solid fa-xmark mr-1"></i> Tutup Kamera
                                    </button>
                                </div>
                            </div>

                            <form action="{{ route('mahasiswa.face.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50/50 hover:bg-indigo-50/30 hover:border-indigo-300 transition-colors relative h-52 group" id="drop-zone">
                                    <div class="text-center space-y-3" id="preview-container">
                                        <div class="w-20 h-20 rounded-full bg-slate-100 border-2 border-dashed border-slate-300 text-slate-400 flex items-center justify-center mx-auto group-hover:border-indigo-300 group-hover:text-indigo-400 transition-colors" id="placeholder-face">
                                            <i class="fa-solid fa-user-plus text-2xl"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-600 group-hover:text-indigo-600 transition-colors">Klik atau seret file gambar ke sini untuk Potong/Crop</p>
                                            <p class="text-[10px] text-slate-400 mt-1">Foto akan dipotong Square (1:1) agar wajah terlihat pas</p>
                                        </div>
                                    </div>
                                    <input type="file" id="foto_wajah_file" accept="image/jpeg,image/png,image/jpg" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="onFileSelected(event)">
                                    <!-- Hidden actual file input sent via form -->
                                    <input type="file" name="foto_wajah" id="foto_wajah" required class="hidden">
                                </div>

                                <!-- Face Scan Status -->
                                <div id="scan-status" class="text-xs font-semibold text-center hidden p-2.5 bg-slate-50 rounded-lg border border-slate-100"></div>

                                @error('foto_wajah')
                                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                                @enderror

                                <div class="flex justify-end">
                                    <button type="submit" id="btn-upload-face" class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Unggah Foto Wajah
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    {{-- ============================================================== --}}
    {{-- MODAL CROPPER FOTO WAJAH (SQUARE 1:1 CROPPER.JS) --}}
    {{-- ============================================================== --}}
    <div id="crop-modal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="p-1.5 bg-indigo-500/20 text-indigo-400 rounded-lg">
                        <i class="fa-solid fa-crop-simple text-lg"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-white">Potong Foto Wajah (Square 1:1)</h3>
                        <p class="text-[10px] text-slate-300">Sesuaikan kotak agar posisi wajah pas di tengah.</p>
                    </div>
                </div>
                <button type="button" onclick="closeCropModal()" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-4">
                <div class="max-h-96 overflow-hidden rounded-xl bg-slate-900 flex items-center justify-center p-2">
                    <img id="crop-image-element" src="" alt="Source for cropping" class="max-w-full block">
                </div>
                <div class="flex items-center justify-between text-xs text-slate-500 px-1">
                    <span><i class="fa-solid fa-arrows-up-down-left-right text-indigo-500 mr-1"></i> Geser & Zoom kotak cropper</span>
                    <span class="font-bold text-indigo-600"><i class="fa-solid fa-vector-square mr-1"></i> Ratio 1:1 Square</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="closeCropModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    Batal
                </button>
                <button type="button" onclick="applySquareCrop()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow hover:shadow-md transition-all flex items-center">
                    <i class="fa-solid fa-check mr-1.5"></i> Potong & Gunakan Foto Ini
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- MODAL UJI PENCOCOKAN WAJAH OTOMATIS (REAL-TIME AUTO SCAN) --}}
    {{-- ============================================================== --}}
    <div id="match-modal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="p-2 bg-indigo-500/20 text-indigo-400 rounded-lg border border-indigo-400/30 animate-pulse">
                        <i class="fa-solid fa-face-viewfinder text-xl"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-base text-white">Simulasi Presensi IoT — Auto Face Scan</h3>
                        <p class="text-xs text-indigo-200">Pencarian & pencocokan wajah otomatis. Berhenti saat skor &ge; 75%.</p>
                    </div>
                </div>
                <button type="button" onclick="closeMatchTestModal()" class="text-slate-400 hover:text-white p-1 transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-6">
                <!-- Comparison Box Side-by-Side -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Left: Reference Photo -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-3">
                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full uppercase tracking-wider">
                            <i class="fa-solid fa-database mr-1"></i> Dataset Terdaftar
                        </span>
                        <div class="w-44 h-44 mx-auto rounded-xl overflow-hidden border-2 border-indigo-200 shadow-md bg-white flex items-center justify-center">
                            @if ($mahasiswa->foto_wajah)
                                <img src="{{ asset('storage/' . $mahasiswa->foto_wajah) }}" id="ref-face-img" alt="Foto Dataset" class="w-full h-full object-cover">
                            @else
                                <div class="text-center p-3 text-slate-400">
                                    <i class="fa-solid fa-user-slash text-3xl mb-1"></i>
                                    <p class="text-[11px]">Belum Ada Foto Dataset</p>
                                </div>
                            @endif
                        </div>
                        <p class="text-xs font-bold text-slate-800">{{ $mahasiswa->nama_lengkap }}</p>
                        <p class="text-[11px] text-slate-500 font-mono">NIM: {{ $mahasiswa->nim }}</p>
                    </div>

                    <!-- Right: Live Laptop Camera -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center space-y-3">
                        <span id="live-cam-badge" class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase tracking-wider flex items-center justify-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping mr-1.5"></span> Live Camera AI
                        </span>

                        <!-- Live Camera Box with Face Finder Frame / Bounding Box -->
                        <div class="w-44 h-44 mx-auto rounded-xl overflow-hidden border-2 border-emerald-400 shadow-md bg-black relative group">
                            <video id="match-video" autoplay playsinline class="w-full h-full object-cover"></video>
                            
                            <!-- Real-time Laser Scan Line Animation -->
                            <div id="scan-overlay-line" class="scanner-line hidden"></div>
                            
                            <!-- AI Face Finder Viewfinder / Target Box Overlay -->
                            <div id="face-bounding-box" class="absolute inset-2 border-2 border-dashed border-emerald-400/80 rounded-xl pointer-events-none hidden flex flex-col justify-between p-1.5 shadow-[0_0_15px_rgba(16,185,129,0.4)]">
                                <!-- 4 Glowing Corner Markers -->
                                <div class="absolute -top-1 -left-1 w-3.5 h-3.5 border-t-2 border-l-2 border-emerald-400 rounded-tl"></div>
                                <div class="absolute -top-1 -right-1 w-3.5 h-3.5 border-t-2 border-r-2 border-emerald-400 rounded-tr"></div>
                                <div class="absolute -bottom-1 -left-1 w-3.5 h-3.5 border-b-2 border-l-2 border-emerald-400 rounded-bl"></div>
                                <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 border-b-2 border-r-2 border-emerald-400 rounded-br"></div>

                                <div class="text-[8px] font-mono font-bold text-emerald-300 bg-slate-900/80 px-1.5 py-0.5 rounded mx-auto tracking-wider border border-emerald-500/30">
                                    [ AI FACE SCANNER ]
                                </div>
                                <div class="text-[8px] font-mono font-semibold text-emerald-200 bg-slate-900/80 px-1.5 py-0.5 rounded mx-auto tracking-tight border border-emerald-500/20">
                                    POSISIKAN WAJAH DI SINI
                                </div>
                            </div>

                            <div id="camera-off-notice" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-2 text-center bg-slate-900">
                                <i class="fa-solid fa-camera text-3xl mb-2 text-indigo-400"></i>
                                <span class="text-xs font-semibold text-slate-300">Kamera Belum Aktif</span>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="button" id="btn-start-match-cam" onclick="startMatchCamera()" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-all shadow flex items-center justify-center">
                                <i class="fa-solid fa-rotate-right mr-1.5"></i> Pindai Ulang Kamera
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Match Status Notification Result -->
                <div id="match-result-box" class="p-4 rounded-xl border-2 border-slate-200 bg-slate-50 text-center space-y-1 shadow-md">
                    <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Auto Scan Python AI</h5>
                    <p id="match-result-text" class="text-sm font-semibold text-slate-600">
                        Posisikan wajah Anda di depan kamera laptop...
                    </p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                @if ($mahasiswa->foto_wajah)
                    <!-- Direct Delete option -->
                    <form action="{{ route('mahasiswa.face.delete') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto dataset ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-lg border border-rose-200 transition-colors">
                            <i class="fa-solid fa-trash-can mr-1.5"></i> Hapus Foto Dataset Ini
                        </button>
                    </form>
                @else
                    <div></div>
                @endif

                <button type="button" onclick="closeMatchTestModal()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PENGAJUAN GANTI FOTO KE ADMIN -->
    <div id="request-photo-modal" class="fixed inset-0 z-50 hidden bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-indigo-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <span class="p-1.5 bg-indigo-600 rounded-lg text-white">
                        <i class="fa-regular fa-paper-plane text-sm"></i>
                    </span>
                    <h3 class="text-sm font-extrabold tracking-tight">Permohonan Izin Ganti Foto</h3>
                </div>
                <button type="button" onclick="closeRequestModal()" class="text-indigo-200 hover:text-white focus:outline-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form Body -->
            <form action="{{ route('mahasiswa.request-photo-change') }}" method="POST" class="p-6 space-y-4" onsubmit="var btn = this.querySelector('button[type=submit]'); btn.innerHTML = '<i class=\'fa-solid fa-spinner fa-spin mr-1.5\'></i> Mengirim...';">
                @csrf
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 space-y-1">
                    <p class="text-xs font-bold text-amber-900 flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-600"></i> Aturan Keamanan 1x Per 30 Hari
                    </p>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        Pergantian foto biometrik wajah dibatasi 1x dalam 30 hari. Karena waktu belum mencukupi, pengajuan ini akan dikirimkan ke <strong>Notifikasi Role Admin</strong> untuk disetujui.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label for="alasan" class="block text-xs font-extrabold text-slate-700">Alasan Permohonan Pergantian Foto:</label>
                    <textarea name="alasan" id="alasan" rows="3" required 
                              placeholder="Contoh: Foto lama kurang jelas saat scan kamera biometrik, mohon izin mengganti foto dataset baru."
                              class="w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-2.5 pt-2">
                    <button type="button" onclick="closeRequestModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-md transition-all flex items-center space-x-1.5">
                        <i class="fa-regular fa-paper-plane"></i>
                        <span>Kirim Permohonan Ke Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function openRequestModal() {
            const modal = document.getElementById('request-photo-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeRequestModal() {
            const modal = document.getElementById('request-photo-modal');
            if (modal) modal.classList.add('hidden');
        }

        @php
            $isPhotoLocked = $mahasiswa->foto_wajah && $mahasiswa->last_photo_updated_at && $mahasiswa->last_photo_updated_at->diffInDays(now()) < 30;
            $daysLeftLock = $mahasiswa->last_photo_updated_at ? max(1, 30 - $mahasiswa->last_photo_updated_at->diffInDays(now())) : 0;
        @endphp

        // Confirm delete dialog with 30-day security rule intercept
        function confirmDelete() {
            @if ($isPhotoLocked)
                Swal.fire({
                    icon: 'warning',
                    title: 'FOTO TERKUNCI (1x Per 30 Hari)',
                    text: 'Demi alasan keamanan biometrik, Anda baru dapat mengubah foto 1x dalam 30 hari (Tersisa {{ $daysLeftLock }} hari lagi). Silakan kirimkan permohonan ke Admin untuk izin penggantian foto.',
                    showCancelButton: true,
                    confirmButtonColor: '#4f46e5',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-regular fa-paper-plane mr-1"></i> Ajukan Ganti Foto Ke Admin',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        openRequestModal();
                    }
                });
                return false;
            @else
                return confirm('Apakah Anda yakin ingin menghapus foto wajah ini?\n\nAnda harus mengunggah ulang foto baru setelah menghapus.');
            @endif
        }

        // ==============================================================
        // CROPPER.JS INTEGRATION (SQUARE 1:1 FACE CROPPER)
        // ==============================================================
        let cropperInstance = null;

        function onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                openCropModal(e.target.result);
            }
            reader.readAsDataURL(file);
        }

        function openCropModal(imageSrc) {
            const modal = document.getElementById('crop-modal');
            const cropImg = document.getElementById('crop-image-element');
            if (!modal || !cropImg) return;

            cropImg.src = imageSrc;
            modal.classList.remove('hidden');

            if (cropperInstance) {
                cropperInstance.destroy();
            }

            cropperInstance = new Cropper(cropImg, {
                aspectRatio: 1, // Square 1:1
                viewMode: 1,
                autoCropArea: 0.85,
                responsive: true,
                restore: true,
                center: true,
                highlight: true,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }

        function closeCropModal() {
            const modal = document.getElementById('crop-modal');
            if (modal) modal.classList.add('hidden');
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
        }

        function applySquareCrop() {
            if (!cropperInstance) return;

            const canvas = cropperInstance.getCroppedCanvas({
                width: 480,
                height: 480,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!canvas) return;

            canvas.toBlob(function(blob) {
                if (!blob) return;

                const file = new File([blob], "cropped_face_" + Date.now() + ".jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);

                const fileInput = document.getElementById('foto_wajah');
                if (fileInput) fileInput.files = dataTransfer.files;

                const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.95);
                updatePreviewSrc(croppedDataUrl);
                runFaceDetection(croppedDataUrl);

                closeCropModal();
            }, 'image/jpeg', 0.95);
        }

        // WebCam handling for Upload
        let webcamStream = null;

        async function toggleWebcamModal() {
            const container = document.getElementById('webcam-container');
            const video = document.getElementById('webcam-video');

            if (!webcamStream) {
                try {
                    webcamStream = await navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } });
                    video.srcObject = webcamStream;
                    container.classList.remove('hidden');
                } catch (err) {
                    alert('Gagal mengakses kamera laptop. Pastikan izin kamera telah diberikan pada browser.');
                    console.error('Webcam access error:', err);
                }
            } else {
                stopWebcam();
            }
        }

        function stopWebcam() {
            if (webcamStream) {
                webcamStream.getTracks().forEach(track => track.stop());
                webcamStream = null;
            }
            const container = document.getElementById('webcam-container');
            if (container) container.classList.add('hidden');
        }

        function captureWebcamPhoto() {
            const video = document.getElementById('webcam-video');
            const canvas = document.getElementById('webcam-canvas');
            if (!video || !webcamStream) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);
            stopWebcam();
            openCropModal(dataUrl);
        }

        function updatePreviewSrc(dataUrl) {
            let preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('placeholder-face');
            
            if (!preview) {
                if (placeholder) placeholder.style.display = 'none';
                preview = document.createElement('img');
                preview.id = 'image-preview';
                preview.className = 'w-24 h-24 rounded-2xl object-cover border-4 border-indigo-100 shadow-md mx-auto';
                const container = document.getElementById('preview-container');
                container.insertBefore(preview, container.firstChild);
            }
            preview.src = dataUrl;
        }

        // Face detection scanning
        async function runFaceDetection(imageSrc) {
            const statusEl = document.getElementById('scan-status');
            const submitBtn = document.getElementById('btn-upload-face');
            
            statusEl.classList.remove('hidden');
            statusEl.innerHTML = '<span class="text-indigo-600 flex items-center justify-center font-bold"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Memindai wajah dengan AI Face Detector...</span>';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            try {
                var img = new Image();
                img.src = imageSrc;
                await new Promise(function(resolve, reject) {
                    img.onload = resolve;
                    img.onerror = reject;
                });

                var MAX = 640;
                var w = img.naturalWidth, h = img.naturalHeight;
                if (w > MAX || h > MAX) {
                    var r = Math.min(MAX / w, MAX / h);
                    w = Math.round(w * r);
                    h = Math.round(h * r);
                }

                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);

                var faceCount = 0;

                if ('FaceDetector' in window) {
                    try {
                        var detector = new FaceDetector({ fastMode: true, maxDetectedFaces: 5 });
                        var blob = await new Promise(function(resolve) { canvas.toBlob(resolve, 'image/jpeg', 0.9); });
                        var bitmap = await createImageBitmap(blob);
                        var nativeFaces = await detector.detect(bitmap);
                        bitmap.close();
                        faceCount = nativeFaces.length;
                    } catch (e) { faceCount = 0; }
                }

                if (faceCount === 0 && typeof tracking !== 'undefined' && typeof tracking.ObjectTracker !== 'undefined') {
                    canvas.style.cssText = 'position:fixed;top:-9999px;left:-9999px;visibility:hidden;';
                    document.body.appendChild(canvas);

                    faceCount = await new Promise(function(resolve) {
                        var done = false;
                        try {
                            var t = new tracking.ObjectTracker('face');
                            t.setInitialScale(1.2);
                            t.setStepSize(1.5);
                            t.setEdgesDensity(0.1);
                            t.on('track', function(ev) {
                                if (!done) {
                                    done = true;
                                    resolve(ev.data ? ev.data.length : 0);
                                }
                            });
                            tracking.track(canvas, t);
                        } catch(e) {
                            if (!done) { done = true; resolve(0); }
                        }
                        setTimeout(function() { if (!done) { done = true; resolve(0); } }, 300);
                    });

                    if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
                }

                if (faceCount === 1) {
                    showScanResult(statusEl, submitBtn, 'success', '1 wajah terdeteksi dengan jelas! Foto siap diunggah.');
                } else if (faceCount > 1) {
                    showScanResult(statusEl, submitBtn, 'warning', 'Terdeteksi ' + faceCount + ' wajah. Pastikan hanya ada 1 wajah.');
                } else {
                    showScanResult(statusEl, submitBtn, 'info', 'Pemindai otomatis tidak mendeteksi wajah. Pastikan foto sesuai panduan, lalu klik Unggah.');
                }
            } catch (error) {
                showScanResult(statusEl, submitBtn, 'info', 'Pemindaian otomatis gagal. Pastikan foto sudah sesuai, lalu klik Unggah.');
            }
        }

        function showScanResult(statusEl, submitBtn, type, message) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            var icons = { success: 'fa-circle-check', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };
            var colors = { success: 'text-emerald-600', warning: 'text-amber-600', info: 'text-amber-500' };
            statusEl.innerHTML = '<span class="' + colors[type] + ' flex items-center justify-center font-bold"><i class="fa-solid ' + icons[type] + ' mr-2 text-base"></i> ' + message + '</span>';
        }

        // ==============================================================
        // REAL-TIME AUTO SCAN WITH SCORE THRESHOLD LOCK-IN (>= 75%)
        // ==============================================================
        let matchStream = null;
        let autoScanTimer = null;
        let isMatchProcessing = false;
        let hasPassedMatch = false;

        function openMatchTestModal() {
            hasPassedMatch = false;
            const modal = document.getElementById('match-modal');
            if (modal) modal.classList.remove('hidden');
            startMatchCamera();
        }

        function closeMatchTestModal() {
            stopMatchCamera();
            const modal = document.getElementById('match-modal');
            if (modal) modal.classList.add('hidden');
        }

        async function startMatchCamera() {
            hasPassedMatch = false;
            const video = document.getElementById('match-video');
            const notice = document.getElementById('camera-off-notice');
            const scanLine = document.getElementById('scan-overlay-line');
            const boundingBox = document.getElementById('face-bounding-box');

            if (!matchStream) {
                try {
                    matchStream = await navigator.mediaDevices.getUserMedia({ video: { width: 480, height: 360 } });
                    video.srcObject = matchStream;
                    if (notice) notice.classList.add('hidden');
                    if (scanLine) scanLine.classList.remove('hidden');
                    if (boundingBox) boundingBox.classList.remove('hidden');

                    const resultBox = document.getElementById('match-result-box');
                    const resultText = document.getElementById('match-result-text');
                    resultBox.className = 'p-4 rounded-xl border-2 border-indigo-300 bg-indigo-50/50 text-center space-y-1 shadow-md';
                    resultText.innerHTML = '<span class="text-indigo-600 font-bold"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Kamera aktif. Auto-scan Python AI sedang mencari wajah...</span>';

                    setTimeout(runAutoScanLoop, 100);

                    if (!autoScanTimer) {
                        autoScanTimer = setInterval(runAutoScanLoop, 500);
                    }
                } catch (err) {
                    alert('Gagal mengakses kamera laptop. Pastikan izin kamera diberikan.');
                    console.error('Match webcam error:', err);
                }
            }
        }

        function stopMatchCamera() {
            if (autoScanTimer) {
                clearInterval(autoScanTimer);
                autoScanTimer = null;
            }
            if (matchStream) {
                matchStream.getTracks().forEach(track => track.stop());
                matchStream = null;
            }
            isMatchProcessing = false;
            const notice = document.getElementById('camera-off-notice');
            const scanLine = document.getElementById('scan-overlay-line');
            const boundingBox = document.getElementById('face-bounding-box');
            if (notice) notice.classList.remove('hidden');
            if (scanLine) scanLine.classList.add('hidden');
            if (boundingBox) boundingBox.classList.add('hidden');
        }

        function lockInSuccessMatch(similarity, engineName) {
            hasPassedMatch = true;

            if (autoScanTimer) {
                clearInterval(autoScanTimer);
                autoScanTimer = null;
            }

            const scanLine = document.getElementById('scan-overlay-line');
            if (scanLine) scanLine.classList.add('hidden');

            const resultBox = document.getElementById('match-result-box');
            const resultText = document.getElementById('match-result-text');

            resultBox.className = 'p-4 rounded-xl border-2 border-emerald-500 bg-emerald-50 text-center space-y-1.5 shadow-lg animate-in zoom-in duration-300';
            resultText.innerHTML = 
                '<div class="flex items-center justify-center gap-2 text-emerald-700 font-extrabold text-base mb-1">' +
                '  <span class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-md animate-bounce"><i class="fa-solid fa-check text-lg"></i></span>' +
                '  <span>VERIFIKASI BERHASIL! (SKOR: ' + similarity + '% &ge; 75%)</span>' +
                '</div>' +
                '<p class="text-xs text-emerald-800 font-bold">Wajah mahasiwa atas nama <u>{{ $mahasiswa->nama_lengkap }}</u> (NIM: {{ $mahasiswa->nim }}) LOLOS verifikasi AI presensi!</p>' +
                '<div class="mt-2 pt-2 border-t border-emerald-200/80 flex items-center justify-between text-[11px] text-emerald-600 font-medium">' +
                '  <span><i class="fa-solid fa-lock mr-1"></i> Status: <strong>DIKUNCI (PASSED)</strong></span>' +
                '  <span>Engine: <strong>' + engineName + '</strong></span>' +
                '</div>';
        }

        async function runAutoScanLoop() {
            if (hasPassedMatch) return;

            const video = document.getElementById('match-video');
            const resultBox = document.getElementById('match-result-box');
            const resultText = document.getElementById('match-result-text');

            if (!video || !matchStream || isMatchProcessing) return;

            isMatchProcessing = true;

            try {
                const canvas = document.createElement('canvas');
                canvas.width = 320; canvas.height = 240;
                canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
                const frameData = canvas.toDataURL('image/jpeg', 0.75);

                const response = await fetch("{{ route('mahasiswa.face.verify-python') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ live_image: frameData })
                });

                const data = await response.json();

                if (data && data.status === 'success' && data.face_detected === false) {
                    // NO FACE IN FRAME
                    resultBox.className = 'p-4 rounded-xl border-2 border-amber-300 bg-amber-50 text-center space-y-1 shadow-sm';
                    resultText.innerHTML = 
                        '<span class="text-amber-800 font-extrabold text-sm flex items-center justify-center">' +
                        '<i class="fa-solid fa-user-slash text-amber-600 text-lg mr-2 animate-pulse"></i> TIDAK TERDETEKSI WAJAH DI KAMERA</span>' +
                        '<span class="text-xs text-amber-700 font-medium block mt-1">Silakan posisikan wajah Anda tepat di dalam kotak pembingkai AI.</span>';
                } else if (data && data.status === 'success' && data.matched && data.similarity >= 75.0) {
                    // SAME PERSON: SCORE >= 75% -> LOCK IN SUCCESS
                    lockInSuccessMatch(data.similarity, data.engine || 'Python AI Engine');
                } else if (data && data.status === 'success' && !data.matched) {
                    // DIFFERENT PERSON / UNMATCHED -> REJECT
                    resultBox.className = 'p-4 rounded-xl border-2 border-rose-200 bg-rose-50 text-center space-y-1 shadow-sm';
                    resultText.innerHTML = 
                        '<span class="text-rose-700 font-extrabold text-base flex items-center justify-center">' +
                        '<i class="fa-solid fa-circle-xmark text-rose-500 text-xl mr-2"></i> WAJAH ORANG LAIN / TIDAK COCOK (' + data.similarity + '% < 75%)</span>' +
                        '<span class="text-xs text-rose-600 font-medium block mt-1">Wajah di kamera laptop berbeda dengan dataset terdaftar. Verifikasi Gagal!</span>';
                } else {
                    resultBox.className = 'p-4 rounded-xl border border-indigo-200 bg-indigo-50 text-center space-y-1';
                    resultText.innerHTML = 
                        '<span class="text-indigo-700 font-bold"><i class="fa-solid fa-magnifying-glass mr-1.5 animate-pulse"></i> MEMINDAI WAJAH KAMERA...</span><br>' +
                        '<span class="text-xs text-indigo-600">Arahkan wajah Anda tepat di tengah kotak pembingkai AI.</span>';
                }
            } catch (err) {
                console.error(err);
            } finally {
                isMatchProcessing = false;
            }
        }
    </script>
</x-mahasiswa-layout>
