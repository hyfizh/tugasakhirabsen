<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ganti Password Default - Student Portal</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts and Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .heading-font {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white border border-slate-200 shadow-xl rounded-2xl overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 p-6 text-white text-center space-y-2 relative overflow-hidden">
            <span class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto text-xl mb-1 shadow-md">
                <i class="fa-solid fa-key"></i>
            </span>
            <h2 class="heading-font text-2xl font-bold tracking-tight">Ganti Password Default</h2>
            <p class="text-xs text-indigo-100 px-4">Demi keamanan akun Anda, Anda wajib mengubah password default bawaan sistem sebelum masuk ke portal dashboard.</p>
        </div>

        <form action="{{ route('password.change.update') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Alerts -->
            @if (session('warning'))
                <div class="p-3.5 bg-amber-50 text-amber-800 border-l-4 border-amber-500 rounded-r text-xs font-semibold leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation mr-1.5 text-amber-500"></i> {{ session('warning') }}
                </div>
            @endif

            <!-- New Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-700">Password Baru</label>
                <input type="password" name="password" id="password" required placeholder="Masukkan password baru minimal 8 karakter"
                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-slate-800 @error('password') border-rose-500 focus:border-rose-500 focus:ring-rose-200 @enderror">
                @error('password')
                    <p class="text-rose-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="Ulangi password baru"
                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-slate-800">
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                <!-- Log out option to exit -->
                <button type="button" class="text-xs text-slate-400 hover:text-red-500 font-semibold" onclick="
                    event.preventDefault();
                    document.getElementById('logout-form').submit();
                ">
                    <i class="fa-solid fa-right-from-bracket mr-1"></i> Log Out
                </button>
                
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-md hover:shadow-lg transition-all flex items-center">
                    Simpan & Masuk <i class="fa-solid fa-chevron-right ml-2 text-xs"></i>
                </button>
            </div>
        </form>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</body>
</html>
