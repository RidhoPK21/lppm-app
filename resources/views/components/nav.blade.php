 <nav aria-label="Sidebar navigation">
     <header>
         <a href="{{ route('app.beranda') }}" class="btn-ghost p-2 h-12 w-full justify-start">
             <div wire:ignore
                 class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                 <img src="/img/logo-dark.png" alt="ITDel Starter Logo" class="size-7 block dark:hidden">
                 <img src="/img/logo-light.png" alt="ITDel Starter Logo" class="size-7 hidden dark:block">
             </div>
             <div class="grid flex-1 text-left text-sm leading-tight">
                 <span class="truncate font-medium">{{ config('app.name') }}</span>
                 <span class="truncate text-xs">v{{ config('sdi.version') }}</span>
             </div>
         </a>
     </header>

     <section
         class="scrollbar [&amp;_[data-new-link]::after]:content-[&#39;New&#39;] [&amp;_[data-new-link]::after]:ml-auto [&amp;_[data-new-link]::after]:text-xs [&amp;_[data-new-link]::after]:font-medium [&amp;_[data-new-link]::after]:bg-sidebar-primary [&amp;_[data-new-link]::after]:text-sidebar-primary-foreground [&amp;_[data-new-link]::after]:px-2 [&amp;_[data-new-link]::after]:py-0.5 [&amp;_[data-new-link]::after]:rounded-md">


         <div role="group" aria-labelledby="group-label-sidebar-content-1">

             <h3 id="group-label-sidebar-content-1">Main</h3>

             <ul>
                 {{-- Beranda --}}
                 <li>
                     <a href="{{ route('app.beranda') }}">
                         <i class="ti ti-home"></i>
                         <span>Beranda</span>
                     </a>
                 </li>

                 @if (in_array('Admin', $auth->roles) || in_array('Admin', $auth->akses))
                     {{-- Hak Akses --}}
                     <li>
                         <a href="{{ route('app.hak-akses') }}">
                             <i class="ti ti-lock"></i>
                             <span>Hak Akses</span>
                         </a>
                     </li>
                 @endif

             </ul>
         </div>

     </section>


     <footer>
         <div id="popover-925347" class="popover ">
             <button id="popover-925347-trigger" type="button" aria-expanded="false"
                 aria-controls="popover-925347-popover"
                 class="btn-ghost p-2 h-12 w-full flex items-center justify-start" data-keep-mobile-sidebar-open="">

                 <img src="{{ $auth->photo ?? '' }}" class="rounded-lg shrink-0 size-8">
                 <div class="grid flex-1 text-left text-sm leading-tight">
                     <span class="truncate font-medium">
                         {{ $auth->name ?? '' }}
                     </span>
                     <span class="truncate text-xs text-gray-500">
                         {{ '@' . ($auth->username ?? '') }}
                     </span>
                 </div>
                 <i class="ti ti-selector" style="font-size: 24px;"></i>

             </button>
             <div id="popover-925347-popover" data-popover aria-hidden="true" data-side="top"
                 class="w-[271px] md:w-[239px]">

                 <div class="grid gap-4">
                     <header class="text-sm font-medium">
                         <img src="{{ $auth->photo ?? '' }}" class="rounded-lg w-[75%] mx-auto">
                         <div class="mt-2 text-center">
                             <span>
                                 {{ $auth->name ?? '' }}
                             </span>
                         </div>
                     </header>
                     <footer class="grid gap-2">
                         <a href="https://sdi.del.ac.id/app/profile" class="btn-sm-primary" target="_blank">
                             Pengaturan Akun
                         </a>
                         <a href="{{ route('auth.logout') }}" class="btn-sm-outline" target="_blank">
                             Keluar
                         </a>
                     </footer>
                 </div>

             </div>
         </div>

     </footer>
 </nav>
