<!DOCTYPE html>
<html>

<head>

    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {{-- Title --}}
    <title>ITDel Starter - @yield('title')</title>

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/png" href="/img/logo-dark.png" />

    <!-- Init JS -->
    <script src="/scripts/init-v1.js"></script>
    <script src="/assets/vendor/externals/tailwind.js"></script>

    <!-- Import CSS files -->
    <link rel="stylesheet" href="/assets/vendor/node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="/assets/vendor/node_modules/basecoat-css/dist/basecoat.cdn.min.css" />
    <link rel="stylesheet" href="/assets/themes/blue.css" />
    <link rel="stylesheet" href="/assets/themes/green.css" />
    <link rel="stylesheet" href="/assets/themes/orange.css" />
    <link rel="stylesheet" href="/assets/themes/red.css" />
    <link rel="stylesheet" href="/assets/themes/pink.css" />
    <link rel="stylesheet" href="/assets/themes/violet.css" />
    <link rel="stylesheet" href="/assets/themes/yellow.css" />

    <!-- CSS -->
    <link rel="stylesheet" href="/styles/custom.css" />
    @livewireStyles

</head>

<body>

    <aside id="sidebar" class="sidebar " data-side="left" aria-hidden="false">

        {{-- Navigation --}}
        @include('components.nav')

    </aside>


    <main id="content">
        @include('components.header')

        <div class="p-4 md:p-6 xl:p-12">
            <div class="max-w-5xl mx-auto">
                @yield('content')
            </div>
        </div>
    </main>


    <div id="toaster" class="toaster ">

    </div>

    @livewireScripts

    <!-- Import JS files -->
    <script src="/scripts/loading-v1.js"></script>
    <script src="/scripts/custom-v1.js"></script>
    <script src="/assets/vendor/node_modules/basecoat-css/dist/js/all.min.js"></script>

</body>

</html>
