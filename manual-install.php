<?php
/**
 * MonKits - Instalador Manual (SIN COMANDOS)
 * Para hostings sin acceso SSH ni comandos
 */

class ManualInstaller {
    private $baseDir;
    
    public function __construct() {
        $this->baseDir = dirname(__FILE__);
    }

    public function run() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step'])) {
            switch ($_POST['step']) {
                case '1': $this->step1(); break;
                case '2': $this->step2(); break;
                case '3': $this->step3(); break;
                default: $this->showMenu();
            }
        } else {
            $this->showMenu();
        }
    }

    private function showMenu() {
        echo '<h1>MonKits - Instalación Manual</h1>';
        echo '<p>Para hostings SIN SSH ni acceso a comandos</p>';
        
        echo '<h2>Pasos de Instalación:</h2>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="step" value="1">';
        echo '<button type="submit">1. Crear archivo .env</button>';
        echo '</form><br>';
        
        echo '<form method="POST">';
        echo '<input type="hidden" name="step" value="2">';
        echo '<button type="submit">2. Crear tablas de base de datos</button>';
        echo '</form><br>';
        
        echo '<form method="POST">';
        echo '<input type="hidden" name="step" value="3">';
        echo '<button type="submit">3. Configurar aplicación</button>';
        echo '</form><br>';
        
        echo '<h3>Requisitos:</h3>';
        echo '<ul>';
        echo '<li>PHP 8.1+: ' . (version_compare(PHP_VERSION, '8.1.0') >= 0 ? '✅' : '❌') . '</li>';
        echo '<li>MySQL disponible: ' . (function_exists('mysqli_connect') ? '✅' : '❌') . '</li>';
        echo '<li>OpenSSL: ' . (extension_loaded('openssl') ? '✅' : '❌') . '</li>';
        echo '</ul>';
    }

    private function step1() {
        if (isset($_POST['create_env'])) {
            $this->createEnvFromForm();
            return;
        }
        
        echo '<h2>Paso 1: Configurar archivo .env</h2>';
        echo '<form method="POST">';
        echo '<input type="hidden" name="step" value="1">';
        echo '<input type="hidden" name="create_env" value="1">';
        
        echo '<h3>Base de Datos</h3>';
        echo '<p><label>Host: <input type="text" name="db_host" value="localhost" required></label></p>';
        echo '<p><label>Base de Datos: <input type="text" name="db_database" required></label></p>';
        echo '<p><label>Usuario: <input type="text" name="db_username" required></label></p>';
        echo '<p><label>Contraseña: <input type="password" name="db_password"></label></p>';
        
        echo '<h3>Aplicación</h3>';
        echo '<p><label>URL del Sitio: <input type="url" name="app_url" value="https://monkits.com" required></label></p>';
        echo '<p><label>Clave de App: <input type="text" name="app_key" value="' . $this->generateKey() . '" required></label></p>';
        
        echo '<button type="submit">Crear .env</button>';
        echo '</form>';
        
        echo '<br><a href="?">← Volver al menú</a>';
    }

    private function step2() {
        if (isset($_POST['create_tables'])) {
            $this->createTables();
            return;
        }
        
        echo '<h2>Paso 2: Crear Tablas</h2>';
        echo '<p>Este paso creará todas las tablas necesarias en tu base de datos.</p>';
        
        $envExists = file_exists($this->baseDir . '/.env');
        if (!$envExists) {
            echo '<p style="color:red">⚠️ Primero debes crear el archivo .env (Paso 1)</p>';
            echo '<a href="?">← Volver al menú</a>';
            return;
        }
        
        echo '<form method="POST">';
        echo '<input type="hidden" name="step" value="2">';
        echo '<input type="hidden" name="create_tables" value="1">';
        echo '<button type="submit">Crear Tablas en BD</button>';
        echo '</form>';
        
        echo '<br><a href="?">← Volver al menú</a>';
    }

    private function step3() {
        echo '<h2>Paso 3: Configuración Final</h2>';
        
        // Crear directorios necesarios
        $this->createDirectories();
        
        // Configurar permisos
        $this->setBasicPermissions();
        
        echo '<h3>✅ Configuración Completada</h3>';
        echo '<p><strong>Tu aplicación MonKits está lista!</strong></p>';
        echo '<p><a href="/">Ir al Sistema de Inventario</a></p>';
        echo '<p style="color:red"><strong>IMPORTANTE:</strong> Elimina los archivos install.php y manual-install.php por seguridad</p>';
    }

    private function createEnvFromForm() {
        $env = "APP_NAME=MonkitsInventario
APP_ENV=production
APP_KEY={$_POST['app_key']}
APP_DEBUG=false
APP_URL={$_POST['app_url']}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$_POST['db_host']}
DB_PORT=3306
DB_DATABASE={$_POST['db_database']}
DB_USERNAME={$_POST['db_username']}
DB_PASSWORD={$_POST['db_password']}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587

