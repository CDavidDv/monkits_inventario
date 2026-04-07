<?php
echo "<pre>";
echo "PHP: " . phpversion() . "\n\n";

// Verificar open_basedir
$basedir = ini_get('open_basedir');
echo "open_basedir: " . ($basedir ?: 'sin restriccion') . "\n\n";

// Verificar rutas accesibles
$paths = [
    'vendor'         => __DIR__ . '/../vendor/autoload.php',
    '.env'           => __DIR__ . '/../.env',
    'storage/logs'   => __DIR__ . '/../storage/logs',
    'bootstrap/cache'=> __DIR__ . '/../bootstrap/cache',
];

foreach ($paths as $name => $path) {
    $exists = @file_exists($path);
    $write  = $exists ? (@is_writable($path) ? 'escribible' : 'NO escribible') : '';
    echo "$name: " . ($exists ? "existe $write" : "NO EXISTE") . "\n";
}

// Extensions requeridas por Laravel
echo "\nExtensiones PHP:\n";
$required = ['openssl','pdo','pdo_mysql','mbstring','tokenizer','xml','ctype','json','bcmath','fileinfo'];
foreach ($required as $ext) {
    echo "  $ext: " . (extension_loaded($ext) ? 'OK' : 'FALTA') . "\n";
}
echo "</pre>";
