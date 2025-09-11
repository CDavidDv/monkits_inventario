<?php
// Configuración inicial para mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

echo "<h1>🔬 DIAGNÓSTICO FINAL - ÚLTIMO INTENTO</h1>";

try {
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h2>📋 Paso a Paso - Diagnóstico Completo</h2>";
    
    // Paso 1: Información del servidor
    echo "<h3>🖥️ Paso 1: Información del Servidor</h3>";
    echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
    echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No definido') . "</p>";
    echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    echo "<p><strong>Script Filename:</strong> " . __FILE__ . "</p>";
    
    // Paso 2: Verificar archivos críticos
    echo "<h3>📁 Paso 2: Archivos Críticos</h3>";
    $criticalFiles = [
        'vendor/autoload.php',
        'bootstrap/app.php',
        '.env',
        'bootstrap/cache/config.php',
        'app/Http/Kernel.php'
    ];
    
    foreach ($criticalFiles as $file) {
        $fullPath = __DIR__ . '/' . $file;
        if (file_exists($fullPath)) {
            echo "<p>✅ $file (" . filesize($fullPath) . " bytes)</p>";
        } else {
            echo "<p>❌ $file NO EXISTE</p>";
        }
    }
    
    // Paso 3: Test autoloader
    echo "<h3>📦 Paso 3: Test Autoloader</h3>";
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        echo "<p>✅ Autoloader cargado exitosamente</p>";
    } else {
        echo "<p>❌ vendor/autoload.php no existe</p>";
        exit;
    }
    
    // Paso 4: Test bootstrap (CRÍTICO - aquí puede fallar)
    echo "<h3>🚀 Paso 4: Test Bootstrap Laravel</h3>";
    try {
        $app = require_once __DIR__ . '/bootstrap/app.php';
        echo "<p>✅ Bootstrap Laravel exitoso</p>";
        
        // Verificar que $app sea una instancia válida
        if ($app instanceof Illuminate\Foundation\Application) {
            echo "<p>✅ Application instance válida</p>";
        } else {
            echo "<p>❌ Application instance inválida: " . gettype($app) . "</p>";
        }
        
    } catch (Throwable $e) {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h4>🚨 ERROR EN BOOTSTRAP</h4>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre style='background:#000; color:#0f0; padding:10px; max-height:200px; overflow:auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</div>";
        exit;
    }
    
    // Paso 5: Test Environment
    echo "<h3>🔧 Paso 5: Test Variables de Entorno</h3>";
    $envVars = ['APP_ENV', 'APP_KEY', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($envVars as $var) {
        $value = env($var);
        if ($value) {
            if ($var === 'APP_KEY' || $var === 'DB_USERNAME') {
                echo "<p>✅ $var = " . str_repeat('*', strlen($value)) . "</p>";
            } else {
                echo "<p>✅ $var = $value</p>";
            }
        } else {
            echo "<p>⚠️ $var = (no definido)</p>";
        }
    }
    
    // Paso 6: Test Kernel (AQUÍ PUEDE FALLAR)
    echo "<h3>⚙️ Paso 6: Test HTTP Kernel</h3>";
    try {
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        echo "<p>✅ HTTP Kernel creado exitosamente</p>";
    } catch (Throwable $e) {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h4>🚨 ERROR EN KERNEL</h4>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "</div>";
        exit;
    }
    
    // Paso 7: Test Request básico
    echo "<h3>📥 Paso 7: Test Request</h3>";
    try {
        $request = Illuminate\Http\Request::create('/', 'GET');
        echo "<p>✅ Request creado: " . $request->method() . " " . $request->path() . "</p>";
    } catch (Throwable $e) {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h4>🚨 ERROR EN REQUEST</h4>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
        exit;
    }
    
    // Paso 8: Test Handle Request (CRÍTICO)
    echo "<h3>🔄 Paso 8: Test Handle Request</h3>";
    try {
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        echo "<p>📊 Response Status: <strong>$statusCode</strong></p>";
        
        if ($statusCode === 200) {
            echo "<div style='background:#10b981; color:white; padding:20px; border-radius:10px;'>";
            echo "<h2>🎉 ¡APLICACIÓN FUNCIONA!</h2>";
            echo "<p>La aplicación está procesando requests correctamente.</p>";
            echo "</div>";
        } else {
            echo "<div style='background:#f59e0b; color:white; padding:15px; border-radius:8px;'>";
            echo "<h4>⚠️ Response con Status: $statusCode</h4>";
            echo "<p>Contenido (primeros 500 chars):</p>";
            echo "<pre style='background:#000; color:#fff; padding:10px; max-height:200px; overflow:auto;'>";
            echo htmlspecialchars(substr($response->getContent(), 0, 500));
            echo "</pre>";
            echo "</div>";
        }
        
        // Cleanup
        $kernel->terminate($request, $response);
        
    } catch (Throwable $e) {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h4>🚨 ERROR AL PROCESAR REQUEST</h4>";
        echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        
        // Stack trace más detallado
        echo "<details>";
        echo "<summary>🔍 Ver Stack Trace Completo</summary>";
        echo "<pre style='background:#000; color:#0f0; padding:10px; max-height:300px; overflow:auto;'>";
        echo htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
        echo "</details>";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Throwable $e) {
    echo "<div style='background:#7c2d12; color:white; padding:20px; border-radius:10px;'>";
    echo "<h2>💥 ERROR FATAL EN DIAGNÓSTICO</h2>";
    echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔬 Final Diagnosis por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>