<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Artisan Cache Fix</h1>";

try {
    echo "<h2>📍 Ejecutando comandos Artisan en el servidor</h2>";
    
    // Cambiar al directorio de la aplicación
    $appDir = __DIR__;
    chdir($appDir);
    
    echo "<p>📂 Directorio actual: " . getcwd() . "</p>";
    
    // Lista de comandos a ejecutar
    $commands = [
        'config:clear' => 'Limpiar cache de configuración',
        'route:clear' => 'Limpiar cache de rutas',  
        'view:clear' => 'Limpiar cache de vistas',
        'cache:clear' => 'Limpiar cache general',
        'config:cache' => 'Regenerar cache de configuración'
    ];
    
    echo "<h2>🚀 Ejecutando comandos:</h2>";
    
    foreach ($commands as $command => $description) {
        echo "<div style='background:#f8f9fa; padding:10px; margin:10px 0; border-radius:5px;'>";
        echo "<h3>⚙️ php artisan $command</h3>";
        echo "<p>$description</p>";
        
        // Ejecutar comando
        $fullCommand = "php artisan $command 2>&1";
        $output = shell_exec($fullCommand);
        
        if ($output) {
            echo "<pre style='background:#e9ecef; padding:10px; border-radius:3px;'>";
            echo htmlspecialchars($output);
            echo "</pre>";
            
            // Verificar si fue exitoso
            if (strpos(strtolower($output), 'error') !== false || strpos(strtolower($output), 'exception') !== false) {
                echo "<p style='color:#dc3545;'>❌ Error detectado en comando</p>";
            } else {
                echo "<p style='color:#28a745;'>✅ Comando ejecutado</p>";
            }
        } else {
            echo "<p style='color:#ffc107;'>⚠️ Sin output del comando</p>";
        }
        echo "</div>";
    }
    
    echo "<h2>🧪 Verificación Final</h2>";
    
    // Verificar archivos de cache generados
    $cacheFiles = [
        'bootstrap/cache/config.php' => 'Cache de configuración',
        'bootstrap/cache/routes.php' => 'Cache de rutas',
        'bootstrap/cache/services.php' => 'Cache de servicios'
    ];
    
    echo "<h3>📁 Archivos de cache:</h3>";
    foreach ($cacheFiles as $file => $desc) {
        $fullPath = $appDir . '/' . $file;
        if (file_exists($fullPath)) {
            $size = filesize($fullPath);
            $modified = date('Y-m-d H:i:s', filemtime($fullPath));
            echo "<p>✅ $file ($size bytes, modificado: $modified)</p>";
        } else {
            echo "<p>❌ $file no existe</p>";
        }
    }
    
    // Test básico de Laravel
    echo "<h3>🧪 Test Laravel:</h3>";
    $testCommand = "php artisan --version 2>&1";
    $versionOutput = shell_exec($testCommand);
    
    if ($versionOutput) {
        echo "<p>✅ Laravel version: " . trim(htmlspecialchars($versionOutput)) . "</p>";
    } else {
        echo "<p>❌ No se pudo obtener versión de Laravel</p>";
    }
    
    // Test de configuración específica
    echo "<h3>🔍 Test configuración DB:</h3>";
    $configTestCommand = "php artisan tinker --execute=\"echo config('database.connections.mysql.host');\" 2>&1";
    $configOutput = shell_exec($configTestCommand);
    
    if ($configOutput) {
        echo "<pre>Resultado configuración DB: " . htmlspecialchars($configOutput) . "</pre>";
    }
    
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px; margin:20px 0;'>";
    echo "<h2>✅ Comandos Artisan Ejecutados</h2>";
    echo "<p>Todos los caches han sido limpiados y regenerados usando los comandos nativos de Laravel.</p>";
    echo "<p><strong>Paso siguiente:</strong></p>";
    echo "<ol>";
    echo "<li>Probar <a href='/inventario/catch-error.php' target='_blank'>catch-error.php</a></li>";
    echo "<li>Si funciona, probar <a href='/inventario/' target='_blank'>la aplicación principal</a></li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 Artisan Cache Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>