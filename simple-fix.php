<?php
// Fix súper simple - solo arregla lo mínimo necesario
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔧 Fix Simple - Solo lo Esencial</h1>";

try {
    // Obtener el hash del archivo principal
    $autoload_content = file_get_contents(__DIR__ . '/vendor/autoload.php');
    preg_match('/ComposerAutoloaderInit([a-f0-9]+)/', $autoload_content, $matches);
    $hash = $matches[1];
    
    echo "<p>🎯 Hash detectado: <code>$hash</code></p>";
    
    // Solo crear el archivo mínimo que falta
    $real_content = "<?php
namespace Composer\\Autoload;

class ComposerAutoloaderInit{$hash}
{
    private static \$loader;

    public static function getLoader()
    {
        if (null !== self::\$loader) {
            return self::\$loader;
        }

        require __DIR__ . '/ClassLoader.php';
        self::\$loader = \$loader = new \\Composer\\Autoload\\ClassLoader();

        \$vendorDir = dirname(__DIR__);
        \$baseDir = dirname(\$vendorDir);

        // PSR-4 básico
        \$loader->setPsr4('Ramsey\\\\Uuid\\\\', array(\$vendorDir . '/ramsey/uuid/src'));
        \$loader->setPsr4('App\\\\', array(\$baseDir . '/app'));

        \$loader->register(true);

        // Incluir funciones UUID
        if (file_exists(\$vendorDir . '/ramsey/uuid/src/functions.php')) {
            require_once \$vendorDir . '/ramsey/uuid/src/functions.php';
        }

        return \$loader;
    }
}";

    file_put_contents(__DIR__ . '/vendor/composer/autoload_real.php', $real_content);
    echo "<p>✅ autoload_real.php creado</p>";
    
    // Limpiar cache si es posible
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    echo "<p>🧪 Probando...</p>";
    
    // Test básico
    require_once __DIR__ . '/vendor/autoload.php';
    echo "<p>✅ Autoloader funciona</p>";
    
    if (class_exists('Ramsey\\Uuid\\Uuid')) {
        echo "<p>✅ UUID disponible</p>";
    }
    
    echo "<h2>🎉 Fix aplicado</h2>";
    echo "<p><a href='/inventario/'>🚀 Probar aplicación</a></p>";
    
} catch (Throwable \$e) {
    echo "<p>❌ Error: " . \$e->getMessage() . "</p>";
    echo "<p>En: " . \$e->getFile() . ":" . \$e->getLine() . "</p>";
}

echo "<p>📅 " . date('Y-m-d H:i:s') . "</p>";
?>