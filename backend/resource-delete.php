<?php
/**
 * ResourceHub - Eliminar Recurso
 * Endpoint para eliminar recursos (eliminación lógica)
 * Método HTTP: DELETE
 * Parámetros: id (query string o body)
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

use ResourceHub\API\Delete\Delete;

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('DELETE');

// Requerir autenticación
requerir_autenticacion();

try {
    // Obtener ID del recurso (puede venir en query string o body)
    $recurso_id = null;
    
    if (isset($_GET['id'])) {
        $recurso_id = (int)$_GET['id'];
    } else {
        $input = file_get_contents('php://input');
        $data = json_decode($input);
        if (isset($data->id)) {
            $recurso_id = (int)$data->id;
        }
    }
    
    if (empty($recurso_id)) {
        json_response([
            'status' => 'error',
            'message' => 'ID del recurso requerido'
        ], 400);
    }
    
    // Verificar permisos: solo el propietario o admin puede eliminar
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
                'message' => 'No tienes permisos para eliminar este recurso'
            ], 403);
        }
    }
    
    // Eliminar el recurso
    $resource = new Delete('resourcehub');
    $resource->delete($recurso_id);
    
    $response = json_decode($resource->getData(), true);
    
    json_response($response, 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al eliminar recurso: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>

