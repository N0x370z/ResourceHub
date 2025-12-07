<?php
/**
 * ResourceHub - Descargar Recurso
 */

ob_start();
require_once __DIR__.'/database.php';
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    if (!isset($_GET['id'])) throw new Exception('ID requerido');
    
    $recurso_id = (int)$_GET['id'];
    
    // Obtener info del recurso
    $sql = "SELECT * FROM recursos WHERE id = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $recurso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recurso = $result->fetch_assoc();
    $stmt->close();
    
    if (!$recurso) throw new Exception('Recurso no encontrado');
    
    // Ruta física
    $ruta_fisica = __DIR__ . '/../' . $recurso['archivo_ruta'];
    
    if (!file_exists($ruta_fisica)) {
        $ruta_alternativa = __DIR__ . '/../uploads/' . basename($recurso['archivo_ruta']);
        if(file_exists($ruta_alternativa)){
             $recurso['archivo_ruta'] = 'uploads/' . basename($recurso['archivo_ruta']);
        } else {
            throw new Exception('El archivo físico no existe en el servidor');
        }
    }
    
    // --- REGISTRO DE DESCARGA---
    try {
        iniciar_sesion_segura();
        $uid = obtener_usuario_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Conexión auxiliar para el log
        $conn2 = new mysqli($host, $user, $password, $database);
        $conn2->set_charset("utf8mb4"); // Importante para los acentos en días
        
        $usuario_sql = $uid ? $uid : "NULL";
        
        // AQUÍ ESTÁ LA MAGIA: 
        // 1. ELT(DAYOFWEEK...) calcula el día en español basado en la fecha actual
        // 2. CURTIME() guarda la hora actual
        $sql_log = "INSERT INTO bitacora_descargas 
                    (recurso_id, usuario_id, ip_address, fecha_hora, dia_semana, hora_descarga) 
                    VALUES 
                    ($recurso_id, 
                     $usuario_sql, 
                     '$ip', 
                     NOW(),
                     ELT(DAYOFWEEK(NOW()), 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                     CURTIME()
                    )";
                    
        $conn2->query($sql_log);
        $conn2->close();
    } catch (Exception $logError) {
        // Fallo silencioso del log para no detener la descarga
    }

    $response = [
        'status' => 'success',
        'data' => [
            'archivo_ruta' => $recurso['archivo_ruta'],
            'archivo_nombre' => $recurso['archivo_nombre']
        ]
    ];

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

if (ob_get_length()) ob_clean();
echo json_encode($response);
$conexion->close();
?>