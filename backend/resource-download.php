<?php
require_once __DIR__.'/database.php';

verificar_metodo('GET');

if (!isset($_GET['id'])) {
    json_response(['status' => 'error', 'message' => 'ID no proporcionado'], 400);
}

$recurso_id = (int)$_GET['id'];
$usuario_id = obtener_usuario_id();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Obtener información del recurso
$sql = "SELECT * FROM recursos WHERE id = ? AND activo = 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $recurso_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    json_response(['status' => 'error', 'message' => 'Recurso no encontrado'], 404);
}

$recurso = $result->fetch_assoc();
$stmt->close();

// Registrar descarga
$sql = "CALL sp_registrar_descarga(?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $recurso_id, $usuario_id, $ip);
$stmt->execute();
$stmt->close();

json_response([
    'status' => 'success',
    'message' => 'Descarga registrada exitosamente',
    'data' => [
        'id' => $recurso['id'],
        'titulo' => utf8_encode($recurso['titulo']),
        'archivo_nombre' => utf8_encode($recurso['archivo_nombre']),
        'archivo_ruta' => utf8_encode($recurso['archivo_ruta']),
        'archivo_tamanio' => $recurso['archivo_tamanio']
    ]
], 200);

$conexion->close();
?>
*/
?>