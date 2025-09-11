<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>📁 Creador de Directorios Laravel</h1>";

try {
    // Directorios que Laravel necesita
    $directories = [
        'storage/logs',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/framework/cache',
        'storage/framework/cache/data',
        'storage/app',
        'storage/app/public',
        'bootstrap/cache'
    ];

    echo "<h2>🔨 Creando directorios...</h2>";

    foreach ($directories as $dir) {
        $fullPath = __DIR__ . '/' . $dir;
        
        if (!is_dir($fullPath)) {
            if (mkdir($fullPath, 0755, true)) {
                echo "<p>✅ Creado: <code>$dir</code></p>";
            } else {
                echo "<p>❌ Error creando: <code>$dir</code></p>";
            }
        } else {
            echo "<p>ℹ️  Ya existe: <code>$dir</code></p>";
        }
        
        // Verificar permisos de escritura
        if (is_writable($fullPath)) {
            echo "<p>&nbsp;&nbsp;&nbsp;🔓 Permisos: OK</p>";
        } else {
            // Intentar cambiar permisos
            if (chmod($fullPath, 0755)) {
                echo "<p>&nbsp;&nbsp;&nbsp;🔧 Permisos corregidos</p>";
            } else {
                echo "<p>&nbsp;&nbsp;&nbsp;⚠️  Permisos: Verificar manualmente</p>";
            }
        }
    }

    // Crear archivo .gitignore para storage/logs si no existe
    $gitignorePath = __DIR__ . '/storage/logs/.gitignore';
    if (!file_exists($gitignorePath)) {
        file_put_contents($gitignorePath, "*\n!.gitignore\n");
        echo "<p>✅ Creado .gitignore en storage/logs</p>";
    }

    echo "<h2>🧪 Verificando Laravel...</h2>";
    
    // Intentar cargar Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p>✅ Autoloader cargado</p>";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "<p>✅ Laravel Bootstrap exitoso</p>";
    
    // Verificar .env
    if (!file_exists(__DIR__ . '/.env')) {
        if (file_exists(__DIR__ . '/.env.example')) {
            copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
            echo "<p>✅ Archivo .env creado desde .env.example</p>";
        } else {
            echo "<p>⚠️  Falta archivo .env</p>";
        }
    } else {
        echo "<p>✅ Archivo .env existe</p>";
    }
    
    echo "<h2>🎉 ¡Directorios creados exitosamente!</h2>";
    echo "<p style='background:#10b981;color:white;padding:15px;border-radius:8px;'>";
    echo "<strong>🚀 Tu aplicación Laravel está lista</strong><br>";
    echo "<a href='/inventario/' style='color:white;text-decoration:underline;'>» Probar MonKits Inventario</a>";
    echo "</p>";

} catch (Exception $e) {
    echo "<div style='background:#ef4444;color:white;padding:15px;border-radius:8px;margin:10px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>📁 Directorios Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>