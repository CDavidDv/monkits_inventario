<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Database Config</h1>";

try {
    $envPath = __DIR__ . '/.env';
    
    echo "<h2>📍 Verificando archivo .env</h2>";
    
    if (!file_exists($envPath)) {
        echo "<p>❌ Archivo .env no encontrado</p>";
        exit;
    }
    
    // Leer archivo .env actual
    $envContent = file_get_contents($envPath);
    echo "<p>✅ .env leído (" . strlen($envContent) . " bytes)</p>";
    
    // Crear backup
    $backup = $envPath . '.backup-' . date('Y-m-d-H-i-s');
    file_put_contents($backup, $envContent);
    echo "<p>💾 Backup creado: " . basename($backup) . "</p>";
    
    echo "<h2>🗄️ Configuración Actual de DB:</h2>";
    $lines = explode("\n", $envContent);
    $dbConfig = [];
    
    foreach ($lines as $line) {
        if (strpos($line, 'DB_') === 0 && strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $dbConfig[$key] = $value;
            echo "<p><code>$key</code> = <code>" . (strlen($value) > 0 ? $value : '(vacío)') . "</code></p>";
        }
    }
    
    echo "<h2>🔧 Configuración Recomendada para Servidor:</h2>";
    
    // Configuración típica de hosting compartido
    $recommendedConfig = [
        'DB_CONNECTION' => 'mysql',
        'DB_HOST' => 'localhost',
        'DB_PORT' => '3306',
        'DB_DATABASE' => 'monkits_inventario',  // Ajustar según tu BD
        'DB_USERNAME' => 'monkits',             // Ajustar según tu usuario
        'DB_PASSWORD' => 'TU_PASSWORD_AQUI'    // Debe ser completado manualmente
    ];
    
    echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px; margin:20px 0;'>";
    echo "<h3>📝 Configuración Sugerida:</h3>";
    echo "<pre style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
    foreach ($recommendedConfig as $key => $value) {
        echo "$key=$value\n";
    }
    echo "</pre>";
    echo "</div>";
    
    echo "<h2>🧪 Test de Conexión Actual:</h2>";
    
    // Intentar conexión con configuración actual
    try {
        $host = $dbConfig['DB_HOST'] ?? 'localhost';
        $database = $dbConfig['DB_DATABASE'] ?? '';
        $username = $dbConfig['DB_USERNAME'] ?? '';
        $password = $dbConfig['DB_PASSWORD'] ?? '';
        
        echo "<p>🔄 Intentando conexión con:</p>";
        echo "<ul>";
        echo "<li>Host: <code>$host</code></li>";
        echo "<li>Database: <code>$database</code></li>";
        echo "<li>Username: <code>$username</code></li>";
        echo "<li>Password: <code>" . (strlen($password) > 0 ? str_repeat('*', strlen($password)) : '(vacío)') . "</code></li>";
        echo "</ul>";
        
        if (empty($database) || empty($username)) {
            echo "<p>⚠️ Faltan datos de configuración DB</p>";
        } else {
            $dsn = "mysql:host=$host;dbname=$database";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            echo "<p>✅ Conexión exitosa a la base de datos!</p>";
            
            // Verificar tablas críticas
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<p>📊 Tablas encontradas: " . count($tables) . "</p>";
            
            $criticalTables = ['users', 'items', 'categories', 'inventory_movements'];
            foreach ($criticalTables as $table) {
                if (in_array($table, $tables)) {
                    echo "<p>✅ Tabla '$table' existe</p>";
                } else {
                    echo "<p>⚠️ Tabla '$table' no existe (necesita migraciones)</p>";
                }
            }
        }
        
    } catch (PDOException $e) {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:8px;'>";
        echo "<h3>❌ Error de Conexión DB:</h3>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
        echo "</div>";
        
        echo "<div style='background:#fff3cd; padding:15px; border-radius:8px; margin:20px 0;'>";
        echo "<h3>🔧 Posibles Soluciones:</h3>";
        echo "<ul>";
        echo "<li>Verificar que la base de datos existe</li>";
        echo "<li>Verificar usuario y contraseña</li>";
        echo "<li>Verificar permisos del usuario de base de datos</li>";
        echo "<li>Contactar al hosting para obtener credenciales correctas</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h2>📋 Instrucciones:</h2>";
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px;'>";
    echo "<h3>✅ Pasos siguientes:</h3>";
    echo "<ol>";
    echo "<li><strong>Obtener credenciales DB correctas</strong> del panel de hosting</li>";
    echo "<li><strong>Editar .env</strong> con las credenciales correctas</li>";
    echo "<li><strong>Ejecutar migraciones</strong> si las tablas no existen</li>";
    echo "<li><strong>Probar nuevamente</strong> la aplicación</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 DB Config Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>