<div class="flex flex-col gap-4">

    @include('components.breadcrumb')

    <div class="card">
        <header>
            <div class="flex justify-between items-center p-4 border-b border-border-secondary">
                <h3 class="text-xl">
                    <i class="ti ti-lock"></i>
                    Hak Akses
                </h3>

                <div class="button-group">
                    <input type="text" class="input" placeholder="Cari..." wire:model.live.debounce.300ms="search" />
                    @if ($this->isEditor)
                        <button type="button" class="btn-icon" wire:click.prevent="prepareChange('')"
                            wire:loading.attr="disabled" wire:target="prepareChange">
                            <span>
                                <i class="ti ti-plus" wire:loading.remove wire:target="prepareChange"></i>
                                <i class="ti ti-loader-2 animate-spin" wire:loading wire:target="prepareChange"></i>
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </header>

        <section>
            <div>
                @if (sizeof($aksesList) <= 0)
                    <div class="alert">
                        <span></span>
                        <p class="text-orange-400">
                            <i class="ti ti-info-circle text-lg"></i>
                            Belum ada data yang tersedia.
                        </p>
                    </div>
                @else
                    <table class="table">
                        <thead>
                            <tr class="table-header">
                                <th>Identitas</th>
                                <th>Akses</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($aksesList as $akses)
                                <tr>
                                    <td>
                                        <small
                                            class="text-muted">{{ '@' . ($akses->user ? $akses->user->username : '') }}</small><br />
                                        <span>{{ $akses->user ? $akses->user->name : '' }}</span>
                                    </td>
                                    <td>
                                        @if ($akses->akses)
                                            <ul class="list-unstyled">
                                                @foreach ($akses->data_akses as $hakAkses)
                                                    <li>
                                                        <i class="ti ti-minus"></i>
                                                        {{ $hakAkses }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted">Tidak ada hak akses</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($this->isEditor)
                                            <div wire:loading.remove wire:target="prepareChange,prepareDelete">
                                                <button class="btn-sm bg-orange-300 text-dark"
                                                    wire:click.prevent="prepareChange('{{ $akses->id }}')">
                                                    <i class="ti ti-pencil"></i>
                                                </button>

                                                <button class="btn-sm bg-red-300 text-dark"
                                                    wire:click.prevent="prepareDelete('{{ $akses->id }}')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>

                                            <button disabled class="btn-sm" wire:loading
                                                wire:target="prepareChange,prepareDelete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    role="status" aria-label="Loading" class="size-4 animate-spin">
                                                    <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="bg-primary h-1" style="border-radius: 15px;"></div>
                @endif

            </div>
        </section>
    </div>

    {{-- Modal --}}
    @include('features.hak-akses.dialogs.change')
    @include('features.hak-akses.dialogs.delete')
    {{-- End Modal --}}
</div>

@section('others-css')
@endsection

@section('others-js')
@endsection
