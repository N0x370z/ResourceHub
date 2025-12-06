<?php
/**
 * ResourceHub - Clase Update
 * 
 * Maneja la actualización de recursos existentes
 */

namespace ResourceHub\API\Update;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Update extends DataBase {
    private $response;
    
    public function __construct($db = 'resourcehub', $user = 'root', $pass = '') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Actualiza un recurso existente
     * 
     * @param object $data - Objeto con los datos del recurso a actualizar
     * @return void
     */
    public function edit($data) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al actualizar el recurso'
        );

        try {
            // Validar que venga el ID
            if (!isset($data->id) || empty($data->id)) {
                $this->response['message'] = 'ID del recurso no proporcionado';
                return;
            }

            $id = (int)$data->id;

            // Verificar que el recurso existe
            $sql = "SELECT id FROM recursos WHERE id = ? AND activo = 1";
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->response['message'] = 'Recurso no encontrado';
                $stmt->close();
                return;
            }
            $stmt->close();

            // Verificar si el título ya existe en otro recurso
            if (isset($data->titulo)) {
                $titulo = $this->escape($data->titulo);
                $sql = "SELECT id FROM recursos WHERE titulo = ? AND id != ? AND activo = 1";
                $stmt = $this->ejecutar_consulta($sql, 'si', [$titulo, $id]);
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $this->response['message'] = 'Ya existe otro recurso con ese título';
                    $stmt->close();
                    return;
                }
                $stmt->close();
            }

            // Construir la consulta de actualización dinámicamente
            $campos = array();
            $tipos = '';
            $valores = array();

            if (isset($data->titulo)) {
                $campos[] = 'titulo = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->titulo);
            }

            if (isset($data->descripcion)) {
                $campos[] = 'descripcion = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->descripcion);
            }

            if (isset($data->tipo_recurso)) {
                $campos[] = 'tipo_recurso = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->tipo_recurso);
            }

            if (isset($data->lenguaje)) {
                $campos[] = 'lenguaje = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->lenguaje);
            }

            if (isset($data->archivo_nombre)) {
                $campos[] = 'archivo_nombre = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->archivo_nombre);
            }

            if (isset($data->archivo_ruta)) {
                $campos[] = 'archivo_ruta = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->archivo_ruta);
            }

            if (isset($data->archivo_tamanio)) {
                $campos[] = 'archivo_tamanio = ?';
                $tipos .= 'i';
                $valores[] = (int)$data->archivo_tamanio;
            }

            if (isset($data->tags)) {
                $campos[] = 'tags = ?';
                $tipos .= 's';
                $valores[] = $this->escape($data->tags);
            }

            // Si no hay campos para actualizar
            if (empty($campos)) {
                $this->response['message'] = 'No hay campos para actualizar';
                return;
            }

            // Agregar el ID al final
            $tipos .= 'i';
            $valores[] = $id;

            // Construir y ejecutar la consulta
            $sql = "UPDATE recursos SET " . implode(', ', $campos) . " WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param($tipos, ...$valores);

            if ($stmt->execute()) {
                $this->response = array(
                    'status' => 'success',
                    'message' => 'Recurso actualizado exitosamente',
                    'data' => array(
                        'id' => $id
                    )
                );
            } else {
                $this->response['message'] = 'Error al actualizar: ' . $stmt->error;
            }

            $stmt->close();

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en edit()', ['exception' => $e->getMessage()]);
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