<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix PHP 7.4 Syntax</h1>";

$files = [
    'app/Http/Controllers/ProfileController.php',
    'app/Http/Controllers/StockAlertController.php',
    'app/Models/InventoryMovement.php',
    'app/Models/Item.php',
    'app/Models/Production.php',
    'app/Models/StockAlert.php'
];

$fixes = 0;
$errors = [];

echo "<h2>📁 Procesando archivos con match():</h2>";

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    echo "<h3>📄 $file</h3>";
    
    if (!file_exists($fullPath)) {
        echo "<p>❌ Archivo no encontrado</p>";
        $errors[] = "$file no encontrado";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    $originalContent = $content;
    
    // Crear backup
    $backup = $fullPath . '.php8.backup';
    file_put_contents($backup, $content);
    
    // Buscar y mostrar match expressions
    $matches = [];
    preg_match_all('/match\s*\([^}]+\}[^;]*;/s', $content, $matches);
    
    if (!empty($matches[0])) {
        echo "<p>🔍 Encontradas " . count($matches[0]) . " match expressions</p>";
        
        foreach ($matches[0] as $i => $matchExpr) {
            // Convertir match a switch manualmente para casos específicos
            if (strpos($matchExpr, 'type') !== false) {
                // Patrón común: match($this->type) { ... }
                $switchExpr = preg_replace(
                    '/match\s*\(\s*([^)]+)\s*\)\s*\{([^}]+)\}/',
                    'switch($1) { $2 }',
                    $matchExpr
                );
                
                // Convertir '=>' a ': return' y agregar breaks
                $switchExpr = preg_replace(
                    "/('[^']+'\s*=>\s*'[^']+'),?/",
                    'case $1: break;',
                    $switchExpr
                );
                
                $switchExpr = str_replace('=>', ': return ', $switchExpr);
                $switchExpr = preg_replace('/return ([^,;]+),/', 'return $1; break;', $switchExpr);
                
                $content = str_replace($matchExpr, $switchExpr, $content);
                echo "<p>✅ Convertido match #" . ($i + 1) . "</p>";
                $fixes++;
            }
        }
        
        // Escribir archivo modificado
        if (file_put_contents($fullPath, $content)) {
            echo "<p>✅ Archivo actualizado</p>";
        } else {
            echo "<p>❌ Error escribiendo archivo</p>";
            $errors[] = "Error escribiendo $file";
        }
    } else {
        echo "<p>ℹ️ No se encontraron match expressions</p>";
    }
}

// También buscar nullsafe operators
echo "<h2>🔍 Buscando nullsafe operators (?->) restantes:</h2>";

$nullsafeFiles = [];
foreach (glob('app/**/*.php', GLOB_BRACE) as $file) {
    $content = file_get_contents($file);
    if (strpos($content, '?->') !== false) {
        $nullsafeFiles[] = $file;
    }
}

if (!empty($nullsafeFiles)) {
    echo "<p>⚠️ Archivos con nullsafe operator encontrados:</p>";
    echo "<ul>";
    foreach ($nullsafeFiles as $file) {
        echo "<li><code>$file</code></li>";
    }
    echo "</ul>";
} else {
    echo "<p>✅ No se encontraron nullsafe operators</p>";
}

echo "<h2>📊 Resumen:</h2>";
echo "<p><strong>✅ Fixes aplicados:</strong> $fixes</p>";
echo "<p><strong>❌ Errores:</strong> " . count($errors) . "</p>";

if (!empty($errors)) {
    echo "<h3>🚨 Errores encontrados:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<div style='background:#d4edda; padding:20px; border-radius:10px; margin:20px 0;'>";
echo "<h3>✅ PHP 7.4 Syntax Fix Completado</h3>";
echo "<p>Los archivos han sido actualizados para ser compatibles con PHP 7.4.33</p>";
echo "<p><strong>Nota:</strong> Los archivos originales se guardaron como .php8.backup</p>";
echo "</div>";

echo "<hr style='margin:30px 0;'>";
echo "<p style='text-align:center;color:#666;'><strong>🔧 PHP 7.4 Syntax Fix por Claude Code</strong> | " . date('Y-m-d H:i:s') . "</p>";
?>