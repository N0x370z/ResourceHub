<?php
/**
 * ResourceHub - Clase Products (Legacy - Compatibilidad)
 * 
 * Esta clase mantiene compatibilidad con código legacy que usa "productos"
 * pero internamente trabaja con la tabla "recursos"
 * 
 * @deprecated Se recomienda usar las clases específicas: Create, Read, Update, Delete
 */

namespace ResourceHub\API;

require_once __DIR__ . '/DataBase.php';

class Products extends DataBase {
    private $response;

    public function __construct($db = 'resourcehub', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Lista todos los recursos activos
     * 
     * @return void
     */
    public function list() {
        try {
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 
                    ORDER BY r.fecha_subida DESC";
            
            $result = $this->conexion->query($sql);

            if ($result) {
                $rows = $result->fetch_all(MYSQLI_ASSOC);

                if (!empty($rows)) {
                    foreach ($rows as $num => $row) {
                        foreach ($row as $key => $value) {
                            $this->response[$num][$key] = utf8_encode($value);
                        }
                    }
                }
                $result->free();
            } else {
                $this->log_error('Error en list()', ['error' => $this->conexion->error]);
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en list()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Busca recursos por término de búsqueda
     * 
     * @param string $search - Término de búsqueda
     * @return void
     */
    public function search($search) {
        try {
            $searchPattern = "%{$search}%";
            
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND (
                        r.id = ? OR 
                        r.titulo LIKE ? OR 
                        r.descripcion LIKE ? OR 
                        r.tipo_recurso LIKE ? OR 
                        r.lenguaje LIKE ? OR 
                        r.tags LIKE ?
                    )
                    ORDER BY r.fecha_subida DESC";
            
            $stmt = $this->conexion->prepare($sql);
            
            if ($stmt) {
                // Verificar si $search es numérico para la búsqueda por ID
                $searchId = is_numeric($search) ? (int)$search : 0;
                $stmt->bind_param("isssss", $searchId, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $rows = $result->fetch_all(MYSQLI_ASSOC);

                    if (!empty($rows)) {
                        foreach ($rows as $num => $row) {
                            foreach ($row as $key => $value) {
                                $this->response[$num][$key] = utf8_encode($value);
                            }
                        }
                    }
                    $result->free();
                }
                $stmt->close();
            } else {
                $this->log_error('Error en search()', ['error' => $this->conexion->error]);
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en search()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene un recurso específico por ID
     * 
     * @param int $id - ID del recurso
     * @return void
     */
    public function single($id) {
        try {
            $id = (int)$id;
            
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.id = ? AND r.activo = 1";
            
            $stmt = $this->conexion->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $row = $result->fetch_assoc();
                    
                    if ($row) {
                        foreach ($row as $key => $value) {
                            $this->response[$key] = utf8_encode($value);
                        }
                    }
                    $result->free();
                }
                $stmt->close();
            } else {
                $this->log_error('Error en single()', ['error' => $this->conexion->error]);
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en single()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Verifica si existe un recurso con el título dado
     * 
     * @param string $name - Título a verificar
     * @param int $id - ID del recurso actual (para excluirlo en modo edición)
     * @return void
     */
    public function singleByName($name, $id = null) {
        try {
            if (!empty($id)) {
                $sql = "SELECT id FROM recursos WHERE titulo = ? AND id != ? AND activo = 1";
                $stmt = $this->conexion->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("si", $name, $id);
                }
            } else {
                $sql = "SELECT id FROM recursos WHERE titulo = ? AND activo = 1";
                $stmt = $this->conexion->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("s", $name);
                }
            }
            
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $this->response = array(
                        'exists' => $result->num_rows > 0,
                        'message' => $result->num_rows > 0 ? 'El título ya está registrado' : 'Título disponible'
                    );
                    $result->free();
                } else {
                    $this->response = array(
                        'exists' => false,
                        'message' => 'Error al verificar el título: ' . $stmt->error
                    );
                }
                $stmt->close();
            } else {
                $this->response = array(
                    'exists' => false,
                    'message' => 'Error al preparar la consulta'
                );
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en singleByName()', ['exception' => $e->getMessage()]);
            $this->response = array(
                'exists' => false,
                'message' => 'Error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Agrega un nuevo recurso
     * 
     * @param object $jsonOBJ - Objeto con los datos del recurso
     * @return void
     */
    public function add($jsonOBJ) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al agregar el recurso'
        );

        try {
            // Validar campos obligatorios
            if (empty($jsonOBJ->titulo) || empty($jsonOBJ->tipo_recurso) || 
                empty($jsonOBJ->archivo_nombre) || empty($jsonOBJ->archivo_ruta)) {
                $this->response['message'] = 'Faltan campos obligatorios';
                return;
            }

            // Verificar si el título ya existe
            $sql = "SELECT id FROM recursos WHERE titulo = ? AND activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $jsonOBJ->titulo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $this->response['message'] = 'Ya existe un recurso con ese título';
                $stmt->close();
                return;
            }
            $stmt->close();

            // Preparar datos
            $titulo = $this->escape($jsonOBJ->titulo);
            $descripcion = isset($jsonOBJ->descripcion) ? $this->escape($jsonOBJ->descripcion) : '';
            $tipo_recurso = $this->escape($jsonOBJ->tipo_recurso);
            $lenguaje = isset($jsonOBJ->lenguaje) ? $this->escape($jsonOBJ->lenguaje) : null;
            $archivo_nombre = $this->escape($jsonOBJ->archivo_nombre);
            $archivo_ruta = $this->escape($jsonOBJ->archivo_ruta);
            $archivo_tamanio = isset($jsonOBJ->archivo_tamanio) ? (int)$jsonOBJ->archivo_tamanio : 0;
            $tags = isset($jsonOBJ->tags) ? $this->escape($jsonOBJ->tags) : '';
            $usuario_id = isset($jsonOBJ->usuario_id) ? (int)$jsonOBJ->usuario_id : null;

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
                        'titulo' => $jsonOBJ->titulo
                    )
                );
            } else {
                $this->response['message'] = 'Error al insertar: ' . $stmt->error;
            }

            $stmt->close();

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en add()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Elimina lógicamente un recurso
     * 
     * @param int $id - ID del recurso a eliminar
     * @return void
     */
    public function delete($id) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al eliminar el recurso'
        );

        try {
            $id = (int)$id;

            // Verificar que el recurso existe
            $sql = "SELECT id, titulo FROM recursos WHERE id = ? AND activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->response['message'] = 'Recurso no encontrado';
                $stmt->close();
                return;
            }

            $recurso = $result->fetch_assoc();
            $stmt->close();

            // Realizar eliminación lógica
            $sql = "UPDATE recursos SET activo = 0 WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $this->response = array(
                    'status' => 'success',
                    'message' => 'Recurso eliminado exitosamente',
                    'data' => array(
                        'id' => $id,
                        'titulo' => utf8_encode($recurso['titulo'])
                    )
                );
            } else {
                $this->response['message'] = 'No se pudo eliminar el recurso';
            }

            $stmt->close();

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en delete()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Edita/actualiza un recurso existente
     * 
     * @param object $jsonOBJ - Objeto con los datos del recurso a actualizar
     * @return void
     */
    public function edit($jsonOBJ) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al actualizar el recurso'
        );

