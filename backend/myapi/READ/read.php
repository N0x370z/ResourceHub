<?php
/**
 * ResourceHub - Clase Read
 * 
 * Maneja la lectura de recursos desde la base de datos
 */

namespace ResourceHub\API\Read;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Read extends DataBase {
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
            $search = $this->escape($search);
            
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND (
                        r.titulo LIKE '%{$search}%' OR 
                        r.descripcion LIKE '%{$search}%' OR 
                        r.tipo_recurso LIKE '%{$search}%' OR 
                        r.lenguaje LIKE '%{$search}%' OR 
                        r.tags LIKE '%{$search}%'
                    )
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
            
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);
            
            if ($stmt) {
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row) {
                    foreach ($row as $key => $value) {
                        $this->response[$key] = utf8_encode($value);
                    }
                }
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en single()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Filtra recursos por tipo
     * 
     * @param string $tipo - Tipo de recurso
     * @return void
     */
    public function filterByType($tipo) {
        try {
            $tipo = $this->escape($tipo);
            
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND r.tipo_recurso = '{$tipo}'
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
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en filterByType()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Filtra recursos por lenguaje
     * 
     * @param string $lenguaje - Lenguaje de programación
     * @return void
     */
    public function filterByLanguage($lenguaje) {
        try {
            $lenguaje = $this->escape($lenguaje);
            
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND r.lenguaje = '{$lenguaje}'
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
            }

        } catch (\Exception $e) {
            $this->log_error('Excepción en filterByLanguage()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene estadísticas generales de recursos
     * 
     * @return void
     */
    public function getStats() {
        try {
            $stats = array();

            // Total de recursos
            $sql = "SELECT COUNT(*) as total FROM recursos WHERE activo = 1";
            $result = $this->conexion->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['total_recursos'] = (int)$row['total'];
                $result->free();
            }

            // Recursos por tipo
            $sql = "SELECT tipo_recurso, COUNT(*) as cantidad 
                    FROM recursos 
                    WHERE activo = 1 
                    GROUP BY tipo_recurso";
            $result = $this->conexion->query($sql);
            if ($result) {
                $tipos = array();
                while ($row = $result->fetch_assoc()) {
                    $tipos[$row['tipo_recurso']] = (int)$row['cantidad'];
                }
                $stats['por_tipo'] = $tipos;
                $result->free();
            }

            // Recursos por lenguaje
            $sql = "SELECT lenguaje, COUNT(*) as cantidad 
                    FROM recursos 
                    WHERE activo = 1 AND lenguaje IS NOT NULL 
                    GROUP BY lenguaje";
            $result = $this->conexion->query($sql);
            if ($result) {
                $lenguajes = array();
                while ($row = $result->fetch_assoc()) {
                    $lenguajes[$row['lenguaje']] = (int)$row['cantidad'];
                }
                $stats['por_lenguaje'] = $lenguajes;
                $result->free();
            }

            $this->response = $stats;

        } catch (\Exception $e) {
            $this->log_error('Excepción en getStats()', ['exception' => $e->getMessage()]);
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