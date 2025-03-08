<?php
// includes/delete_planta.php
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

// Obtener y validar el ID de la planta
$id_planta = intval($_POST['id_planta'] ?? 0);

if ($id_planta <= 0) {
    $_SESSION['error'] = 'ID de planta no válido';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Eliminar la planta
    $query = "DELETE FROM plantas WHERE id_planta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_planta);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Planta eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la planta: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al eliminar la planta: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=empresas');
exit;