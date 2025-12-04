<?php
/**
 * ResourceHub - Agregar Recurso
 * Endpoint para crear nuevos recursos
 */

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

use ResourceHub\API\Create\Create;

// Verificar método HTTP
verificar_metodo('POST');

// Requerir autenticación
requerir_autenticacion();

$resource = new Create('resourcehub');
$resourceData = file_get_contents('php://input');

if (!empty($resourceData)) {
    $jsonOBJ = json_decode($resourceData);
    
    // Agregar el usuario_id de la sesión
    $jsonOBJ->usuario_id = obtener_usuario_id();
    
    $resource->addResource($jsonOBJ);
}

echo $resource->getData();
?>