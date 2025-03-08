<?php
// includes/get_empresa.php
session_start();
require_once '../config/database.php';

// Ocultar errores para producción
error_reporting(0);
ini_set('display_errors', 0);

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de empresa no proporcionado']);
    exit;
}

$id = intval($_GET['id']);

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Consulta para obtener los datos de una empresa específica
    $query = "
        SELECT id_empresa, nombre_empresa, rfc, direccion, telefono, correo, sitio_web, estado
        FROM empresas
        WHERE id_empresa = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Empresa no encontrada']);
        exit;
    }
    
    $empresa = $result->fetch_assoc();
    echo json_encode($empresa);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]);
}
// No incluir ?> al final del archivo