        try {
            // Validar que venga el ID
            if (!isset($jsonOBJ->id) || empty($jsonOBJ->id)) {
                $this->response['message'] = 'ID del recurso no proporcionado';
                return;
            }

            $id = (int)$jsonOBJ->id;

            // Verificar que el recurso existe
            $sql = "SELECT id FROM recursos WHERE id = ? AND activo = 1";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->response['message'] = 'Recurso no encontrado';
                $stmt->close();
                return;
            }
            $stmt->close();

            // Verificar si el título ya existe en otro recurso
            if (isset($jsonOBJ->titulo)) {
                $sql = "SELECT id FROM recursos WHERE titulo = ? AND id != ? AND activo = 1";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param("si", $jsonOBJ->titulo, $id);
                $stmt->execute();
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

            if (isset($jsonOBJ->titulo)) {
                $campos[] = 'titulo = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->titulo);
            }

            if (isset($jsonOBJ->descripcion)) {
                $campos[] = 'descripcion = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->descripcion);
            }

            if (isset($jsonOBJ->tipo_recurso)) {
                $campos[] = 'tipo_recurso = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->tipo_recurso);
            }

            if (isset($jsonOBJ->lenguaje)) {
                $campos[] = 'lenguaje = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->lenguaje);
            }

            if (isset($jsonOBJ->archivo_nombre)) {
                $campos[] = 'archivo_nombre = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->archivo_nombre);
            }

            if (isset($jsonOBJ->archivo_ruta)) {
                $campos[] = 'archivo_ruta = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->archivo_ruta);
            }

            if (isset($jsonOBJ->archivo_tamanio)) {
                $campos[] = 'archivo_tamanio = ?';
                $tipos .= 'i';
                $valores[] = (int)$jsonOBJ->archivo_tamanio;
            }

            if (isset($jsonOBJ->tags)) {
                $campos[] = 'tags = ?';
                $tipos .= 's';
                $valores[] = $this->escape($jsonOBJ->tags);
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
