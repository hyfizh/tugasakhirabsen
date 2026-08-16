<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\AuditLog;
use App\Models\SuratPeringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalMahasiswa = Mahasiswa::query()->count();
        $totalDosen = Dosen::query()->count();
        $totalKelas = Kelas::query()->count();
        
        $todayDate = date('Y-m-d');
        $todayAbsensi = Absensi::query()->where('tanggal', $todayDate)->get();
        $totalHadir = $todayAbsensi->whereIn('status', ['H', 'Hadir'])->count();
        $attendanceRate = $totalMahasiswa > 0 ? round(($totalHadir / max(1, $totalMahasiswa)) * 100) : 0;

        $totalSp1 = SuratPeringatan::query()->where('status', 'Aktif')->where('tingkat_sp', 1)->count();
        $totalSp2 = SuratPeringatan::query()->where('status', 'Aktif')->where('tingkat_sp', 2)->count();
        $totalSp3 = SuratPeringatan::query()->where('status', 'Aktif')->where('tingkat_sp', 3)->count();
        $totalSp = $totalSp1 + $totalSp2 + $totalSp3;

        $recentLogs = AuditLog::query()->orderBy('created_at', 'desc')->take(4)->get();
        $todayAbsensiList = Absensi::query()->with(['mahasiswa.kelas', 'jadwal.mataKuliah'])
            ->where('tanggal', $todayDate)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalMahasiswa',
            'totalDosen',
            'totalKelas',
            'attendanceRate',
            'totalSp',
            'totalSp1',
            'totalSp2',
            'totalSp3',
            'recentLogs',
            'todayAbsensiList'
        ));
    }

    // --- KELAS CRUD ---
    public function indexKelas()
    {
        $kelas = Kelas::query()->withCount('mahasiswas')->get();
        return view('admin.kelas.index', compact('kelas'));
    }

    public function storeKelas(Request $request)
    {
        $request->validate(['nama_kelas' => 'required|string|max:50']);
        Kelas::query()->create($request->only('nama_kelas'));
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function updateKelas(Request $request, Kelas $kelas)
    {
        $request->validate(['nama_kelas' => 'required|string|max:50']);
        $kelas->update($request->only('nama_kelas'));
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroyKelas(Kelas $kelas)
    {
        if ($kelas->mahasiswas()->exists()) {
            return redirect()->route('admin.kelas.index')->with('error', 'Kelas tidak bisa dihapus karena memiliki mahasiswa.');
        }
        $kelas->delete();
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    // --- DOSEN CRUD ---
    public function indexDosen()
    {
        $dosens = Dosen::query()->with('user')->get();
        return view('admin.dosen.index', compact('dosens'));
    }

    public function storeDosen(Request $request)
    {
        $request->validate([
            'nip' => 'required|string|unique:dosens,nip',
            'nama_dosen' => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => 'dosen',
                'is_password_changed' => true, // default dosen already changed or set up
            ]);

            Dosen::query()->create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_dosen' => $request->nama_dosen,
                'no_hp' => $request->no_hp,
            ]);
        });

        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function updateDosen(Request $request, Dosen $dosen)
    {
        $request->validate([
            'nip' => 'required|string|unique:dosens,nip,' . $dosen->id,
            'nama_dosen' => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:20',
        ]);

        $dosen->update($request->only('nip', 'nama_dosen', 'no_hp'));
        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroyDosen(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            $user = $dosen->user;
            $dosen->delete();
            if ($user) $user->delete();
        });
        return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }

    // --- MAHASISWA CRUD ---
    public function indexMahasiswa(Request $request)
    {
        $query = Mahasiswa::query()->with(['user', 'kelas']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $mahasiswas = $query->latest()->get();
        $kelas = Kelas::query()->get();
        return view('admin.mahasiswa.index', compact('mahasiswas', 'kelas'));
    }

    public function storeMahasiswa(Request $request)
    {
        $request->validate([
            'nim'               => 'required|string|unique:mahasiswas,nim|unique:users,username',
            'nama_lengkap'      => 'required|string|max:100',
            'kelas_id'          => 'required|exists:kelas,id',
            'email'             => 'nullable|email|unique:mahasiswas,email|unique:users,email',
            'no_hp'             => 'nullable|string|max:20',
            'rfid_uid'          => 'nullable|string|unique:mahasiswas,rfid_uid',
            'foto_wajah_base64' => 'nullable|string',
            'foto_wajah'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Set email, no_hp, and rfid_uid to null when empty so student completes profile & verifies email mandatorily
        $email = $request->filled('email') ? trim($request->email) : null;
        $noHp = $request->filled('no_hp') ? trim($request->no_hp) : null;
        $rfidUid = $request->filled('rfid_uid') ? trim($request->rfid_uid) : null;

        DB::transaction(function () use ($request, $email, $noHp, $rfidUid) {
            $user = User::query()->create([
                'username'            => $request->nim,
                'email'               => $email,
                'password'            => Hash::make('12345678'),
                'role'                => 'mahasiswa',
                'is_password_changed' => false,
                'email_verified_at'   => null,
            ]);

            $path = null;
            if ($request->filled('foto_wajah_base64')) {
                $base64Image = $request->foto_wajah_base64;
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $data = substr($base64Image, strpos($base64Image, ',') + 1);
                    $data = base64_decode($data);
                    $ext = strtolower($type[1]) === 'png' ? 'png' : 'jpg';

                    $filename = $request->nim . '_' . time() . '.' . $ext;
                    $path = 'profiles/' . $filename;
                    Storage::disk('public')->put($path, $data);
                }
            } elseif ($request->file('foto_wajah')) {
                $file = $request->file('foto_wajah');
                $filename = $request->nim . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profiles', $filename, 'public');
            }

            Mahasiswa::query()->create([
                'user_id'               => $user->id,
                'nim'                   => $request->nim,
                'nama_lengkap'          => $request->nama_lengkap,
                'email'                 => $email,
                'no_hp'                 => $noHp,
                'rfid_uid'              => $rfidUid,
                'kelas_id'              => $request->kelas_id,
                'foto_wajah'            => $path,
                'last_photo_updated_at' => $path ? now() : null,
            ]);
        });

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan beserta foto biometrik wajah terdaftar.');
    }

    public function updateMahasiswa(Request $request, Mahasiswa $mahasiswa)
    {
        $request->validate([
            'nim'               => 'required|string|unique:mahasiswas,nim,' . $mahasiswa->id,
            'nama_lengkap'      => 'required|string|max:100',
            'email'             => 'required|email|unique:mahasiswas,email,' . $mahasiswa->id,
            'no_hp'             => 'nullable|string|max:20',
            'kelas_id'          => 'required|exists:kelas,id',
            'rfid_uid'          => 'nullable|string|unique:mahasiswas,rfid_uid,' . $mahasiswa->id,
            'foto_wajah_base64' => 'nullable|string',
            'foto_wajah'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $updateData = $request->only('nim', 'nama_lengkap', 'email', 'no_hp', 'kelas_id', 'rfid_uid');

        if ($request->filled('foto_wajah_base64')) {
            $base64Image = $request->foto_wajah_base64;
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $data = base64_decode($data);
                $ext = strtolower($type[1]) === 'png' ? 'png' : 'jpg';

                $filename = $request->nim . '_' . time() . '.' . $ext;
                $path = 'profiles/' . $filename;
                Storage::disk('public')->put($path, $data);
                $updateData['foto_wajah'] = $path;
                $updateData['last_photo_updated_at'] = now();
            }
        } elseif ($request->file('foto_wajah')) {
            $file = $request->file('foto_wajah');
            $filename = $request->nim . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');
            $updateData['foto_wajah'] = $path;
            $updateData['last_photo_updated_at'] = now();
        }

        $mahasiswa->update($updateData);
        
        if ($mahasiswa->user) {
            $mahasiswa->user->update([
                'username' => $request->nim,
                'email'    => $request->email
            ]);
        }

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    public function destroyMahasiswa($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        \Log::debug('destroyMahasiswa called', ['id' => $mahasiswa->id, 'nim' => $mahasiswa->nim]);
        DB::transaction(function () use ($mahasiswa) {
            $user = $mahasiswa->user;
            $mahasiswa->delete();
            if ($user) $user->delete();
        });
        \Log::debug('destroyMahasiswa finished');
        return redirect()->route('admin.mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }

    public function resetPasswordMahasiswa(Mahasiswa $mahasiswa)
    {
        $user = $mahasiswa->user;
        if (!$user) {
            $user = User::query()
                ->where('username', $mahasiswa->nim)
                ->orWhere('email', $mahasiswa->email)
                ->first();
        }

        if (!$user) {
            return redirect()->back()->with('error', 'Akun User untuk mahasiswa ini tidak ditemukan.');
        }

        $defaultPassword = '12345678';

        $user->update([
            'password' => Hash::make($defaultPassword),
            'is_password_changed' => false,
        ]);

        if (!$mahasiswa->user_id) {
            $mahasiswa->update(['user_id' => $user->id]);
        }

        \App\Models\AuditLog::create([
            'tipe_log' => 'RESET_PASSWORD',
            'deskripsi' => "Admin mereset password mahasiswa {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim}) ke password default ('{$defaultPassword}') dan mengubah status menjadi belum ubah password.",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->route('admin.mahasiswa.index')
            ->with('success', "Password mahasiswa {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim}) berhasil direset ke password default ('{$defaultPassword}'). Status diubah menjadi 'Belum Ubah' dan mahasiswa wajib mengganti password saat login berikutnya.");
    }

    // --- MATA KULIAH CRUD ---
    public function indexMataKuliah()
    {
        $mataKuliahs = MataKuliah::query()->get();
        return view('admin.matakuliah.index', compact('mataKuliahs'));
    }

    public function storeMataKuliah(Request $request)
    {
        $request->validate([
            'kode_mk' => 'required|string|unique:mata_kuliahs,kode_mk',
            'nama_mk' => 'required|string|max:100',
            'sks' => 'required|integer|min:1|max:6',
        ]);

        MataKuliah::query()->create($request->only('kode_mk', 'nama_mk', 'sks'));
        return redirect()->route('admin.matakuliah.index')->with('success', 'Mata Kuliah berhasil ditambahkan.');
    }

    public function updateMataKuliah(Request $request, MataKuliah $matakuliah)
    {
        $request->validate([
            'kode_mk' => 'required|string|unique:mata_kuliahs,kode_mk,' . $matakuliah->id,
            'nama_mk' => 'required|string|max:100',
            'sks' => 'required|integer|min:1|max:6',
        ]);

        $matakuliah->update($request->only('kode_mk', 'nama_mk', 'sks'));
        return redirect()->route('admin.matakuliah.index')->with('success', 'Mata Kuliah berhasil diperbarui.');
    }

    public function destroyMataKuliah(MataKuliah $matakuliah)
    {
        if ($matakuliah->jadwals()->exists()) {
            return redirect()->route('admin.matakuliah.index')->with('error', 'Mata Kuliah tidak bisa dihapus karena sudah masuk ke jadwal kuliah.');
        }
        $matakuliah->delete();
        return redirect()->route('admin.matakuliah.index')->with('success', 'Mata Kuliah berhasil dihapus.');
    }

    // --- JADWAL CRUD ---
    public function indexJadwal(Request $request)
    {
        $kelas = Kelas::query()->get();
        $selectedKelasId = $request->get('kelas_id', $kelas->first()->id ?? null);
        $jadwals = Jadwal::query()->with(['kelas', 'mataKuliah', 'dosen'])->get();
        $mataKuliahs = MataKuliah::query()->get();
        $dosens = Dosen::query()->get();

        $timeSlots = [
            1 => '07:30 - 08:20',
            2 => '08:20 - 09:10',
            3 => '09:10 - 10:00',
            4 => '10:15 - 11:05',
            5 => '11:05 - 11:55',
            6 => '13:00 - 13:50',
            7 => '13:50 - 14:40',
            8 => '14:40 - 15:30',
            9 => '15:45 - 16:35',
            10 => '16:35 - 17:25'
        ];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $grid = [];
        $selectedKelas = $selectedKelasId ? Kelas::query()->find($selectedKelasId) : null;
        $selectedKelasName = $selectedKelas->nama_kelas ?? 'SEMUA KELAS';

        // Initialize grid matrix (10 time slots x 7 days)
        for ($r = 1; $r <= 10; $r++) {
            foreach ($days as $day) {
                $grid[$r][$day] = ['status' => 'empty'];
            }
        }

        $classJadwalsQuery = Jadwal::query()->with(['kelas', 'mataKuliah', 'dosen']);
        if ($selectedKelasId) {
            $classJadwalsQuery->where('kelas_id', $selectedKelasId);
        }
        $classJadwals = $classJadwalsQuery->get();

        foreach ($classJadwals as $jdw) {
            // Helper to parse slot number or time string
            $start = is_numeric($jdw->jam_mulai) ? (int)$jdw->jam_mulai : 1;
            $end = is_numeric($jdw->jam_selesai) ? (int)$jdw->jam_selesai : $start;

            if ($start > 10) $start = 1;
            if ($end > 10) $end = $start;

            $span = max(1, $end - $start + 1);

            $grid[$start][$jdw->hari] = [
                'status' => 'occupied',
                'rowspan' => $span,
                'data' => $jdw
            ];

            for ($i = 1; $i < $span; $i++) {
                if (($start + $i) <= 10) {
                    $grid[$start + $i][$jdw->hari] = ['status' => 'span'];
                }
            }
        }

        return view('admin.jadwal.index', compact(
            'jadwals', 'kelas', 'mataKuliahs', 'dosens', 
            'selectedKelasId', 'selectedKelasName', 'grid', 'timeSlots', 'days'
        ));
    }

    public function storeJadwal(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'dosen_id' => 'required|exists:dosens,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|integer|min:1|max:10',
            'jam_selesai' => 'required|integer|min:1|max:10|gte:jam_mulai',
            'toleransi_keterlambatan' => 'required|integer|min:0|max:60',
        ]);

        Jadwal::query()->create($request->all());
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function updateJadwal(Request $request, Jadwal $jadwal)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'dosen_id' => 'required|exists:dosens,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|integer|min:1|max:10',
            'jam_selesai' => 'required|integer|min:1|max:10|gte:jam_mulai',
            'toleransi_keterlambatan' => 'required|integer|min:0|max:60',
        ]);

        $jadwal->update($request->all());
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroyJadwal(Jadwal $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    // --- BATCH RFID SCAN ---
    public function batchScanRfid(Request $request)
    {
        // For simulation, check if there's a cached RFID UID scanned
        $scannedUid = Cache::get('temp_rfid_uid');
        
        if ($request->ajax()) {
            return response()->json([
                'rfid_uid' => $scannedUid ?: null,
            ]);
        }

        $selectedKelasId = $request->get('kelas_id');
        $kelas = Kelas::query()->get();
        
        $students = [];
        if ($selectedKelasId) {
            $students = Mahasiswa::query()->where('kelas_id', $selectedKelasId)->get();
        }

        return view('admin.rfid.scan', compact('scannedUid', 'kelas', 'selectedKelasId', 'students'));
    }

    public function assignRfid(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'rfid_uid' => 'required|string|unique:mahasiswas,rfid_uid',
        ]);

        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        $mahasiswa->update(['rfid_uid' => $request->rfid_uid]);

        // Remove from cache after assigning
        if (Cache::get('temp_rfid_uid') === $request->rfid_uid) {
            Cache::forget('temp_rfid_uid');
        }

        return redirect()->route('admin.rfid.scan', ['kelas_id' => $mahasiswa->kelas_id])
            ->with('success', 'RFID UID berhasil diasosiasikan ke mahasiswa: ' . $mahasiswa->nama_lengkap);
    }

    public function clearScannedRfid()
    {
        Cache::forget('temp_rfid_uid');
        return redirect()->route('admin.rfid.scan')->with('success', 'Temp RFID UID cleared.');
    }

    // --- STASIUN REGISTRASI SENSOR IOT (RFID TAG & BIOMETRIK WAJAH WEBRTC) ---
    public function indexIotDevice()
    {
        $mahasiswas = Mahasiswa::with(['kelas', 'user'])->orderBy('nama_lengkap', 'asc')->get();
        
        // Mahasiswa yang belum melengkapi RFID UID atau Foto Wajah
        $mahasiswasPending = Mahasiswa::with(['kelas', 'user'])
            ->where(function($query) {
                $query->whereNull('foto_wajah')
                      ->orWhereNull('rfid_uid');
            })
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('admin.iot-device', compact('mahasiswas', 'mahasiswasPending'));
    }

    public function assignIotDevice(Request $request)
    {
        $request->validate([
            'mahasiswa_id'      => 'required|exists:mahasiswas,id',
            'rfid_uid'          => 'required|string|max:50',
            'foto_wajah_base64' => 'required|string',
        ]);

        try {
            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);

            // Cek keunikan RFID UID jika dipakai oleh mahasiswa lain
            $existingRfid = Mahasiswa::where('rfid_uid', trim($request->rfid_uid))
                ->where('id', '!=', $mahasiswa->id)
                ->first();

            if ($existingRfid) {
                return redirect()->back()->with('error', "Kode RFID Tag ({$request->rfid_uid}) sudah terikat pada mahasiswa lain: {$existingRfid->nama_lengkap} (NIM: {$existingRfid->nim}).");
            }

            // Decode string Base64 gambar dari WebRTC Camera
            $base64Image = $request->foto_wajah_base64;
            $path = null;

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) {
                    return redirect()->back()->with('error', 'Gagal memproses data gambar snapshot kamera. Silakan ambil foto snapshot ulang.');
                }

                $ext = strtolower($type[1]) === 'png' ? 'png' : 'jpg';
                $slugNama = Str::slug($mahasiswa->nama_lengkap);
                $filename = $mahasiswa->nim . '_' . ($slugNama ?: 'mahasiswa') . '_' . time() . '.' . $ext;
                $path = 'profiles/' . $filename;

                // Simpan berkas aktual ke disk public
                Storage::disk('public')->put($path, $data);
            } else {
                return redirect()->back()->with('error', 'Format gambar Base64 tidak valid. Silakan ambil foto snapshot kamera ulang.');
            }

            // Hapus foto lama jika ada
            if ($mahasiswa->foto_wajah && Storage::disk('public')->exists($mahasiswa->foto_wajah)) {
                Storage::disk('public')->delete($mahasiswa->foto_wajah);
            }

            // UPDATE (Bukan Create) pada tabel mahasiswas
            $mahasiswa->update([
                'rfid_uid'              => trim($request->rfid_uid),
                'foto_wajah'            => $path,
                'last_photo_updated_at' => now(),
            ]);

            \App\Models\AuditLog::create([
                'tipe_log'   => 'IOT_SENSOR_ASSIGNED',
                'deskripsi'  => "Admin mendaftarkan sensor fisik RFID Tag ({$mahasiswa->rfid_uid}) dan dataset foto biometrik wajah untuk mahasiswa {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim})",
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('admin.iot-device.index')
                ->with('success', "✅ Registrasi sensor fisik RFID Tag ({$mahasiswa->rfid_uid}) dan foto biometrik wajah berhasil disimpan untuk {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim}).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed assigning IoT device sensors: " . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses registrasi sensor IoT: ' . $e->getMessage());
        }
    }

    public function assignRfidDevice(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'rfid_uid'     => 'required|string|max:50',
        ]);

        try {
            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);

            // Cek keunikan RFID Tag
            $existingRfid = Mahasiswa::where('rfid_uid', trim($request->rfid_uid))
                ->where('id', '!=', $mahasiswa->id)
                ->first();

            if ($existingRfid) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => "Kode RFID Tag ({$request->rfid_uid}) sudah terikat pada mahasiswa lain: {$existingRfid->nama_lengkap} (NIM: {$existingRfid->nim})."], 422);
                }
                return redirect()->back()->with('error', "Kode RFID Tag ({$request->rfid_uid}) sudah terikat pada mahasiswa lain: {$existingRfid->nama_lengkap} (NIM: {$existingRfid->nim}).");
            }

            $mahasiswa->update([
                'rfid_uid' => trim($request->rfid_uid),
            ]);

            \App\Models\AuditLog::create([
                'tipe_log'   => 'IOT_RFID_BINDING',
                'deskripsi'  => "Admin mendaftarkan kartu RFID Tag ({$mahasiswa->rfid_uid}) untuk mahasiswa {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim})",
                'ip_address' => $request->ip(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "✅ Pendaftaran kartu RFID ({$mahasiswa->rfid_uid}) berhasil untuk {$mahasiswa->nama_lengkap}."]);
            }

            return redirect()->route('admin.iot-device.index')
                ->with('success', "✅ Pendaftaran kartu RFID ({$mahasiswa->rfid_uid}) berhasil disimpan untuk {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim}).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed assigning RFID device: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat memproses registrasi RFID: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses registrasi RFID: ' . $e->getMessage());
        }
    }

    public function assignFaceDevice(Request $request)
    {
        $request->validate([
            'mahasiswa_id'      => 'required|exists:mahasiswas,id',
            'foto_wajah_base64' => 'required|string',
        ]);

        try {
            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);

            $base64Image = $request->foto_wajah_base64;
            $path = null;

            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $data = base64_decode($data);
                if ($data === false) {
                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'Gagal memproses data gambar snapshot kamera. Silakan ambil foto snapshot ulang.'], 422);
                    }
                    return redirect()->back()->with('error', 'Gagal memproses data gambar snapshot kamera. Silakan ambil foto snapshot ulang.');
                }

                $ext = strtolower($type[1]) === 'png' ? 'png' : 'jpg';
                $slugNama = Str::slug($mahasiswa->nama_lengkap);
                $filename = $mahasiswa->nim . '_' . ($slugNama ?: 'mahasiswa') . '_' . time() . '.' . $ext;
                $path = 'profiles/' . $filename;

                Storage::disk('public')->put($path, $data);
            } else {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Format gambar Base64 tidak valid.'], 422);
                }
                return redirect()->back()->with('error', 'Format gambar Base64 tidak valid.');
            }

            if ($mahasiswa->foto_wajah && Storage::disk('public')->exists($mahasiswa->foto_wajah)) {
                Storage::disk('public')->delete($mahasiswa->foto_wajah);
            }

            $mahasiswa->update([
                'foto_wajah'            => $path,
                'last_photo_updated_at' => now(),
            ]);

            \App\Models\AuditLog::create([
                'tipe_log'   => 'IOT_FACE_ENROLLED',
                'deskripsi'  => "Admin merekam foto dataset biometrik wajah untuk mahasiswa {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim})",
                'ip_address' => $request->ip(),
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => "✅ Pendaftaran foto biometrik wajah berhasil untuk {$mahasiswa->nama_lengkap}.", 'foto_url' => asset('storage/' . $path)]);
            }

            return redirect()->route('admin.iot-device.index')
                ->with('success', "✅ Registrasi foto biometrik wajah berhasil disimpan untuk {$mahasiswa->nama_lengkap} (NIM: {$mahasiswa->nim}).");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed assigning Face device: " . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan foto biometrik: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan foto biometrik: ' . $e->getMessage());
        }
    }

    // --- LAPORAN KOMPEN MATRIKS 10 JAM ---
    public function laporanKompen()
    {
        // Compensation Matrix Calculation:
        // Rule: Alpa x2, Izin x1, Sakit x0.
        // We retrieve all student attendance records along with their schedules.
        
        $students = Mahasiswa::query()->with(['kelas', 'absensis' => function ($query) {
            $query->with('jadwal');
        }])->get();

        $kompenData = $students->map(function ($student) {
            $totalAlpaHours = 0;
            $totalIzinHours = 0;
            $totalSakitHours = 0;
            $compensationPenalty = 0;

            foreach ($student->absensis as $abs) {
                $hours = 1;
                if ($abs->jadwal) {
                    $hours = ($abs->jadwal->jam_selesai - $abs->jadwal->jam_mulai) + 1;
                }

                if ($abs->status === 'A') {
                    $totalAlpaHours += $hours;
                    $compensationPenalty += $hours * 2;
                } elseif ($abs->status === 'T') {
                    // Jika mahasiswa datang di jam ke-2 atau lebih pada matakuliah multi-jam (terlambat), jam ke-1 yang dilewati terhitung Alpa
                    if ($abs->jadwal && $abs->jam_pelajaran_ke > $abs->jadwal->jam_mulai) {
                        $missedHours = $abs->jam_pelajaran_ke - $abs->jadwal->jam_mulai;
                        $totalAlpaHours += $missedHours;
                        $compensationPenalty += $missedHours * 2;
                    }
                } elseif ($abs->status === 'I') {
                    $totalIzinHours += $hours;
                    $compensationPenalty += $hours * 1;
                } elseif ($abs->status === 'S') {
                    $totalSakitHours += $hours;
                    $compensationPenalty += $hours * 0;
                }
            }

            // Assign / check SP level based on total Alpa hours
            $spLevel = 0;
            $spStatus = 'Aman';
            if ($totalAlpaHours >= 10 && $totalAlpaHours < 30) {
                $spLevel = 1;
                $spStatus = 'SP 1 (Peringatan Awal)';
            } elseif ($totalAlpaHours >= 30 && $totalAlpaHours < 50) {
                $spLevel = 2;
                $spStatus = 'SP 2 (Peringatan Keras)';
            } elseif ($totalAlpaHours >= 50) {
                $spLevel = 3;
                $spStatus = 'SP 3 (Terancam Drop Out)';
            }

            // Sync SP to database table if needed
            if ($spLevel > 0) {
                SuratPeringatan::query()->updateOrCreate(
                    ['mahasiswa_id' => $student->id, 'status' => 'Aktif'],
                    ['tingkat_sp' => $spLevel, 'total_jam_alpa' => $totalAlpaHours]
                );
            } else {
                // If student returns to safe hours, mark previous active SP as 'Selesai'
                SuratPeringatan::query()->where('mahasiswa_id', $student->id)
                    ->where('status', 'Aktif')
                    ->update(['status' => 'Selesai']);
            }

            return (object) [
                'id' => $student->id,
                'nim' => $student->nim,
                'nama_lengkap' => $student->nama_lengkap,
                'kelas' => $student->kelas->nama_kelas ?? '-',
                'total_alpa' => $totalAlpaHours,
                'total_izin' => $totalIzinHours,
                'total_sakit' => $totalSakitHours,
                'kompen_jam' => $compensationPenalty,
                'sp_level' => $spLevel,
                'sp_status' => $spStatus,
            ];
        })->filter(function ($data) {
            return $data->sp_level >= 1;
        });

        return view('admin.laporan.kompen', compact('kompenData'));
    }

    // --- AUDIT LOGS ---
    public function auditLogs()
    {
        $logs = AuditLog::query()->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.audit-logs', compact('logs'));
    }

    // --- REKAP ABSENSI UNTUK DICETAK ---
    public function rekapAbsen(Request $request)
    {
        $kelas = Kelas::query()->get();
        $selectedKelasId = $request->get('kelas_id', $kelas->first()->id ?? null);
        $selectedMatkulId = $request->get('mata_kuliah_id', null);
        
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $monthsList = [
            1  => 'Januari',   2  => 'Februari', 3  => 'Maret',    4  => 'April',
            5  => 'Mei',       6  => 'Juni',     7  => 'Juli',     8  => 'Agustus',
            9  => 'September', 10 => 'Oktober',  11 => 'November', 12 => 'Desember'
        ];
        $yearsList = range(date('Y') - 2, date('Y') + 1);

        // Fetch MataKuliah list associated with selected class schedules
        $mataKuliahList = MataKuliah::query();
        if ($selectedKelasId) {
            $mataKuliahList->whereHas('jadwals', function($q) use ($selectedKelasId) {
                $q->where('kelas_id', $selectedKelasId);
            });
        }
        $mataKuliahList = $mataKuliahList->get();
        if ($mataKuliahList->isEmpty()) {
            $mataKuliahList = MataKuliah::all();
        }
        
        $dateStr = sprintf('%04d-%02d-01', $tahun, $bulan);
        $timestamp = strtotime($dateStr);
        
        $monday = date('Y-m-d', strtotime('monday this week', $timestamp));
        $saturday = date('Y-m-d', strtotime('saturday this week', $timestamp));
        
        $daysOfWeek = [
            'Senin'   => date('Y-m-d', strtotime($monday)),
            'Selasa'  => date('Y-m-d', strtotime('+1 day', strtotime($monday))),
            'Rabu'    => date('Y-m-d', strtotime('+2 days', strtotime($monday))),
            'Kamis'   => date('Y-m-d', strtotime('+3 days', strtotime($monday))),
            'Jumat'   => date('Y-m-d', strtotime('+4 days', strtotime($monday))),
            'Sabtu'   => date('Y-m-d', strtotime('+5 days', strtotime($monday))),
        ];

        $students = [];
        $weeklyAbsensi = [];
        $weeklyTotals = [];
        $monthlyTotals = [];
        
        if ($selectedKelasId) {
            $students = Mahasiswa::query()
                ->where('kelas_id', $selectedKelasId)
                ->orderBy('nama_lengkap')
                ->get();
                
            if ($students->isNotEmpty()) {
                $studentIds = $students->pluck('id');
                
                // Fetch weekly records
                $weeklyQuery = Absensi::query()
                    ->whereIn('mahasiswa_id', $studentIds)
                    ->whereBetween('tanggal', [$monday, $saturday]);

                if ($selectedMatkulId) {
                    $weeklyQuery->whereHas('jadwal', function($q) use ($selectedMatkulId) {
                        $q->where('mata_kuliah_id', $selectedMatkulId);
                    });
                }
                $absensiRecords = $weeklyQuery->get();
                    
                foreach ($absensiRecords as $rec) {
                    $dateKey = $rec->tanggal->format('Y-m-d');
                    $weeklyAbsensi[$rec->mahasiswa_id][$dateKey][$rec->jam_pelajaran_ke] = $rec->status;
                    
                    if (!isset($weeklyTotals[$rec->mahasiswa_id])) {
                        $weeklyTotals[$rec->mahasiswa_id] = ['S' => 0, 'I' => 0, 'A' => 0];
                    }
                    if (in_array($rec->status, ['S', 'I', 'A'])) {
                        $weeklyTotals[$rec->mahasiswa_id][$rec->status]++;
                    }
                }
                
                // Fetch monthly records for selected month & year
                $monthlyQuery = Absensi::query()
                    ->whereIn('mahasiswa_id', $studentIds)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun);

                if ($selectedMatkulId) {
                    $monthlyQuery->whereHas('jadwal', function($q) use ($selectedMatkulId) {
                        $q->where('mata_kuliah_id', $selectedMatkulId);
                    });
                }
                $monthlyRecords = $monthlyQuery->get();
                    
                foreach ($monthlyRecords as $rec) {
                    if (!isset($monthlyTotals[$rec->mahasiswa_id])) {
                        $monthlyTotals[$rec->mahasiswa_id] = ['S' => 0, 'I' => 0, 'A' => 0];
                    }
                    if (in_array($rec->status, ['S', 'I', 'A'])) {
                        $monthlyTotals[$rec->mahasiswa_id][$rec->status]++;
                    }
                }
            }
        }
        
        $selectedKelas = $selectedKelasId ? Kelas::find($selectedKelasId) : null;

        return view('admin.laporan.rekap', compact(
            'kelas', 'selectedKelasId', 'selectedKelas', 'selectedMatkulId', 'mataKuliahList', 'dateStr', 'monday', 'saturday',
            'daysOfWeek', 'students', 'weeklyAbsensi', 'weeklyTotals', 'monthlyTotals',
            'bulan', 'tahun', 'monthsList', 'yearsList'
        ));
    }

    public function updateAbsensiStatus(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'jadwal_id'    => 'nullable|exists:jadwals,id',
            'tanggal'      => 'required|date',
            'status'       => 'required|in:H,T,I,S,A',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
        $jadwalId = $request->jadwal_id;

        if (!$jadwalId) {
            $jadwalId = Jadwal::where('kelas_id', $mahasiswa->kelas_id)->first()?->id;
        }

        $absensi = Absensi::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'tanggal'      => $request->tanggal,
                'jadwal_id'    => $jadwalId,
            ],
            [
                'jam_pelajaran_ke'       => 1,
                'status'                 => $request->status,
                'waktu_tap_rfid'         => now(),
                'waktu_verifikasi_wajah' => now(),
            ]
        );

        $statusNames = [
            'H' => 'Hadir Tepat Waktu',
            'T' => 'Terlambat',
            'I' => 'Izin',
            'S' => 'Sakit',
            'A' => 'Alpa',
        ];

        $statusText = $statusNames[$request->status] ?? $request->status;

        AuditLog::create([
            'tipe_log'   => 'ABSENSI_UPDATED',
            'deskripsi'  => "Admin merubah status absensi {$mahasiswa->nama_lengkap} ({$mahasiswa->nim}) tanggal {$request->tanggal} menjadi '{$statusText}'" . ($request->keterangan ? " (Surat/Keterangan: {$request->keterangan})" : ''),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', "Status absensi {$mahasiswa->nama_lengkap} berhasil diubah menjadi {$statusText}.");
    }

    // --- CETAK SURAT PERINGATAN II & III ---
    public function cetakSp(Request $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['kelas', 'absensis' => function ($query) {
            $query->with('jadwal');
        }]);

        $totalAlpaHours = 0;
        $totalIzinHours = 0;
        $totalSakitHours = 0;
        $compensationPenalty = 0;

        foreach ($mahasiswa->absensis as $abs) {
            $hours = 1;
            if ($abs->jadwal) {
                $hours = ($abs->jadwal->jam_selesai - $abs->jadwal->jam_mulai) + 1;
            }

            if ($abs->status === 'A') {
                $totalAlpaHours += $hours;
                $compensationPenalty += $hours * 2;
            } elseif ($abs->status === 'I') {
                $totalIzinHours += $hours;
                $compensationPenalty += $hours * 1;
            } elseif ($abs->status === 'S') {
                $totalSakitHours += $hours;
                $compensationPenalty += $hours * 0;
            }
        }

        // Determine SP Level
        $spLevel = 0;
        $spTitle = '';
        if ($totalAlpaHours >= 10 && $totalAlpaHours < 30) {
            $spLevel = 1;
            $spTitle = 'Surat Peringatan 1';
        } elseif ($totalAlpaHours >= 30 && $totalAlpaHours < 50) {
            $spLevel = 2;
            $spTitle = 'Surat Peringatan 2';
        } elseif ($totalAlpaHours >= 50) {
            $spLevel = 3;
            $spTitle = 'Surat Peringatan 3';
        }

        // Only allow printing warning letters for SP 1, SP 2, and SP 3
        if ($spLevel < 1) {
            return redirect()->back()->with('error', 'Cetak Surat Peringatan hanya diizinkan untuk mahasiswa yang telah memiliki akumulasi Alpa minimal 10 Jam (SP I, SP II, atau SP III).');
        }

        return view('admin.laporan.sp_letter', compact('mahasiswa', 'totalAlpaHours', 'compensationPenalty', 'spLevel', 'spTitle'));
    }

    public function downloadSpPdf(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['kelas', 'absensis' => function ($query) {
            $query->with('jadwal');
        }]);

        $totalAlpaHours = 0;
        $totalIzinHours = 0;
        $totalSakitHours = 0;
        $compensationPenalty = 0;

        foreach ($mahasiswa->absensis as $abs) {
            $hours = 1;
            if ($abs->jadwal) {
                $hours = ($abs->jadwal->jam_selesai - $abs->jadwal->jam_mulai) + 1;
            }

            if ($abs->status === 'A') {
                $totalAlpaHours += $hours;
                $compensationPenalty += $hours * 2;
            } elseif ($abs->status === 'I') {
                $totalIzinHours += $hours;
                $compensationPenalty += $hours * 1;
            } elseif ($abs->status === 'S') {
                $totalSakitHours += $hours;
                $compensationPenalty += $hours * 0;
            }
        }

        $spLevel = 0;
        $spTitle = '';
        if ($totalAlpaHours >= 10 && $totalAlpaHours < 30) {
            $spLevel = 1;
            $spTitle = 'Surat Peringatan 1';
        } elseif ($totalAlpaHours >= 30 && $totalAlpaHours < 50) {
            $spLevel = 2;
            $spTitle = 'Surat Peringatan 2';
        } elseif ($totalAlpaHours >= 50) {
            $spLevel = 3;
            $spTitle = 'Surat Peringatan 3';
        }

        if ($spLevel < 1) {
            return redirect()->back()->with('error', 'Cetak Surat Peringatan hanya diizinkan untuk mahasiswa yang memiliki akumulasi Alpa minimal 10 Jam.');
        }

        $spRoman = $spLevel == 1 ? 'I' : ($spLevel == 2 ? 'II' : 'III');
        $nomorSurat = '414/PL9.8/EP/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y');
        $mingguKe = 16;
        $tanggalAkhirHitung = \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y');
        $semesterTipe = 'Genap';
        $tahunAkademik = '2025-2026';
        $pejabatNama = 'Humaira, ST., MT';
        $pejabatNip = '19810319 200604 2 002';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.sp_letter_pdf', compact(
            'mahasiswa', 
            'totalAlpaHours', 
            'compensationPenalty',
            'spLevel', 
            'spTitle',
            'spRoman',
            'nomorSurat',
            'tanggalSurat',
            'mingguKe',
            'tanggalAkhirHitung',
            'semesterTipe',
            'tahunAkademik',
            'pejabatNama',
            'pejabatNip'
        ))->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'chroot' => public_path(),
            ]);

        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $mahasiswa->nama_lengkap);
        $filename = "Surat_Peringatan_{$cleanName}_{$mahasiswa->nim}.pdf";

        return $pdf->stream($filename);
    }

    public function kirimSpEmail(Mahasiswa $mahasiswa)
    {
        $user = $mahasiswa->user;
        $email = $mahasiswa->email ?? $user?->email;
        $isVerified = $user && $user->email_verified_at !== null;

        if (empty($email) || !$isVerified) {
            return redirect()->back()->with('error', "SP diterbitkan di sistem, tetapi GAGAL TERKIRIM via email karena Mahasiswa ({$mahasiswa->nama_lengkap}) belum melengkapi & memverifikasi alamat emailnya.");
        }

        $mahasiswa->load(['kelas', 'absensis' => function ($query) {
            $query->with('jadwal');
        }]);

        $totalAlpaHours = 0;
        $compensationPenalty = 0;

        foreach ($mahasiswa->absensis as $abs) {
            $hours = 1;
            if ($abs->jadwal) {
                $hours = ($abs->jadwal->jam_selesai - $abs->jadwal->jam_mulai) + 1;
            }

            if ($abs->status === 'A') {
                $totalAlpaHours += $hours;
                $compensationPenalty += $hours * 2;
            } elseif ($abs->status === 'I') {
                $compensationPenalty += $hours * 1;
            }
        }

        $spLevel = 0;
        $spTitle = '';
        if ($totalAlpaHours >= 10 && $totalAlpaHours < 30) {
            $spLevel = 1;
            $spTitle = 'Surat Peringatan 1';
        } elseif ($totalAlpaHours >= 30 && $totalAlpaHours < 50) {
            $spLevel = 2;
            $spTitle = 'Surat Peringatan 2';
        } elseif ($totalAlpaHours >= 50) {
            $spLevel = 3;
            $spTitle = 'Surat Peringatan 3';
        }

        if ($spLevel < 1) {
            return redirect()->back()->with('error', "Mahasiswa ({$mahasiswa->nama_lengkap}) belum memenuhi ambang batas minimal Alpa (10 Jam) untuk penerbitan SP.");
        }

        $spRoman = $spLevel == 1 ? 'I' : ($spLevel == 2 ? 'II' : 'III');
        $nomorSurat = '414/PL9.8/EP/' . date('Y');
        $tanggalSurat = \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y');
        $mingguKe = 16;
        $tanggalAkhirHitung = \Carbon\Carbon::now()->locale('id')->isoFormat('DD MMMM Y');
        $semesterTipe = 'Genap';
        $tahunAkademik = '2025-2026';
        $pejabatNama = 'Humaira, ST., MT';
        $pejabatNip = '19810319 200604 2 002';

        // Generate PDF attachment in memory
        $pdfContent = null;
        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.laporan.sp_letter_pdf', compact(
                'mahasiswa', 
                'totalAlpaHours', 
                'compensationPenalty',
                'spLevel', 
                'spTitle',
                'spRoman',
                'nomorSurat',
                'tanggalSurat',
                'mingguKe',
                'tanggalAkhirHitung',
                'semesterTipe',
                'tahunAkademik',
                'pejabatNama',
                'pejabatNip'
            ))->setPaper('a4', 'portrait')
                ->setOption([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'chroot' => public_path(),
                ]);
            $pdfContent = $pdf->output();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed generating SP PDF: " . $e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\SuratPeringatanMail($mahasiswa, $spLevel, $spTitle, $totalAlpaHours, $compensationPenalty, $pdfContent));

            AuditLog::create([
                'tipe_log'   => 'SP_SENT_EMAIL',
                'deskripsi'  => "Admin mengirimkan dokumen {$spTitle} ke email terverifikasi {$email} ({$mahasiswa->nama_lengkap})",
                'ip_address' => request()->ip(),
            ]);

            return redirect()->back()->with('success', "Surat Peringatan ({$spTitle}) berhasil dikirimkan via email terverifikasi ($email) beserta lampiran PDF! ✅");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed sending SP Email: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengirimkan Surat Peringatan via Email. Periksa koneksi jaringan.');
        }
    }

    // --- SETTINGS ---
    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'nama_institusi' => 'required|string|max:100',
            'email_notifikasi' => 'required|email',
        ]);
        
        return redirect()->route('admin.settings')->with('success', 'Pengaturan sistem EduAttend IoT berhasil diperbarui.');
    }

    // --- MANAJEMEN PERANGKAT IOT HARDWARE ---
    public function indexPerangkat()
    {
        return view('admin.perangkat');
    }

    // --- MANAJEMEN PERMOHONAN GANTI FOTO MAHASISWA ---
    public function indexPermohonanFoto()
    {
        $permohonans = \App\Models\PermohonanGantiFoto::with('mahasiswa.kelas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.permohonan_foto.index', compact('permohonans'));
    }

    public function approvePermohonanFoto($id)
    {
        $req = \App\Models\PermohonanGantiFoto::findOrFail($id);
        $req->update(['status' => 'approved']);

        $mhs = $req->mahasiswa;
        if ($mhs) {
            // Reset last_photo_updated_at so student can upload a new photo!
            $mhs->update(['last_photo_updated_at' => null]);
        }

        \App\Models\AuditLog::create([
            'tipe_log' => 'APPROVE_PHOTO_CHANGE',
            'deskripsi' => "Menyetujui permohonan pergantian foto profil mahasiswa {$mhs->nama_lengkap} (NIM: {$mhs->nim})",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', "Permohonan ganti foto {$mhs->nama_lengkap} disetujui. Mahasiswa sekarang dapat mengunggah foto baru.");
    }

    public function rejectPermohonanFoto(Request $request, $id)
    {
        $req = \App\Models\PermohonanGantiFoto::findOrFail($id);
        $req->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note', 'Permohonan ditolak oleh Admin.')
        ]);

        return redirect()->back()->with('warning', "Permohonan pergantian foto {$req->mahasiswa->nama_lengkap} telah ditolak.");
    }
}
