<dialog wire:ignore.self id="dialog-delete" class="dialog w-full" aria-labelledby="dialog-delete-title">
    <div>
        <header class="border-b pb-3 mb-3">
            <h2 id="dialog-delete-title">
                Hapus Hak Akses
            </h2>
        </header>

        <section>
            <form wire:submit.prevent="onDelete">
                <div class="mb-5">
                    <p>{!! $infoDeleteMessage !!}</p>
                </div>

                <div class="grid gap-3 mb-5">
                    <label for="input-konfirmasi-id" class="label">Konfirmasi ID</label>
                    <input class="input" id="input-konfirmasi-id" type="text" wire:model="dataKonfirmasi">
                    @error('dataKonfirmasi')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn flex justify-center items-center gap-2 relative"
                        wire:loading.attr="disabled">

                        <!-- Teks normal -->
                        <span class="inline-flex items-center transition-opacity duration-300"
                            wire:loading.class="opacity-0" wire:target="onDelete">
                            Ya, Tetap Hapus
                        </span>

                        <!-- Loading -->
                        <span class="flex items-center gap-2 absolute transition-opacity duration-300 opacity-0"
                            wire:loading.class.remove="opacity-0" wire:target="onDelete">
                            <i class="ti ti-loader-2 animate-spin text-lg"></i>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </section>

        <button type="button" aria-label="Close dialog" onclick="this.closest('dialog').close()">
            <i class="ti ti-x text-2xl"></i>
        </button>
    </div>
</dialog>
