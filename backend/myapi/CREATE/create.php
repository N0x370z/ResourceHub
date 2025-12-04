<?php
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
     * Agrega un nuevo producto a la base de datos
     * @param object $jsonOBJ - Objeto con los datos del producto
     */
    public function add($jsonOBJ) {
        // SE INICIALIZA LA RESPUESTA
        $this->response = array(
            'status'  => 'error',
            'message' => 'Ya existe un producto con ese nombre'
        );
        
        // SE VERIFICA QUE EL NOMBRE NO EXISTA (usando prepared statement)
        $sql = "SELECT * FROM productos WHERE nombre = ? AND eliminado = 0";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $jsonOBJ->nombre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $this->conexion->set_charset("utf8mb4");
            $sql = "INSERT INTO productos (nombre, marca, modelo, precio, detalles, unidades, imagen, eliminado) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssdsss", 
                $jsonOBJ->nombre, 
                $jsonOBJ->marca, 
                $jsonOBJ->modelo, 
                $jsonOBJ->precio, 
                $jsonOBJ->detalles, 
                $jsonOBJ->unidades, 
                $jsonOBJ->imagen
            );
            
            if ($stmt->execute()) {
                $this->response['status'] = "success";
                $this->response['message'] = "Producto agregado";
            } else {
                $this->response['message'] = "ERROR: No se ejecutó la consulta. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $stmt->close();
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