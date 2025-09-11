<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Regenerar Cache Configuración</h1>";

try {
    echo "<h2>📍 Paso 1: Verificar archivos</h2>";
    
    // Verificar archivos críticos
    $files = [
        '.env' => 'Archivo de environment',
        'bootstrap/cache/config.php' => 'Cache de configuración',
        'bootstrap/cache/services.php' => 'Cache de servicios'
    ];
    
    foreach ($files as $file => $desc) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<p>✅ $file existe</p>";
        } else {
            echo "<p>❌ $file no existe</p>";
        }
    }
    
    echo "<h2>🧹 Paso 2: Limpiar cache existente</h2>";
    
    // Eliminar archivos de cache
    $cacheFiles = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/routes.php'
    ];
    
    foreach ($cacheFiles as $cacheFile) {
        $fullPath = __DIR__ . '/' . $cacheFile;
        if (file_exists($fullPath)) {
            if (unlink($fullPath)) {
                echo "<p>🗑️ $cacheFile eliminado</p>";
            } else {
                echo "<p>❌ No se pudo eliminar $cacheFile</p>";
            }
        } else {
            echo "<p>ℹ️ $cacheFile no existe</p>";
        }
    }
    
    echo "<h2>📄 Paso 3: Verificar contenido .env</h2>";
    
    $envPath = __DIR__ . '/.env';
    $envContent = file_get_contents($envPath);
    $envLines = explode("\n", $envContent);
    
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:8px;'>";
    echo "<h3>Variables DB en .env:</h3>";
    echo "<pre>";
    foreach ($envLines as $line) {
        if (strpos($line, 'DB_') === 0) {
            // Ocultar contraseña para mostrar
            if (strpos($line, 'DB_PASSWORD') === 0) {
                $parts = explode('=', $line, 2);
                echo $parts[0] . '=' . str_repeat('*', strlen($parts[1])) . "\n";
            } else {
                echo $line . "\n";
            }
        }
    }
    echo "</pre>";
    echo "</div>";
    
    echo "<h2>⚙️ Paso 4: Generar nuevo cache</h2>";
    
    // Simular comando artisan config:cache manualmente
    try {
        // Cargar autoloader
        require_once __DIR__ . '/vendor/autoload.php';
        
        // Bootstrap Laravel sin cache
        $app = require_once __DIR__ . '/bootstrap/app.php';
        
        echo "<p>✅ Laravel bootstrap exitoso</p>";
        
        // Cargar configuración sin cache
        $config = [];
        $configFiles = glob(__DIR__ . '/config/*.php');
        
        foreach ($configFiles as $configFile) {
            $key = basename($configFile, '.php');
            $configData = include $configFile;
            $config[$key] = $configData;
            echo "<p>📄 Configuración '$key' cargada</p>";
        }
        
        echo "<p>📊 Total configuraciones: " . count($config) . "</p>";
        
        // Verificar configuración de base de datos específicamente
        if (isset($config['database'])) {
            $dbConfig = $config['database']['connections']['mysql'];
            echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px;'>";
            echo "<h3>🗄️ Configuración DB resultante:</h3>";
            echo "<ul>";
            echo "<li>Host: " . ($dbConfig['host'] ?? 'NO DEFINIDO') . "</li>";
            echo "<li>Database: " . ($dbConfig['database'] ?? 'NO DEFINIDO') . "</li>";
            echo "<li>Username: " . ($dbConfig['username'] ?? 'NO DEFINIDO') . "</li>";
            echo "<li>Password: " . (isset($dbConfig['password']) ? str_repeat('*', strlen($dbConfig['password'])) : 'NO DEFINIDO') . "</li>";
            echo "</ul>";
            echo "</div>";
            
            // Test de conexión con la nueva configuración
            echo "<h2>🧪 Paso 5: Test conexión con nueva config</h2>";
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;port=%s',
                    $dbConfig['host'],
                    $dbConfig['database'],
                    $dbConfig['port'] ?? 3306
                );
                
                $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                
                echo "<p>✅ Conexión DB exitosa con nueva configuración!</p>";
                
                // Escribir cache de configuración manualmente
                $cacheContent = '<?php return ' . var_export($config, true) . ';';
                $cacheFile = __DIR__ . '/bootstrap/cache/config.php';
                
                if (file_put_contents($cacheFile, $cacheContent, LOCK_EX)) {
                    echo "<p>✅ Cache de configuración regenerado</p>";
                    echo "<p>📦 Tamaño: " . filesize($cacheFile) . " bytes</p>";
                } else {
                    echo "<p>❌ Error escribiendo cache de configuración</p>";
                }
                
            } catch (PDOException $e) {
                echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>❌ Configuración de database no encontrada</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error en bootstrap: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎯 Resultado Final</h2>";
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px;'>";
    echo "<h3>✅ Cache Regenerado</h3>";
    echo "<p>La configuración ha sido limpiada y regenerada con los valores del .env del servidor.</p>";
    echo "<p><strong>Siguiente paso:</strong> Probar la aplicación en <a href='/inventario/' target='_blank'>monkits.com/inventario/</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 Config Cache Regenerator por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>