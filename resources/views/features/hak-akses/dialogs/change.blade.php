<dialog wire:ignore.self id="dialog-change" class="dialog w-full" aria-labelledby="dialog-change-title">
    <div>
        <header class="border-b pb-3 mb-3">
            <h2 id="dialog-change-title">{{ $infoDialogTitle }}</h2>
        </header>

        <section>
            <form wire:submit.prevent="onChange">

                @if (!$dataId)
                    <div class="grid gap-3 mb-5">
                        <label for="select-user-id-trigger" class="label w-full">Pilih Pengguna</label>
                        <div wire:ignore.self id="select-user-id" class="select">
                            <button type="button" class="btn-outline justify-between font-normal w-full"
                                id="select-user-id-trigger" aria-haspopup="listbox" aria-expanded="false"
                                aria-controls="select-user-id-listbox">
                                <span class="truncate">--- Pilih Pengguna ---</span>
                                <i class="ti ti-selector text-muted-foreground opacity-50 shrink-0"
                                    style="font-size: 24px"></i>
                            </button>
                            <div wire:ignore.self id="select-user-id-popover" data-popover aria-hidden="true">
                                <header>
                                    <i class="ti ti-search text-xl"></i>
                                    <input type="text" placeholder="Cari Pengguna..." autocomplete="off"
                                        autocorrect="off" spellcheck="false" aria-autocomplete="list" role="combobox"
                                        aria-expanded="false" aria-controls="select-user-id-listbox"
                                        aria-labelledby="select-user-id-trigger"
                                        wire:model.live.debounce.300ms="searchPengguna" />
                                </header>

                                <div role="listbox" id="select-user-id-listbox" aria-orientation="vertical"
                                    aria-labelledby="select-user-id-trigger">
                                    <div role="group" aria-labelledby="group-label-select-user-id-items-1">
                                        <div role="heading" id="group-label-select-user-id-items-1">Hasil Pencarian:
                                        </div>

                                        @foreach ($searchPenggunaList as $key => $pengguna)
                                            <div id="select-user-id-items-1-{{ $key }}" role="option"
                                                data-value="{{ $pengguna->id }}"
                                                wire:click="setUserId('{{ $pengguna->id }}')">
                                                [{{ $pengguna->username }}]
                                                {{ $pengguna->name }} </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="select-user-id-value" wire:model="dataUserId" />
                        </div>
                        @error('dataUserId')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="grid gap-3 mb-5">
                    <label for="check-hak-akses" class="label w-full">Pilih Hak Akses</label>
                    <fieldset id="check-hak-akses" class="flex flex-col gap-2">
                        @foreach ($optionRoles as $role)
                            <label class="font-normal leading-tight">
                                <input type="checkbox" name="check-hak-akses" value="{{ $role }}"
                                    wire:model="dataHakAkses" class="mr-2">
                                {{ $role }}
                            </label>
                        @endforeach
                    </fieldset>
                    @error('dataHakAkses')
                        <p class="text-red-600 text-sm">{{ $message }}</p>
                    @enderror
                </div>


                <div>
                    <button type="submit" class="btn w-full flex justify-center items-center gap-2 relative"
                        wire:loading.attr="disabled">

                        <!-- Teks normal -->
                        <span class="inline-flex items-center transition-opacity duration-300"
                            wire:loading.class="opacity-0" wire:target="onChange">
                            Simpan
                        </span>

                        <!-- Loading -->
                        <span class="flex items-center gap-2 absolute transition-opacity duration-300 opacity-0"
                            wire:loading.class.remove="opacity-0" wire:target="onChange">
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
