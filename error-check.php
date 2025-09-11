<?php
// Script super básico para detectar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

echo "<h1>🔍 Detector de Errores Básico</h1>";
echo "<p>Versión PHP: " . PHP_VERSION . "</p>";

// 1. Comprobar archivos básicos
echo "<h2>📁 Verificando archivos...</h2>";

$files_to_check = [
    'vendor/autoload.php',
    'vendor/composer/autoload_real.php', 
    'vendor/composer/autoload_files.php',
    'vendor/composer/ClassLoader.php',
    'vendor/ramsey/uuid/src/functions.php'
];

foreach ($files_to_check as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p>✅ $file existe</p>";
    } else {
        echo "<p>❌ $file NO EXISTE</p>";
    }
}

// 2. Intentar cargar autoloader paso a paso
echo "<h2>🔄 Probando autoloader...</h2>";

try {
    echo "<p>Paso 1: Intentando incluir autoload.php...</p>";
    
    // Solo incluir, no ejecutar nada más
    $content = file_get_contents(__DIR__ . '/vendor/autoload.php');
    echo "<p>✅ Contenido del autoload.php leído (" . strlen($content) . " bytes)</p>";
    
    // Buscar el hash en el contenido
    if (preg_match('/ComposerAutoloaderInit([a-f0-9]+)/', $content, $matches)) {
        $hash = $matches[1];
        echo "<p>🎯 Hash encontrado: <code>$hash</code></p>";
        
        // Verificar si el archivo autoload_real.php tiene este hash
        $real_file = __DIR__ . '/vendor/composer/autoload_real.php';
        if (file_exists($real_file)) {
            $real_content = file_get_contents($real_file);
            if (strpos($real_content, $hash) !== false) {
                echo "<p>✅ Hash coincide en autoload_real.php</p>";
            } else {
                echo "<p>❌ Hash NO coincide en autoload_real.php</p>";
                echo "<p>🔧 Esto confirma que necesitas el fix</p>";
            }
        }
    } else {
        echo "<p>❌ No se pudo encontrar hash en autoload.php</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>📍 En: " . $e->getFile() . ":" . $e->getLine() . "</p>";
} catch (ParseError $e) {
    echo "<p>❌ Error de sintaxis: " . $e->getMessage() . "</p>";
    echo "<p>📍 En: " . $e->getFile() . ":" . $e->getLine() . "</p>";
} catch (Throwable $e) {
    echo "<p>❌ Error crítico: " . $e->getMessage() . "</p>";
    echo "<p>📍 En: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><strong>📋 Resultado:</strong> Si ves este mensaje, el diagnóstico funcionó</p>";
echo "<p>📅 " . date('Y-m-d H:i:s') . "</p>";
?>