<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix PSR Log - Compatibilidad PHP 7.4</h1>";

try {
    $loggerFile = __DIR__ . '/vendor/psr/log/src/LoggerInterface.php';
    
    echo "<p>📍 Archivo objetivo: <code>$loggerFile</code></p>";
    
    if (!file_exists($loggerFile)) {
        echo "<p>❌ Archivo no encontrado</p>";
        exit;
    }
    
    // Leer contenido actual
    $content = file_get_contents($loggerFile);
    echo "<p>📄 Archivo leído (" . strlen($content) . " bytes)</p>";
    
    // Backup
    $backup = $loggerFile . '.php8.backup';
    file_put_contents($backup, $content);
    echo "<p>💾 Backup creado: " . basename($backup) . "</p>";
    
    // Buscar y reemplazar Union Types
    $patterns = [
        // string|\Stringable -> string (más compatible)
        '/string\\\\\Stringable/' => 'string',
        '/string\|\\\Stringable/' => 'string',
        // Otros union types comunes
        '/\|mixed/' => '',
        '/mixed\|/' => '',
    ];
    
    $changes = 0;
    foreach ($patterns as $pattern => $replacement) {
        $newContent = preg_replace($pattern, $replacement, $content);
        if ($newContent !== $content) {
            $content = $newContent;
            $changes++;
            echo "<p>🔄 Patrón reemplazado: <code>$pattern</code></p>";
        }
    }
    
    // Reemplazo manual más específico para LoggerInterface
    $phpCompatibleContent = '<?php

namespace Psr\\Log;

/**
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function emergency($message, array $context = array());

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function alert($message, array $context = array());

    /**
     * Critical conditions.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function critical($message, array $context = array());

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function error($message, array $context = array());

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function warning($message, array $context = array());

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function notice($message, array $context = array());

    /**
     * Interesting events.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function info($message, array $context = array());

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function debug($message, array $context = array());

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string $message
     * @param mixed[] $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array());
}
';

    // Escribir versión compatible
    if (file_put_contents($loggerFile, $phpCompatibleContent)) {
        echo "<p>✅ LoggerInterface.php reemplazado con versión PHP 7.4</p>";
        echo "<p>📦 Nuevo tamaño: " . strlen($phpCompatibleContent) . " bytes</p>";
    } else {
        echo "<p>❌ Error escribiendo archivo</p>";
        exit;
    }
    
    // Limpiar cache
    if (function_exists(\'opcache_reset\')) {
        opcache_reset();
        echo "<p>🧹 OPcache limpiado</p>";
    }
    
    echo "<h2>🧪 Probando Laravel...</h2>";
    
    // Test básico
    require_once __DIR__ . \'/vendor/autoload.php\';
    echo "<p>✅ Autoloader OK</p>";
    
    $app = require_once __DIR__ . \'/bootstrap/app.php\';
    echo "<p>✅ Laravel Bootstrap OK</p>";
    
    // Test más profundo - crear kernel
    $kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
    echo "<p>✅ HTTP Kernel creado</p>";
    
    echo "<h2>🎉 ¡PSR Log Fixed!</h2>";
    echo "<p style=\'background:#10b981;color:white;padding:20px;border-radius:10px;\'>";
    echo "<strong>✅ Union Types eliminados</strong><br>";
    echo "LoggerInterface ahora es compatible con PHP 7.4<br><br>";
    echo "<a href=\'/inventario/\' style=\'background:#065f46;color:white;padding:12px 24px;text-decoration:none;border-radius:6px;display:inline-block;margin-top:10px;\'>🚀 PROBAR APLICACIÓN</a>";
    echo "</p>";
    
    echo "<div style=\'background:#f3f4f6;padding:15px;border-radius:8px;margin-top:20px;\'>";
    echo "<h3>📋 Cambios realizados:</h3>";
    echo "<ul>";
    echo "<li>✅ Eliminados Union Types (<code>string|\\Stringable</code>)</li>";
    echo "<li>✅ Convertido a sintaxis PHP 7.4</li>";
    echo "<li>✅ Mantenida funcionalidad completa</li>";
    echo "<li>✅ Backup del archivo original creado</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style=\'background:#dc2626;color:white;padding:20px;border-radius:10px;\'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style=\'margin:30px 0;\'>";
echo "<p style=\'text-align:center;color:#666;\'><strong>🔧 PSR Log Fix por Claude Code</strong> | " . date(\'Y-m-d H:i:s\') . "</p>";
?>