<?php
/**
 * ResourceHub - Verificar Autenticación (Anti-Caché)
 */

ob_start();
require_once __DIR__ . '/database.php';
if (ob_get_length()) ob_clean();

// HEADERS CRÍTICOS: Prevenir que el navegador guarde el estado "logueado" en caché
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
            'authenticated' => false
        ];
    }
    
    if (ob_get_length()) ob_clean();
    echo json_encode($response);
    
} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'authenticated' => false]);
}

$conexion->close();
?>