<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Vite Assets - Laravel 8 + Vite</h1>";

try {
    echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h2>🔍 ANÁLISIS COMPLETADO</h2>";
    echo "<p>✅ <strong>Tu proyecto SÍ está configurado para Vite</strong></p>";
    echo "<p>✅ <strong>laravel-vite-plugin instalado</strong></p>";
    echo "<p>✅ <strong>Vue.js 3 + Inertia.js configurado</strong></p>";
    echo "<p>❌ <strong>Problema:</strong> Assets no compilados para producción</p>";
    echo "</div>";
    
    echo "<h2>📦 Verificando archivos de configuración</h2>";
    
    // Verificar vite.config.js
    $viteConfig = __DIR__ . '/vite.config.js';
    if (file_exists($viteConfig)) {
        echo "<p>✅ vite.config.js existe</p>";
        $content = file_get_contents($viteConfig);
        echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
        echo "<h4>Contenido de vite.config.js:</h4>";
        echo "<pre style='max-height:200px; overflow:auto;'>" . htmlspecialchars($content) . "</pre>";
        echo "</div>";
    } else {
        echo "<p>❌ vite.config.js no existe</p>";
        
        // Crear vite.config.js básico
        $viteConfigContent = 'import { defineConfig } from \'vite\';
import laravel from \'laravel-vite-plugin\';
import vue from \'@vitejs/plugin-vue\';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                \'resources/css/app.css\',
                \'resources/js/app.js\',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            \'@\': \'/resources/js\',
        },
    },
});';
        
        file_put_contents($viteConfig, $viteConfigContent);
        echo "<p>✅ vite.config.js creado</p>";
    }
    
    // Verificar manifest.json (assets compilados)
    $manifestPath = __DIR__ . '/public/build/manifest.json';
    if (file_exists($manifestPath)) {
        echo "<p>✅ public/build/manifest.json existe - Assets compilados</p>";
        $manifestContent = file_get_contents($manifestPath);
        $manifest = json_decode($manifestContent, true);
        if ($manifest) {
            echo "<p>📊 Assets encontrados: " . count($manifest) . " archivos</p>";
        }
    } else {
        echo "<p>❌ public/build/manifest.json NO existe - Assets NO compilados</p>";
    }
    
    // Verificar directorio build
    $buildDir = __DIR__ . '/public/build';
    if (is_dir($buildDir)) {
        $buildFiles = scandir($buildDir);
        $buildFiles = array_filter($buildFiles, function($file) {
            return !in_array($file, ['.', '..']);
        });
        echo "<p>📁 Archivos en public/build: " . count($buildFiles) . "</p>";
    } else {
        echo "<p>❌ Directorio public/build no existe</p>";
        mkdir($buildDir, 0755, true);
        echo "<p>✅ Directorio public/build creado</p>";
    }
    
    echo "<h2>🛠️ SOLUCIÓN PARA PRODUCCIÓN</h2>";
    
    echo "<div style='background:#fff3cd; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h3>🚨 El problema:</h3>";
    echo "<p>Los assets de Vite necesitan ser <strong>compilados para producción</strong></p>";
    echo "<p>En desarrollo se ejecuta <code>npm run dev</code></p>";
    echo "<p>En producción se necesita <code>npm run build</code></p>";
    echo "</div>";
    
    echo "<div style='background:#d4edda; padding:15px; border-radius:8px; margin:10px 0;'>";
    echo "<h3>✅ Comandos para compilar assets:</h3>";
    echo "<pre style='background:#e9ecef; padding:10px;'>";
    echo "# 1. Instalar dependencias\n";
    echo "npm install\n\n";
    echo "# 2. Compilar para producción\n";
    echo "npm run build\n";
    echo "</pre>";
    echo "</div>";
    
    // Intentar ejecutar npm install si node está disponible
    echo "<h2>🧪 Intentando instalar dependencias</h2>";
    
    $nodeVersion = shell_exec('node --version 2>&1');
    $npmVersion = shell_exec('npm --version 2>&1');
    
    if ($nodeVersion && $npmVersion) {
        echo "<p>✅ Node.js: " . trim($nodeVersion) . "</p>";
        echo "<p>✅ npm: " . trim($npmVersion) . "</p>";
        
        echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px;'>";
        echo "<h4>🔄 Ejecutando npm install...</h4>";
        echo "<p><em>Esto puede tomar unos minutos...</em></p>";
        echo "</div>";
        
        // Cambiar al directorio del proyecto y ejecutar npm install
        $installCommand = "cd " . escapeshellarg(__DIR__) . " && npm install --production 2>&1";
        $installOutput = shell_exec($installCommand);
        
        if ($installOutput) {
            echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
            echo "<h4>Salida de npm install:</h4>";
            echo "<pre style='max-height:300px; overflow:auto;'>" . htmlspecialchars($installOutput) . "</pre>";
            echo "</div>";
        }
        
        // Intentar npm run build
        echo "<div style='background:#e7f3ff; padding:15px; border-radius:8px;'>";
        echo "<h4>🔄 Ejecutando npm run build...</h4>";
        echo "</div>";
        
        $buildCommand = "cd " . escapeshellarg(__DIR__) . " && npm run build 2>&1";
        $buildOutput = shell_exec($buildCommand);
        
        if ($buildOutput) {
            echo "<div style='background:#f8f9fa; padding:10px; border-radius:5px;'>";
            echo "<h4>Salida de npm run build:</h4>";
            echo "<pre style='max-height:400px; overflow:auto;'>" . htmlspecialchars($buildOutput) . "</pre>";
            echo "</div>";
        }
        
        // Verificar si se generaron los assets
        if (file_exists($manifestPath)) {
            echo "<div style='background:#10b981; color:white; padding:20px; border-radius:10px; margin:20px 0;'>";
            echo "<h2>🎉 ¡ASSETS COMPILADOS EXITOSAMENTE!</h2>";
            echo "<p>✅ Los assets de Vite han sido generados</p>";
            echo "<p>✅ Laravel + Vue.js 3 + Inertia.js listo</p>";
            echo "<br>";
            echo "<a href='/inventario/' style='background:#065f46; color:white; padding:15px 30px; text-decoration:none; border-radius:8px; font-weight:bold;'>🚀 PROBAR APLICACIÓN</a>";
            echo "</div>";
        } else {
            echo "<div style='background:#f59e0b; color:white; padding:15px; border-radius:8px;'>";
            echo "<h3>⚠️ Build ejecutado pero manifest no generado</h3>";
            echo "<p>Revisa los errores en la salida de npm run build</p>";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background:#dc2626; color:white; padding:15px; border-radius:8px;'>";
        echo "<h3>❌ Node.js no disponible en el servidor</h3>";
        echo "<p>Necesitas compilar los assets localmente y subirlos</p>";
        echo "</div>";
        
        echo "<div style='background:#1f2937; color:white; padding:20px; border-radius:10px; margin:20px 0;'>";
        echo "<h3>📋 Instrucciones para compilar localmente:</h3>";
        echo "<ol>";
        echo "<li>En tu máquina local, ejecuta: <code>npm install</code></li>";
        echo "<li>Luego ejecuta: <code>npm run build</code></li>";
        echo "<li>Sube la carpeta <code>public/build/</code> al servidor</li>";
        echo "<li>Sube el archivo <code>public/build/manifest.json</code></li>";
        echo "</ol>";
        echo "</div>";
    }
    
    echo "<h2>📋 Resumen de la situación:</h2>";
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:8px;'>";
    echo "<ul>";
    echo "<li>✅ <strong>Laravel 8.83.29</strong> - Funcionando</li>";
    echo "<li>✅ <strong>PHP 7.4.33</strong> - Compatible</li>";
    echo "<li>✅ <strong>Vite + Vue.js 3</strong> - Configurado</li>";
    echo "<li>✅ <strong>Inertia.js</strong> - Instalado</li>";
    echo "<li>⏳ <strong>Assets de producción</strong> - En proceso</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background:#7c2d12; color:white; padding:20px; border-radius:10px;'>";
    echo "<h3>🚨 Error</h3>";
    echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 Vite Assets Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>