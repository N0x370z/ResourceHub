<?php
/**
 * ResourceHub - Clase Auth
 * 
 * Maneja operaciones de autenticación y autorización
 */

namespace ResourceHub\API;

require_once __DIR__ . '/DataBase.php';

class Auth extends DataBase {
    private $response;

    public function __construct($db = 'resourcehub', $user = 'root', $pass = '') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Verifica las credenciales de un usuario
     * 
     * @param string $email - Email del usuario
     * @param string $password - Contraseña del usuario
     * @return void
     */
    public function login($email, $password) {
        $this->response = array(
            'status' => 'error',
            'message' => 'Credenciales inválidas'
        );

        try {
            $sql = "SELECT id, nombre, email, password, rol, activo 
                    FROM usuarios 
                    WHERE email = ?";
            
            $stmt = $this->conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $stmt->close();
                    return;
                }

                $usuario = $result->fetch_assoc();
                $stmt->close();

                // Verificar si el usuario está activo
                if (!$usuario['activo']) {
                    $this->response['message'] = 'Usuario desactivado. Contacta al administrador';
                    return;
                }

                // Verificar contraseña
                if (password_verify($password, $usuario['password'])) {
                    $this->response = array(
                        'status' => 'success',
                        'message' => 'Inicio de sesión exitoso',
                        'data' => array(
                            'id' => $usuario['id'],
                            'nombre' => $usuario['nombre'],
                            'email' => $usuario['email'],
                            'rol' => $usuario['rol']
                        )
                    );
                }
            }

        } catch (\Exception $e) {
            $this->response['message'] = 'Error del servidor: ' . $e->getMessage();
            $this->log_error('Error en login()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Verifica si un email ya está registrado
     * 
     * @param string $email - Email a verificar
     * @return void
     */
    public function checkEmail($email) {
        $this->response = array(
            'exists' => false,
            'message' => 'Email disponible'
        );

        try {
            $sql = "SELECT id FROM usuarios WHERE email = ?";
            $stmt = $this->conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $this->response = array(
                        'exists' => true,
                        'message' => 'El email ya está registrado'
                    );
                }
                
                $result->free();
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->log_error('Error en checkEmail()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene información de un usuario por ID
     * 
     * @param int $id - ID del usuario
     * @return void
     */
    public function getUser($id) {
        $this->response = array(
            'status' => 'error',
            'message' => 'Usuario no encontrado'
        );

        try {
            $id = (int)$id;
            
            $sql = "SELECT id, nombre, email, rol, fecha_registro, activo 
                    FROM usuarios 
                    WHERE id = ?";
            
            $stmt = $this->conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $usuario = $result->fetch_assoc();
                    $this->response = array(
                        'status' => 'success',
                        'data' => $usuario
                    );
                }
                
                $result->free();
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->log_error('Error en getUser()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Retorna los datos en formato JSON
     * 
     * @return string - JSON con los datos
     */
    public function getData() {
        return json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
?>




