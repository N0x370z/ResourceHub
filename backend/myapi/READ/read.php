<?php
namespace TECWEB\MYAPI\Read;

use TECWEB\MYAPI\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Read extends DataBase {
    private $response;

    public function __construct($db = 'marketzone', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Lista todos los productos no eliminados
     */
    public function list() {
        // SE REALIZA LA QUERY DE BÚSQUEDA
        if ($result = $this->conexion->query("SELECT * FROM productos WHERE eliminado = 0")) {
            // SE OBTIENEN LOS RESULTADOS
            $rows = $result->fetch_all(MYSQLI_ASSOC);

            if (!is_null($rows)) {
                // SE CODIFICAN A UTF-8 LOS DATOS Y SE MAPEAN AL ARREGLO DE RESPUESTA
                foreach ($rows as $num => $row) {
                    foreach ($row as $key => $value) {
                        $this->response[$num][$key] = utf8_encode($value);
                    }
                }
            }
            $result->free();
        } else {
            die('Query Error: ' . mysqli_error($this->conexion));
        }
    }

    /**
     * Busca productos por ID, nombre, marca o detalles
     * @param string $search - Término de búsqueda
     */
    public function search($search) {
        // SE REALIZA LA QUERY DE BÚSQUEDA
        $sql = "SELECT * FROM productos WHERE (id = '{$search}' OR nombre LIKE '%{$search}%' OR marca LIKE '%{$search}%' OR detalles LIKE '%{$search}%') AND eliminado = 0";
        
        if ($result = $this->conexion->query($sql)) {
            // SE OBTIENEN LOS RESULTADOS
            $rows = $result->fetch_all(MYSQLI_ASSOC);

            if (!is_null($rows)) {
                // SE CODIFICAN A UTF-8 LOS DATOS Y SE MAPEAN AL ARREGLO DE RESPUESTA
                foreach ($rows as $num => $row) {
                    foreach ($row as $key => $value) {
                        $this->response[$num][$key] = utf8_encode($value);
                    }
                }
            }
            $result->free();
        } else {
            die('Query Error: ' . mysqli_error($this->conexion));
        }
    }

    /**
     * Obtiene un producto específico por ID
     * @param int $id - ID del producto
     */
    public function single($id) {
        // SE REALIZA LA QUERY DE BÚSQUEDA
        $sql = "SELECT * FROM productos WHERE id = {$id}";
        
        if ($result = $this->conexion->query($sql)) {
            // SE OBTIENE EL RESULTADO
            $row = $result->fetch_assoc();
            
            if (!is_null($row)) {
                // SE CODIFICAN A UTF-8 LOS DATOS
                foreach ($row as $key => $value) {
                    $this->response[$key] = utf8_encode($value);
                }
            }
            $result->free();
        } else {
            die('Query Error: ' . mysqli_error($this->conexion));
        }
    }

    /**
     * Verifica si existe un producto con el nombre dado
     * @param string $name - Nombre a verificar
     * @param int $id - ID del producto actual (para excluirlo en modo edición)
     */
    public function singleByName($name, $id = null) {
        // ESCAPAR CARACTERES ESPECIALES
        $name = $this->conexion->real_escape_string($name);
        
        // SE REALIZA LA QUERY DE BÚSQUEDA
        if (!empty($id)) {
            $sql = "SELECT id FROM productos WHERE nombre = '{$name}' AND id != {$id} AND eliminado = 0";
        } else {
            $sql = "SELECT id FROM productos WHERE nombre = '{$name}' AND eliminado = 0";
        }
        
        if ($result = $this->conexion->query($sql)) {
            // SI ENCUENTRA AL MENOS UN REGISTRO
            $this->response = array(
                'exists' => $result->num_rows > 0,
                'message' => $result->num_rows > 0 ? 'El nombre ya está registrado' : 'Nombre disponible'
            );
            $result->free();
        } else {
            $this->response = array(
                'exists' => false,
                'message' => 'Error al verificar el nombre'
            );
        }
    }

    /**
     * Retorna los datos en formato JSON
     * @return string - JSON con los datos
     */
    public function getData() {
        return json_encode($this->response, JSON_PRETTY_PRINT);
    }
}
?>