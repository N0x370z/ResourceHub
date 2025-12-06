<?php
/**
 * ResourceHub - Configuración de Base de Datos
 * 
 * Este archivo maneja la conexión a la base de datos MySQL
 * y proporciona funciones auxiliares para el manejo de sesiones
 */

// =============================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// =============================================
$host = 'localhost';
$user = 'root';
$password = '';  // Cambiar según tu configuración
$database = 'resourcehub';

// =============================================
// CREAR CONEXIÓN
// =============================================
$conexion = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a la base de datos'
    ]));
}

// Establecer charset UTF-8
$conexion->set_charset("utf8mb4");

// =============================================
// FUNCIONES AUXILIARES
// =============================================

/**
 * Inicia una sesión segura
 */
function iniciar_sesion_segura() {
    // Configuración de sesión segura
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function esta_autenticado() {
    iniciar_sesion_segura();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica si el usuario es administrador
 * @return bool
 */
function es_admin() {
    iniciar_sesion_segura();
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

/**
 * Obtiene el ID del usuario actual
 * @return int|null
 */
function obtener_usuario_id() {
    iniciar_sesion_segura();
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Registra un acceso en la bitácora
 * @param mysqli $conexion
 * @param int $usuario_id
 * @param string $accion
 */
function registrar_acceso($conexion, $usuario_id, $accion) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $sql = "INSERT INTO bitacora_acceso (usuario_id, accion, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("isss", $usuario_id, $accion, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}

/**
 * Valida y sanitiza un email
 * @param string $email
 * @return string|false
 */
function validar_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Valida la fortaleza de una contraseña
 * @param string $password
 * @return array ['valid' => bool, 'message' => string]
 */
function validar_password($password) {
    $result = ['valid' => true, 'message' => 'Contraseña válida'];
    
    if (strlen($password) < 8) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe tener al menos 8 caracteres';
        return $result;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos una letra mayúscula';
        return $result;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos una letra minúscula';
        return $result;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos un número';
        return $result;
    }
    
    return $result;
}

/**
 * Genera un hash seguro de la contraseña
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verifica una contraseña contra su hash
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verificar_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Responde con JSON y termina la ejecución
 * @param array $data
 * @param int $http_code
 */
function json_response($data, $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Verifica que el método HTTP sea el esperado
 * @param string $method
 */
function verificar_metodo($method) {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        json_response([
            'status' => 'error',
            'message' => "Método HTTP no permitido. Se esperaba $method"
        ], 405);
    }
}

/**
 * Verifica que el usuario esté autenticado
 * Redirige al login si no lo está
 */
function requerir_autenticacion() {
    if (!esta_autenticado()) {
        json_response([
            'status' => 'error',
            'message' => 'No autenticado',
            'redirect' => 'login.html'
        ], 401);
    }
}

/**
 * Verifica que el usuario sea administrador
 */
function requerir_admin() {
    requerir_autenticacion();
    
    if (!es_admin()) {
        json_response([
            'status' => 'error',
            'message' => 'Acceso denegado. Se requieren permisos de administrador'
        ], 403);
    }
}

/**
 * Sanitiza una cadena para prevenir XSS
 * @param string $string
 * @return string
 */
function sanitizar_texto($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Valida un archivo subido
 * @param array $file Información del archivo ($_FILES['nombre'])
 * @param array $allowed_types Tipos MIME permitidos
 * @param int $max_size Tamaño máximo en bytes
 * @return array ['valid' => bool, 'message' => string]
 */
function validar_archivo($file, $allowed_types = [], $max_size = 10485760) {
    $result = ['valid' => false, 'message' => ''];
    
    // Verificar si hay error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'Error al subir el archivo';
        return $result;
    }
    
    // Verificar tamaño
    if ($file['size'] > $max_size) {
        $result['message'] = 'El archivo excede el tamaño máximo permitido';
        return $result;
    }
    
    // Verificar tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!empty($allowed_types) && !in_array($mime, $allowed_types)) {
        $result['message'] = 'Tipo de archivo no permitido';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Archivo válido';
    return $result;
}

// =============================================
// CONFIGURACIÓN DE ERRORES (solo para desarrollo)
// =============================================
// En producción, comentar estas líneas
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>