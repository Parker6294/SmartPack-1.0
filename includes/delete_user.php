<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    $_SESSION['error'] = 'No tienes permiso para realizar esta acción.';
    header('Location: ../views/dashboard.php');
    exit;
}

// Verificar si se envió un ID de usuario
if (!isset($_POST['id_usuario']) || !is_numeric($_POST['id_usuario'])) {
    $_SESSION['error'] = 'ID de usuario no válido.';
    header('Location: ../views/dashboard.php');
    exit;
}

$id_usuario = intval($_POST['id_usuario']);

// Evitar que se elimine a sí mismo
if ($id_usuario === $_SESSION['id_usuario']) {
    $_SESSION['error'] = 'No puedes eliminar tu propio usuario.';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Usuario eliminado correctamente.';
    } else {
        $_SESSION['error'] = 'No se pudo eliminar el usuario.';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al eliminar el usuario: ' . $e->getMessage();
}

header('Location: ../views/dashboard.php');
exit;
?>