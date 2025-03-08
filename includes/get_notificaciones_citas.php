<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Configurar encabezados para JSON
header('Content-Type: application/json');

try {
    // Consulta para obtener las notificaciones del usuario
    $query = "
        SELECT n.*, c.folio, DATE_FORMAT(n.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_formateada
        FROM notificaciones_citas n
        JOIN citas_evaluacion c ON n.id_cita = c.id_cita
        WHERE n.id_usuario = ?
        ORDER BY n.fecha_creacion DESC
        LIMIT 20
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    
    // Consulta para contar notificaciones no leídas
    $query = "
        SELECT COUNT(*) as total
        FROM notificaciones_citas
        WHERE id_usuario = ? AND leida = 0
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones,
        'no_leidas' => intval($count)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener notificaciones: ' . $e->getMessage()]);
}