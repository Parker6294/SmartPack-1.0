<?php
session_start();

// Redirigir al login si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    header('Location: views/login.php');
    exit;
} else {
    header('Location: views/dashboard.php');
    exit;
}
?>