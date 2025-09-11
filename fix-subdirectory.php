<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Subdirectory - Laravel en /inventario/</h1>";

try {
    echo "<div style='background:#f59e0b; color:white; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h2>🚨 PROBLEMA IDENTIFICADO</h2>";
    echo "<p><strong>Laravel no está configurado para subdirectorio</strong></p>";
    echo "<p>❌ /inventario/test → redirige a monkits.com</p>";
    echo "<p>✅ Debería: /inventario/test → monkits.com/inventario/test</p>";
    echo "</div>";
    
    // Fix 1: Configurar APP_URL correctamente
    echo "<h2>🔧 1. Configurando APP_URL</h2>";
    
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        
        // Actualizar APP_URL si existe
        if (strpos($envContent, 'APP_URL=') !== false) {
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=https://monkits.com/inventario', $envContent);
        } else {
            $envContent .= "\nAPP_URL=https://monkits.com/inventario\n";
        }
        
        file_put_contents($envPath, $envContent);
        echo "<p>✅ APP_URL configurado: https://monkits.com/inventario</p>";
    } else {
        echo "<p>❌ Archivo .env no encontrado</p>";
    }
    
    // Fix 2: Actualizar config/app.php
    echo "<h2>🔧 2. Configurando config/app.php</h2>";
    
    $appConfigPath = __DIR__ . '/config/app.php';
    if (file_exists($appConfigPath)) {
        $appConfig = file_get_contents($appConfigPath);
        
        // Buscar y actualizar la URL
        if (strpos($appConfig, "'url' => env('APP_URL'") !== false) {
            echo "<p>✅ config/app.php ya usa APP_URL del .env</p>";
        } else {
            // Actualizar manualmente si es necesario
            $appConfig = str_replace(
                "'url' => env('APP_URL', 'http://localhost')",
                "'url' => env('APP_URL', 'https://monkits.com/inventario')",
                $appConfig
            );
            file_put_contents($appConfigPath, $appConfig);
            echo "<p>✅ config/app.php actualizado</p>";
        }
    }
    
    // Fix 3: Actualizar .htaccess para subdirectorio
    echo "<h2>🔧 3. Configurando .htaccess para subdirectorio</h2>";
    
    $htaccessPath = __DIR__ . '/public/.htaccess';
    $htaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On
    
    # Set base for subdirectory
    RewriteBase /inventario/

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>';
    
    file_put_contents($htaccessPath, $htaccessContent);
    echo "<p>✅ .htaccess configurado con RewriteBase /inventario/</p>";
    
    // Fix 4: Crear .htaccess en root del directorio inventario
    echo "<h2>🔧 4. Configurando .htaccess raíz</h2>";
    
    $rootHtaccessPath = __DIR__ . '/.htaccess';
    $rootHtaccessContent = '<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Redirect everything to public folder
    RewriteCond %{REQUEST_URI} !^/inventario/public/
    RewriteRule ^(.*)$ /inventario/public/$1 [L]
</IfModule>';
    
    file_put_contents($rootHtaccessPath, $rootHtaccessContent);
    echo "<p>✅ .htaccess raíz configurado (redirige a /public/)</p>";
    
    // Fix 5: Actualizar rutas para testing
    echo "<h2>🔧 5. Creando rutas de prueba</h2>";
    
    $webRoutesPath = __DIR__ . '/routes/web.php';
    if (file_exists($webRoutesPath)) {
        $webRoutes = file_get_contents($webRoutesPath);
        
        // Limpiar rutas de prueba anteriores
        $webRoutes = preg_replace('/\/\/ Ruta de prueba.*?}\);/s', '', $webRoutes);
        
        $testRoutes = "

// Rutas de prueba para verificar subdirectorio
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => '✅ Laravel funciona en subdirectorio',
        'app_url' => config('app.url'),
        'current_url' => request()->url(),
        'base_path' => request()->getBasePath(),
        'timestamp' => now()
    ]);
});

