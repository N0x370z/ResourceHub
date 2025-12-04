<?php
require_once __DIR__.'/database.php';

iniciar_sesion_segura();

if (esta_autenticado()) {
    json_response([
        'status' => 'success',
        'authenticated' => true,
        'data' => [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['nombre'],
            'email' => $_SESSION['email'],
            'rol' => $_SESSION['rol']
        ]
    ], 200);
} else {
    json_response([
        'status' => 'error',
        'authenticated' => false,
        'message' => 'No autenticado'
    ], 401);
}
?>