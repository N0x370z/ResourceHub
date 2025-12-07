<?php
/**
 * ResourceHub - Clase Read
 * Maneja la lectura de recursos desde la base de datos
 * VERSION ROBUSTA: Detecta automáticamente la configuración del servidor
 */

namespace ResourceHub\API\Read;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Read extends DataBase {
    private $response;

    public function __construct($db = 'resourcehub', $user = 'root', $pass = '') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Función auxiliar segura para codificación
     * Detecta si mb_convert_encoding existe para evitar errores fatales
     */
    private function encode_utf8($string) {
        if ($string === null) return null;
        
        // Verifica si la extensión mbstring está activa
        if (function_exists('mb_convert_encoding')) {
            // Intenta convertir silenciosamente
            return @mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
        }
        
        // Si no existe la función, devuelve el string original
        // (La mayoría de las veces esto funciona si la BD ya está en UTF8)
        return $string;
    }

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
                            $this->response[$num][$key] = $this->encode_utf8($value);
                        }
                    }
                }
                $result->free();
            }
        } catch (\Exception $e) {
            // No hacemos log para no ensuciar la salida JSON en caso de error
        }
    }

    public function search($search) {
        try {
            $searchPattern = "%{$search}%";
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND (
                        r.titulo LIKE ? OR 
                        r.descripcion LIKE ? OR 
                        r.tipo_recurso LIKE ? OR 
                        r.lenguaje LIKE ? OR 
                        r.tags LIKE ?
                    )
                    ORDER BY r.fecha_subida DESC";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $rows = $result->fetch_all(MYSQLI_ASSOC);
                    if (!empty($rows)) {
                        foreach ($rows as $num => $row) {
                            foreach ($row as $key => $value) {
                                $this->response[$num][$key] = $this->encode_utf8($value);
                            }
                        }
                    }
                }
                $stmt->close();
            }
        } catch (\Exception $e) {}
    }

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
                        $this->response[$key] = $this->encode_utf8($value);
                    }
                }
                $stmt->close();
            }
        } catch (\Exception $e) {}
    }

    public function filterByType($tipo) {
        try {
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND r.tipo_recurso = ?
                    ORDER BY r.fecha_subida DESC";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $tipo);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result) {
                    $rows = $result->fetch_all(MYSQLI_ASSOC);
                    if (!empty($rows)) {
                        foreach ($rows as $num => $row) {
                            foreach ($row as $key => $value) {
                                $this->response[$num][$key] = $this->encode_utf8($value);
                            }
                        }
                    }
                }
                $stmt->close();
            }
        } catch (\Exception $e) {}
    }

    public function filterByLanguage($lenguaje) {
        try {
            $sql = "SELECT r.*, u.nombre as nombre_usuario 
                    FROM recursos r 
                    LEFT JOIN usuarios u ON r.usuario_id = u.id 
                    WHERE r.activo = 1 AND r.lenguaje = ?
                    ORDER BY r.fecha_subida DESC";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $lenguaje);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result) {
                    $rows = $result->fetch_all(MYSQLI_ASSOC);
                    if (!empty($rows)) {
                        foreach ($rows as $num => $row) {
                            foreach ($row as $key => $value) {
                                $this->response[$num][$key] = $this->encode_utf8($value);
                            }
                        }
                    }
                }
                $stmt->close();
            }
        } catch (\Exception $e) {}
    }

    public function getStats() {
        try {
            $stats = array();
            $sql = "SELECT COUNT(*) as total FROM recursos WHERE activo = 1";
            $result = $this->conexion->query($sql);
            if ($result) {
                $row = $result->fetch_assoc();
                $stats['total_recursos'] = (int)$row['total'];
            }

            $sql = "SELECT tipo_recurso, COUNT(*) as cantidad FROM recursos WHERE activo = 1 GROUP BY tipo_recurso";
            $result = $this->conexion->query($sql);
            if ($result) {
                $tipos = array();
                while ($row = $result->fetch_assoc()) $tipos[$row['tipo_recurso']] = (int)$row['cantidad'];
                $stats['por_tipo'] = $tipos;
            }

            $sql = "SELECT lenguaje, COUNT(*) as cantidad FROM recursos WHERE activo = 1 AND lenguaje IS NOT NULL GROUP BY lenguaje";
            $result = $this->conexion->query($sql);
            if ($result) {
                $lenguajes = array();
                while ($row = $result->fetch_assoc()) $lenguajes[$row['lenguaje']] = (int)$row['cantidad'];
                $stats['por_lenguaje'] = $lenguajes;
            }
            $this->response = $stats;
        } catch (\Exception $e) {}
    }

    public function getData() {
        // Limpieza agresiva del buffer
        if (ob_get_length()) ob_clean(); 
        
        // Devolver JSON
        return json_encode($this->response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// SIN etiqueta de cierre PHP al final para evitar espacios en blanco