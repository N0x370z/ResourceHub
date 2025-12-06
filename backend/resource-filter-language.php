<?php
/**
 * ResourceHub - Filtrar Recursos por Lenguaje
 * Endpoint para obtener recursos filtrados por lenguaje de programación
 * Método HTTP: GET
 * Parámetros: language (query string)
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
    // Validar parámetro de lenguaje
    if (!isset($_GET['language']) || empty(trim($_GET['language']))) {
        json_response([
            'status' => 'error',
            'message' => 'Parámetro de lenguaje requerido'
        ], 400);
    }
    
    $lenguaje = trim($_GET['language']);
    
    $resource = new Read('resourcehub');
    $resource->filterByLanguage($lenguaje);
    
    $data = json_decode($resource->getData(), true);
    
    // Si no hay datos, retornar array vacío
    if (empty($data)) {
        $data = [];
    }
    
    json_response([
        'status' => 'success',
        'data' => $data,
        'count' => count($data),
        'filter' => ['language' => $lenguaje]
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al filtrar recursos: ' . $e->getMessage()
    ], 500);
}
?>




