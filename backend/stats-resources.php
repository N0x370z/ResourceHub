<?php
/**
 * ResourceHub - Estadísticas de Recursos
 * Endpoint para obtener estadísticas generales de recursos
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
    $resource->getStats();
    
    $stats = json_decode($resource->getData(), true);
    
    json_response([
        'status' => 'success',
        'data' => $stats
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ], 500);
}
?>

