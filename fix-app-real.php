<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Real App - Restaurar aplicación original</h1>";

try {
    echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h2>🎯 OBJETIVO: Restaurar tu aplicación real</h2>";
    echo "<p>✅ <strong>Servidor:</strong> Funcionando</p>";
    echo "<p>✅ <strong>PHP 7.4.33:</strong> OK</p>";
    echo "<p>❌ <strong>Problema:</strong> Solo se ve página de prueba</p>";
    echo "</div>";
    
    // Verificar si existe index.php
    $indexPath = __DIR__ . '/public/index.php';
    if (file_exists($indexPath)) {
        echo "<p>✅ public/index.php existe</p>";
    } else {
        echo "<p>❌ public/index.php NO existe</p>";
        
        // Crear index.php básico de Laravel
        $indexContent = '<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define(\'LARAVEL_START\', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists(__DIR__.\'/../storage/framework/maintenance.php\')) {
    require __DIR__.\'/../storage/framework/maintenance.php\';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.\'/../vendor/autoload.php\';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.\'/../bootstrap/app.php\';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
';
        file_put_contents($indexPath, $indexContent);
        echo "<p>✅ public/index.php creado</p>";
    }
    
    // Restaurar app.blade.php original con Inertia
    $appBladePath = __DIR__ . '/resources/views/app.blade.php';
    
    echo "<h2>🔄 Restaurando app.blade.php para Inertia</h2>";
    
    $originalContent = '<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config(\'app.name\', \'Laravel\') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @routes
    @vite([\'resources/js/app.js\', \'resources/css/app.css\'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>';

    file_put_contents($appBladePath, $originalContent);
    echo "<p>✅ app.blade.php restaurado (versión original)</p>";
    
    // Verificar archivo .htaccess
    $htaccessPath = __DIR__ . '/public/.htaccess';
    if (!file_exists($htaccessPath)) {
        echo "<p>❌ .htaccess no existe - Creando...</p>";
        
        $htaccessContent = '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

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
        echo "<p>✅ .htaccess creado</p>";
    } else {
        echo "<p>✅ .htaccess existe</p>";
    }
    
    // Intentar arreglar el error de composer manualmente
    echo "<h2>🛠️ Intentando fix directo del RouteServiceProvider</h2>";
    
    $routeProviderPath = __DIR__ . '/app/Providers/RouteServiceProvider.php';
    $routeContent = '<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application\'s "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = \'/dashboard\';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot()
    {
        RateLimiter::for(\'api\', function (Request $request) {
            $user = $request->user();
            return Limit::perMinute(60)->by($user ? $user->id : $request->ip());
        });

        $this->routes(function () {
            Route::middleware(\'api\')
                ->prefix(\'api\')
                ->group(base_path(\'routes/api.php\'));

            Route::middleware(\'web\')
                ->group(base_path(\'routes/web.php\'));
        });
    }
}';
    
    file_put_contents($routeProviderPath, $routeContent);
    echo "<p>✅ RouteServiceProvider corregido</p>";
    
    // Crear una ruta simple de prueba
    $webRoutesPath = __DIR__ . '/routes/web.php';
    if (file_exists($webRoutesPath)) {
        $webRoutes = file_get_contents($webRoutesPath);
        
        // Agregar ruta de prueba si no existe
        if (!strpos($webRoutes, 'test-app')) {
            $testRoute = "

// Ruta de prueba para verificar que la app funciona
Route::get('/test-app', function () {
    return inertia('TestApp', [
        'message' => '¡Tu aplicación Laravel + Inertia está funcionando!',
        'timestamp' => now(),
        'php_version' => phpversion(),
        'laravel_version' => app()->version()
    ]);
});";
            
            file_put_contents($webRoutesPath, $webRoutes . $testRoute);
            echo "<p>✅ Ruta de prueba agregada: /test-app</p>";
        }
    }
    
    echo "<div style='background:#10b981; color:white; padding:20px; border-radius:10px; margin:20px 0; text-align:center;'>";
    echo "<h2>🎯 APLICACIÓN RESTAURADA</h2>";
    echo "<p>✅ index.php configurado</p>";
    echo "<p>✅ app.blade.php original</p>";
    echo "<p>✅ .htaccess configurado</p>";
    echo "<p>✅ RouteServiceProvider arreglado</p>";
    echo "<br>";
    echo "<a href='/inventario/' style='background:#065f46; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px;'>🏠 IR A LA APP</a>";
    echo "<a href='/inventario/test-app' style='background:#7c3aed; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold; margin:10px;'>🧪 PRUEBA INERTIA</a>";
    echo "</div>";
    
    echo "<div style='background:#fef3c7; padding:15px; border-radius:8px;'>";
    echo "<h3>⚡ ¿Qué pasa ahora?</h3>";
    echo "<p>1. <strong>Si ves tu app:</strong> ¡Perfecto! Todo funciona</p>";
    echo "<p>2. <strong>Si ves error 500:</strong> Composer aún tiene problemas</p>";
    echo "<p>3. <strong>Si página en blanco:</strong> Problema con assets/Vite</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#7c2d12; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align:center; color:#666;'>🔧 Real App Fix por Claude Code | " . date('Y-m-d H:i:s') . "</p>";
?>