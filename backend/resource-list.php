<?php
/**
 * ResourceHub - Listar Recursos
 * Endpoint para listar todos los recursos activos
 * Método HTTP: GET
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

use ResourceHub\API\Read\Read;

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('GET');

try {
    $resource = new Read('resourcehub');
    $resource->list();
    
    $data = json_decode($resource->getData(), true);
    
    // Si no hay datos, retornar array vacío
    if (empty($data)) {
        $data = [];
    }
    
    json_response([
        'status' => 'success',
        'data' => $data,
        'count' => count($data)
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al listar recursos: ' . $e->getMessage()
    ], 500);
}
?>