<div class="card mt-8 mx-auto" style="max-width: 360px;">
    <header>
        <div class="mx-auto" wire:ignore>
            <img src="/img/logo-dark-text.png" class="block dark:hidden" style="height: 72px;" alt="Logo-dark" />
            <img src="/img/logo-light-text.png" class="hidden dark:block" style="height: 72px;" alt="Logo-light" />
        </div>

        <div class="flex items-center">
            <hr class="grow border-t border-gray-300">
            <span class="px-3 text-gray-500 text-sm">Masuk</span>
            <hr class="grow border-t border-gray-300">
        </div>
    </header>
    <section>
        <form class="form grid gap-6" wire:submit.prevent="submit">
            <!-- Elemen tersembunyi untuk binding Livewire -->
            <input type="hidden" wire:model="systemId" id="systemIdInput">
            <input type="hidden" wire:model="info" id="infoInput">

            <div class="grid gap-2">
                <label for="inputUsername">Username</label>
                <input type="text" id="inputUsername" wire:model="username"
                    @error('username') aria-invalid="true" @enderror>

                @error('username')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-2">
                <label for="inputPassword">Password</label>
                <input type="password" id="inputPassword" wire:model="password"
                    @error('password') aria-invalid="true" @enderror>
                @error('password')
                    <p class="text-red-600 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-3">
                <button type="submit" class="btn w-full flex justify-center items-center gap-2 relative"
                    wire:loading.attr="disabled">

                    <!-- Teks normal -->
                    <span class="inline-flex items-center transition-opacity duration-300"
                        wire:loading.class="opacity-0" wire:target="submit">
                        Masuk
                    </span>

                    <!-- Loading -->
                    <span class="flex items-center gap-2 absolute transition-opacity duration-300 opacity-0"
                        wire:loading.class.remove="opacity-0" wire:target="submit">
                        <i class="ti ti-loader-2 animate-spin text-lg"></i>
                        Memproses...
                    </span>
                </button>

                <a href="{{ $urlLoginSSO }}" class="btn-outline w-full">Masuk dengan SSO</a>
            </div>

            <div class="mb-3">
                <p class="text-center text-sm">Belum memiliki akun? <a target="_blank"
                        href="https://sdi.del.ac.id/auth/register" class="underline-offset-4 hover:underline">
                        Daftar dengan akun CIS
                    </a>
                </p>
            </div>
        </form>
    </section>
</div>

@section('others-js')
    <script src="/assets/vendor/node_modules/crypto-js/crypto-js.js"></script>
    <script src="/scripts/auth/login-livewire-v1.js"></script>
@endsection
