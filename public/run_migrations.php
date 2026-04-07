<?php
// BORRAR ESTE ARCHIVO INMEDIATAMENTE DESPUES DE USARLO
// Solo corre migraciones pendientes (SIN seeders)

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";

$kernel->call('migrate', ['--force' => true]);
echo $kernel->output();

$kernel->call('optimize:clear');
echo $kernel->output();

echo "</pre><br><b style='color:red'>BORRA ESTE ARCHIVO DEL SERVIDOR AHORA</b>";
