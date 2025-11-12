@if ($breadcrumbs ?? false)
    <ol class="text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm wrap-break-word sm:gap-2.5">

        @foreach ($breadcrumbs as $bc)
            @if ($bc['url'] && !$loop->last)
                <li class="inline-flex items-center gap-1.5">
                    <a href="{{ $bc['url'] }}" class="hover:text-foreground transition-colors">{{ $bc['label'] }}</a>
                </li>
                <li>
                    <i class="ti ti-chevron-right text-xs"></i>
                </li>
            @else
                <li class="inline-flex items-center gap-1.5">
                    <span>{{ $bc['label'] }}</span>
                </li>
            @endif
        @endforeach
    </ol>
@endif
