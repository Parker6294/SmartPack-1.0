<?php
// Función para verificar si existe una sesión activa
function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../views/login.php');
        exit;
    }
}

// Función para limpiar inputs
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si el usuario es administrador
function isAdmin() {
    return isset($_SESSION['id_rol']) && $_SESSION['id_rol'] === 1;
}

// Clase para gestionar usuarios
class UserManager {
    private $conn;

    public function __construct($db) {
        $this->conn = $db->getConnection();
    }

    // Obtener todos los usuarios con sus roles
    public function getAllUsers() {
        try {
            $query = "SELECT u.id_usuario, u.nombre, u.usuario, r.nombre_rol, 
                             u.estado, u.ultimo_acceso, u.fecha_creacion
                      FROM usuarios u
                      JOIN roles r ON u.id_rol = r.id_rol
                      ORDER BY u.fecha_creacion DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }

    // Obtener todos los roles activos
    public function getAllRoles() {
        try {
            $query = "SELECT id_rol, nombre_rol FROM roles WHERE estado = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Error al obtener roles: " . $e->getMessage());
        }
    }

    // Agregar nuevo usuario
    public function addUser($nombre, $usuario, $password, $id_rol) {
        try {
            // Verificar si el usuario ya existe
            $stmt = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario ya existe'
                ];
            }

            // Insertar nuevo usuario
            $query = "INSERT INTO usuarios (nombre, usuario, password, id_rol, estado) 
                     VALUES (?, ?, ?, ?, 1)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$nombre, $usuario, $password, $id_rol]);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente'
            ];
        } catch(PDOException $e) {
            throw new Exception("Error al crear usuario: " . $e->getMessage());
        }
    }

    // Actualizar estado del usuario
    public function updateUserStatus($id_usuario, $estado) {
        try {
            $query = "UPDATE usuarios SET estado = ? WHERE id_usuario = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$estado, $id_usuario]);
            
            return [
                'success' => true,
                'message' => 'Estado actualizado exitosamente'
            ];
        } catch(PDOException $e) {
            throw new Exception("Error al actualizar estado: " . $e->getMessage());
        }
    }

    // Obtener usuario por ID
    public function getUserById($id_usuario) {
        try {
            $query = "SELECT u.*, r.nombre_rol 
                     FROM usuarios u 
                     JOIN roles r ON u.id_rol = r.id_rol 
                     WHERE u.id_usuario = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            throw new Exception("Error al obtener usuario: " . $e->getMessage());
        }
    }

    // Actualizar usuario
    public function updateUser($id_usuario, $nombre, $usuario, $id_rol, $password = null) {
        try {
            // Verificar si el usuario existe (excepto para el usuario actual)
            $stmt = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ? AND id_usuario != ?");
            $stmt->execute([$usuario, $id_usuario]);
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario ya existe'
                ];
            }

            // Preparar la consulta base
            $query = "UPDATE usuarios SET nombre = ?, usuario = ?, id_rol = ?";
            $params = [$nombre, $usuario, $id_rol];

            // Si se proporciona contraseña, incluirla
            if ($password !== null && trim($password) !== '') {
                $query .= ", password = ?";
                $params[] = $password;
            }

            $query .= " WHERE id_usuario = ?";
            $params[] = $id_usuario;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente'
            ];
        } catch(PDOException $e) {
            throw new Exception("Error al actualizar usuario: " . $e->getMessage());
        }
    }
    public function getTotalUsers() {
        try {
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch(PDOException $e) {
            throw new Exception("Error al obtener total de usuarios: " . $e->getMessage());
        }
    }
}
?>