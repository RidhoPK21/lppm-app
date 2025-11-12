# ITDel Starter APP

## Logs

### [26-10-2025] 

- [Abdullah Ubaid] Inisialisasi proyek

## Syntax

### Membuat Model

Command: `php artisan make:model ExampleModel`

### Membuat Livewire

Command: `php artisan make:livewire Example/ExampleLivewire`

### Composer Audit
composer audit adalah perintah Composer yang digunakan untuk memeriksa keamanan paket PHP yang terinstal di proyek.

Command: `composer audit`

### Static Code Analisis

Membuatuhkan file: phpstan.neon
```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    level: 5       # level 0 (ringan) sampai 8 (strict)
    paths:
        - app
        - routes
```

command install: `composer require --dev larastan/larastan`

command run: `vendor/bin/phpstan analyse`

### Melakukan Pengujian Coverage

Command: `php artisan test --coverage`

### Melakukan Pengujian Spesifik

commands:
```bash
php artisan test tests/Unit/Middleware/CheckAuthMiddlewareTest.php

php artisan test tests/Feature/Livewire/HakAkses/HakAksesLivewireTest.php
php artisan test tests/Feature/Livewire/Beranda/BerandaLivewireTest.php
php artisan test tests/Feature/Livewire/Auth/LoginLivewireTest.php


php artisan test tests/Unit/Provider/AppServiceProviderTest.php
```

### Membersihkan Semua Cache Laravel
Commands:
```bash
php artisan optimize:clear
composer dump-autoload
```

### Memformat Kode PHP Secara Otomatis

Command install: `composer require laravel/pint --dev`

command run: `vendor/bin/pint`

command test: `vendor/bin/pint --test`

### Menjalnkan Github Action di Local

Command install: `choco install act-cli`

Command install container (butuh docker): `docker pull catthehacker/ubuntu:full-latest`

Command run: `act`