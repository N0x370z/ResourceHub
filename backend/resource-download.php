<?php

ob_start();
require_once __DIR__.'/database.php';
if (ob_get_length()) ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    if (!isset($_GET['id'])) throw new Exception('ID requerido');
    
    $recurso_id = (int)$_GET['id'];
    
    // Obtener info
    $sql = "SELECT * FROM recursos WHERE id = ? AND activo = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $recurso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recurso = $result->fetch_assoc();
    $stmt->close();
    
    if (!$recurso) throw new Exception('Recurso no encontrado');

    $ruta_fisica = __DIR__ . '/../' . $recurso['archivo_ruta'];
    
    if (!file_exists($ruta_fisica)) {
        // Intenta buscar solo por nombre en uploads si la ruta completa falla
        $ruta_alternativa = __DIR__ . '/../uploads/' . basename($recurso['archivo_ruta']);
        if(file_exists($ruta_alternativa)){
             $recurso['archivo_ruta'] = 'uploads/' . basename($recurso['archivo_ruta']);
        } else {
            throw new Exception('El archivo físico no existe en el servidor');
        }
    }
    
    try {
        iniciar_sesion_segura();
        $uid = obtener_usuario_id();
        $ip = $_SERVER['REMOTE_ADDR'];
        $conn2 = new mysqli($host, $user, $password, $database);
        $conn2->query("INSERT INTO bitacora_descargas (recurso_id, usuario_id, ip_address, fecha_hora) VALUES ($recurso_id, " . ($uid ? $uid : "NULL") . ", '$ip', NOW())");
        $conn2->close();
    } catch (Exception $logError) {}

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
