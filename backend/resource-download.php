<?php
/**
 * ResourceHub - Registrar Descarga de Recurso
 * Endpoint para registrar una descarga y obtener información del archivo
 * Método HTTP: GET
 * Parámetros: id (query string)
 */

require_once __DIR__.'/database.php';

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('GET');

try {
    // Validar parámetro ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        json_response([
            'status' => 'error',
            'message' => 'ID del recurso requerido'
        ], 400);
    }
    
    $recurso_id = (int)$_GET['id'];
    
    if ($recurso_id <= 0) {
        json_response([
            'status' => 'error',
            'message' => 'ID inválido'
        ], 400);
    }
    
    // Obtener información del recurso
    $sql = "SELECT * FROM recursos WHERE id = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        json_response([
            'status' => 'error',
            'message' => 'Error al preparar consulta: ' . $conexion->error
        ], 500);
    }
    
    $stmt->bind_param("i", $recurso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        json_response([
            'status' => 'error',
            'message' => 'Recurso no encontrado o inactivo'
        ], 404);
    }
    
    $recurso = $result->fetch_assoc();
    $stmt->close();
    
    // Verificar que el archivo existe físicamente
    $archivo_ruta = $recurso['archivo_ruta'];
    if (!file_exists($archivo_ruta)) {
        json_response([
            'status' => 'error',
            'message' => 'El archivo no existe en el servidor'
        ], 404);
    }
    
    // Obtener información del usuario (si está autenticado)
    iniciar_sesion_segura();
    $usuario_id = obtener_usuario_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Registrar descarga en bitácora (si el procedimiento existe)
    try {
        $sql = "CALL sp_registrar_descarga(?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            // Si no hay usuario autenticado, pasar NULL
            $usuario_id_param = $usuario_id ? $usuario_id : null;
            $stmt->bind_param("iis", $recurso_id, $usuario_id_param, $ip);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Si falla el procedimiento almacenado, registrar manualmente
        $dia_semana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][date('w')];
        $hora = date('H:i:s');
        
        $sql = "INSERT INTO bitacora_descargas (recurso_id, usuario_id, ip_address, dia_semana, hora_descarga) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if ($stmt) {
            $usuario_id_param = $usuario_id ? $usuario_id : null;
            $stmt->bind_param("iisss", $recurso_id, $usuario_id_param, $ip, $dia_semana, $hora);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    // Retornar información del recurso
    json_response([
        'status' => 'success',
        'message' => 'Descarga registrada exitosamente',
        'data' => [
            'id' => $recurso['id'],
            'titulo' => $recurso['titulo'],
            'descripcion' => $recurso['descripcion'],
            'tipo_recurso' => $recurso['tipo_recurso'],
            'lenguaje' => $recurso['lenguaje'],
            'archivo_nombre' => $recurso['archivo_nombre'],
            'archivo_ruta' => $recurso['archivo_ruta'],
            'archivo_tamanio' => $recurso['archivo_tamanio'],
            'archivo_url' => $recurso['archivo_ruta'], // URL para descarga
            'tags' => $recurso['tags'],
            'fecha_subida' => $recurso['fecha_subida']
        ]
    ], 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al procesar descarga: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>