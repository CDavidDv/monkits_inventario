<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico del Servidor</h2>";

// 1. Información PHP
echo "<h3>📋 Información PHP</h3>";
echo "Versión PHP: " . PHP_VERSION . "<br>";
echo "Memoria límite: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "<br>";

// 2. Extensiones requeridas
echo "<h3>🔧 Extensiones PHP</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
foreach ($required_extensions as $ext) {
    echo "- $ext: " . (extension_loaded($ext) ? "✅ OK" : "❌ FALTA") . "<br>";
}

// 3. Verificar archivos y permisos
echo "<h3>📁 Archivos y Permisos</h3>";
$files_to_check = [
    '.env' => 'Archivo de configuración',
    'vendor/autoload.php' => 'Autoloader Composer',
    'storage/logs' => 'Directorio de logs',
    'storage/framework/sessions' => 'Directorio de sesiones',
    'bootstrap/cache' => 'Cache de bootstrap'
];

foreach ($files_to_check as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $perms = is_readable($path) && is_writable($path) ? "✅ R/W" : "⚠️ Permisos";
        echo "- $desc: ✅ Existe ($perms)<br>";
    } else {
        echo "- $desc: ❌ No existe<br>";
    }
}

// 4. Verificar Composer
echo "<h3>📦 Composer</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "- Autoloader: ✅ Cargado<br>";
} else {
    echo "- Autoloader: ❌ No encontrado<br>";
    exit("Error: Ejecuta 'composer install' en el servidor");
}

// 5. Verificar Laravel
echo "<h3>🚀 Laravel</h3>";
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "- Bootstrap: ✅ OK<br>";
    
    // Verificar .env
    if (file_exists(__DIR__ . '/.env')) {
        echo "- Archivo .env: ✅ Existe<br>";
    } else {
        echo "- Archivo .env: ❌ Falta<br>";
    }
    
} catch (Exception $e) {
    echo "- Bootstrap: ❌ Error: " . $e->getMessage() . "<br>";
}

// 6. Verificar Base de Datos
echo "<h3>🗄️ Base de Datos</h3>";
try {
    if (file_exists(__DIR__ . '/.env')) {
        $env = file_get_contents(__DIR__ . '/.env');
        preg_match('/DB_HOST=(.*)/', $env, $host);
        preg_match('/DB_DATABASE=(.*)/', $env, $database);
        preg_match('/DB_USERNAME=(.*)/', $env, $username);
        
        echo "- Host: " . ($host[1] ?? 'No configurado') . "<br>";
        echo "- Database: " . ($database[1] ?? 'No configurado') . "<br>";
        echo "- Username: " . ($username[1] ?? 'No configurado') . "<br>";
    }
} catch (Exception $e) {
    echo "- Error leyendo .env: " . $e->getMessage() . "<br>";
}

// 7. Logs de Laravel
echo "<h3>📄 Últimos Logs</h3>";
$log_file = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($log_file)) {
    $logs = file_get_contents($log_file);
    $recent_logs = substr($logs, -100000); // Últimos 5KB
    echo "<pre style='background:#f5f5f5; padding:10px; max-height:300px; overflow:auto;'>";
    echo htmlspecialchars($recent_logs);
    echo "</pre>";
} else {
    echo "❌ No se encontró archivo de log<br>";
}

echo "<hr>";
echo "<p><strong>✨ Creado por Claude Code</strong></p>";
echo "<p>📅 " . date('Y-m-d H:i:s') . "</p>";
?>