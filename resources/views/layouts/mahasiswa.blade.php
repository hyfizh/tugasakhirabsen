<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Absen TI - Portal Mahasiswa</title>

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
<body class="bg-dashboard text-slate-800 antialiased font-sans min-h-screen md:h-screen md:overflow-hidden overscroll-y-auto" x-data="{ isMobile: window.innerWidth < 768 }" @resize.window="isMobile = window.innerWidth < 768">
    
    <!-- Mobile Pull to Refresh Indicator -->
    <div id="mobile-ptr-indicator" class="fixed top-2 left-1/2 -translate-x-1/2 z-50 hidden bg-indigo-600 text-white text-xs font-bold px-4 py-2 rounded-full shadow-lg transition-transform flex items-center space-x-2">
        <i class="fa-solid fa-rotate animate-spin"></i>
        <span>Memperbarui Halaman...</span>
    </div>

    <div class="min-h-screen md:h-screen w-full flex overflow-x-hidden md:overflow-hidden bg-dashboard" x-data="{ sidebarOpen: window.innerWidth >= 768 }">
        
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

        <!-- Left Sidebar Navigation (Responsive Mobile Drawer & Desktop Fixed Collapsible) -->
        <aside :class="{
                   'w-64 translate-x-0': sidebarOpen,
                   '-translate-x-full md:translate-x-0 md:w-20': !sidebarOpen
               }" 
               class="fixed md:static inset-y-0 left-0 z-40 sidebar-bg text-slate-700 flex-shrink-0 transition-transform md:transition-all duration-300 ease-in-out flex flex-col border-r border-slate-200/80 shadow-2xl md:shadow-sm h-full" style="height: 100vh;">
            
            <!-- Brand Logo Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center space-x-3 overflow-hidden" x-show="sidebarOpen">
                    <span class="w-10 h-10 bg-indigo-600 rounded-xl text-white flex items-center justify-center shadow-md shadow-indigo-200">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </span>
                    <div>
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight block leading-tight">Absen TI</span>
                        <span class="text-[11px] font-semibold text-slate-400 block tracking-wide">Student Portal</span>
                    </div>
                </div>
                <div x-show="!sidebarOpen" class="w-full flex justify-center">
                    <span class="w-10 h-10 bg-indigo-600 rounded-xl text-white flex items-center justify-center shadow-md shadow-indigo-200">
                        <i class="fa-solid fa-graduation-cap text-lg"></i>
                    </span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-slate-700 focus:outline-none hidden md:block">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-chevron-left text-xs' : 'fa-bars text-sm'"></i>
                </button>
            </div>

            <!-- Sidebar Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto no-scrollbar">
                
                <!-- 1. Dashboard -->
                <a href="{{ route('mahasiswa.dashboard') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('mahasiswa.dashboard') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-border-all w-5 text-center text-base {{ request()->routeIs('mahasiswa.dashboard') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Dashboard</span>
                </a>

                <!-- 2. Profil & Dataset Wajah -->
                <a href="{{ route('mahasiswa.profile.form') }}" 
                   class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('mahasiswa.profile.*') || request()->routeIs('mahasiswa.face.*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                    <i class="fa-solid fa-user-gear w-5 text-center text-base {{ request()->routeIs('mahasiswa.profile.*') || request()->routeIs('mahasiswa.face.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                    <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Profil & Dataset</span>
                </a>

                <!-- 3. Riwayat Absensi (If exists in web.php) -->
                @if (Route::has('mahasiswa.riwayat'))
                    <a href="{{ route('mahasiswa.riwayat') }}" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('mahasiswa.riwayat') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                        <i class="fa-solid fa-clipboard-user w-5 text-center text-base {{ request()->routeIs('mahasiswa.riwayat') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                        <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Riwayat Absensi</span>
                    </a>
                @endif

                <!-- 4. Ubah Password -->
                @if (Route::has('password.change'))
                    <a href="{{ route('password.change') }}" 
                       class="flex items-center px-4 py-2.5 rounded-xl transition-all group {{ request()->routeIs('password.change*') ? 'active-nav-pill shadow-sm' : 'text-slate-600 hover:bg-slate-200/50 hover:text-slate-900 font-medium' }}">
                        <i class="fa-solid fa-key w-5 text-center text-base {{ request()->routeIs('password.change*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"></i>
                        <span class="ml-3.5 text-xs font-semibold" x-show="sidebarOpen">Ubah Password</span>
                    </a>
                @endif

            </nav>

            <!-- Sidebar Footer / Logout Button -->
            <div class="p-4 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="w-full flex items-center px-4 py-2.5 rounded-xl text-rose-600 hover:bg-rose-50 font-bold transition-all text-xs">
                        <i class="fa-solid fa-right-from-bracket w-5 text-center text-sm"></i>
                        <span class="ml-3.5" x-show="sidebarOpen">Keluar System</span>
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
                    <h1 class="text-xl sm:text-3xl font-extrabold text-indigo-900 tracking-tight truncate">Absen TI Mahasiswa</h1>
                </div>

                <!-- Right Tools (Clock, Search, Profile) -->
                <div class="flex items-center space-x-3 sm:space-x-6">
                    <!-- Live Clock Widget -->
                    <div class="hidden sm:flex items-center space-x-2 text-xs font-bold text-slate-500 bg-slate-200/50 px-3.5 py-1.5 rounded-full">
                        <i class="fa-regular fa-clock text-indigo-600"></i>
                        <span id="current-time">Loading Clock...</span>
                    </div>

                    <!-- Quick Refresh Button for Mobile & Desktop -->
                    <button onclick="window.location.reload()" title="Refresh Halaman" class="p-2 text-indigo-600 hover:text-indigo-800 transition-colors focus:outline-none bg-indigo-50 hover:bg-indigo-100 rounded-full active:scale-95 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-rotate text-sm"></i>
                    </button>

                    <!-- Notification Bell -->
                    <button class="relative p-2 text-slate-500 hover:text-slate-800 transition-colors focus:outline-none">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-600 rounded-full ring-2 ring-white"></span>
                    </button>

                    <!-- User Avatar & Profile Tag -->
                    <div class="flex items-center space-x-3 pl-2 border-l border-slate-200/80">
                        <div class="w-10 h-10 rounded-full border-2 border-indigo-500/30 overflow-hidden shadow-sm flex-shrink-0 bg-indigo-600 text-white font-extrabold text-xs flex items-center justify-center">
                            {{ strtoupper(substr(Auth::user()->username ?? 'MH', 0, 2)) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <span class="font-extrabold text-xs text-slate-900 block leading-tight">{{ Auth::user()->username ?? 'Mahasiswa' }}</span>
                            <span class="text-[10px] font-bold text-indigo-600 block">Mahasiswa Aktif</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Dashboard Content Area -->
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
                        @if (session('warning'))
                            Toast.fire({ icon: 'warning', title: {!! json_encode(session('warning')) !!} });
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
                    <i class="fa-solid fa-graduation-cap text-2xl animate-pulse"></i>
                </div>
                <!-- Circular Spinner Ring -->
                <div class="absolute -inset-1.5 rounded-2xl border-2 border-indigo-600 border-t-transparent animate-spin"></div>
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-slate-900 tracking-tight">Memuat Halaman...</h4>
                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">EduAttend Student Portal</p>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const clockEl = document.getElementById('current-time');
            if (clockEl) {
                const now = new Date();
                const options = { 
                    weekday: 'short', 
                    day: 'numeric',
                    month: 'short', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false 
                };
                clockEl.textContent = now.toLocaleDateString('id-ID', options);
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

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

            document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])').forEach(link => {
                link.addEventListener('click', function (e) {
                    const targetUrl = link.getAttribute('href');
                    if (targetUrl && targetUrl !== '#' && !targetUrl.startsWith('javascript:')) {
                        showLoading();
                    }
                });
            });

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function () {
                    showLoading();
                });
            });

            window.addEventListener('pageshow', function () {
                hideLoading();
            });

            // Mobile Pull-to-Refresh Gesture Handler
            (function() {
                let startY = 0;
                let isPulling = false;
                const threshold = 60;
                const mainEl = document.querySelector('main');
                const ptrIndicator = document.getElementById('mobile-ptr-indicator');

                window.addEventListener('touchstart', function(e) {
                    const mainTop = mainEl ? mainEl.scrollTop : 0;
                    if (window.scrollY === 0 || mainTop === 0) {
                        startY = e.touches[0].pageY;
                        isPulling = true;
                    }
                }, { passive: true });

                window.addEventListener('touchmove', function(e) {
                    if (!isPulling) return;
                    const currentY = e.touches[0].pageY;
                    const diffY = currentY - startY;
                    const mainTop = mainEl ? mainEl.scrollTop : 0;

                    if (diffY > threshold && (window.scrollY === 0 || mainTop === 0)) {
                        if (ptrIndicator) {
                            ptrIndicator.classList.remove('hidden');
                        }
                    }
                }, { passive: true });

                window.addEventListener('touchend', function(e) {
                    if (isPulling && ptrIndicator && !ptrIndicator.classList.contains('hidden')) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 250);
                    }
                    isPulling = false;
                });
            })();
        });
    </script>
</body>
</html>
