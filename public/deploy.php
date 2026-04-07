<?php
// deploy.php - Script permanente de deploy
// Protegido por clave. Nunca borrar del servidor.

define('DEPLOY_KEY', 'monkits2026');  // <-- cambia esta clave

$root    = dirname(__DIR__);
$zipFile = $root . '/deploy_package.zip';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['key'] ?? '') !== DEPLOY_KEY) {
        $message = 'Clave incorrecta.';
    } elseif (!file_exists($zipFile)) {
        $message = 'No se encontró deploy_package.zip en el servidor. Súbelo primero via FTP.';
    } else {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $count = $zip->numFiles;
            $zip->extractTo($root);
            $zip->close();

            // Limpiar view cache
            $viewsDir = $root . '/storage/framework/views';
            if (is_dir($viewsDir)) {
                foreach (glob("$viewsDir/*.php") as $f) {
                    @unlink($f);
                }
            }

            // Borrar el ZIP después de usarlo
            @unlink($zipFile);

            $message = "Deploy exitoso: $count archivos actualizados. View cache limpiado.";
            $success = true;
        } else {
            $message = 'Error al abrir el ZIP.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MonKits Deploy</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 80px auto; padding: 20px; }
        input, button { display: block; width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; font-size: 16px; }
        button { background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 6px; }
        button:hover { background: #1d4ed8; }
        .ok  { background: #dcfce7; color: #166534; padding: 12px; border-radius: 6px; }
        .err { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; }
        h2 { color: #1e293b; }
        .step { background: #f1f5f9; padding: 10px; border-radius: 6px; font-size: 13px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>MonKits Deploy</h2>

    <?php if ($message): ?>
        <div class="<?= $success ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <div class="step">
        <b>Pasos:</b><br>
        1. Corre <code>deploy.ps1</code> en tu PC<br>
        2. Sube <code>deploy_package.zip</code> via FTP a:<br>
        &nbsp;&nbsp;<code>public_html/inventario/</code><br>
        3. Ingresa la clave y haz Deploy
    </div>

    <form method="POST">
        <label>Clave de deploy:</label>
        <input type="password" name="key" autofocus placeholder="Clave secreta">
        <button type="submit">Desplegar</button>
    </form>
    <?php else: ?>
        <p><a href="/inventario/dashboard">Ir al dashboard →</a></p>
    <?php endif; ?>
</body>
</html>
