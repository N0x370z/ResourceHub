<?php
/**
 * ResourceHub - Clase Create
 * 
 * Maneja la creación de nuevos recursos en el sistema
 */

namespace ResourceHub\API\Create;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Create extends DataBase {
    private $response;

    public function __construct($db = 'resourcehub', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Agrega un nuevo recurso a la base de datos
     * 
     * @param object $data - Objeto con los datos del recurso
     * @return void
     */
    public function addResource($data) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al agregar el recurso'
        );

        try {
            // Validar que vengan los campos obligatorios
            if (empty($data->titulo) || empty($data->tipo_recurso) || 
                empty($data->archivo_nombre) || empty($data->archivo_ruta)) {
                $this->response['message'] = 'Faltan campos obligatorios';
                return;
            }

            // Escapar y preparar datos
            $titulo = $this->escape($data->titulo);
            $descripcion = isset($data->descripcion) ? $this->escape($data->descripcion) : '';
            $tipo_recurso = $this->escape($data->tipo_recurso);
            $lenguaje = isset($data->lenguaje) ? $this->escape($data->lenguaje) : null;
            $archivo_nombre = $this->escape($data->archivo_nombre);
            $archivo_ruta = $this->escape($data->archivo_ruta);
            $archivo_tamanio = isset($data->archivo_tamanio) ? (int)$data->archivo_tamanio : 0;
            $tags = isset($data->tags) ? $this->escape($data->tags) : '';
            $usuario_id = isset($data->usuario_id) ? (int)$data->usuario_id : null;

            // Verificar si el título ya existe
            $sql = "SELECT id FROM recursos WHERE titulo = ? AND activo = 1";
            $stmt = $this->ejecutar_consulta($sql, 's', [$titulo]);
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $this->response['message'] = 'Ya existe un recurso con ese título';
                $stmt->close();
                return;
            }
            $stmt->close();

            // Insertar el recurso
            if ($lenguaje) {
                $sql = "INSERT INTO recursos 
                        (titulo, descripcion, tipo_recurso, lenguaje, archivo_nombre, 
                         archivo_ruta, archivo_tamanio, tags, usuario_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("ssssssssi", 
                    $titulo, $descripcion, $tipo_recurso, $lenguaje, $archivo_nombre,
                    $archivo_ruta, $archivo_tamanio, $tags, $usuario_id
                );
            } else {
                $sql = "INSERT INTO recursos 
                        (titulo, descripcion, tipo_recurso, archivo_nombre, 
                         archivo_ruta, archivo_tamanio, tags, usuario_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("sssssisi", 
                    $titulo, $descripcion, $tipo_recurso, $archivo_nombre,
                    $archivo_ruta, $archivo_tamanio, $tags, $usuario_id
                );
            }

            if ($stmt->execute()) {
                $this->response = array(
                    'status' => 'success',
                    'message' => 'Recurso agregado exitosamente',
                    'data' => array(
                        'id' => $this->ultimo_id(),
                        'titulo' => $data->titulo
                    )
                );
            } else {
                $this->response['message'] = 'Error al insertar: ' . $stmt->error;
            }

            $stmt->close();

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en addResource', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Retorna los datos en formato JSON
     * 
     * @return string - JSON con los datos
     */
    public function getData() {
        return json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
?>