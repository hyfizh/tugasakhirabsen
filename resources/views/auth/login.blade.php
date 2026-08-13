<x-guest-layout>
    <div class="w-full max-w-[440px] mx-auto bg-white rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-10 shadow-xl sm:shadow-[0_25px_60px_rgba(59,40,204,0.10)] border border-slate-100">
        
        <!-- Header Brand Logo Badge -->
        <div class="flex flex-col items-center text-center space-y-3">
            <div class="w-14 h-14 bg-indigo-600 rounded-2xl text-white flex items-center justify-center shadow-lg shadow-indigo-200">
                <i class="fa-solid fa-microchip text-2xl"></i>
            </div>
            <div>
                <h2 class="font-extrabold text-xl text-slate-900 tracking-tight">ABSEN TI</h2>
                <p class="text-[11px] font-extrabold text-indigo-600 uppercase tracking-wider mt-0.5">Politeknik Negeri Padang</p>
            </div>
        </div>

        <!-- Headline & Subtitle -->
        <div class="text-center mt-6 mb-8 space-y-1.5">
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Selamat Datang</h1>
            <p class="text-xs text-slate-500 font-medium leading-relaxed"></p>
        </div>

        <!-- Session Status Alert -->
        <x-auth-session-status class="mb-5 text-xs text-emerald-600 font-bold text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Username / Email Field (Flex Input Group) -->
            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-extrabold text-slate-700">Username</label>
                <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50/60 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:bg-white focus-within:border-indigo-500 transition-all" style="height: 50px !important;">
                    <div class="w-12 h-full flex items-center justify-center text-slate-400 flex-shrink-0">
                        <i class="fa-regular fa-user text-sm"></i>
                    </div>
                    <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="w-full h-full bg-transparent border-0 focus:ring-0 text-xs font-semibold text-slate-800 placeholder-slate-400 outline-none" style="border: none !important; box-shadow: none !important; padding-left: 0 !important; padding-right: 1rem !important;">
                </div>
                <x-input-error :messages="$errors->get('username')" class="text-xs text-rose-500 font-semibold mt-1" />
            </div>

            <!-- Password Field (Flex Input Group with Eye Toggle) -->
            <div class="space-y-1.5" x-data="{ showPass: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-extrabold text-slate-700">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline">
                            Lupa Password?
                        </a>
                    @endif
                </div>
                <div class="flex items-center rounded-2xl border border-slate-200 bg-slate-50/60 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:bg-white focus-within:border-indigo-500 transition-all" style="height: 50px !important;">
                    <div class="w-12 h-full flex items-center justify-center text-slate-400 flex-shrink-0">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <input id="password" :type="showPass ? 'text' : 'password'" name="password" required
                           class="w-full h-full bg-transparent border-0 focus:ring-0 text-xs font-semibold text-slate-800 placeholder-slate-400 outline-none" style="border: none !important; box-shadow: none !important; padding-left: 0 !important; padding-right: 0.5rem !important;">
                    <button type="button" @click="showPass = !showPass" class="w-12 h-full flex items-center justify-center text-slate-400 hover:text-slate-600 focus:outline-none flex-shrink-0">
                        <i class="fa-regular text-sm" :class="showPass ? 'fa-eye' : 'fa-eye-slash'"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="text-xs text-rose-500 font-semibold mt-1" />
            </div>

            <!-- Checkbox Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded-md border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                    <span class="ml-2.5 text-xs font-semibold text-slate-600">Ingat Saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-extrabold text-xs rounded-2xl shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all duration-200 flex items-center justify-center space-x-2.5" style="height: 50px !important;">
                    <span>Masuk ke Portal System</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </div>
        </form>

        <!-- Footer Contact Link -->
        <div class="mt-8 text-center border-t border-slate-100 pt-6">
            <p class="text-xs text-slate-500 font-medium">
                Belum memiliki akun? <a href="#" onclick="alert('Silakan hubungi Administrator IT Jurusan TEKNOLOGI INFORMASI untuk pembuatan akun baru.'); return false;" class="font-extrabold text-indigo-600 hover:text-indigo-700 hover:underline">Hubungi Admin IT</a>
            </p>
        </div>

    </div>
</x-guest-layout>
