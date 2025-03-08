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
if (!isset($_POST['nombre']) || !isset($_POST['usuario']) || !isset($_POST['password']) || !isset($_POST['id_rol'])) {
    $_SESSION['error'] = 'Faltan datos requeridos.';
    header('Location: ../views/dashboard.php');
    exit;
}

$nombre = trim($_POST['nombre']);
$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);
$id_rol = intval($_POST['id_rol']);

// Validaciones básicas
if (empty($nombre) || empty($usuario) || empty($password) || $id_rol <= 0) {
    $_SESSION['error'] = 'Todos los campos son obligatorios.';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso.';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, usuario, password, id_rol, estado, ultimo_acceso, fecha_creacion) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
    $stmt->bind_param("sssi", $nombre, $usuario, $password_hash, $id_rol);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Usuario creado correctamente.';
    } else {
        $_SESSION['error'] = 'Error al crear el usuario.';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}

header('Location: ../views/dashboard.php');
exit;
?>