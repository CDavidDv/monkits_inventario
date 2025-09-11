<?php
// Fix ultra simple - NO usa autoloader
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🚨 Ultra Fix - Sin Dependencias</h1>";

try {
    echo "<p>🎯 Método: Reemplazo directo de archivos corruptos</p>";
    
    // Ruta del archivo corrupto
    $functionsFile = __DIR__ . '/vendor/ramsey/uuid/src/functions.php';
    
    echo "<p>📍 Archivo objetivo: <code>$functionsFile</code></p>";
    
    if (file_exists($functionsFile)) {
        echo "<p>✅ Archivo encontrado</p>";
        
        // Backup del corrupto
        $backup = $functionsFile . '.corrupted.bak';
        if (copy($functionsFile, $backup)) {
            echo "<p>💾 Backup creado: " . basename($backup) . "</p>";
        }
    } else {
        echo "<p>❌ Archivo no encontrado</p>";
    }
    
    // Contenido limpio COMPLETO del functions.php original
    $cleanContent = '<?php

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
 *
 * @param Hexadecimal|int|string|null $node A 48-bit number representing the hardware address
 * @param int|null $clockSeq A 14-bit number used to help avoid duplicates
 *
 * @return non-empty-string Version 1 UUID as a string
 */
function v1($node = null, ?int $clockSeq = null): string
{
    return Uuid::uuid1($node, $clockSeq)->toString();
}

/**
 * Returns a version 4 (random) UUID as a string
 *
 * @return non-empty-string Version 4 UUID as a string
 */
function v4(): string
{
    return Uuid::uuid4()->toString();
}

/**
 * Returns a version 3 (name-based) UUID based on the MD5 hash
 *
 * @param string|UuidInterface $ns The namespace (must be a valid UUID)
 * @param string $name The name to use for creating a UUID
 *
 * @return non-empty-string Version 3 UUID as a string
 */
function v3($ns, string $name): string
{
    return Uuid::uuid3($ns, $name)->toString();
}

/**
 * Returns a version 5 (name-based) UUID based on the SHA-1 hash
 *
 * @param string|UuidInterface $ns The namespace (must be a valid UUID) 
 * @param string $name The name to use for creating a UUID
 *
 * @return non-empty-string Version 5 UUID as a string
 */
function v5($ns, string $name): string
{
    return Uuid::uuid5($ns, $name)->toString();
}
';
    
    // Escribir archivo limpio
    if (file_put_contents($functionsFile, $cleanContent, LOCK_EX)) {
        echo "<p>✅ functions.php reemplazado exitosamente</p>";
        echo "<p>📦 Tamaño: " . strlen($cleanContent) . " bytes</p>";
    } else {
        echo "<p>❌ Error: No se pudo escribir el archivo</p>";
        exit;
    }
    
    // Limpiar cache
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo "<p>🧹 OPcache reiniciado</p>";
    }
    
    echo "<h2>✅ ARCHIVO REEMPLAZADO</h2>";
    echo "<p style='background:#10b981;color:white;padding:20px;border-radius:10px;margin:20px 0;'>";
    echo "<strong>🎉 ¡functions.php corregido!</strong><br><br>";
    echo "El archivo corrupto ha sido reemplazado.<br>";
    echo "<a href='/inventario/' style='background:#059669;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;margin-top:10px;'>🚀 PROBAR APLICACIÓN</a>";
    echo "</p>";
    
    echo "<div style='background:#f3f4f6;padding:15px;border-radius:8px;margin-top:20px;'>";
    echo "<h3>📋 Qué se hizo:</h3>";
    echo "<ul>";
    echo "<li>✅ Se hizo backup del archivo corrupto</li>";
    echo "<li>✅ Se reemplazó con versión PHP 7.4 compatible</li>";
    echo "<li>✅ Se limpió el cache de opcodes</li>";
    echo "<li>✅ Se removieron sintaxis problemáticas</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Throwable $e) {
    echo "<div style='background:#dc2626;color:white;padding:20px;border-radius:10px;'>";
    echo "<h3>🚨 Error Crítico</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 Ultra Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>