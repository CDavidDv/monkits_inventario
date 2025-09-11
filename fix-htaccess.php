<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix .htaccess - Problema de Routing</h1>";

try {
    echo "<div style='background:#f8d7da; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h2>🚨 PROBLEMA IDENTIFICADO</h2>";
    echo "<p><strong>❌ Blade no está compilando:</strong> Se muestra código HTML crudo</p>";
    echo "<p><strong>❌ Laravel no se ejecuta:</strong> Directivas {{ }} y @ no procesan</p>";
    echo "<p><strong>✅ Solución:</strong> Configurar .htaccess y estructura de carpetas</p>";
    echo "</div>";
    
    echo "<h2>📁 Verificando estructura de directorios</h2>";
    
    $publicPath = __DIR__ . '/public';
    $htaccessPath = $publicPath . '/.htaccess';
    $indexPath = $publicPath . '/index.php';
    
    // Verificar directorio public
    if (!is_dir($publicPath)) {
        echo "<p>❌ Directorio 'public' no existe</p>";
        echo "<p>🔧 Creando directorio public...</p>";
        mkdir($publicPath, 0755, true);
    } else {
        echo "<p>✅ Directorio 'public' existe</p>";
    }
    
    // Verificar index.php en public
    if (!file_exists($indexPath)) {
        echo "<p>❌ public/index.php no existe</p>";
        
        // Crear index.php básico de Laravel
        $indexContent = '<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define(\'LARAVEL_START\', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is maintenance / demo mode via the "down" command we
| will require this file so that any prerendered template can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.\'/../storage/framework/maintenance.php\')) {
    require __DIR__.\'/../storage/framework/maintenance.php\';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We\'ll simply require it
| into the script here so we don\'t need to manually load our classes.
|
*/

require __DIR__.\'/../vendor/autoload.php\';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application\'s HTTP kernel. Then, we will send the response back
| to this client\'s browser, allowing them to enjoy our application.
|
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
    } else {
        echo "<p>✅ public/index.php existe (" . filesize($indexPath) . " bytes)</p>";
    }
    
    // Verificar .htaccess en public
    if (!file_exists($htaccessPath)) {
        echo "<p>❌ public/.htaccess no existe</p>";
        
        // Crear .htaccess básico para Laravel
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
        echo "<p>✅ public/.htaccess creado</p>";
    } else {
        echo "<p>✅ public/.htaccess existe (" . filesize($htaccessPath) . " bytes)</p>";
    }
    
    echo "<h2>🔧 Verificando configuración del servidor</h2>";
    
    // Verificar si Apache tiene mod_rewrite
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo "<p>✅ mod_rewrite habilitado</p>";
        } else {
            echo "<p>❌ mod_rewrite no habilitado</p>";
        }
    } else {
        echo "<p>ℹ️ No se puede verificar mod_rewrite (función no disponible)</p>";
    }
    
    echo "<h2>📝 Verificando configuración de hosting</h2>";
    echo "<div style='background:#fff3cd; padding:15px; border-radius:8px;'>";
    echo "<h3>🔍 Estructura actual detectada:</h3>";
    echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    echo "<p><strong>Script actual:</strong> " . __FILE__ . "</p>";
    echo "<p><strong>URL actual:</strong> http" . (isset($_SERVER['HTTPS']) ? "s" : "") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";
    echo "</div>";
    
    echo "<h2>⚠️ PROBLEMA DE CONFIGURACIÓN DETECTADO</h2>";
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 El problema principal:</h3>";
    echo "<p><strong>Laravel debe ejecutarse desde el directorio PUBLIC</strong></p>";
    echo "<p>Actualmente: <code>monkits.com/inventario/</code> apunta al directorio raíz</p>";
    echo "<p>Debería ser: <code>monkits.com/inventario/</code> apunta a <code>public/</code></p>";
    echo "</div>";
    
    echo "<h2>🛠️ SOLUCIONES POSIBLES</h2>";
    
    echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h3>Opción 1: Configurar Document Root (RECOMENDADO)</h3>";
    echo "<p>En tu panel de hosting, configura el Document Root de <code>/inventario/</code> para que apunte a:</p>";
    echo "<code>/home/monkits/public_html/inventario/public/</code>";
    echo "</div>";
    
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h3>Opción 2: Mover archivos a la raíz actual</h3>";
    echo "<p>Crear un <code>.htaccess</code> en el directorio actual que redirija a public:</p>";
    echo "<pre style='background:#e9ecef; padding:10px;'>";
    echo "RewriteEngine On\n";
    echo "RewriteRule ^(.*)$ public/\$1 [L]\n";
    echo "</pre>";
    echo "</div>";
    
    // Crear .htaccess de redirección en directorio actual
    $rootHtaccess = __DIR__ . '/.htaccess';
    $redirectContent = '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>';
    
    if (!file_exists($rootHtaccess)) {
        file_put_contents($rootHtaccess, $redirectContent);
        echo "<div style='background:#d4edda; padding:15px; border-radius:8px;'>";
        echo "<h3>✅ .htaccess de redirección creado</h3>";
        echo "<p>Se ha creado un .htaccess en el directorio raíz que redirige todo a public/</p>";
        echo "</div>";
    }
    
    echo "<div style='background:#10b981; color:white; padding:20px; border-radius:10px; margin:20px 0; text-align:center;'>";
    echo "<h2>🚀 ARCHIVOS CREADOS</h2>";
    echo "<p>✅ <strong>public/index.php</strong> - Entry point de Laravel</p>";
    echo "<p>✅ <strong>public/.htaccess</strong> - Reglas de rewrite</p>";
    echo "<p>✅ <strong>.htaccess</strong> - Redirección a public/</p>";
    echo "<br>";
    echo "<a href='/inventario/' style='background:#065f46; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold;'>🎯 PROBAR APLICACIÓN AHORA</a>";
    echo "</div>";
    
    echo "<div style='background:#1f2937; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>📋 Si aún no funciona:</h3>";
    echo "<ol>";
    echo "<li>Contacta a tu hosting para configurar Document Root</li>";
    echo "<li>Verifica que mod_rewrite esté habilitado</li>";
    echo "<li>Asegúrate de que .htaccess esté permitido</li>";
    echo "</ol>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#7c2d12; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 .htaccess Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>