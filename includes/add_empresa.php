<?php
// includes/add_empresa.php
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
$nombre_empresa = trim($_POST['nombre_empresa'] ?? '');
$rfc = trim($_POST['rfc'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$sitio_web = trim($_POST['sitio_web'] ?? '');

// Validación básica
if (empty($nombre_empresa)) {
    $_SESSION['error'] = 'El nombre de la empresa es obligatorio';
    header('Location: ../views/dashboard.php');
    exit;
}

try {
    // Insertar la nueva empresa
    $query = "
        INSERT INTO empresas (nombre_empresa, rfc, direccion, telefono, correo, sitio_web)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssss', $nombre_empresa, $rfc, $direccion, $telefono, $correo, $sitio_web);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Empresa agregada correctamente';
    } else {
        $_SESSION['error'] = 'Error al agregar la empresa: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al agregar la empresa: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php');
exit;