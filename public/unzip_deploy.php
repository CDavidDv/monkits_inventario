<?php
// BORRAR ESTE ARCHIVO INMEDIATAMENTE DESPUES DE USARLO
// Uso: sube inventario.zip a public_html/inventario/ y luego accede a este script

$zipFile = __DIR__ . '/../inventario.zip';
$extractTo = __DIR__ . '/..';

if (!file_exists($zipFile)) {
    die("ERROR: No se encontró inventario.zip en " . dirname($zipFile));
}

$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    echo "<pre>";
    echo "Descomprimiendo " . $zip->numFiles . " archivos...\n";
    $zip->extractTo($extractTo);
    $zip->close();
    echo "Listo!\n";

    // Permisos
    chmod($extractTo . '/storage', 0775);
    chmod($extractTo . '/bootstrap/cache', 0775);
    echo "Permisos configurados.\n";
    echo "</pre>";
    echo "<b style='color:green'>Descompresion exitosa.</b><br>";
    echo "<b style='color:red'>BORRA inventario.zip Y ESTE ARCHIVO DEL SERVIDOR AHORA</b>";
} else {
    echo "ERROR: No se pudo abrir el ZIP";
}
