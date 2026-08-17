<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;

// Redirect home to login/dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
});

// Generic dashboard route for Breeze compatibility
Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($role === 'mahasiswa') {
        return redirect()->route('mahasiswa.dashboard');
    }
    abort(403);
})->middleware(['auth'])->name('dashboard');

// --- ADMIN PORTAL ROUTES ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Kelas CRUD
    Route::get('/kelas', [AdminController::class, 'indexKelas'])->name('kelas.index');
    Route::post('/kelas', [AdminController::class, 'storeKelas'])->name('kelas.store');
    Route::put('/kelas/{kelas}', [AdminController::class, 'updateKelas'])->name('kelas.update');
    Route::delete('/kelas/{kelas}', [AdminController::class, 'destroyKelas'])->name('kelas.destroy');

    // Dosen CRUD
    Route::get('/dosen', [AdminController::class, 'indexDosen'])->name('dosen.index');
    Route::post('/dosen', [AdminController::class, 'storeDosen'])->name('dosen.store');
    Route::put('/dosen/{dosen}', [AdminController::class, 'updateDosen'])->name('dosen.update');
    Route::delete('/dosen/{dosen}', [AdminController::class, 'destroyDosen'])->name('dosen.destroy');

    // Mahasiswa CRUD
    Route::get('/mahasiswa', [AdminController::class, 'indexMahasiswa'])->name('mahasiswa.index');
    Route::post('/mahasiswa', [AdminController::class, 'storeMahasiswa'])->name('mahasiswa.store');
    Route::put('/mahasiswa/{mahasiswa}', [AdminController::class, 'updateMahasiswa'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{mahasiswa}', [AdminController::class, 'destroyMahasiswa'])->name('mahasiswa.destroy');
    Route::post('/mahasiswa/{mahasiswa}/reset-password', [AdminController::class, 'resetPasswordMahasiswa'])->name('mahasiswa.reset-password');

    // Mata Kuliah CRUD
    Route::get('/matakuliah', [AdminController::class, 'indexMataKuliah'])->name('matakuliah.index');
    Route::post('/matakuliah', [AdminController::class, 'storeMataKuliah'])->name('matakuliah.store');
    Route::put('/matakuliah/{matakuliah}', [AdminController::class, 'updateMataKuliah'])->name('matakuliah.update');
    Route::delete('/matakuliah/{matakuliah}', [AdminController::class, 'destroyMataKuliah'])->name('matakuliah.destroy');

    // Jadwal CRUD
    Route::get('/jadwal', [AdminController::class, 'indexJadwal'])->name('jadwal.index');
    Route::post('/jadwal', [AdminController::class, 'storeJadwal'])->name('jadwal.store');
    Route::put('/jadwal/{jadwal}', [AdminController::class, 'updateJadwal'])->name('jadwal.update');
    Route::delete('/jadwal/{jadwal}', [AdminController::class, 'destroyJadwal'])->name('jadwal.destroy');

    // RFID Scanning
    Route::get('/rfid/scan', [AdminController::class, 'batchScanRfid'])->name('rfid.scan');
    Route::post('/rfid/assign', [AdminController::class, 'assignRfid'])->name('rfid.assign');
    Route::get('/rfid/clear', [AdminController::class, 'clearScannedRfid'])->name('rfid.clear');

    // Stasiun Registrasi Sensor IoT (Dashboard 2-Tab: RFID Binding & Face Enrollment)
    Route::get('/iot-device', [AdminController::class, 'indexIotDevice'])->name('iot-device.index');
    Route::post('/iot-device/assign', [AdminController::class, 'assignIotDevice'])->name('iot-device.assign');
    Route::post('/iot-device/assign-rfid', [AdminController::class, 'assignRfidDevice'])->name('iot-device.assign-rfid');
    Route::post('/iot-device/assign-face', [AdminController::class, 'assignFaceDevice'])->name('iot-device.assign-face');

    // Laporan Kompen
    Route::get('/laporan/kompen', [AdminController::class, 'laporanKompen'])->name('laporan.kompen');

    // Laporan Rekap Absen (Lihat, Download PDF & Ubah Status Absensi)
    Route::get('/laporan/rekap', [AdminController::class, 'rekapAbsen'])->name('laporan.rekap');
    Route::get('/laporan/rekap/download-pdf', [AdminController::class, 'downloadRekapPdf'])->name('laporan.rekap.download-pdf');
    Route::post('/absensi/update-status', [AdminController::class, 'updateAbsensiStatus'])->name('absensi.update-status');

    // Cetak, Download & Kirim Email Surat Peringatan (SP 1, 2, 3)
    Route::get('/laporan/cetak-sp/{mahasiswa}', [AdminController::class, 'cetakSp'])->name('laporan.cetak-sp');
    Route::get('/laporan/download-sp-pdf/{mahasiswa}', [AdminController::class, 'downloadSpPdf'])->name('laporan.download-sp-pdf');
    Route::post('/laporan/kirim-sp-email/{mahasiswa}', [AdminController::class, 'kirimSpEmail'])->name('laporan.kirim-sp-email');

    // Audit Logs
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');

    // Perangkat IoT CRUD & Ping Test
    Route::get('/perangkat', [AdminController::class, 'indexPerangkat'])->name('perangkat.index');
    Route::post('/perangkat', [AdminController::class, 'storePerangkat'])->name('perangkat.store');
    Route::put('/perangkat/{perangkat}', [AdminController::class, 'updatePerangkat'])->name('perangkat.update');
    Route::delete('/perangkat/{perangkat}', [AdminController::class, 'destroyPerangkat'])->name('perangkat.destroy');
    Route::get('/perangkat/{id}/ping', [AdminController::class, 'pingPerangkat'])->name('perangkat.ping');

    // Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Permohonan Ganti Foto Mahasiswa
    Route::get('/permohonan-foto', [AdminController::class, 'indexPermohonanFoto'])->name('permohonan-foto.index');
    Route::post('/permohonan-foto/{id}/approve', [AdminController::class, 'approvePermohonanFoto'])->name('permohonan-foto.approve');
    Route::post('/permohonan-foto/{id}/reject', [AdminController::class, 'rejectPermohonanFoto'])->name('permohonan-foto.reject');
});

// --- MAHASISWA PORTAL ROUTES ---
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->group(function () {
    // Forced change password routes (must be bypassed from ForceChangePassword middleware)
    Route::get('/change-password', [MahasiswaController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [MahasiswaController::class, 'updatePassword'])->name('password.change.update');

    // Protected student actions
    Route::middleware(['force_change_password'])->group(function () {
        Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('mahasiswa.dashboard');
        Route::get('/profile', [MahasiswaController::class, 'showProfileForm'])->name('mahasiswa.profile.form');
        Route::put('/profile', [MahasiswaController::class, 'updateProfile'])->name('mahasiswa.profile.update');
        Route::get('/riwayat', [MahasiswaController::class, 'riwayatAbsen'])->name('mahasiswa.riwayat');

        // Email Verification Routes
        Route::post('/email/send-otp', [EmailVerificationController::class, 'sendOtp'])->name('mahasiswa.email.send-otp');
        Route::post('/email/verify-otp', [EmailVerificationController::class, 'verifyOtp'])->name('mahasiswa.email.verify-otp');
    });
});

require __DIR__.'/auth.php';
