<?php
/**
 * ResourceHub - Cerrar Sesión (Destrucción Total)
 */

// 1. Iniciar buffer y cargar dependencias
ob_start();
require_once __DIR__ . '/database.php';
if (ob_get_length()) ob_clean();

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    // Necesitamos iniciar la sesión para poder destruirla
    iniciar_sesion_segura();
    
    // 1. Vaciar el array de sesión
    $_SESSION = array();

    // 2. Borrar la cookie de sesión usando los parámetros EXACTOS
    // Esto es lo que fallaba: si no coinciden los params, la cookie no se borra
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // 3. Destruir la sesión en el servidor
    session_destroy();
    
    $response = [
        'status' => 'success',
        'message' => 'Sesión destruida correctamente'
    ];

} catch (Exception $e) {
    // Incluso si falla, el JS forzará la salida
    $response = [
        'status' => 'success', 
        'message' => 'Salida forzada'
    ];
}

// Limpieza final
if (ob_get_length()) ob_clean();
echo json_encode($response);
$conexion->close();
?>