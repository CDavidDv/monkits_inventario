<?php
// BORRAR DESPUES DE USAR
echo "<pre>";
$vars = ['REQUEST_URI','SCRIPT_NAME','PHP_SELF','SCRIPT_FILENAME','PATH_INFO','DOCUMENT_ROOT','HTTP_HOST','HTTPS','HTTP_X_FORWARDED_PROTO','HTTP_X_FORWARDED_HOST'];
foreach ($vars as $v) {
    echo str_pad($v, 30) . " = " . ($_SERVER[$v] ?? '(no definido)') . "\n";
}
echo "</pre>";
