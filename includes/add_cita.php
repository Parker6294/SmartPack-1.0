<?php
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
$id_usuario_asignado = intval($_POST['id_usuario_asignado'] ?? 0);
$id_empresa = intval($_POST['id_empresa'] ?? 0);
$id_planta = intval($_POST['id_planta'] ?? 0);
$fecha_programada = trim($_POST['fecha_programada'] ?? '');
$hora_programada = trim($_POST['hora_programada'] ?? '');
$cliente = trim($_POST['cliente'] ?? '');
$domicilio = trim($_POST['domicilio'] ?? '');
$solicitante = trim($_POST['solicitante'] ?? '');
$maquina = trim($_POST['maquina'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$no_serie = trim($_POST['no_serie'] ?? '');
$linea = trim($_POST['linea'] ?? '');
$notas_adicionales = trim($_POST['notas_adicionales'] ?? '');

// Validación básica
if ($id_usuario_asignado <= 0 || $id_empresa <= 0 || $id_planta <= 0 || empty($fecha_programada) || empty($hora_programada)) {
    $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios';
    header('Location: ../views/dashboard.php?section=citas-section');
    exit;
}

try {
    // Generar folio único (año + mes + día + contador secuencial)
    $fecha_actual = date('Ymd');
    $query = "SELECT MAX(SUBSTRING_INDEX(folio, '-', -1)) as ultimo FROM citas_evaluacion WHERE folio LIKE ?";
    $stmt = $conn->prepare($query);
    $param = $fecha_actual . "-%";
    $stmt->bind_param('s', $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $contador = 1;
    if ($row && $row['ultimo']) {
        $contador = intval($row['ultimo']) + 1;
    }
    
    $folio = $fecha_actual . '-' . str_pad($contador, 4, '0', STR_PAD_LEFT);
    
    // Insertar la nueva cita
    $query = "
        INSERT INTO citas_evaluacion (
            id_usuario_asignador, id_usuario_asignado, id_empresa, id_planta, 
            fecha_programada, hora_programada, estado, folio, cliente, 
            domicilio, solicitante, maquina, marca, modelo, no_serie, linea, notas_adicionales
        ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $conn->prepare($query);
    $id_usuario_asignador = $_SESSION['id_usuario'];
    
    $stmt->bind_param(
        'iiissssssssssss',
        $id_usuario_asignador, $id_usuario_asignado, $id_empresa, $id_planta,
        $fecha_programada, $hora_programada, $folio, $cliente,
        $domicilio, $solicitante, $maquina, $marca, $modelo, $no_serie, $linea, $notas_adicionales
    );
    
    if ($stmt->execute()) {
        $id_cita = $conn->insert_id;
        
        // Crear entrada inicial en detalles_evaluacion
        $query = "INSERT INTO detalles_evaluacion (id_cita) VALUES (?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id_cita);
        $stmt->execute();
        
        // Obtener el nombre del asignador para la notificación
        $query = "SELECT nombre FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id_usuario_asignador);
        $stmt->execute();
        $result = $stmt->get_result();
        $nombre_asignador = $result->fetch_assoc()['nombre'];
        
        // Crear notificación para el usuario asignado
        $mensaje = $nombre_asignador . " te ha asignado una nueva cita de evaluación para el " . date('d/m/Y', strtotime($fecha_programada)) . " a las " . date('H:i', strtotime($hora_programada));
        $query = "INSERT INTO notificaciones_citas (id_usuario, id_cita, mensaje) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iis', $id_usuario_asignado, $id_cita, $mensaje);
        $stmt->execute();
        
        $_SESSION['success'] = 'Cita programada correctamente con folio: ' . $folio;
    } else {
        $_SESSION['error'] = 'Error al programar la cita: ' . $stmt->error;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error al programar la cita: ' . $e->getMessage();
}

// Redireccionar de vuelta al dashboard
header('Location: ../views/dashboard.php?section=citas-section');
exit;