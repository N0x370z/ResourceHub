<?php
/**
 * ResourceHub - Cerrar Sesión
 * 
 * Este endpoint cierra la sesión del usuario actual
 * Método HTTP: POST o GET
 */

require_once __DIR__ . '/database.php';

// Iniciar sesión
iniciar_sesion_segura();

$response = [
    'status' => 'error',
    'message' => 'No hay sesión activa'
];

try {
    // Verificar si hay sesión activa
    if (esta_autenticado()) {
        $usuario_id = obtener_usuario_id();
        
        // Registrar logout en bitácora
        registrar_acceso($conexion, $usuario_id, 'logout');
        
        // Destruir sesión
        session_unset();
        session_destroy();
        
        // Eliminar cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        $response = [
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente',
            'redirect' => 'login.html'
        ];
        json_response($response, 200);
    } else {
        json_response($response, 400);
    }

} catch (Exception $e) {
    $response['message'] = 'Error al cerrar sesión: ' . $e->getMessage();
    json_response($response, 500);
}

$conexion->close();
?>