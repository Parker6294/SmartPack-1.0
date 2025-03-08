<?php
// includes/toggle_empresa_status.php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['error'] = 'No autorizado';
    header('Location: ../views/dashboard.php');
    exit;
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Método no permitido';
    header('Location: ../views/dashboard.php');
    exit;
}

// Obtener y validar los datos
$id_empresa = intval($_POST['id_empresa'] ?? 0);
$estado_actual = intval($_POST['estado'] ?? 0);
$nuevo_estado = $estado_actual ? 0 : 1; // Cambiar estado (toggle)

if ($id_empresa <= 0) {
    $_SESSION['error'] = 'ID de empresa no válido';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Actualizar el estado de la empresa
    $query = "UPDATE empresas SET estado = ? WHERE id_empresa = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $nuevo_estado, $id_empresa);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Estado de la empresa actualizado correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar el estado de la empresa: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al actualizar el estado de la empresa: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=empresas');
exit;