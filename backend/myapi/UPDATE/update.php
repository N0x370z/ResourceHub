<?php
namespace ResourceHub\API\Update;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Update extends DataBase {
    private $response;
    
    public function __construct($db = 'resourcehub', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Edita/actualiza un producto existente
     * @param object $jsonOBJ - Objeto con los datos del producto a actualizar
     */
    public function edit($jsonOBJ) {
        // SE INICIALIZA LA RESPUESTA
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al actualizar el producto'
        );
        
        // VERIFICAR SI YA EXISTE OTRO PRODUCTO CON EL MISMO NOMBRE (usando prepared statement)
        $sql = "SELECT * FROM productos WHERE nombre = ? AND id != ? AND eliminado = 0";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("si", $jsonOBJ->nombre, $jsonOBJ->id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $this->conexion->set_charset("utf8mb4");
            $sql = "UPDATE productos SET 
                    nombre = ?,
                    marca = ?,
                    modelo = ?,
                    precio = ?,
                    detalles = ?,
                    unidades = ?,
                    imagen = ?
                    WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssdsssi", 
                $jsonOBJ->nombre,
                $jsonOBJ->marca,
                $jsonOBJ->modelo,
                $jsonOBJ->precio,
                $jsonOBJ->detalles,
                $jsonOBJ->unidades,
                $jsonOBJ->imagen,
                $jsonOBJ->id
            );
            
            if ($stmt->execute()) {
                $this->response['status'] = "success";
                $this->response['message'] = "Producto actualizado correctamente";
            } else {
                $this->response['message'] = "ERROR: No se ejecutó la consulta. " . $stmt->error;
            }
            $stmt->close();
        } else {
            $this->response['message'] = "Ya existe un producto con ese nombre";
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