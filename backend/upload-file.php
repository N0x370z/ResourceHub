<?php

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$response = ['status' => 'error', 'message' => 'Error desconocido'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ningún archivo o hubo un error en la subida');
    }

    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('No se pudo crear el directorio de subidas');
        }
    }

    $file = $_FILES['archivo'];
    
    // Generar nombre único para evitar sobrescribir
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreOriginal = pathinfo($file['name'], PATHINFO_FILENAME);
    // Limpiar nombre de archivo de caracteres raros
    $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $nombreOriginal);
    $nuevoNombre = $nombreLimpio . '_' . time() . '.' . $extension;
    
    $destino = $uploadDir . $nuevoNombre;

    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $destino)) {
        $response = [
            'status' => 'success',
            'message' => 'Archivo subido correctamente',
            'data' => [
                'archivo_nombre' => $file['name'], // Nombre original para mostrar
                // Ruta relativa para guardar en BD
                'archivo_ruta' => 'uploads/' . $nuevoNombre, 
                'archivo_tamanio' => $file['size']
            ]
        ];
    } else {
        throw new Exception('Error al mover el archivo al directorio de destino');
    }

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

if (ob_get_length()) ob_clean();
echo json_encode($response);
?>