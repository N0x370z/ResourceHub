<?php
/**
 * ResourceHub - Inicio de Sesión
 * 
 * Este endpoint maneja la autenticación de usuarios
 * Método HTTP: POST
 * Parámetros: email, password
 */

require_once __DIR__ . '/database.php';

// Verificar método HTTP
verificar_metodo('POST');

// Obtener datos JSON del body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Respuesta por defecto
$response = [
    'status' => 'error',
    'message' => 'Credenciales inválidas'
];

try {
    // Validar que vengan los campos necesarios
    if (empty($data['email']) || empty($data['password'])) {
        $response['message'] = 'Email y contraseña son obligatorios';
        json_response($response, 400);
    }

    $email = trim($data['email']);
    $password = $data['password'];

    // Validar formato de email
    if (!validar_email($email)) {
        $response['message'] = 'Email no válido';
        json_response($response, 400);
    }

    // Buscar usuario por email
    $sql = "SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Usuario no encontrado
        $stmt->close();
        json_response($response, 401);
    }

    $usuario = $result->fetch_assoc();
    $stmt->close();

    // Verificar si el usuario está activo
    if (!$usuario['activo']) {
        $response['message'] = 'Usuario desactivado. Contacta al administrador';
        json_response($response, 403);
    }

    // Verificar contraseña
    if (!verificar_password($password, $usuario['password'])) {
        // Contraseña incorrecta
        json_response($response, 401);
    }

    // ✅ AUTENTICACIÓN EXITOSA
    // Iniciar sesión
    iniciar_sesion_segura();
    
    // Guardar datos en sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['ultimo_acceso'] = time();

    // Registrar en bitácora
    registrar_acceso($conexion, $usuario['id'], 'login');

    // Respuesta exitosa
    $response = [
        'status' => 'success',
        'message' => 'Inicio de sesión exitoso',
        'data' => [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'rol' => $usuario['rol']
        ],
        'redirect' => $usuario['rol'] === 'admin' ? 'dashboard.html' : 'index.html'
    ];
    
    json_response($response, 200);

} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
    json_response($response, 500);
}

$conexion->close();
?>