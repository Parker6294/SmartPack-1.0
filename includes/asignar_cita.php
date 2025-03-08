<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Verificar datos recibidos
if (!isset($_POST['id_cita']) || !isset($_POST['id_usuario_asignado'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$idCita = $_POST['id_cita'];
$idUsuarioAsignado = $_POST['id_usuario_asignado'];
$notasAsignacion = isset($_POST['notas_asignacion']) ? $_POST['notas_asignacion'] : '';
$idUsuarioAsignador = $_SESSION['id_usuario'];

try {
    // Actualizar la cita
    $stmt = $conn->prepare("
        UPDATE citas 
        SET id_usuario_asignado = ?, 
            id_usuario_asignador = ?, 
            notas_asignacion = ?,
            fecha_asignacion = NOW()
        WHERE id_cita = ?
    ");
    
    $stmt->bind_param("iisi", $idUsuarioAsignado, $idUsuarioAsignador, $notasAsignacion, $idCita);
    $resultado = $stmt->execute();
    
    if ($resultado) {
        // Éxito
        echo json_encode(['success' => true]);
        
        // Opcional: Crear notificación
        $stmtNotif = $conn->prepare("
            INSERT INTO notificaciones (id_usuario, titulo, mensaje, fecha_hora, tipo, leida)
            VALUES (?, 'Nueva cita asignada', ?, NOW(), 'cita', 0)
        ");
        
        $mensaje = "Se te ha asignado una nueva cita. Revisa la sección de citas para más detalles.";
        $stmtNotif->bind_param("is", $idUsuarioAsignado, $mensaje);
        $stmtNotif->execute();
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar la base de datos']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>