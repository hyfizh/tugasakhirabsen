<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

<title>Absen TI - Admin Portal</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts and Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            background-color: #f6f7fd;
        }
        .heading-font {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-dashboard {
            background-color: #f6f7fd;
        }
        .sidebar-bg {
            background-color: #f9fafe;
        }
        .active-nav-pill {
            background-color: #e8ebfc;
            color: #3b28cc;
            font-weight: 700;
        }
        /* Hide all visible scrollbars for a clean, modern aesthetic */
        ::-webkit-scrollbar, .no-scrollbar::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        * {
            -ms-overflow-style: none !important;  /* IE and Edge */
            scrollbar-width: none !important;  /* Firefox */
        }
    </style>
</head>
<body class="bg-dashboard text-slate-800 antialiased font-sans h-screen overflow-hidden" x-data="{ isMobile: window.innerWidth < 768 }" @resize.window="isMobile = window.innerWidth < 768">
    <div class="h-screen w-screen flex overflow-hidden bg-dashboard" x-data="{ sidebarOpen: window.innerWidth >= 768, laporanOpen: {{ request()->routeIs('admin.laporan.*') ? 'true' : 'false' }} }">
        
        <!-- Mobile Sidebar Dark Backdrop Overlay -->
        <div x-show="sidebarOpen && isMobile" 
             @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-30 md:hidden" style="display: none;"></div>

        <!-- Left Sidebar Navigation (Responsive Drawer on Mobile, Fixed Collapsible on Desktop) -->
        <aside :class="{
                   'w-64 translate-x-0': sidebarOpen,
                   '-translate-x-full md:translate-x-0 md:w-20': !sidebarOpen
               }" 
               class="fixed md:static inset-y-0 left-0 z-40 sidebar-bg text-slate-700 flex-shrink-0 transition-transform md:transition-all duration-300 ease-in-out flex flex-col border-r border-slate-200/80 shadow-2xl md:shadow-sm h-full" style="height: 100vh;">
            
            <!-- Brand Logo Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden" x-show="sidebarOpen">
                    <span class="w-10 h-10 bg-indigo-600 rounded-xl text-white flex items-center justify-center shadow-md shadow-indigo-200">
                        <i class="fa-solid fa-microchip text-lg"></i>
                    </span>
                    <div>
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-tight">Absen TI</span>
                        <span class="text-[11px] font-semibold text-slate-400 block tracking-wide">University Admin</span>
                    </div>
                </div>
                <div x-show="!sidebarOpen" class="w-full flex justify-center">
                    <span class="w-10 h-10 bg-indigo-600 rounded-xl text-white flex items-center justify-center shadow-md shadow-indigo-200">
                        <i class="fa-solid fa-microchip text-lg"></i>
                    </span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-slate-700 focus:outline-none hidden md:block">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-chevron-left text-xs' : 'fa-bars text-sm'"></i>
                </button>
            </div>

            <!-- Sidebar Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto no-scrollbar">
                
                <!-- 1. Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.dashboard') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-border-all w-5 text-center text-base {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Dashboard</span>
                </a>

                <!-- 2. Mahasiswa -->
                <a href="{{ route('admin.mahasiswa.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.mahasiswa.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-user w-5 text-center text-base {{ request()->routeIs('admin.mahasiswa.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Mahasiswa</span>
                </a>

                <!-- 2a. Stasiun Sensor IoT (WebRTC & RFID 2-Tab Dashboard) -->
                <a href="{{ route('admin.iot-device.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.iot-device.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-microchip w-5 text-center text-base {{ request()->routeIs('admin.iot-device.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Stasiun Sensor IoT</span>
                </a>

                <!-- 2b. Permohonan Foto -->
                @php
                    $pendingPhotoBadge = \App\Models\PermohonanGantiFoto::where('status', 'pending')->count();
                @endphp
                <a href="{{ route('admin.permohonan-foto.index') }}" 
                   class="flex items-center justify-between px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.permohonan-foto.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <div class="flex items-center">
                        <i class="fa-solid fa-camera-retro w-5 text-center text-base {{ request()->routeIs('admin.permohonan-foto.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                        <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Permohonan Foto</span>
                    </div>
                    @if ($pendingPhotoBadge > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white animate-pulse" x-show="sidebarOpen">
                            {{ $pendingPhotoBadge }}
                        </span>
                    @endif
                </a>

                <!-- 3. Dosen -->
                <a href="{{ route('admin.dosen.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.dosen.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-graduation-cap w-5 text-center text-base {{ request()->routeIs('admin.dosen.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Dosen</span>
                </a>

                <!-- 4. Kelas -->
                <a href="{{ route('admin.kelas.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.kelas.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-door-closed w-5 text-center text-base {{ request()->routeIs('admin.kelas.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Kelas</span>
                </a>

                <!-- 5. Mata Kuliah -->
                <a href="{{ route('admin.matakuliah.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.matakuliah.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-book-open w-5 text-center text-base {{ request()->routeIs('admin.matakuliah.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Mata Kuliah</span>
                </a>

                <!-- 6. Jadwal Kuliah -->
                <a href="{{ route('admin.jadwal.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.jadwal.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-calendar-days w-5 text-center text-base {{ request()->routeIs('admin.jadwal.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Jadwal Kuliah</span>
                </a>

                <!-- 7. Laporan Submenu Dropdown -->
                <div class="space-y-1">
                    <button @click="laporanOpen = !laporanOpen" 
                            class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.laporan.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                        <div class="flex items-center">
                            <i class="fa-solid fa-file-lines w-5 text-center text-base {{ request()->routeIs('admin.laporan.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                            <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Laporan Presensi</span>
                        </div>
                        <i class="fa-solid text-[10px] transition-transform duration-200" :class="laporanOpen ? 'fa-chevron-down' : 'fa-chevron-right'" x-show="sidebarOpen"></i>
                    </button>

                    <!-- Submenu items -->
                    <div x-show="laporanOpen && sidebarOpen" x-collapse class="pl-9 space-y-1 pt-1">
                        <a href="{{ route('admin.laporan.rekap') }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ request()->routeIs('admin.laporan.rekap') ? 'text-indigo-600 font-bold bg-indigo-50/60' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-circle-notch text-[8px] mr-2 {{ request()->routeIs('admin.laporan.rekap') ? 'text-indigo-600' : 'text-slate-300' }}"></i>
                            Rekap Kehadiran
                        </a>
                        <a href="{{ route('admin.laporan.kompen') }}" 
                           class="flex items-center px-3 py-2 rounded-lg text-xs font-semibold transition-all {{ request()->routeIs('admin.laporan.kompen') ? 'text-indigo-600 font-bold bg-indigo-50/60' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="fa-solid fa-circle-notch text-[8px] mr-2 {{ request()->routeIs('admin.laporan.kompen') ? 'text-indigo-600' : 'text-slate-300' }}"></i>
                            Kompensasi & SP
                        </a>
                    </div>
                </div>

                <!-- 9. Perangkat Hardware IoT -->
                <a href="{{ route('admin.perangkat.index') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.perangkat.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-microchip w-5 text-center text-base {{ request()->routeIs('admin.perangkat.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Perangkat Hardware</span>
                </a>

                <!-- 10. Audit Log -->
                <a href="{{ route('admin.audit-logs') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.audit-logs') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center text-base {{ request()->routeIs('admin.audit-logs') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Audit Log</span>
                </a>
            </nav>

            <!-- Bottom Settings & Logout -->
            <div class="px-4 py-4 border-t border-slate-200/60 space-y-1 flex-shrink-0">
                <a href="{{ route('admin.settings') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('admin.settings') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-gear w-5 text-center text-base {{ request()->routeIs('admin.settings') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Settings</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2.5 rounded-xl text-rose-600 hover:bg-rose-50 font-semibold transition-all">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center text-base text-rose-500"></i>
                        <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden" style="flex: 1 1 0%; display: flex; flex-direction: column; min-width: 0; overflow: hidden; height: 100%;">
            
            <!-- Topbar Header -->
            <header class="h-20 bg-dashboard flex items-center justify-between px-4 sm:px-8 z-10 flex-shrink-0">
                <!-- Title -->
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-600 focus:outline-none md:hidden p-1">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-xl sm:text-3xl font-extrabold text-indigo-900 tracking-tight truncate">Absen TI Dashboard</h1>
                </div>

                <!-- Right Tools (Search, Notifications, Profile) -->
                <div class="flex items-center space-x-6">
                    <!-- Search Input Box -->
                    <div class="relative hidden sm:block">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input type="text" placeholder="Search data..." 
                               class="w-64 pl-10 pr-4 py-2 rounded-full border-0 bg-slate-200/50 text-xs font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all outline-none">
                    </div>

                    <!-- Notification Bell with Pending Photo Requests Dropdown -->
                    @php
                        $pendingPhotoRequests = \App\Models\PermohonanGantiFoto::with('mahasiswa.kelas')
                            ->where('status', 'pending')
                            ->orderBy('created_at', 'desc')
                            ->get();
                        $pendingCount = $pendingPhotoRequests->count();
                    @endphp

                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" class="relative p-2 text-slate-500 hover:text-slate-800 transition-colors focus:outline-none">
                            <i class="fa-regular fa-bell text-lg"></i>
                            @if ($pendingCount > 0)
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white ring-2 ring-white animate-pulse">
                                    {{ $pendingCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div x-show="notifOpen" @click.away="notifOpen = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 z-50">
                            
                            <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="font-extrabold text-xs text-slate-900">Notifikasi System</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-700">
                                    {{ $pendingCount }} Permohonan Foto
                                </span>
                            </div>

                            <div class="max-h-72 overflow-y-auto no-scrollbar divide-y divide-slate-50">
                                @forelse ($pendingPhotoRequests as $req)
                                    <div class="p-3.5 hover:bg-slate-50 transition-colors flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 font-bold text-xs">
                                            <i class="fa-solid fa-camera"></i>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <p class="text-xs font-bold text-slate-800 leading-tight">
                                                {{ $req->mahasiswa->nama_lengkap ?? 'Mahasiswa' }} (NIM: {{ $req->mahasiswa->nim ?? '-' }})
                                            </p>
                                            <p class="text-[11px] text-slate-500">Ingin mengganti foto profil / dataset biometrik wajah.</p>
                                            <div class="pt-1.5 flex items-center space-x-2">
                                                <form method="POST" action="{{ route('admin.permohonan-foto.approve', $req->id) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold shadow-xs transition-all">
                                                        <i class="fa-solid fa-check mr-1"></i> Setujui
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.permohonan-foto.reject', $req->id) }}">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-[10px] font-bold transition-all">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-400">
                                        <i class="fa-regular fa-bell-slash text-xl block mb-1.5 opacity-50"></i>
                                        <span class="text-xs font-medium">Tidak ada permohonan foto baru.</span>
                                    </div>
                                @endforelse
                            </div>

                            <div class="px-4 pt-2 border-t border-slate-100 text-center">
                                <a href="{{ route('admin.permohonan-foto.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700">
                                    Lihat Kelola Permohonan Foto →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Help Question Mark -->
                    <button class="p-2 text-slate-500 hover:text-slate-800 transition-colors focus:outline-none">
                        <i class="fa-regular fa-circle-question text-lg"></i>
                    </button>

                    <!-- User Avatar & Profile Tag -->
                    <div class="flex items-center space-x-3 pl-2 border-l border-slate-200/80">
                        <div class="w-10 h-10 rounded-full border-2 border-indigo-500/30 overflow-hidden shadow-sm flex-shrink-0 bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">
                            AD
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="font-extrabold text-xs text-slate-900 block leading-tight">Admin System</span>
                            <span class="text-[10px] font-bold text-indigo-600 block">Super Administrator</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Dashboard Content Area (Fixed Topbar & Sidebar, Scrollable Content) -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto px-4 sm:px-8 py-4 sm:py-6 space-y-6" style="height: calc(100vh - 5rem); max-height: calc(100vh - 5rem); overflow-y: auto;">
                <!-- SweetAlert2 Toast Notifications -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true,
                        });

                        @if (session('success'))
                            Toast.fire({ icon: 'success', title: {!! json_encode(session('success')) !!} });
                        @endif
                        @if (session('error'))
                            Toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
                        @endif
                    });
                </script>

                <!-- Yield Content -->
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Top Progress Bar Loader -->
    <div id="page-progress-bar" class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-sky-400 to-indigo-600 z-50 transition-all duration-300 w-0 opacity-0"></div>

    <!-- Aesthetic Loading Modal Overlay -->
    <div id="menu-loading-overlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 backdrop-blur-[3px] opacity-0 pointer-events-none transition-all duration-200">
        <div class="bg-white/95 rounded-2xl p-6 shadow-2xl border border-indigo-100/80 flex flex-col items-center space-y-4 max-w-xs w-full text-center transform scale-95 transition-all duration-200" id="menu-loading-box">
            <!-- Pulsing Logo Ring -->
            <div class="relative w-16 h-16 flex items-center justify-center">
                <div class="absolute inset-0 rounded-2xl bg-indigo-600/10 animate-ping"></div>
                <div class="w-14 h-14 bg-indigo-600 rounded-2xl text-white flex items-center justify-center shadow-lg shadow-indigo-300 z-10">
                    <i class="fa-solid fa-microchip text-2xl animate-pulse"></i>
                </div>
                <!-- Circular Spinner Ring -->
                <div class="absolute -inset-1.5 rounded-2xl border-2 border-indigo-600 border-t-transparent animate-spin"></div>
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-slate-900 tracking-tight">Memuat Halaman...</h4>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">EduAttend IoT Portal</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const progressBar = document.getElementById('page-progress-bar');
            const loadingOverlay = document.getElementById('menu-loading-overlay');
            const loadingBox = document.getElementById('menu-loading-box');

            function showLoading() {
                if (progressBar) {
                    progressBar.style.width = '70%';
                    progressBar.style.opacity = '1';
                }
                if (loadingOverlay && loadingBox) {
                    loadingOverlay.classList.remove('pointer-events-none', 'opacity-0');
                    loadingOverlay.classList.add('opacity-100');
                    loadingBox.classList.remove('scale-95');
                    loadingBox.classList.add('scale-100');
                }
            }

            function hideLoading() {
                if (progressBar) {
                    progressBar.style.width = '100%';
                    setTimeout(() => {
                        progressBar.style.opacity = '0';
                        progressBar.style.width = '0';
                    }, 300);
                }
                if (loadingOverlay && loadingBox) {
                    loadingOverlay.classList.remove('opacity-100');
                    loadingOverlay.classList.add('opacity-0', 'pointer-events-none');
                    loadingBox.classList.remove('scale-100');
                    loadingBox.classList.add('scale-95');
                }
            }

            // Attach listener to all navigation links
            document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
                link.addEventListener('click', function (e) {
                    const targetUrl = link.getAttribute('href');
                    if (targetUrl && targetUrl !== '#' && !targetUrl.startsWith('javascript:')) {
                        showLoading();
                    }
                });
            });

            // Also show loading on form submit
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function () {
                    showLoading();
                });
            });

            // Hide loading when page is fully loaded or restored from cache
            window.addEventListener('pageshow', function () {
                hideLoading();
            });
        });
    </script>
</body>
</html>
