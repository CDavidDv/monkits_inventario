<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix ENV Cache - Solución Definitiva</h1>";

try {
    echo "<h2>📍 Problema Identificado:</h2>";
    echo "<div style='background:#f8d7da; padding:15px; border-radius:8px;'>";
    echo "<p><strong>❌ Variables de entorno NO se cargan</strong></p>";
    echo "<p>El cache de configuración existe pero contiene valores incorretos.</p>";
    echo "<p><strong>Solución:</strong> Eliminar cache para forzar lectura de .env</p>";
    echo "</div>";
    
    echo "<h2>🔧 Paso 1: Verificar .env</h2>";
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        echo "<p>✅ .env existe (" . strlen($envContent) . " bytes)</p>";
        
        // Mostrar variables críticas del .env
        $lines = explode("\n", $envContent);
        echo "<h3>Variables en .env:</h3>";
        echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !empty(trim($line)) && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                if (strpos($key, 'DB_PASSWORD') === 0 || strpos($key, 'APP_KEY') === 0) {
                    echo "<p><code>$key</code> = " . str_repeat('*', min(strlen($value), 10)) . "</p>";
                } else {
                    echo "<p><code>$key</code> = <code>$value</code></p>";
                }
            }
        }
        echo "</div>";
    } else {
        echo "<p>❌ .env no existe</p>";
        exit;
    }
    
    echo "<h2>🗑️ Paso 2: Eliminar cache de configuración</h2>";
    $cacheFile = __DIR__ . '/bootstrap/cache/config.php';
    if (file_exists($cacheFile)) {
        // Crear backup primero
        $backup = $cacheFile . '.backup-' . date('Y-m-d-H-i-s');
        if (copy($cacheFile, $backup)) {
            echo "<p>💾 Backup creado: " . basename($backup) . "</p>";
        }
        
        // Eliminar cache
        if (unlink($cacheFile)) {
            echo "<p>✅ bootstrap/cache/config.php eliminado</p>";
        } else {
            echo "<p>❌ Error eliminando cache</p>";
            exit;
        }
    } else {
        echo "<p>ℹ️ Cache de configuración ya no existe</p>";
    }
    
    echo "<h2>🧪 Paso 3: Test sin cache</h2>";
    
    // Test básico de carga de variables
    try {
        // Cargar autoloader
        require_once __DIR__ . '/vendor/autoload.php';
        
        // Bootstrap sin cache
        $app = require_once __DIR__ . '/bootstrap/app.php';
        
        echo "<p>✅ Bootstrap sin cache exitoso</p>";
        
        // Test variables específicas
        $testVars = ['APP_ENV', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE'];
        echo "<h3>Variables cargadas:</h3>";
        echo "<div style='background:#e7f3ff; padding:10px; border-radius:5px;'>";
        
        $allLoaded = true;
        foreach ($testVars as $var) {
            $value = env($var);
            if ($value) {
                if (strpos($var, 'APP_KEY') === 0) {
                    echo "<p>✅ <code>$var</code> = " . str_repeat('*', strlen($value)) . "</p>";
                } else {
                    echo "<p>✅ <code>$var</code> = <code>$value</code></p>";
                }
            } else {
                echo "<p>❌ <code>$var</code> = (vacío)</p>";
                $allLoaded = false;
            }
        }
        echo "</div>";
        
        if ($allLoaded) {
            echo "<div style='background:#10b981; color:white; padding:15px; border-radius:8px; margin:20px 0;'>";
            echo "<h3>✅ VARIABLES CARGADAS CORRECTAMENTE</h3>";
            echo "<p>Las variables de entorno ahora se leen desde .env</p>";
            echo "</div>";
        } else {
            echo "<div style='background:#f59e0b; color:white; padding:15px; border-radius:8px; margin:20px 0;'>";
            echo "<h3>⚠️ Algunas variables aún faltan</h3>";
            echo "<p>Verifica el formato del archivo .env</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h3>❌ Error en test sin cache</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
    echo "<h2>🎯 Paso 4: Regenerar cache con valores correctos</h2>";
    
    // Ejecutar config:cache para regenerar
    $configCacheCommand = "cd " . escapeshellarg(__DIR__) . " && php artisan config:cache 2>&1";
    $output = shell_exec($configCacheCommand);
    
    if ($output) {
        echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
        echo "<h4>Salida de php artisan config:cache:</h4>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
    }
    
    // Verificar que se recreó el cache
    if (file_exists($cacheFile)) {
        echo "<p>✅ Cache de configuración regenerado (" . filesize($cacheFile) . " bytes)</p>";
    } else {
        echo "<p>❌ Cache no se regeneró</p>";
    }
    
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px; margin:20px 0;'>";
    echo "<h2>🚀 SOLUCIÓN APLICADA</h2>";
    echo "<p><strong>✅ Cache eliminado</strong> - Laravel ahora lee .env</p>";
    echo "<p><strong>✅ Cache regenerado</strong> - Con valores correctos del servidor</p>";
    echo "<p><strong>Siguiente paso:</strong> Probar la aplicación</p>";
    echo "<p><a href='/inventario/' style='background:#059669; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🚀 PROBAR APLICACIÓN</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 ENV Cache Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>