<?php
/**
 * ResourceHub - Agregar Recurso
 */

ob_start();

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

// Limpieza de seguridad
if (ob_get_length()) ob_clean();

use ResourceHub\API\Create\Create;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

if (!esta_autenticado()) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    
    if (empty($data)) {
        throw new Exception('No se recibieron datos');
    }
    
    // --- LÓGICA AGREGADA: LENGUAJE POR DEFECTO ---
    if (empty($data->lenguaje) || trim($data->lenguaje) === '') {
        $data->lenguaje = 'N/A';
    }
    // ---------------------------------------------
    
    $data->usuario_id = obtener_usuario_id();
    
    $resource = new Create('resourcehub');
    $resource->addResource($data);
    
    $response = json_decode($resource->getData(), true);
    
    http_response_code(200); 

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Error al agregar recurso: ' . $e->getMessage()
    ];
}

// Limpieza final antes de enviar JSON
if (ob_get_length()) ob_clean();
echo json_encode($response);
$conexion->close();
?>