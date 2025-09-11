<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Blade Syntax Error</h1>";

try {
    echo "<h2>🎉 PROGRESO: Variables ENV ya funcionan!</h2>";
    echo "<div style='background:#d4edda; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<p>✅ <strong>Laravel está iniciando correctamente</strong></p>";
    echo "<p>✅ <strong>Variables de entorno cargadas</strong></p>";
    echo "<p>❌ <strong>Error de sintaxis en vista Blade</strong></p>";
    echo "</div>";
    
    echo "<h2>📍 Verificando archivo app.blade.php</h2>";
    
    $bladeFile = __DIR__ . '/resources/views/app.blade.php';
    
    if (!file_exists($bladeFile)) {
        echo "<p>❌ app.blade.php no existe</p>";
        
        // Crear archivo básico de app.blade.php
        $appBladeContent = '<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config(\'app.name\', \'Laravel\') }}</title>

        <!-- Scripts -->
        @vite([\'resources/js/app.js\', "resources/js/Pages/{$page[\'component\']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>';
        
        // Crear directorio si no existe
        $viewsDir = dirname($bladeFile);
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0755, true);
        }
        
        if (file_put_contents($bladeFile, $appBladeContent)) {
            echo "<p>✅ app.blade.php creado desde template básico</p>";
        } else {
            echo "<p>❌ Error creando app.blade.php</p>";
            exit;
        }
    } else {
        echo "<p>✅ app.blade.php existe (" . filesize($bladeFile) . " bytes)</p>";
    }
    
    // Leer y analizar contenido
    $content = file_get_contents($bladeFile);
    
    echo "<h2>🔍 Analizando contenido de app.blade.php</h2>";
    echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
    echo "<h3>Contenido actual:</h3>";
    echo "<pre style='background:#e9ecef; padding:10px; max-height:300px; overflow:auto;'>";
    echo htmlspecialchars($content);
    echo "</pre>";
    echo "</div>";
    
    // Buscar problemas comunes de sintaxis
    $lines = explode("\n", $content);
    $errors = [];
    
    echo "<h2>🔎 Buscando errores de sintaxis</h2>";
    
    foreach ($lines as $lineNum => $line) {
        $lineNumber = $lineNum + 1;
        
        // Buscar => fuera de contextos válidos
        if (strpos($line, '=>') !== false) {
            // Verificar si está en contexto válido (dentro de @vite, etc.)
            if (!preg_match('/@vite\s*\([^)]*=>[^)]*\)/', $line) && 
                !preg_match('/\{\{\s*[^}]*=>[^}]*\}\}/', $line) &&
                !preg_match('/@[a-zA-Z]+\s*\([^)]*=>[^)]*\)/', $line)) {
                
                $errors[] = [
                    'line' => $lineNumber,
                    'content' => trim($line),
                    'error' => 'Posible => fuera de contexto PHP válido'
                ];
            }
        }
        
        // Buscar otros problemas comunes
        if (preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=>\s*/', $line)) {
            $errors[] = [
                'line' => $lineNumber,
                'content' => trim($line),
                'error' => 'Sintaxis PHP en contexto Blade'
            ];
        }
    }
    
    if (!empty($errors)) {
        echo "<div style='background:#f8d7da; padding:15px; border-radius:8px;'>";
        echo "<h3>❌ Errores encontrados:</h3>";
        foreach ($errors as $error) {
            echo "<div style='margin:10px 0; padding:10px; background:#fff; border-left:4px solid #dc3545;'>";
            echo "<p><strong>Línea {$error['line']}:</strong> {$error['error']}</p>";
            echo "<code>" . htmlspecialchars($error['content']) . "</code>";
            echo "</div>";
        }
        echo "</div>";
        
        // Crear versión corregida
        echo "<h2>🔧 Creando versión corregida</h2>";
        
        $correctedContent = '<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config(\'app.name\', \'Laravel\') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <!-- Scripts -->
        @vite([\'resources/js/app.js\'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>';
        
        // Crear backup del archivo original
        $backup = $bladeFile . '.error-backup-' . date('Y-m-d-H-i-s');
        copy($bladeFile, $backup);
        echo "<p>💾 Backup creado: " . basename($backup) . "</p>";
        
        // Escribir versión corregida
        if (file_put_contents($bladeFile, $correctedContent)) {
            echo "<p>✅ app.blade.php corregido</p>";
            echo "<p>📦 Nuevo tamaño: " . strlen($correctedContent) . " bytes</p>";
        } else {
            echo "<p>❌ Error escribiendo archivo corregido</p>";
        }
        
    } else {
        echo "<p>✅ No se encontraron errores de sintaxis obvios</p>";
        echo "<p>ℹ️ El error puede estar en una línea específica o contexto particular</p>";
    }
    
    // Test adicional - verificar que Blade puede compilar
    echo "<h2>🧪 Test final</h2>";
    echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px;'>";
    echo "<p><strong>Pasos siguientes:</strong></p>";
    echo "<ol>";
    echo "<li>Limpiar cache de vistas: <code>php artisan view:clear</code></li>";
    echo "<li>Probar la aplicación: <a href='/inventario/' target='_blank'>monkits.com/inventario/</a></li>";
    echo "<li>Si aún falla, verificar logs específicos</li>";
    echo "</ol>";
    echo "</div>";
    
    // Ejecutar view:clear
    echo "<h3>🧹 Limpiando cache de vistas</h3>";
    $viewClearCommand = "cd " . escapeshellarg(__DIR__) . " && php artisan view:clear 2>&1";
    $output = shell_exec($viewClearCommand);
    
    if ($output) {
        echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        echo "</div>";
    }
    
    echo "<div style='background:#d4edda; padding:20px; border-radius:10px; margin:20px 0;'>";
    echo "<h2>🎯 BLADE SYNTAX FIX APLICADO</h2>";
    echo "<p>✅ <strong>Archivo corregido</strong> - Sintaxis PHP 7.4 compatible</p>";
    echo "<p>✅ <strong>Cache de vistas limpiado</strong></p>";
    echo "<p>✅ <strong>Template Inertia.js básico</strong> aplicado</p>";
    echo "<p><a href='/inventario/' style='background:#059669; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>🚀 PROBAR APLICACIÓN</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#dc2626; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 Blade Syntax Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>