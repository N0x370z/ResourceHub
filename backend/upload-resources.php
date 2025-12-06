<?php
/**
 * ResourceHub - Subida de Archivos
 * 
 * Este endpoint maneja la subida de archivos al servidor
 * Método HTTP: POST (multipart/form-data)
 * Parámetros: archivo (file)
 */

require_once __DIR__.'/database.php';

// Requerir autenticación
requerir_autenticacion();

// Configurar headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar método HTTP
verificar_metodo('POST');

$response = ['status' => 'error', 'message' => 'Error al subir archivo'];

try {
    // Verificar que se haya enviado un archivo
    if (!isset($_FILES['archivo'])) {
        json_response([
            'status' => 'error',
            'message' => 'No se recibió ningún archivo'
        ], 400);
    }

    $file = $_FILES['archivo'];

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
        ];

        $error_msg = $error_messages[$file['error']] ?? 'Error desconocido al subir el archivo';
        json_response(['status' => 'error', 'message' => $error_msg], 400);
    }

    // Validar tamaño del archivo (máximo 10MB)
    $max_size = 10 * 1024 * 1024; // 10MB en bytes
    if ($file['size'] > $max_size) {
        json_response([
            'status' => 'error',
            'message' => 'El archivo excede el tamaño máximo permitido (10MB)'
        ], 400);
    }

    // Validar que el archivo no esté vacío
    if ($file['size'] === 0) {
        json_response([
            'status' => 'error',
            'message' => 'El archivo está vacío'
        ], 400);
    }

    // Obtener información del archivo
    $nombre_original = $file['name'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    $mime_type = mime_content_type($file['tmp_name']);

    // Lista de extensiones permitidas
    $extensiones_permitidas = [
        'php', 'js', 'html', 'css', 'json', 'xml',
        'txt', 'md', 'pdf', 'doc', 'docx',
        'zip', 'rar', 'tar', 'gz',
        'jpg', 'jpeg', 'png', 'gif', 'svg',
        'sql', 'py', 'java', 'cpp', 'c', 'h',
        'rb', 'go', 'rs', 'swift', 'kt',
        'csv', 'xls', 'xlsx'
    ];

    // Validar extensión
    if (!in_array($extension, $extensiones_permitidas)) {
        json_response([
            'status' => 'error',
            'message' => 'Tipo de archivo no permitido. Extensión: .' . $extension
        ], 400);
    }

    // Crear directorio de uploads si no existe
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            json_response([
                'status' => 'error',
                'message' => 'Error al crear el directorio de uploads'
            ], 500);
        }
    }

    // Verificar que el directorio tenga permisos de escritura
    if (!is_writable($upload_dir)) {
        json_response([
            'status' => 'error',
            'message' => 'El directorio de uploads no tiene permisos de escritura'
        ], 500);
    }

    // Sanitizar el nombre del archivo
    $nombre_limpio = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($nombre_original, PATHINFO_FILENAME));
    
    // Generar nombre único para evitar sobrescrituras
    $nombre_unico = $nombre_limpio . '_' . uniqid() . '_' . time() . '.' . $extension;
    
    // Ruta completa del archivo
    $ruta_completa = $upload_dir . $nombre_unico;
    $ruta_relativa = 'uploads/' . $nombre_unico;

    // Mover el archivo desde el temporal al destino final
    if (!move_uploaded_file($file['tmp_name'], $ruta_completa)) {
        json_response([
            'status' => 'error',
            'message' => 'Error al guardar el archivo en el servidor'
        ], 500);
    }

    // Establecer permisos del archivo
    chmod($ruta_completa, 0644);

    // Registrar en bitácora
    $usuario_id = obtener_usuario_id();
    registrar_acceso($conexion, $usuario_id, 'upload_archivo');

    // Respuesta exitosa
    json_response([
        'status' => 'success',
        'message' => 'Archivo subido exitosamente',
        'data' => [
            'archivo_nombre' => $nombre_original,
            'archivo_nombre_servidor' => $nombre_unico,
            'archivo_ruta' => $ruta_relativa,
            'archivo_tamanio' => $file['size'],
            'archivo_extension' => $extension,
            'archivo_tipo_mime' => $mime_type
        ]
    ], 200);

} catch (Exception $e) {
    json_response([
        'status' => 'error',
        'message' => 'Error del servidor: ' . $e->getMessage()
    ], 500);
}

$conexion->close();
?>