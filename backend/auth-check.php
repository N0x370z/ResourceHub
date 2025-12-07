<?php
/**
 * ResourceHub - Verificar Estado de Autenticacion
 * Version Blindada contra errores de JSON y Headers
 */

// Iniciar buffer de salida inmediatamente para atrapar cualquier error previo
ob_start();

require_once __DIR__ . '/database.php';

// Limpiar cualquier basura (espacios, warnings) que se haya generado al incluir database.php
if (ob_get_length()) ob_clean();

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar método HTTP
verificar_metodo('GET');

try {
    iniciar_sesion_segura();
    
    if (esta_autenticado()) {
        $response = [
            'status' => 'success',
            'authenticated' => true,
            'data' => [
                'id' => $_SESSION['usuario_id'],
                'nombre' => $_SESSION['nombre'],
                'email' => $_SESSION['email'],
                'rol' => $_SESSION['rol']
            ]
        ];
    } else {
        $response = [
            'status' => 'success',
            'authenticated' => false,
            'data' => null
        ];
    }
    
    // Asegurarse una vez más de que no haya basura antes de imprimir el JSON
    if (ob_get_length()) ob_clean();
    
    echo json_encode($response);
    
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al verificar autenticación: ' . $e->getMessage()
    ]);
}

$conexion->close();
?>