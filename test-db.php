<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🗄️ Test de Conexión Base de Datos</h2>";

// Cargar variables de entorno
if (file_exists(__DIR__ . '/.env')) {
    $env_content = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $env_content);
    
    $config = [];
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    echo "<h3>📋 Configuración de Base de Datos</h3>";
    echo "- Host: " . ($config['DB_HOST'] ?? 'No configurado') . "<br>";
    echo "- Database: " . ($config['DB_DATABASE'] ?? 'No configurado') . "<br>";
    echo "- Username: " . ($config['DB_USERNAME'] ?? 'No configurado') . "<br>";
    echo "- Port: " . ($config['DB_PORT'] ?? '3306') . "<br>";
    
    // Intentar conexión con PDO
    echo "<h3>🔌 Test de Conexión</h3>";
    try {
        $dsn = "mysql:host=" . ($config['DB_HOST'] ?? 'localhost') . 
               ";port=" . ($config['DB_PORT'] ?? '3306') . 
               ";dbname=" . ($config['DB_DATABASE'] ?? '');
        
        $pdo = new PDO($dsn, $config['DB_USERNAME'] ?? '', $config['DB_PASSWORD'] ?? '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ <strong>Conexión exitosa</strong><br>";
        
        // Test de consulta simple
        $stmt = $pdo->query("SELECT VERSION() as version, NOW() as current_time");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "- MySQL Version: " . $result['version'] . "<br>";
        echo "- Server Time: " . $result['current_time'] . "<br>";
        
        // Verificar tablas de Laravel
        echo "<h3>📊 Tablas de Laravel</h3>";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $laravel_tables = ['users', 'migrations', 'failed_jobs', 'password_resets'];
        foreach ($laravel_tables as $table) {
            if (in_array($table, $tables)) {
                echo "✅ $table<br>";
            } else {
                echo "❌ $table (falta)<br>";
            }
        }
        
        echo "<p><strong>Total de tablas encontradas:</strong> " . count($tables) . "</p>";
        
    } catch (PDOException $e) {
        echo "❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "<br>";
        echo "<p>Verifica que:</p>";
        echo "<ul>";
        echo "<li>El servidor MySQL esté funcionando</li>";
        echo "<li>Las credenciales en .env sean correctas</li>";
        echo "<li>El usuario tenga permisos en la base de datos</li>";
        echo "<li>La base de datos exista</li>";
        echo "</ul>";
    }
    
} else {
    echo "❌ <strong>Archivo .env no encontrado</strong><br>";
    echo "Crea el archivo .env con la configuración de tu base de datos.";
}

echo "<hr>";
echo "<p><strong>✨ Test creado por Claude Code</strong></p>";
echo "<p>📅 " . date('Y-m-d H:i:s') . "</p>";
?>