VITE_APP_NAME=\"MonkitsInventario\"";

        if (file_put_contents($this->baseDir . '/.env', $env)) {
            echo '<h3>✅ Archivo .env creado exitosamente</h3>';
            echo '<p>Continúa con el Paso 2</p>';
        } else {
            echo '<h3>❌ Error creando .env</h3>';
            echo '<p>Verifica los permisos de escritura</p>';
        }
        
        echo '<br><a href="?">← Volver al menú</a>';
    }

    private function createTables() {
        $env = $this->loadEnv();
        
        try {
            $conn = new mysqli($env['DB_HOST'], $env['DB_USERNAME'], $env['DB_PASSWORD'], $env['DB_DATABASE']);
            
            if ($conn->connect_error) {
                throw new Exception("Error de conexión: " . $conn->connect_error);
            }

            // SQL para crear las tablas principales
            $sql = "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `email_verified_at` timestamp NULL DEFAULT NULL,
                `password` varchar(255) NOT NULL,
                `two_factor_secret` text,
                `two_factor_recovery_codes` text,
                `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
                `remember_token` varchar(100) DEFAULT NULL,
                `current_team_id` bigint unsigned DEFAULT NULL,
                `profile_photo_path` varchar(2048) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `users_email_unique` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `categories` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text,
                `color` varchar(7) DEFAULT '#3B82F6',
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `suppliers` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `contact_name` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `address` text,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `items` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `description` text,
                `category_id` bigint unsigned NOT NULL,
                `type` enum('component','kit','element') NOT NULL DEFAULT 'component',
                `current_stock` int NOT NULL DEFAULT 0,
                `min_stock` int NOT NULL DEFAULT 0,
                `max_stock` int DEFAULT NULL,
                `unit_price` decimal(10,2) DEFAULT NULL,
                `barcode` varchar(255) DEFAULT NULL,
                `location` varchar(255) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `items_category_id_foreign` (`category_id`),
                CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `sessions` (
                `id` varchar(255) NOT NULL,
                `user_id` bigint unsigned DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `payload` longtext NOT NULL,
                `last_activity` int NOT NULL,
                PRIMARY KEY (`id`),
                KEY `sessions_user_id_index` (`user_id`),
                KEY `sessions_last_activity_index` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            INSERT INTO `users` (`name`, `email`, `password`, `created_at`, `updated_at`) 
            VALUES ('Admin', 'admin@monkits.com', '$2y$12\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW())
            ON DUPLICATE KEY UPDATE `name`=`name`;

            INSERT INTO `categories` (`name`, `description`, `color`, `created_at`, `updated_at`) VALUES
            ('Resistencias', 'Componentes resistivos', '#EF4444', NOW(), NOW()),
            ('Capacitores', 'Capacitores y condensadores', '#3B82F6', NOW(), NOW()),
            ('Semiconductores', 'Transistores, diodos, IC', '#8B5CF6', NOW(), NOW())
            ON DUPLICATE KEY UPDATE `name`=`name`;
            ";

            if ($conn->multi_query($sql)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->next_result());
                
                echo '<h3>✅ Tablas creadas exitosamente</h3>';
                echo '<p>Usuario por defecto: admin@monkits.com / password</p>';
                echo '<p>Continúa con el Paso 3</p>';
            } else {
                echo '<h3>❌ Error creando tablas</h3>';
                echo '<p>' . $conn->error . '</p>';
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo '<h3>❌ Error: ' . $e->getMessage() . '</h3>';
        }
        
        echo '<br><a href="?">← Volver al menú</a>';
    }

    private function createDirectories() {
        $dirs = [
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/app/public',
            'bootstrap/cache'
        ];
        
        foreach ($dirs as $dir) {
            $fullPath = $this->baseDir . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0775, true);
            }
        }
    }

    private function setBasicPermissions() {
        $dirs = ['storage', 'bootstrap/cache'];
        foreach ($dirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (is_dir($path)) {
                chmod($path, 0775);
            }
        }
    }

    private function loadEnv() {
        $env = [];
        $lines = file($this->baseDir . '/.env');
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !startsWith(trim($line), '#')) {
                list($key, $value) = explode('=', trim($line), 2);
                $env[trim($key)] = trim($value);
            }
        }
        return $env;
    }

    private function generateKey() {
        return 'base64:' . base64_encode(random_bytes(32));
    }
}

function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

$installer = new ManualInstaller();
$installer->run();
?>
<!DOCTYPE html>
<html>
<head>
    <title>MonKits - Instalación Manual</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        input, button { padding: 10px; margin: 5px; }
        input[type="text"], input[type="url"], input[type="password"] { width: 300px; }
        button { background: #007cba; color: white; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #005a87; }
        h1 { color: #2c3e50; }
        h2 { color: #34495e; }
        label { font-weight: bold; }
    </style>
</head>
<body>
</body>
</html>