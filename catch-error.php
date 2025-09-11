<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

echo "<h1>🔍 Capturador de Errores Laravel</h1>";
echo "<p>📋 Este script simula exactamente lo que hace <code>public/index.php</code></p>";

try {
    echo "<div style='background:#f3f4f6;padding:15px;border-radius:8px;margin:10px 0;'>";
    echo "<h2>📍 Paso a Paso:</h2>";
    
    // Paso 1: Autoloader
    echo "<p>🔄 Paso 1: Cargando autoloader...</p>";
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p>✅ Autoloader cargado</p>";
    
    // Paso 2: Bootstrap Laravel
    echo "<p>🔄 Paso 2: Bootstrap Laravel...</p>";
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "<p>✅ Bootstrap completado</p>";
    
    // Paso 3: Crear kernel HTTP
    echo "<p>🔄 Paso 3: Creando HTTP Kernel...</p>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p>✅ HTTP Kernel creado</p>";
    
    // Paso 4: Capturar request
    echo "<p>🔄 Paso 4: Capturando request...</p>";
    $request = Illuminate\Http\Request::capture();
    echo "<p>✅ Request capturado: " . $request->method() . " " . $request->path() . "</p>";
    
    // Paso 5: Procesar request (aquí puede fallar)
    echo "<p>🔄 Paso 5: Procesando request...</p>";
    $response = $kernel->handle($request);
    echo "<p>✅ Request procesado</p>";
    
    // Paso 6: Verificar response
    echo "<p>🔄 Paso 6: Verificando response...</p>";
    $statusCode = $response->getStatusCode();
    echo "<p>📊 Status Code: <strong>$statusCode</strong></p>";
    
    if ($statusCode === 200) {
        echo "<div style='background:#10b981;color:white;padding:20px;border-radius:10px;margin:20px 0;'>";
        echo "<h2>🎉 ¡APLICACIÓN FUNCIONA!</h2>";
        echo "<p>El request se procesó correctamente.</p>";
        echo "<p><a href='/inventario/' style='color:white;text-decoration:underline;font-weight:bold;'>» Probar Aplicación Real</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#f59e0b;color:white;padding:20px;border-radius:10px;margin:20px 0;'>";
        echo "<h2>⚠️ Response con Error</h2>";
        echo "<p>Status: $statusCode</p>";
        echo "<p>Contenido parcial:</p>";
        echo "<pre style='background:#000;color:#fff;padding:10px;border-radius:5px;overflow:auto;'>";
        echo htmlspecialchars(substr($response->getContent(), 0, 1000));
        echo "</pre>";
        echo "</div>";
    }
    
    // Cleanup
    $kernel->terminate($request, $response);
    
    echo "</div>";
    
    echo "<h2>🔍 Información Adicional</h2>";
    
    // Verificar .env
    echo "<h3>📄 Archivo .env</h3>";
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        echo "<p>✅ .env existe (" . filesize($envPath) . " bytes)</p>";
        
        // Mostrar algunas variables clave (sin valores sensibles)
        $envContent = file_get_contents($envPath);
        $envLines = explode("\n", $envContent);
        echo "<p><strong>Variables importantes:</strong></p>";
        echo "<ul>";
        foreach ($envLines as $line) {
            if (strpos($line, 'APP_') === 0 || strpos($line, 'DB_') === 0) {
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = $parts[0];
                    $value = strlen($parts[1]) > 0 ? '***' : '(vacío)';
                    echo "<li><code>$key</code> = $value</li>";
                }
            }
        }
        echo "</ul>";
    } else {
        echo "<p>❌ .env no existe</p>";
    }
    
    // Verificar conexión DB
    echo "<h3>🗄️ Base de Datos</h3>";
    try {
        $pdo = new PDO(
            "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'monkits_inventario'),
            $_ENV['DB_USERNAME'] ?? 'monkits',
            $_ENV['DB_PASSWORD'] ?? ''
        );
        echo "<p>✅ Conexión DB exitosa</p>";
        
        // Verificar tablas importantes
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>📊 Tablas encontradas: " . count($tables) . "</p>";
        if (in_array('users', $tables)) {
            echo "<p>✅ Tabla 'users' existe</p>";
        } else {
            echo "<p>⚠️ Tabla 'users' no existe (necesita migraciones)</p>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error DB: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626;color:white;padding:20px;border-radius:10px;margin:20px 0;'>";
    echo "<h2>🚨 ERROR CAPTURADO</h2>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background:#000;color:#0f0;padding:10px;border-radius:5px;overflow:auto;max-height:300px;'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
    echo "</div>";
} catch (Throwable $e) {
    echo "<div style='background:#7c2d12;color:white;padding:20px;border-radius:10px;margin:20px 0;'>";
    echo "<h2>💥 ERROR FATAL</h2>";
    echo "<p><strong>Tipo:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔍 Error Catcher por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>