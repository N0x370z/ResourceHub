<?php
/**
 * ResourceHub - Filtrar Recursos por Tipo
 * Endpoint para obtener recursos filtrados por tipo
 * Método HTTP: GET
 * Parámetros: type (query string)
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
    // Validar parámetro de tipo
    if (!isset($_GET['type']) || empty(trim($_GET['type']))) {
        json_response([
            'status' => 'error',
            'message' => 'Parámetro de tipo requerido'
        ], 400);
    }
    
    $tipo = trim($_GET['type']);
    
    // Validar tipo de recurso
    $tipos_validos = ['codigo', 'documentacion', 'biblioteca', 'herramienta', 'tutorial', 'otro'];
    if (!in_array($tipo, $tipos_validos)) {
        json_response([
            'status' => 'error',
            'message' => 'Tipo de recurso inválido. Valores permitidos: ' . implode(', ', $tipos_validos)
        ], 400);
    }
    
    $resource = new Read('resourcehub');
    $resource->filterByType($tipo);
    
    $data = json_decode($resource->getData(), true);
    
    // Si no hay datos, retornar array vacío
    if (empty($data)) {
        $data = [];
    }
    
    json_response([
        'status' => 'success',
        'data' => $data,
        'count' => count($data),
        'filter' => ['type' => $tipo]
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al filtrar recursos: ' . $e->getMessage()
    ], 500);
}
?>

