<?php
/**
 * ResourceHub - Editar Recurso (Corregido JSON y N/A)
 */

// 1. Buffer inicial
ob_start();

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

// Limpiar basura del include
if (ob_get_length()) ob_clean();

use ResourceHub\API\Update\Update;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['PUT', 'PATCH'])) {
    if (ob_get_length()) ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Verificar autenticación
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
    
    if (!isset($data->id) || empty($data->id)) {
        throw new Exception('ID del recurso requerido');
    }

    // --- LÓGICA AGREGADA: LENGUAJE POR DEFECTO AL EDITAR ---
    if (isset($data->lenguaje) && (empty($data->lenguaje) || trim($data->lenguaje) === '')) {
        $data->lenguaje = 'N/A';
    }
    // -------------------------------------------------------
    
    $recurso_id = (int)$data->id;
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
            throw new Exception('No tienes permisos para editar este recurso');
        }
    }
    
    $resource = new Update('resourcehub');
    $resource->edit($data);
    
    $response = json_decode($resource->getData(), true);
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => 'Error al actualizar recurso: ' . $e->getMessage()
    ];
}

// 2. Limpieza final crítica
if (ob_get_length()) ob_clean();
echo json_encode($response);
$conexion->close();
?>