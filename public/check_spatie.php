<?php
// BORRAR DESPUES DE USAR
echo "<pre>";

$files = [
    'vendor/spatie/laravel-permission/src/Middleware/PermissionMiddleware.php',
    'vendor/spatie/laravel-permission/src/PermissionServiceProvider.php',
    'vendor/composer/autoload_classmap.php',
    'vendor/composer/autoload_psr4.php',
];

foreach ($files as $f) {
    $path = __DIR__ . '/../' . $f;
    echo ($f) . ": " . (file_exists($path) ? "EXISTE" : "NO EXISTE") . "\n";
}

echo "\n--- autoload_psr4 entry for Spatie ---\n";
$psr4 = include __DIR__ . '/../vendor/composer/autoload_psr4.php';
foreach ($psr4 as $ns => $paths) {
    if (strpos($ns, 'Spatie') !== false) {
        echo "$ns => " . implode(', ', $paths) . "\n";
    }
}

echo "\n--- class_exists check ---\n";
require __DIR__ . '/../vendor/autoload.php';
echo "PermissionMiddleware: " . (class_exists('Spatie\Permission\Middleware\PermissionMiddleware') ? 'OK' : 'NO ENCONTRADO') . "\n";
echo "</pre>";
