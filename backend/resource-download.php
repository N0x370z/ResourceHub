<?php
/**
 * ResourceHub - Descargar Recurso
 */

ob_start();
require_once __DIR__.'/database.php';
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');

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
    
    $archivoRuta = (string)$recurso['archivo_ruta'];
    if (!preg_match('#^uploads/[A-Za-z0-9._-]+$#', $archivoRuta)) {
        throw new Exception('Ruta de archivo no válida');
    }

    // Ruta física
    $ruta_fisica = __DIR__ . '/../' . $archivoRuta;
    
    if (!file_exists($ruta_fisica)) {
        throw new Exception('El archivo físico no existe en el servidor');
    }
    
    // --- REGISTRO DE DESCARGA---
    try {
        iniciar_sesion_segura();
        $uid = obtener_usuario_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Conexión auxiliar para el log
        $conn2 = new mysqli($host, $user, $password, $database);
        $conn2->set_charset("utf8mb4"); // Importante para los acentos en días
        
        // ELT(DAYOFWEEK...) calcula el día en español basado en la fecha actual
        $sql_log = "INSERT INTO bitacora_descargas 
                    (recurso_id, usuario_id, ip_address, fecha_hora, dia_semana, hora_descarga) 
                    VALUES 
                    (?, ?, ?, NOW(),
                     ELT(DAYOFWEEK(NOW()), 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
                     CURTIME())";
        $stmtLog = $conn2->prepare($sql_log);
        if ($stmtLog) {
            $stmtLog->bind_param('iis', $recurso_id, $uid, $ip);
            $stmtLog->execute();
            $stmtLog->close();
        }
        $conn2->close();
    } catch (Exception $logError) {
        // Fallo silencioso del log para no detener la descarga
    }

    $response = [
        'status' => 'success',
        'data' => [
            'archivo_ruta' => $archivoRuta,
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