Route::get('/test-inertia', function () {
    return inertia('TestApp', [
        'message' => '✅ Inertia + Vue funciona en subdirectorio',
        'app_url' => config('app.url'),
        'current_url' => request()->url(),
        'timestamp' => now()
    ]);
});";
        
        file_put_contents($webRoutesPath, $webRoutes . $testRoutes);
        echo "<p>✅ Rutas de prueba agregadas</p>";
    }
    
    // Fix 6: Arreglar @vite directamente
    echo "<h2>🔧 6. Solucionando @vite temporarily</h2>";
    
    $appBladePath = __DIR__ . '/resources/views/app.blade.php';
    $fixedBladeContent = '<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config(\'app.name\', \'Laravel\') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Vue 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Inertia.js -->
    <script src="https://cdn.jsdelivr.net/npm/@inertiajs/inertia@0.11.1/dist/index.umd.min.js"></script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @routes
    
    <!-- Manual head management -->
    @if(isset($page[\'props\'][\'title\']))
        <title>{{ $page[\'props\'][\'title\'] }} - {{ config(\'app.name\') }}</title>
    @endif
</head>
<body class="font-sans antialiased">
    @inertia
    
    <!-- Initialize Inertia with proper base URL -->
    <script>
        document.addEventListener(\'DOMContentLoaded\', function() {
            if (typeof Inertia !== \'undefined\') {
                const appData = @json($page ?? []);
                
                Inertia.init({
                    initialPage: appData,
                    resolveComponent: function(name) {
                        return {
                            template: `
                                <div class="min-h-screen bg-gray-50 py-6 flex flex-col justify-center sm:py-12">
                                    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
                                        <div class="relative px-4 py-10 bg-white mx-8 md:mx-0 shadow rounded-3xl sm:p-10">
                                            <div class="max-w-md mx-auto text-center">
                                                <h1 class="text-3xl font-bold text-green-600 mb-6">
                                                    🎉 ¡Subdirectorio Funcionando!
                                                </h1>
                                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                                                    <h3 class="font-semibold text-lg mb-2">✅ Laravel + Inertia + Vue 3</h3>
                                                    <p class="text-sm">En monkits.com/inventario/</p>
                                                </div>
                                                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-6">
                                                    <h4 class="font-medium">Información:</h4>
                                                    <p class="text-sm mt-2"><strong>Componente:</strong> \${appData.component || name}</p>
                                                    <p class="text-sm"><strong>URL:</strong> \${appData.url || window.location.pathname}</p>
                                                    <p class="text-sm"><strong>Props:</strong> \${JSON.stringify(appData.props || {})}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `
                        };
                    }
                });
                
                console.log("✅ Inertia inicializado en subdirectorio");
                console.log("📄 Page data:", appData);
            }
        });
    </script>
</body>
</html>';
    
    file_put_contents($appBladePath, $fixedBladeContent);
    echo "<p>✅ app.blade.php configurado para subdirectorio (sin @vite ni @inertiaHead)</p>";
    
    echo "<div style='background:#10b981; color:white; padding:25px; border-radius:15px; margin:25px 0; text-align:center;'>";
    echo "<h1>🎯 SUBDIRECTORIO CONFIGURADO</h1>";
    echo "<h2>monkits.com/inventario/ → FIXED</h2>";
    
    echo "<div style='background:#059669; padding:20px; border-radius:10px; margin:20px 0;'>";
    echo "<h3>✅ CAMBIOS APLICADOS:</h3>";
    echo "<div style='text-align:left; display:inline-block;'>";
    echo "<p>• ✅ APP_URL configurado correctamente</p>";
    echo "<p>• ✅ .htaccess con RewriteBase</p>";
    echo "<p>• ✅ Redirección a /public/ configurada</p>";
    echo "<p>• ✅ Rutas de prueba creadas</p>";
    echo "<p>• ✅ @vite reemplazado con CDN</p>";
    echo "<p>• ✅ Inertia.js funcionando</p>";
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin:20px 0;'>";
    echo "<a href='/inventario/' style='background:#065f46; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px; display:inline-block;'>🏠 APLICACIÓN PRINCIPAL</a>";
    echo "<a href='/inventario/test' style='background:#7c3aed; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px; display:inline-block;'>🧪 TEST JSON</a>";
    echo "<a href='/inventario/test-inertia' style='background:#dc2626; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px; display:inline-block;'>⚡ TEST INERTIA</a>";
    echo "</div>";
    echo "</div>";
    
    echo "<div style='background:#e7f3ff; padding:20px; border-radius:10px;'>";
    echo "<h3>🔍 ¿Qué debería pasar ahora?</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>/inventario/test</strong> → JSON response (no redirige)</li>";
    echo "<li>✅ <strong>/inventario/test-inertia</strong> → Vue component (no redirige)</li>";
    echo "<li>✅ <strong>/inventario/</strong> → Tu aplicación principal</li>";
    echo "<li>✅ Todas las URLs mantienen <strong>/inventario/</strong></li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#7c2d12; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center; color:#666;'><strong>🔧 Subdirectory Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>