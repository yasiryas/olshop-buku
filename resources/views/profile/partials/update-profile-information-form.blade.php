<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: { type: 'success', message: 'Tautan verifikasi baru telah dikirim ke email Anda.' }
                                }));
                            });
                        </script>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="phone_number" value="Nomor Telepon / WhatsApp" />
                <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" placeholder="08xxxxxxxxxx" />
                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
            </div>

            <div>
                <x-input-label for="post_code" value="Kode Pos" />
                <x-text-input id="post_code" name="post_code" type="text" class="mt-1 block w-full" :value="old('post_code', $user->post_code)" placeholder="Contoh: 40135" />
                <x-input-error class="mt-2" :messages="$errors->get('post_code')" />
            </div>
        </div>

        <div>
            <x-input-label for="address" value="Alamat Lengkap" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="city" value="Kota / Kabupaten" />
            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $user->city)" placeholder="Contoh: Bandung" />
            <p class="mt-1 text-xs text-gray-500">Data ini otomatis mengisi form checkout agar tidak perlu diisi ulang.</p>
            <x-input-error class="mt-2" :messages="$errors->get('city')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { type: 'success', message: 'Profil berhasil diperbarui.' }
                        }));
                    });
                </script>
            @endif
        </div>
    </form>
</section>
