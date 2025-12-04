<?php
namespace TECWEB\MYAPI\Create;

use TECWEB\MYAPI\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Create extends DataBase {
    private $response;

    public function __construct($db = 'marketzone', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Agrega un nuevo producto a la base de datos
     * @param object $jsonOBJ - Objeto con los datos del producto
     */
    public function add($jsonOBJ) {
        // SE INICIALIZA LA RESPUESTA
        $this->response = array(
            'status'  => 'error',
            'message' => 'Ya existe un producto con ese nombre'
        );
        
        // SE VERIFICA QUE EL NOMBRE NO EXISTA
        $sql = "SELECT * FROM productos WHERE nombre = '{$jsonOBJ->nombre}' AND eliminado = 0";
        $result = $this->conexion->query($sql);
        
        if ($result->num_rows == 0) {
            $this->conexion->set_charset("utf8");
            $sql = "INSERT INTO productos VALUES (null, '{$jsonOBJ->nombre}', '{$jsonOBJ->marca}', '{$jsonOBJ->modelo}', {$jsonOBJ->precio}, '{$jsonOBJ->detalles}', {$jsonOBJ->unidades}, '{$jsonOBJ->imagen}', 0)";
            
            if ($this->conexion->query($sql)) {
                $this->response['status'] = "success";
                $this->response['message'] = "Producto agregado";
            } else {
                $this->response['message'] = "ERROR: No se ejecutó $sql. " . mysqli_error($this->conexion);
            }
        }
        
        $result->free();
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