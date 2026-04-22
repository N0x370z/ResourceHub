<?php

ob_start();
require_once __DIR__ . '/database.php';
header('Content-Type: application/json; charset=utf-8');

$response = ['status' => 'error', 'message' => 'Error desconocido'];

try {
    verificar_metodo('POST');
    requerir_autenticacion();

    if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No se recibió ningún archivo o hubo un error en la subida');
    }

    $uploadDir = __DIR__ . '/../uploads/';
    
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de subidas');
        }
    }

    $file = $_FILES['archivo'];
    $allowedMimeTypes = [
        'application/pdf',
        'application/zip',
        'application/x-zip-compressed',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'text/plain',
        'text/csv',
        'application/json',
        'image/png',
        'image/jpeg',
        'image/gif'
    ];
    $fileValidation = validar_archivo($file, $allowedMimeTypes, 10485760);
    if (!$fileValidation['valid']) {
        throw new Exception($fileValidation['message']);
    }
    
    // Generar nombre único para evitar sobrescribir
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['pdf', 'zip', 'docx', 'doc', 'xlsx', 'xls', 'txt', 'csv', 'json', 'png', 'jpg', 'jpeg', 'gif'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new Exception('Extensión de archivo no permitida');
    }

    $nombreOriginal = pathinfo($file['name'], PATHINFO_FILENAME);
    // Limpiar nombre de archivo de caracteres raros
    $nombreLimpio = preg_replace('/[^A-Za-z0-9\-_]/', '_', $nombreOriginal);
    $nombreLimpio = trim($nombreLimpio, '_');
    if ($nombreLimpio === '') {
        $nombreLimpio = 'archivo';
    }
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
$conexion->close();
?>