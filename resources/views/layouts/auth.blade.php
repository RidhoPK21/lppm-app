<!DOCTYPE html>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

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

    <title>ITDel Starter - @yield('title')</title>
</head>

<body class="bg-background">
    <!-- Loading Bar -->
    <div id="loading-bar"
        class="fixed top-0 left-0 h-1 w-0 z-50 rounded-full shadow-md transition-all duration-200 hidden">
    </div>

    <div class="w-screen">
        @yield('content')
    </div>

    @livewireScripts

    <!-- Import JS files -->
    <script src="/scripts/loading-v1.js"></script>
    <script src="/scripts/custom-v1.js"></script>
    <script src="/assets/vendor/node_modules/basecoat-css/dist/js/all.min.js"></script>
    @yield('others-js')

</body>

</html>
