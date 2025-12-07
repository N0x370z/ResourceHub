<?php
/**
 * ResourceHub - Eliminar Recurso (Corregido JSON)
 */

// 1. Buffer inicial
ob_start();

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

// Limpiar basura del include
if (ob_get_length()) ob_clean();

use ResourceHub\API\Delete\Delete;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
        throw new Exception('ID del recurso requerido');
    }
    
    $usuario_id = obtener_usuario_id();
    $es_admin = es_admin();
    
    // Validar propiedad
    if (!$es_admin) {
        $sql = "SELECT usuario_id FROM recursos WHERE id = ? AND activo = 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $recurso_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception('Recurso no encontrado');
        }
        
        $recurso = $result->fetch_assoc();
        $stmt->close();
        
        if ($recurso['usuario_id'] != $usuario_id) {
            throw new Exception('No tienes permisos para eliminar este recurso');
        }
    }
    
    $resource = new Delete('resourcehub');
    $resource->delete($recurso_id);
    
    $response = json_decode($resource->getData(), true);
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Error al eliminar recurso: ' . $e->getMessage()
    ];
}

// 2. Limpieza final crítica
if (ob_get_length()) ob_clean();
echo json_encode($response);
$conexion->close();
?>