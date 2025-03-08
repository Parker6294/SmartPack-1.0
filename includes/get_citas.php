<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar el tipo de citas solicitado
if (!isset($_GET['tipo'])) {
    echo json_encode(['success' => false, 'error' => 'Tipo de citas no especificado']);
    exit;
}

$tipo = $_GET['tipo'];
$idUsuario = $_SESSION['id_usuario'];

try {
    $citas = [];
    
    // Consulta según el tipo de citas
    if ($tipo == 'asignadas') {
        // Obtener citas asignadas al usuario
        $stmt = $conn->prepare("
            SELECT c.*, e.nombre_empresa, p.nombre_planta, 
                   u.nombre as nombre_asignador,
                   DATE_FORMAT(c.fecha_programada, '%d/%m/%Y') as fecha_formateada,
                   DATE_FORMAT(c.hora_programada, '%H:%i') as hora_formateada,
                   CASE 
                       WHEN c.estado = 0 THEN 'pendiente'
                       WHEN c.estado = 1 THEN 'completada'
                       WHEN c.estado = 2 THEN 'cancelada'
                   END as estado,
                   CASE 
                       WHEN c.estado = 0 THEN 'Pendiente'
                       WHEN c.estado = 1 THEN 'Completada'
                       WHEN c.estado = 2 THEN 'Cancelada'
                   END as estado_texto
            FROM citas c
            JOIN empresas e ON c.id_empresa = e.id_empresa
            JOIN plantas p ON c.id_planta = p.id_planta
            LEFT JOIN usuarios u ON c.id_usuario_asignador = u.id_usuario
            WHERE c.id_usuario_asignado = ?
            ORDER BY c.fecha_programada DESC, c.hora_programada ASC
        ");
        
        $stmt->bind_param("i", $idUsuario);
    } 
    elseif ($tipo == 'creadas') {
        // Obtener citas creadas por el usuario
        $stmt = $conn->prepare("
            SELECT c.*, e.nombre_empresa, p.nombre_planta, 
                   u.nombre as nombre_asignado,
                   DATE_FORMAT(c.fecha_programada, '%d/%m/%Y') as fecha_formateada,
                   DATE_FORMAT(c.hora_programada, '%H:%i') as hora_formateada,
                   CASE 
                       WHEN c.estado = 0 THEN 'pendiente'
                       WHEN c.estado = 1 THEN 'completada'
                       WHEN c.estado = 2 THEN 'cancelada'
                   END as estado,
                   CASE 
                       WHEN c.estado = 0 THEN 'Pendiente'
                       WHEN c.estado = 1 THEN 'Completada'
                       WHEN c.estado = 2 THEN 'Cancelada'
                   END as estado_texto
            FROM citas c
            JOIN empresas e ON c.id_empresa = e.id_empresa
            JOIN plantas p ON c.id_planta = p.id_planta
            LEFT JOIN usuarios u ON c.id_usuario_asignado = u.id_usuario
            WHERE c.id_usuario_asignador = ?
            ORDER BY c.fecha_programada DESC, c.hora_programada ASC
        ");
        
        $stmt->bind_param("i", $idUsuario);
    } 
    else {
        echo json_encode(['success' => false, 'error' => 'Tipo de citas no válido']);
        exit;
    }
    
    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Extraer los resultados
    while ($row = $result->fetch_assoc()) {
        $citas[] = $row;
    }
    
    // Devolver los resultados como JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'citas' => $citas]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>