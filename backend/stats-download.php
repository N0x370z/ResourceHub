<?php
/**
 * ResourceHub - Estadísticas de Descargas
 * Endpoint para obtener estadísticas detalladas de descargas
 * Método HTTP: GET
 */

require_once __DIR__.'/database.php';

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('GET');

$response = array();

try {
    // Estadística 1: Descargas por tipo de recurso
    $sql = "SELECT r.tipo_recurso, COUNT(bd.id) as total 
            FROM recursos r 
            LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id 
            GROUP BY r.tipo_recurso
            ORDER BY total DESC";
    $result = $conexion->query($sql);
    
    $por_tipo = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $por_tipo[$row['tipo_recurso']] = (int)$row['total'];
        }
        $result->free();
    }
    
    // Estadística 2: Descargas por lenguaje
    $sql = "SELECT r.lenguaje, COUNT(bd.id) as total 
            FROM recursos r 
            LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id 
            WHERE r.lenguaje IS NOT NULL 
            GROUP BY r.lenguaje
            ORDER BY total DESC
            LIMIT 10";
    $result = $conexion->query($sql);
    
    $por_lenguaje = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $por_lenguaje[$row['lenguaje']] = (int)$row['total'];
        }
        $result->free();
    }
    
    // Estadística 3: Descargas por día de la semana
    $sql = "SELECT dia_semana, COUNT(*) as total 
            FROM bitacora_descargas 
            GROUP BY dia_semana
            ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')";
    $result = $conexion->query($sql);
    
    $por_dia = array(
        'Lunes' => 0,
        'Martes' => 0,
        'Miércoles' => 0,
        'Jueves' => 0,
        'Viernes' => 0,
        'Sábado' => 0,
        'Domingo' => 0
    );
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $por_dia[$row['dia_semana']] = (int)$row['total'];
        }
        $result->free();
    }
    
    // Estadística 4: Descargas por hora del día
    $sql = "SELECT HOUR(hora_descarga) as hora, COUNT(*) as total 
            FROM bitacora_descargas 
            GROUP BY hora
            ORDER BY hora";
    $result = $conexion->query($sql);
    
    $por_hora = array();
    for ($i = 0; $i < 24; $i++) {
        $por_hora[$i] = 0;
    }
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $por_hora[(int)$row['hora']] = (int)$row['total'];
        }
        $result->free();
    }
    
    // Total de descargas
    $sql = "SELECT COUNT(*) as total FROM bitacora_descargas";
    $result = $conexion->query($sql);
    $total_descargas = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        $total_descargas = (int)$row['total'];
        $result->free();
    }
    
    // Obtener recursos más descargados
    $sql = "SELECT r.id, r.titulo, r.tipo_recurso, r.lenguaje, COUNT(bd.id) as total_descargas
            FROM recursos r
            LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id
            WHERE r.activo = 1
            GROUP BY r.id
            ORDER BY total_descargas DESC
            LIMIT 10";
    $result = $conexion->query($sql);
    
    $mas_descargados = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $mas_descargados[] = array(
                'id' => (int)$row['id'],
                'titulo' => $row['titulo'],
                'tipo_recurso' => $row['tipo_recurso'],
                'lenguaje' => $row['lenguaje'],
                'total_descargas' => (int)$row['total_descargas']
            );
        }
        $result->free();
    }
    
    // Obtener descargas por usuario (si hay usuarios autenticados)
    $sql = "SELECT u.id, u.nombre, u.email, COUNT(bd.id) as total_descargas
            FROM usuarios u
            LEFT JOIN bitacora_descargas bd ON u.id = bd.usuario_id
            WHERE bd.usuario_id IS NOT NULL
            GROUP BY u.id
            ORDER BY total_descargas DESC
            LIMIT 10";
    $result = $conexion->query($sql);
    
    $por_usuario = array();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $por_usuario[] = array(
                'usuario_id' => (int)$row['id'],
                'nombre' => $row['nombre'],
                'email' => $row['email'],
                'total_descargas' => (int)$row['total_descargas']
            );
        }
        $result->free();
    }
    
    $response = array(
        'status' => 'success',
        'total_descargas' => $total_descargas,
        'por_tipo' => $por_tipo,
        'por_lenguaje' => $por_lenguaje,
        'por_dia' => $por_dia,
        'por_hora' => $por_hora,
        'mas_descargados' => $mas_descargados,
        'por_usuario' => $por_usuario
    );
    
    json_response($response, 200);
    
} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>