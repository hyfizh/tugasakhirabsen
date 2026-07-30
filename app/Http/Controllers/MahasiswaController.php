<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Absensi;
use App\Models\SuratPeringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MahasiswaController extends Controller
{
    private function getMahasiswa()
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        $mahasiswa = $user->mahasiswa;
        if (!$mahasiswa) {
            // Search by username (NIM) or email
            $mahasiswa = Mahasiswa::query()
                ->where('nim', $user->username)
                ->orWhere('email', $user->email)
                ->first();

            if ($mahasiswa) {
                $mahasiswa->update(['user_id' => $user->id]);
            } else {
                $defaultKelas = \App\Models\Kelas::first();
                $mahasiswa = Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $user->username ?? '2001001',
                    'nama_lengkap' => $user->username ?? 'Mahasiswa',
                    'email' => $user->email ?? 'mahasiswa@polinema.ac.id',
                    'kelas_id' => $defaultKelas->id ?? 1,
                ]);
            }
        }
        return $mahasiswa;
    }

    public function dashboard()
    {
        $mahasiswa = $this->getMahasiswa();
        $user = Auth::user();

        // Calculate attendance stats
        $absensis = Absensi::query()->where('mahasiswa_id', $mahasiswa->id)->get();
        $totalSessions = $absensis->count();
        $hadirCount = $absensis->where('status', 'H')->count();
        $sakitIzinCount = $absensis->whereIn('status', ['S', 'I'])->count();
        $alpaCount = $absensis->where('status', 'A')->count();

        $attendanceRate = $totalSessions > 0 ? round(($hadirCount / $totalSessions) * 100) : 100;

        // Get SP status
        $spActive = SuratPeringatan::query()->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'Aktif')
            ->orderBy('tingkat_sp', 'desc')
            ->first();

        // Check completeness
        $completenessScore = 0;
        if ($mahasiswa->nim) $completenessScore += 25;
        if ($mahasiswa->nama_lengkap) $completenessScore += 25;
        if ($mahasiswa->rfid_uid) $completenessScore += 25;
        if ($mahasiswa->foto_wajah) $completenessScore += 25;

        // Today's schedules
        $hariIni = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
        $todaySchedules = \App\Models\Jadwal::query()
            ->where('kelas_id', $mahasiswa->kelas_id)
            ->where('hari', $hariIni)
            ->with(['mataKuliah', 'dosen'])
            ->orderBy('jam_mulai')
            ->get();

        return view('mahasiswa.dashboard', compact(
            'mahasiswa',
            'user',
            'attendanceRate',
            'hadirCount',
            'sakitIzinCount',
            'alpaCount',
            'spActive',
            'completenessScore',
            'todaySchedules',
            'hariIni'
        ));
    }

    public function showProfileForm()
    {
        $mahasiswa = $this->getMahasiswa();
        return view('mahasiswa.profile', compact('mahasiswa'));
    }

    public function updateProfile(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        $request->validate([
            'email' => 'required|email|unique:mahasiswas,email,' . $mahasiswa->id,
            'no_hp' => 'nullable|string|max:20',
        ]);

        $mahasiswa->update($request->only('email', 'no_hp'));

        if ($mahasiswa->user) {
            $mahasiswa->user->update(['email' => $request->email]);
        }

        return redirect()->route('mahasiswa.profile.form')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    private function canChangePhoto($mahasiswa)
    {
        if (!$mahasiswa->last_photo_updated_at) {
            return true;
        }
        return $mahasiswa->last_photo_updated_at->diffInDays(now()) >= 30;
    }

    public function showFaceUpload()
    {
        return redirect()->route('mahasiswa.profile.form');
    }

    public function updateFace(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        // 1-Month Limit Check for existing photo
        if ($mahasiswa->foto_wajah && !$this->canChangePhoto($mahasiswa)) {
            $daysLeft = 30 - $mahasiswa->last_photo_updated_at->diffInDays(now());
            return redirect()->route('mahasiswa.profile.form')
                ->with('photo_locked', true)
                ->with('days_left', max(1, $daysLeft))
                ->with('last_updated', $mahasiswa->last_photo_updated_at->format('d M Y'))
                ->with('error', "Foto profil hanya dapat diubah 1x dalam 30 hari demi alasan keamanan biometrik absensi. Terakhir diubah pada {$mahasiswa->last_photo_updated_at->format('d M Y')}. Silakan kirimkan permohonan ke Admin jika butuh mengganti foto segera.");
        }

        // Block upload if photo already exists without deleting or approval
        if ($mahasiswa->foto_wajah && Storage::disk('public')->exists($mahasiswa->foto_wajah)) {
            return redirect()->route('mahasiswa.profile.form')
                ->with('error', 'Anda sudah memiliki foto wajah. Hapus foto lama terlebih dahulu sebelum mengunggah foto baru.');
        }

        $request->validate([
            'foto_wajah' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
        ]);

        if ($request->file('foto_wajah')) {
            $file = $request->file('foto_wajah');
            $filename = $mahasiswa->nim . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');
            
            $mahasiswa->update([
                'foto_wajah' => $path,
                'last_photo_updated_at' => now(),
            ]);
        }

        return redirect()->route('mahasiswa.profile.form')->with('success', 'Foto wajah berhasil diunggah.');
    }

    public function deleteFace()
    {
        $mahasiswa = $this->getMahasiswa();

        // 1-Month Limit Check for photo deletion
        if ($mahasiswa->foto_wajah && !$this->canChangePhoto($mahasiswa)) {
            $daysLeft = 30 - $mahasiswa->last_photo_updated_at->diffInDays(now());
            return redirect()->route('mahasiswa.profile.form')
                ->with('photo_locked', true)
                ->with('days_left', max(1, $daysLeft))
                ->with('last_updated', $mahasiswa->last_photo_updated_at->format('d M Y'))
                ->with('error', "Foto profil hanya dapat dihapus/diubah 1x dalam 30 hari. Terakhir diubah pada {$mahasiswa->last_photo_updated_at->format('d M Y')}. Silakan kirim permohonan ke Admin.");
        }

        if ($mahasiswa->foto_wajah && Storage::disk('public')->exists($mahasiswa->foto_wajah)) {
            Storage::disk('public')->delete($mahasiswa->foto_wajah);
        }

        $mahasiswa->update(['foto_wajah' => null]);

        return redirect()->route('mahasiswa.profile.form')->with('success', 'Foto wajah berhasil dihapus. Silakan unggah foto baru.');
    }

    public function requestPhotoChange(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        $existing = \App\Models\PermohonanGantiFoto::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->route('mahasiswa.profile.form')
                ->with('warning', 'Anda sudah memiliki permohonan penggantian foto yang sedang menunggu konfirmasi Admin.');
        }

        \App\Models\PermohonanGantiFoto::create([
            'mahasiswa_id' => $mahasiswa->id,
            'alasan' => $request->input('alasan', 'Mengajukan permohonan izin pergantian foto biometrik wajah sebelum 30 hari.'),
            'status' => 'pending',
        ]);

        return redirect()->route('mahasiswa.profile.form')
            ->with('success', 'Permohonan penggantian foto berhasil dikirim ke Admin IT. Notifikasi telah diteruskan ke Admin.');
    }

    public function verifyFacePython(Request $request)
    {
        $mahasiswa = $this->getMahasiswa();

        if (!$mahasiswa->foto_wajah || !Storage::disk('public')->exists($mahasiswa->foto_wajah)) {
            return response()->json([
                'status' => 'error',
                'matched' => false,
                'message' => 'Belum ada foto dataset terdaftar di database.'
            ], 400);
        }

        $rawImages = $request->input('live_images');
        if (!$rawImages && $request->input('live_image')) {
            $rawImages = [$request->input('live_image')];
        }

        if (empty($rawImages) || !is_array($rawImages)) {
            return response()->json([
                'status' => 'error',
                'matched' => false,
                'message' => 'Frame gambar tidak ditemukan.'
            ], 400);
        }

        $tempDir = storage_path('app');
        $tempPaths = [];

        foreach ($rawImages as $idx => $base64Image) {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                $data = substr($base64Image, strpos($base64Image, ',') + 1);
                $data = base64_decode($data);
            } else {
                $data = base64_decode($base64Image);
            }

            $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'temp_burst_' . $mahasiswa->nim . '_' . time() . '_' . $idx . '.jpg';
            file_put_contents($tempPath, $data);
            $tempPaths[] = $tempPath;
        }

        $datasetPath = storage_path('app/public/' . $mahasiswa->foto_wajah);
        $scriptPath = base_path('python/face_verify.py');

        // Build command with dataset + all temp frame paths
        $escapedPaths = array_map('escapeshellarg', $tempPaths);
        $command = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($datasetPath) . " " . implode(" ", $escapedPaths) . " 2>&1";
        $output = shell_exec($command);

        // Clean up temporary live snapshot files
        foreach ($tempPaths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        if (!$output) {
            return response()->json([
                'status' => 'error',
                'matched' => false,
                'message' => 'Gagal mengeksekusi script Python AI face verification.'
            ], 500);
        }

        $result = json_decode($output, true);
        return response()->json($result ?: [
            'status' => 'error',
            'matched' => false,
            'message' => 'Output invalid dari Python script.'
        ]);
    }

    public function riwayatAbsen()
    {
        $mahasiswa = $this->getMahasiswa();
        $absensis = Absensi::query()->with('jadwal.mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('mahasiswa.riwayat-absen', compact('absensis'));
    }

    // --- FORCE CHANGE PASSWORD ---
    public function showChangePasswordForm()
    {
        return view('auth.force-change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'is_password_changed' => true,
        ]);

        return redirect()->route('mahasiswa.dashboard')->with('success', 'Password Anda berhasil diubah! Selamat datang di dashboard Anda.');
    }
}
