<?php
/**
 * ResourceHub - Buscar Recursos
 * Endpoint para buscar recursos por término de búsqueda
 * Método HTTP: GET
 * Parámetros: search (query string)
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
    // Validar parámetro de búsqueda
    if (!isset($_GET['search']) || empty(trim($_GET['search']))) {
        json_response([
            'status' => 'error',
            'message' => 'Parámetro de búsqueda requerido'
        ], 400);
    }
    
    $search = trim($_GET['search']);
    
    // Validar longitud mínima
    if (strlen($search) < 2) {
        json_response([
            'status' => 'error',
            'message' => 'El término de búsqueda debe tener al menos 2 caracteres'
        ], 400);
    }
    
    $resource = new Read('resourcehub');
    $resource->search($search);
    
    $data = json_decode($resource->getData(), true);
    
    // Si no hay datos, retornar array vacío
    if (empty($data)) {
        $data = [];
    }
    
    json_response([
        'status' => 'success',
        'data' => $data,
        'count' => count($data),
        'search_term' => $search
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al buscar recursos: ' . $e->getMessage()
    ], 500);
}
?>