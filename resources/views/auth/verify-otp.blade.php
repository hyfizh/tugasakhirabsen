<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 text-center">Verifikasi OTP & Reset Password</h2>
        <p class="mt-2 text-sm text-gray-600 text-center">
            Kami telah mengirimkan kode verifikasi 6 digit ke email:<br>
            <strong class="text-indigo-600 font-semibold">{{ $email }}</strong>
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.otp.submit') }}">
        @csrf

        <!-- Email (Hidden) -->
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- OTP Code -->
        <div>
            <x-input-label for="otp_code" :value="__('Kode OTP (6 Digit)')" />
            <x-text-input id="otp_code" class="block mt-1 w-full text-center tracking-widest text-lg font-bold" 
                          type="text" 
                          name="otp_code" 
                          maxlength="6" 
                          pattern="[0-9]{6}"
                          placeholder="••••••" 
                          required 
                          autofocus />
            <x-input-error :messages="$errors->get('otp_code')" class="mt-2" />
        </div>

        <!-- Password Baru -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" 
                          type="password" 
                          name="password" 
                          required 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" 
                          type="password" 
                          name="password_confirmation" 
                          required 
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                {{ __('Kirim Ulang Kode?') }}
            </a>

            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
