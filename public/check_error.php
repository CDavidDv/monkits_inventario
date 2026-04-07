<?php
// BORRAR DESPUES DE USAR
$logFile = __DIR__ . '/../storage/logs/laravel.log';
echo "<pre style='font-size:11px;background:#111;color:#0f0;padding:10px;overflow:auto;max-height:400px'>";
if (file_exists($logFile)) {
    $lines = file($logFile);
    echo htmlspecialchars(implode('', array_slice($lines, -60)));
} else {
    echo "No hay log todavia";
}
echo "</pre><hr><pre>";

// Verificar chunks de build
$buildDir = __DIR__ . '/build/assets';
if (is_dir($buildDir)) {
    $files = scandir($buildDir);
    echo "Archivos en build/assets: " . (count($files) - 2) . "\n";
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') echo "  $f\n";
    }
} else {
    echo "build/assets NO EXISTE\n";
}

// Verificar directorios de storage/framework
echo "\nstorage/framework:\n";
foreach (['sessions','views','cache','cache/data'] as $d) {
    $path = __DIR__ . "/../storage/framework/$d";
    echo "  $d: " . (is_dir($path) ? 'existe ' . (is_writable($path) ? '(writable)' : '(NO writable)') : 'NO EXISTE') . "\n";
}
echo "</pre>";
