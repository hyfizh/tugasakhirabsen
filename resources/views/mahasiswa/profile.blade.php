<x-mahasiswa-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div>
            <h1 class="heading-font text-3xl font-extrabold text-slate-900 tracking-tight">Profil &amp; Data Saya</h1>
            <p class="text-slate-500 mt-1">Lihat informasi akademik, biodata kontak terdaftar, dan status dataset pengenalan wajah Anda.</p>
        </div>

        <!-- Warning Alert for Unverified Email -->
        @if (empty(auth()->user()->email_verified_at) || empty(auth()->user()->email))
            <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4" x-data>
                <div class="flex items-start space-x-3">
                    <i class="fa-solid fa-envelope-circle-check text-amber-600 text-2xl mt-0.5"></i>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">VERIFIKASI EMAIL GMAIL DIPERLUKAN</h3>
                        <p class="text-xs text-amber-700 mt-0.5">Silakan lengkapi dan verifikasi alamat Gmail Anda agar dapat menerima informasi akademik &amp; Surat Peringatan (SP).</p>
                    </div>
                </div>
                <button type="button" @click="$dispatch('open-email-modal')" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-paper-plane mr-1.5"></i> Verifikasi Email Sekarang
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6">
            
            <!-- Card 1: Data Diri Mahasiswa -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center space-x-3">
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <i class="fa-solid fa-address-card text-lg"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Biodata &amp; Informasi Kontak</h3>
                        <p class="text-[10px] text-slate-400">NIM dan Nama bersifat permanen, hanya email &amp; nomor handphone yang dapat diedit.</p>
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
                            <div class="flex items-center justify-between mb-1">
                                <label for="email" class="block text-sm font-semibold text-slate-700">Alamat Email Gmail</label>
                                @if (auth()->user()->email_verified_at)
                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-extrabold text-[11px] rounded-full inline-flex items-center shadow-xs">
                                        <i class="fa-solid fa-circle-check text-xs mr-1 text-emerald-600"></i> Terverifikasi
                                    </span>
                                @else
                                    <button type="button" @click="$dispatch('open-email-modal')" class="px-2.5 py-0.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-[11px] rounded-full inline-flex items-center shadow-xs transition-all cursor-pointer" x-data>
                                        <i class="fa-solid fa-paper-plane text-xs mr-1"></i> Verifikasi OTP
                                    </button>
                                @endif
                            </div>
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
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Perubahan Kontak
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 2: Status Dataset Wajah Biometrik AI (Read Only) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center space-x-3">
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <i class="fa-solid fa-face-smile text-lg"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Foto Dataset Wajah Biometrik AI (Read-Only)</h3>
                        <p class="text-[10px] text-slate-400">Dataset foto biometrik wajah terdaftar dan dikelola secara terpusat oleh Admin Jurusan.</p>
                    </div>
                </div>

                <div class="p-6">
                    @if ($mahasiswa->foto_wajah)
                        <div class="flex flex-col sm:flex-row items-center gap-6 p-5 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <img src="{{ asset('storage/' . $mahasiswa->foto_wajah) }}" alt="Foto Wajah {{ $mahasiswa->nama_lengkap }}" class="w-32 h-32 rounded-2xl object-cover border-2 border-indigo-500 shadow-md">
                            <div class="space-y-2 text-center sm:text-left">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
                                    <i class="fa-solid fa-circle-check mr-1.5 text-emerald-600"></i> Foto Dataset Terdaftar &amp; Aktif
                                </span>
                                <h4 class="font-bold text-slate-800 text-sm">{{ basename($mahasiswa->foto_wajah) }}</h4>
                                <p class="text-xs text-slate-500">
                                    Didaftarkan oleh Admin pada: 
                                    <strong>{{ $mahasiswa->last_photo_updated_at ? $mahasiswa->last_photo_updated_at->format('d M Y, H:i') : '-' }} WIB</strong>
                                </p>
                                <p class="text-xs text-slate-400">
                                    Digunakan untuk pencocokan biometrik AI presensi IoT pada kamera ruang kuliah.
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-slate-400 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center mx-auto text-xl font-bold">
                                <i class="fa-solid fa-user-slash"></i>
                            </div>
                            <h4 class="font-bold text-slate-700 text-sm">Belum Ada Foto Dataset Wajah</h4>
                            <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                                Foto dataset biometrik wajah Anda belum didaftarkan. Pendaftaran foto biometrik dilakukan secara terpusat oleh <strong>Admin Jurusan</strong> via kamera webcam saat registrasi data mahasiswa.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- MODAL VERIFIKASI EMAIL VIA KODE OTP -->
        <div x-data="{ 
                showModal: {{ session('otp_sent') ? 'true' : 'false' }},
                step: '{{ session('otp_sent') ? 'otp' : 'email' }}'
            }" 
            @open-email-modal.window="showModal = true; step = 'email'"
            x-show="showModal" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Backdrop -->
                <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Body -->
                <div x-show="showModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full p-6 space-y-5 border border-slate-100">
                    
                    <!-- STEP 1: Input Email -->
                    <div x-show="step === 'email'" class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-envelope text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">Lengkapi Alamat Email</h3>
                                    <p class="text-xs text-slate-500 font-medium">Masukkan Gmail aktif untuk pengiriman kode OTP.</p>
                                </div>
                            </div>
                            <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <form action="{{ route('mahasiswa.email.send-otp') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Alamat Email Gmail Baru</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required placeholder="contoh: mahasiswa@gmail.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-amber-500 outline-none">
                            </div>

                            <div class="pt-2 flex items-center justify-end space-x-3 border-t border-slate-100">
                                <button type="button" @click="showModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md transition-all">
                                    Kirim Kode OTP <i class="fa-solid fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- STEP 2: Input OTP Kode 6 Digit -->
                    <div x-show="step === 'otp'" class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    <i class="fa-solid fa-shield-halved text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-extrabold text-slate-900">Verifikasi Kode OTP</h3>
                                    <p class="text-xs text-slate-500 font-medium">Masukkan 6-digit angka OTP yang telah dikirim.</p>
                                </div>
                            </div>
                            <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        @if (session('pending_email'))
                            <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-xs text-indigo-800 font-medium">
                                Kode OTP dikirim ke: <strong>{{ session('pending_email') }}</strong>
                            </div>
                        @endif

                        <form action="{{ route('mahasiswa.email.verify-otp') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Kode OTP (6-Digit)</label>
                                <input type="text" name="otp" maxlength="6" required placeholder="123456" class="w-full text-center tracking-[0.5em] text-lg font-mono font-bold px-4 py-3 rounded-xl border border-slate-200 text-indigo-600 focus:ring-2 focus:ring-indigo-500 outline-none">
                            </div>

                            <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                                <button type="button" @click="step = 'email'" class="text-xs font-bold text-indigo-600 hover:underline">
                                    <i class="fa-solid fa-rotate-left mr-1"></i> Ubah Email
                                </button>
                                <div class="flex space-x-2">
                                    <button type="button" @click="showModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-extrabold rounded-xl shadow-md transition-all">
                                        Verifikasi OTP <i class="fa-solid fa-circle-check ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-mahasiswa-layout>
