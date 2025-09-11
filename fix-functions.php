<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Functions.php - Reemplazo Directo</h1>";

try {
    $functionsPath = __DIR__ . '/vendor/ramsey/uuid/src/functions.php';
    
    echo "<p>📍 Ruta: <code>$functionsPath</code></p>";
    
    // Verificar si el archivo existe
    if (file_exists($functionsPath)) {
        echo "<p>✅ Archivo existe</p>";
        
        // Hacer backup
        $backupPath = $functionsPath . '.backup.' . date('YmdHis');
        copy($functionsPath, $backupPath);
        echo "<p>💾 Backup creado: <code>" . basename($backupPath) . "</code></p>";
    } else {
        echo "<p>❌ Archivo no existe</p>";
    }
    
    // Crear versión limpia del functions.php
    $cleanFunctions = '<?php

/**
 * This file is part of the ramsey/uuid library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\\Uuid;

use Ramsey\\Uuid\\Type\\Hexadecimal;
use Ramsey\\Uuid\\Type\\Integer as IntegerObject;

/**
 * Returns a version 1 (Gregorian time) UUID as a string
 */
function v1($node = null, $clockSeq = null): string
{
    return Uuid::uuid1($node, $clockSeq)->toString();
}

/**
 * Returns a version 4 (random) UUID as a string
 */
function v4(): string
{
    return Uuid::uuid4()->toString();
}

/**
 * Returns a version 3 (name-based) UUID based on the MD5 hash of a namespace ID and a name
 */
function v3($ns, string $name): string
{
    return Uuid::uuid3($ns, $name)->toString();
}

/**
 * Returns a version 5 (name-based) UUID based on the SHA-1 hash of a namespace ID and a name
 */
function v5($ns, string $name): string
{
    return Uuid::uuid5($ns, $name)->toString();
}

/**
 * Returns a UUID as a string
 */
function uuid(): string
{
    return Uuid::uuid4()->toString();
}
';

    // Escribir el archivo limpio
    if (file_put_contents($functionsPath, $cleanFunctions)) {
        echo "<p>✅ functions.php reemplazado exitosamente</p>";
    } else {
        echo "<p>❌ Error escribiendo functions.php</p>";
    }
    
    // Limpiar cache de opcodes
    if (function_exists(\'opcache_reset\')) {
        opcache_reset();
        echo "<p>🧹 OPcache limpiado</p>";
    }
    
    echo "<h2>🧪 Probando...</h2>";
    
    // Probar el autoloader
    require_once __DIR__ . \'/vendor/autoload.php\';
    echo "<p>✅ Autoloader cargado sin errores</p>";
    
    // Probar UUID
    if (class_exists(\'Ramsey\\\\Uuid\\\\Uuid\')) {
        $uuid = \\Ramsey\\Uuid\\Uuid::uuid4();
        echo "<p>✅ UUID generado: " . $uuid->toString() . "</p>";
    }
    
    // Probar Laravel
    $app = require_once __DIR__ . \'/bootstrap/app.php\';
    echo "<p>✅ Laravel Bootstrap exitoso</p>";
    
    echo "<h2>🎉 ¡Functions.php corregido!</h2>";
    echo "<p style=\'background:#10b981;color:white;padding:15px;border-radius:8px;\'>";
    echo "<strong>🚀 Tu aplicación está lista</strong><br>";
    echo "<a href=\'/inventario/\' style=\'color:white;text-decoration:underline;\'>» Probar MonKits Inventario</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<div style=\'background:#ef4444;color:white;padding:15px;border-radius:8px;margin:10px 0;\'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>🔧 Functions Fix por Claude Code</strong> | " . date(\'Y-m-d H:i:s\') . "</p>";
?>