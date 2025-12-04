<?php
/**
 * ResourceHub - Registro de Usuarios
 * 
 * Este endpoint maneja el registro de nuevos usuarios
 * Método HTTP: POST
 * Parámetros: nombre, email, password, password_confirm
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
    'message' => 'Error en el registro'
];

try {
    // Validar que vengan todos los campos
    if (empty($data['nombre']) || empty($data['email']) || 
        empty($data['password']) || empty($data['password_confirm'])) {
        $response['message'] = 'Todos los campos son obligatorios';
        json_response($response, 400);
    }

    // Sanitizar datos
    $nombre = sanitizar_texto($data['nombre']);
    $email = trim($data['email']);
    $password = $data['password'];
    $password_confirm = $data['password_confirm'];

    // Validar nombre (mínimo 3 caracteres)
    if (strlen($nombre) < 3) {
        $response['message'] = 'El nombre debe tener al menos 3 caracteres';
        json_response($response, 400);
    }

    // Validar email
    $email_valido = validar_email($email);
    if (!$email_valido) {
        $response['message'] = 'El email no es válido';
        json_response($response, 400);
    }
    $email = $email_valido;

    // Validar que las contraseñas coincidan
    if ($password !== $password_confirm) {
        $response['message'] = 'Las contraseñas no coinciden';
        json_response($response, 400);
    }

    // Validar fortaleza de la contraseña
    $validacion_pass = validar_password($password);
    if (!$validacion_pass['valid']) {
        $response['message'] = $validacion_pass['message'];
        json_response($response, 400);
    }

    // Verificar que el email no esté registrado
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = 'El email ya está registrado';
        $stmt->close();
        json_response($response, 409);
    }
    $stmt->close();

    // Hashear contraseña
    $password_hash = hash_password($password);

    // Insertar nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'usuario')";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sss", $nombre, $email, $password_hash);

    if ($stmt->execute()) {
        $usuario_id = $conexion->insert_id;
        
        // Registrar en bitácora
        registrar_acceso($conexion, $usuario_id, 'registro');

        $response = [
            'status' => 'success',
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'id' => $usuario_id,
                'nombre' => $nombre,
                'email' => $email
            ]
        ];
        json_response($response, 201);
    } else {
        $response['message'] = 'Error al registrar el usuario: ' . $conexion->error;
        json_response($response, 500);
    }

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Error del servidor: ' . $e->getMessage();
    json_response($response, 500);
}

$conexion->close();
?>