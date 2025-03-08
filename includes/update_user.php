<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'No tienes permiso para realizar esta acción.';
    header('Location: ../views/dashboard.php');
    exit;
}

// Verificar datos del formulario
if (!isset($_POST['id_usuario']) || !isset($_POST['nombre']) || !isset($_POST['usuario']) || !isset($_POST['id_rol'])) {
    $_SESSION['error'] = 'Faltan datos requeridos.';
    header('Location: ../views/dashboard.php');
    exit;
}

$id_usuario = intval($_POST['id_usuario']);
$nombre = trim($_POST['nombre']);
$usuario = trim($_POST['usuario']);
$id_rol = intval($_POST['id_rol']);
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

try {
    // Verificar si el nombre de usuario existe para otro usuario
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ? AND id_usuario != ?");
    $stmt->bind_param("si", $usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso.';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Actualizar usuario
    if (!empty($password)) {
        // Actualizar incluyendo la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, password = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssii", $nombre, $usuario, $password_hash, $id_rol, $id_usuario);
    } else {
        // Actualizar sin cambiar la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, id_rol = ? WHERE id_usuario = ?");
        $stmt->bind_param("ssii", $nombre, $usuario, $id_rol, $id_usuario);
    }
    
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Usuario actualizado correctamente.';
    } else {
        $_SESSION['info'] = 'No se realizaron cambios.';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al actualizar el usuario: ' . $e->getMessage();
}

header('Location: ../views/dashboard.php');
exit;
?>