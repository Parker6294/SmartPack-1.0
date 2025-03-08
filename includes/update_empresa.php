<?php
// includes/update_empresa.php
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
$id_empresa = intval($_POST['id_empresa'] ?? 0);
$nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
$rfc = trim($_POST['rfc'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$sitio_web = trim($_POST['sitio_web'] ?? '');

// Validación básica
if (empty($nombre_empresa) || $id_empresa <= 0) {
    $_SESSION['error'] = 'El nombre de la empresa es obligatorio';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Verificar que la empresa existe
    $query = "SELECT id_empresa FROM empresas WHERE id_empresa = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'La empresa no existe';
        header('Location: ../views/dashboard.php');
        exit;
    }
    
    // Actualizar la empresa
    $query = "
        UPDATE empresas 
        SET nombre_empresa = ?, rfc = ?, direccion = ?, telefono = ?, 
            correo = ?, sitio_web = ?
        WHERE id_empresa = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssssi', $nombre_empresa, $rfc, $direccion, $telefono, 
                      $correo, $sitio_web, $id_empresa);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Empresa actualizada correctamente';
    } else {
        $_SESSION['error'] = 'Error al actualizar la empresa: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al actualizar la empresa: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=empresas');
exit;