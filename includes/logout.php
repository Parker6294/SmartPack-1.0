<?php
session_start();

// Destruir la sesión
session_destroy();
session_write_close();
unset($_SESSION);

// Si la petición es por AJAX (desde JavaScript)
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    exit;
}

// Si es una petición normal (desde el botón de logout)
header("Location: ../views/login.php");
exit;
?>