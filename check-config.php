<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico Configuración Laravel</h1>";

echo "<h2>📁 Archivos de Configuración Requeridos:</h2>";

$requiredFiles = [
    'config/app.php' => 'Configuración principal de la aplicación',
    'config/view.php' => 'Configuración del sistema de vistas',
    'config/database.php' => 'Configuración de base de datos',
    'config/cache.php' => 'Configuración de cache',
    'config/filesystems.php' => 'Configuración de archivos',
    'config/logging.php' => 'Configuración de logs',
    'config/mail.php' => 'Configuración de correo',
    'config/queue.php' => 'Configuración de colas',
    'config/session.php' => 'Configuración de sesiones',
    'bootstrap/cache/config.php' => 'Cache de configuración',
    'bootstrap/cache/services.php' => 'Cache de servicios',
];

$missing = [];
$existing = [];

echo "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse:collapse;'>";
echo "<tr style='background:#f5f5f5;'>";
echo "<th>Archivo</th><th>Estado</th><th>Descripción</th>";
echo "</tr>";

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✅ Existe' : '❌ Falta';
    $color = $exists ? '#d4edda' : '#f8d7da';
    
    echo "<tr style='background:$color;'>";
    echo "<td><code>$file</code></td>";
    echo "<td>$status</td>";
    echo "<td>$description</td>";
    echo "</tr>";
    
    if (!$exists) {
        $missing[] = $file;
    } else {
        $existing[] = $file;
    }
}
echo "</table>";

echo "<h2>📊 Resumen:</h2>";
echo "<p><strong>✅ Archivos presentes:</strong> " . count($existing) . "</p>";
echo "<p><strong>❌ Archivos faltantes:</strong> " . count($missing) . "</p>";

if (!empty($missing)) {
    echo "<div style='background:#f8d7da; padding:20px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>🚨 Archivos Críticos Faltantes:</h3>";
    echo "<ul>";
    foreach ($missing as $file) {
        echo "<li><code>$file</code></li>";
    }
    echo "</ul>";
    echo "<p><strong>Solución:</strong> Necesitas subir estos archivos de configuración desde tu proyecto local.</p>";
    echo "</div>";
}

// Verificar .env
echo "<h2>🔧 Archivo .env:</h2>";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "<p>✅ .env existe</p>";
    
    // Verificar variables críticas
    $envContent = file_get_contents($envPath);
    $criticalVars = ['APP_KEY', 'APP_ENV', 'APP_DEBUG'];
    
    echo "<h3>Variables críticas:</h3>";
    foreach ($criticalVars as $var) {
        if (strpos($envContent, $var . '=') !== false) {
            echo "<p>✅ $var definida</p>";
        } else {
            echo "<p>❌ $var faltante</p>";
        }
    }
} else {
    echo "<p>❌ .env no existe</p>";
}

// Verificar permisos de directorios
echo "<h2>📂 Directorios y Permisos:</h2>";
$directories = [
    'bootstrap/cache',
    'storage/app',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs'
];

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath);
        $status = $writable ? '✅ Escribible' : '❌ No escribible';
        echo "<p>$status <code>$dir</code></p>";
    } else {
        echo "<p>❌ No existe <code>$dir</code></p>";
    }
}

// Test de carga de configuración
echo "<h2>🧪 Test de Configuración:</h2>";
try {
    if (file_exists(__DIR__ . '/config/app.php')) {
        $appConfig = include __DIR__ . '/config/app.php';
        if (is_array($appConfig) && isset($appConfig['providers'])) {
            echo "<p>✅ config/app.php carga correctamente</p>";
            echo "<p>📦 Service Providers definidos: " . count($appConfig['providers']) . "</p>";
        } else {
            echo "<p>❌ config/app.php tiene formato incorrecto</p>";
        }
    } else {
        echo "<p>❌ config/app.php no existe</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error cargando configuración: " . $e->getMessage() . "</p>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔍 Config Check por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>