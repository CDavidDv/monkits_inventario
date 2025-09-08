<?php
/**
 * MonKits Inventory System - Instalador Web
 * Este archivo automatiza la instalación sin SSH
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutos

class MonKitsInstaller {
    private $baseDir;
    private $errors = [];
    private $success = [];

    public function __construct() {
        $this->baseDir = dirname(__FILE__);
    }

    public function run() {
        echo "<h1>MonKits Inventory System - Instalador</h1>";
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processInstallation();
        } else {
            $this->showForm();
        }
    }

    private function showForm() {
        echo '<form method="POST">
        <h2>Configuración de Base de Datos</h2>
        <p><label>Host de BD: <input type="text" name="db_host" value="localhost" required></label></p>
        <p><label>Nombre de BD: <input type="text" name="db_database" required></label></p>
        <p><label>Usuario de BD: <input type="text" name="db_username" required></label></p>
        <p><label>Password de BD: <input type="password" name="db_password" required></label></p>
        
        <h2>Configuración de Aplicación</h2>
        <p><label>URL del sitio: <input type="url" name="app_url" value="https://monkits.com" required></label></p>
        <p><label>Nombre de la App: <input type="text" name="app_name" value="MonkitsInventario" required></label></p>
        
        <p><button type="submit">Instalar MonKits</button></p>
        </form>';
    }

    private function processInstallation() {
        echo "<h2>Procesando Instalación...</h2>";
        
        // 1. Crear archivo .env
        $this->createEnvFile();
        
        // 2. Instalar dependencias composer
        $this->installComposer();
        
        // 3. Generar clave de aplicación
        $this->generateAppKey();
        
        // 4. Ejecutar migraciones
        $this->runMigrations();
        
        // 5. Crear storage link
        $this->createStorageLink();
        
        // 6. Configurar permisos
        $this->setPermissions();
        
        $this->showResults();
    }

    private function createEnvFile() {
        $envContent = "APP_NAME=" . $_POST['app_name'] . "
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=" . $_POST['app_url'] . "

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=" . $_POST['db_host'] . "
DB_PORT=3306
DB_DATABASE=" . $_POST['db_database'] . "
DB_USERNAME=" . $_POST['db_username'] . "
DB_PASSWORD=" . $_POST['db_password'] . "

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@monkits.com
MAIL_FROM_NAME=MonkitsInventario

VITE_APP_NAME=\"" . $_POST['app_name'] . "\"
";

        if (file_put_contents($this->baseDir . '/.env', $envContent)) {
            $this->success[] = "✅ Archivo .env creado exitosamente";
        } else {
            $this->errors[] = "❌ Error al crear archivo .env";
        }
    }

    private function installComposer() {
        echo "<p>Instalando dependencias de Composer...</p>";
        
        $composerPath = $this->findComposer();
        if (!$composerPath) {
            $this->errors[] = "❌ Composer no encontrado. Debes instalarlo manualmente.";
            return;
        }
        
        $command = "cd " . $this->baseDir . " && $composerPath install --no-dev --optimize-autoloader 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'error') === false && strpos($output, 'failed') === false) {
            $this->success[] = "✅ Dependencias de Composer instaladas";
        } else {
            $this->errors[] = "❌ Error instalando Composer: " . $output;
        }
    }

    private function findComposer() {
        $paths = ['composer', 'composer.phar', '/usr/local/bin/composer', './composer.phar'];
        foreach ($paths as $path) {
            $test = shell_exec("$path --version 2>&1");
            if (strpos($test, 'Composer') !== false) {
                return $path;
            }
        }
        return false;
    }

    private function generateAppKey() {
        $command = "cd " . $this->baseDir . " && php artisan key:generate --force 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'generated') !== false) {
            $this->success[] = "✅ Clave de aplicación generada";
        } else {
            $this->errors[] = "❌ Error generando clave: " . $output;
        }
    }

    private function runMigrations() {
        echo "<p>Ejecutando migraciones de base de datos...</p>";
        
        $command = "cd " . $this->baseDir . " && php artisan migrate --force 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'Migrated') !== false) {
            $this->success[] = "✅ Migraciones ejecutadas exitosamente";
            
            // Ejecutar seeders
            $seedCommand = "cd " . $this->baseDir . " && php artisan db:seed --force 2>&1";
            $seedOutput = shell_exec($seedCommand);
            
            if (strpos($seedOutput, 'error') === false) {
                $this->success[] = "✅ Datos iniciales insertados";
            }
        } else {
            $this->errors[] = "❌ Error en migraciones: " . $output;
        }
    }

    private function createStorageLink() {
        $command = "cd " . $this->baseDir . " && php artisan storage:link 2>&1";
        $output = shell_exec($command);
        
        if (strpos($output, 'linked') !== false) {
            $this->success[] = "✅ Storage link creado";
        }
    }

    private function setPermissions() {
        $dirs = ['storage', 'bootstrap/cache'];
        foreach ($dirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (is_dir($path)) {
                chmod($path, 0775);
                $this->chmodRecursive($path, 0664, 0775);
            }
        }
        $this->success[] = "✅ Permisos configurados";
    }

    private function chmodRecursive($path, $filemode, $dirmode) {
        if (is_dir($path)) {
            if (!chmod($path, $dirmode)) {
                return false;
            }
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file != '.' && $file != '..') {
                    $fullpath = $path . '/' . $file;
                    return $this->chmodRecursive($fullpath, $filemode, $dirmode);
                }
            }
            closedir($dh);
        } else {
            if (!chmod($path, $filemode)) {
                return false;
            }
        }
        return true;
    }

    private function showResults() {
        echo "<h2>Resultados de la Instalación</h2>";
        
        if (!empty($this->success)) {
            echo "<h3>Éxitos:</h3><ul>";
            foreach ($this->success as $msg) {
                echo "<li>$msg</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($this->errors)) {
            echo "<h3>Errores:</h3><ul>";
            foreach ($this->errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        
        if (empty($this->errors)) {
            echo "<h2>🎉 ¡Instalación Completada!</h2>";
            echo "<p>Tu aplicación MonKits está lista. <a href='/'>Ir al sitio</a></p>";
            echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo install.php por seguridad.</p>";
        } else {
            echo "<h2>⚠️ Instalación con errores</h2>";
            echo "<p>Por favor revisa los errores arriba.</p>";
        }
    }
}

// Solo ejecutar si se accede directamente
if (basename($_SERVER['SCRIPT_NAME']) === 'install.php') {
    $installer = new MonKitsInstaller();
    $installer->run();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>MonKits Installer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        input, button { padding: 8px; margin: 5px; }
        button { background: #007cba; color: white; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
</body>
</html>