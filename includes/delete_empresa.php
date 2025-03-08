<?php
// includes/delete_empresa.php
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

// Obtener y validar el ID de la empresa
$id_empresa = intval($_POST['id_empresa'] ?? 0);

if ($id_empresa <= 0) {
    $_SESSION['error'] = 'ID de empresa no válido';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Verificar si la empresa tiene plantas asociadas
    $query = "SELECT COUNT(*) as total FROM plantas WHERE id_empresa = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        $_SESSION['error'] = 'No se puede eliminar la empresa porque tiene plantas asociadas';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Eliminar la empresa
    $query = "DELETE FROM empresas WHERE id_empresa = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_empresa);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Empresa eliminada correctamente';
    } else {
        $_SESSION['error'] = 'Error al eliminar la empresa: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al eliminar la empresa: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=empresas');
exit;