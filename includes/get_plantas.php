<?php
// includes/get_plantas.php
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

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Consulta para obtener todas las plantas con el nombre de la empresa
    $query = "
        SELECT p.id_planta, p.nombre_planta, p.id_empresa, e.nombre_empresa, 
               p.ubicacion, p.codigo_planta, p.responsable, p.telefono, p.correo, p.estado
        FROM plantas p
        JOIN empresas e ON p.id_empresa = e.id_empresa
        ORDER BY e.nombre_empresa ASC, p.nombre_planta ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $plantas = [];
    while ($row = $result->fetch_assoc()) {
        $plantas[] = $row;
    }
    
    echo json_encode($plantas);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener datos: ' . $e->getMessage()]);
}
// No incluir ?> al final del archivo