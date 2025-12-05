<?php
/**
 * ResourceHub - Agregar Recurso
 * Endpoint para crear nuevos recursos
 * Método HTTP: POST
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

use ResourceHub\API\Create\Create;

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('POST');

// Requerir autenticación
requerir_autenticacion();

try {
    // Obtener datos del body
    $input = file_get_contents('php://input');
    $data = json_decode($input);
    
    if (empty($data)) {
        json_response([
            'status' => 'error',
            'message' => 'No se recibieron datos'
        ], 400);
    }
    
    // Validar campos obligatorios
    if (empty($data->titulo) || empty($data->tipo_recurso) || 
        empty($data->archivo_nombre) || empty($data->archivo_ruta)) {
        json_response([
            'status' => 'error',
            'message' => 'Faltan campos obligatorios: titulo, tipo_recurso, archivo_nombre, archivo_ruta'
        ], 400);
    }
    
    // Validar tipo de recurso
    $tipos_validos = ['codigo', 'documentacion', 'biblioteca', 'herramienta', 'tutorial', 'otro'];
    if (!in_array($data->tipo_recurso, $tipos_validos)) {
        json_response([
            'status' => 'error',
            'message' => 'Tipo de recurso inválido. Valores permitidos: ' . implode(', ', $tipos_validos)
        ], 400);
    }
    
    // Agregar el usuario_id de la sesión
    $data->usuario_id = obtener_usuario_id();
    
    // Crear el recurso
    $resource = new Create('resourcehub');
    $resource->addResource($data);
    
    $response = json_decode($resource->getData(), true);
    
    // Si fue exitoso, retornar con código 201 (Created)
    if (isset($response['status']) && $response['status'] === 'success') {
        json_response($response, 201);
    } else {
        json_response($response, 400);
    }
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al agregar recurso: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>