<?php
// includes/update_planta.php
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

// Obtener y validar los datos del formulario
$id_planta = intval($_POST['id_planta'] ?? 0);
$id_empresa = intval($_POST['id_empresa'] ?? 0);
$nombre_planta = trim($_POST['nombre_planta'] ?? '');
$ubicacion = trim($_POST['ubicacion'] ?? '');
$codigo_planta = trim($_POST['codigo_planta'] ?? '');
$responsable = trim($_POST['responsable'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');

// Validación básica
if (empty($nombre_planta) || $id_empresa <= 0 || $id_planta <= 0) {
    $_SESSION['error'] = 'El nombre de la planta y la empresa son obligatorios';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Verificar que la planta existe
    $query = "SELECT id_planta FROM plantas WHERE id_planta = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_planta);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'La planta no existe';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Verificar que la empresa existe
    $query = "SELECT id_empresa FROM empresas WHERE id_empresa = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'La empresa seleccionada no existe';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Actualizar la planta
    $query = "
        UPDATE plantas 
        SET id_empresa = ?, nombre_planta = ?, ubicacion = ?, codigo_planta = ?, 
            responsable = ?, telefono = ?, correo = ?
        WHERE id_planta = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('issssssi', $id_empresa, $nombre_planta, $ubicacion, $codigo_planta, 
                      $responsable, $telefono, $correo, $id_planta);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Planta actualizada correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar la planta: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al actualizar la planta: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=empresas');
exit;