<?php
session_start();
require_once '../config/database.php';

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'No tienes permiso para realizar esta acción.';
    header('Location: ../views/dashboard.php');
    exit;
}

// Verificar si se enviaron los datos necesarios
if (!isset($_POST['id_usuario']) || !isset($_POST['estado'])) {
    $_SESSION['error'] = 'Faltan datos requeridos.';
    header('Location: ../views/dashboard.php');
    exit;
}

$id_usuario = intval($_POST['id_usuario']);
$estado_actual = intval($_POST['estado']);
$nuevo_estado = $estado_actual ? 0 : 1; // Cambiar estado (toggle)

// Evitar que un administrador se desactive a sí mismo
if ($id_usuario === $_SESSION['id_usuario'] && $nuevo_estado === 0) {
    $_SESSION['error'] = 'No puedes desactivar tu propio usuario.';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE usuarios SET estado = ? WHERE id_usuario = ?");
    $stmt->bind_param("ii", $nuevo_estado, $id_usuario);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = 'Estado del usuario actualizado correctamente.';
    } else {
        $_SESSION['error'] = 'No se pudo actualizar el estado del usuario.';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al actualizar el estado del usuario: ' . $e->getMessage();
}

header('Location: ../views/dashboard.php');
exit;
?>