<?php
/**
 * ResourceHub - Editar Recurso
 * Endpoint para actualizar recursos existentes
 * Método HTTP: PUT o PATCH
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

use ResourceHub\API\Update\Update;

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP (aceptar PUT o PATCH)
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['PUT', 'PATCH'])) {
    json_response([
        'status' => 'error',
        'message' => 'Método HTTP no permitido. Se esperaba PUT o PATCH'
    ], 405);
}

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
    
    // Validar que venga el ID
    if (!isset($data->id) || empty($data->id)) {
        json_response([
            'status' => 'error',
            'message' => 'ID del recurso requerido'
        ], 400);
    }
    
    // Verificar permisos: solo el propietario o admin puede editar
    $recurso_id = (int)$data->id;
    $usuario_id = obtener_usuario_id();
    $es_admin = es_admin();
    
    // Verificar si el recurso existe y pertenece al usuario (si no es admin)
    if (!$es_admin) {
        $sql = "SELECT usuario_id FROM recursos WHERE id = ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $recurso_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            json_response([
                'status' => 'error',
                'message' => 'Recurso no encontrado'
            ], 404);
        }
        
        $recurso = $result->fetch_assoc();
        $stmt->close();
        
        // Verificar que el usuario sea el propietario
        if ($recurso['usuario_id'] != $usuario_id) {
            json_response([
                'status' => 'error',
                'message' => 'No tienes permisos para editar este recurso'
            ], 403);
        }
    }
    
    // Actualizar el recurso
    $resource = new Update('resourcehub');
    $resource->edit($data);
    
    $response = json_decode($resource->getData(), true);
    
    json_response($response, 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al actualizar recurso: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>




