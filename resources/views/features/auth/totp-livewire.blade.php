<div>
    <div class="card mt-8" style="max-width: 360px;">
        <header>
            <div class="mx-auto">
                <img src="/img/logo-dark-text.png" class="block dark:hidden" style="height: 72px;" alt="Logo-dark" />
                <img src="/img/logo-light-text.png" class="hidden dark:block" style="height: 72px;" alt="Logo-light" />
            </div>

            @if ($qrCode != null)
                <div>
                    <div class="flex items-center">
                        <hr class="grow border-t border-gray-300">
                        <span class="px-3 text-gray-500 text-sm">QRCODE-TOTP</span>
                        <hr class="grow border-t border-gray-300">
                    </div>

                    <p class="text-sm text-center">
                        Scan QRCode pada smartphone kamu, menggunakan aplikasi Authenticator seperti <a
                            class="text-blue-600"
                            href="https://play.google.com/store/apps/details?id=com.azure.authenticator"
                            target="_blank">
                            Microsoft Authenticator
                        </a>
                        atau
                        <a class="text-blue-600"
                            href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                            target="_blank">
                            Google Authenticator
                        </a>
                    </p>

                    <div class="mb-3">
                        <img class="mx-auto" src="{{ $qrCode }}" alt="QR Code">
                    </div>

                </div>
            @endif

            <div class="flex items-center">
                <hr class="grow border-t border-gray-300">
                <span class="px-3 text-gray-500 text-sm">Verifikasi-TOTP</span>
                <hr class="grow border-t border-gray-300">
            </div>

            <p class="text-sm text-center">
                Silahkan menyelesaikan verifikasi 2 langkah dengan memasukkan kode yang
                tampil pada aplikasi Authenticator.
            </p>
        </header>
        <section>
            <form class="form grid gap-6" wire:submit.prevent="submit">
                <div class="grid gap-2">
                    <label for="inputToken">Token</label>
                    <input type="number" id="inputToken" wire:model="token"
                        @error('token') aria-invalid="true" @enderror>

                    @error('token')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-3">
                    <button type="submit" class="btn w-full flex justify-center items-center gap-2 relative"
                        wire:loading.attr="disabled">
                        <!-- Teks normal -->
                        <span class="inline-flex items-center transition-opacity duration-300"
                            wire:loading.class="opacity-0" wire:target="submit">
                            Verifikasi
                        </span>

                        <!-- Loading -->
                        <span class="flex items-center gap-2 absolute transition-opacity duration-300 opacity-0"
                            wire:loading.class.remove="opacity-0" wire:target="submit">
                            <i class="ti ti-loader-2 animate-spin text-lg"></i>
                            Memproses...
                        </span>
                    </button>

                    <button onclick="onLogout()" type="button" class="btn-outline w-full">Keluar</button>
                </div>
            </form>
        </section>
    </div>
</div>



@section('others-js')
@endsection
