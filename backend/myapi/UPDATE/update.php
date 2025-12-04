<?php
namespace TECWEB\MYAPI\Update;

use TECWEB\MYAPI\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Update extends DataBase {
    private $response;
    
    public function __construct($db = 'marketzone', $user = 'root', $pass = 'JoshelinLun407') {
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
        
        // VERIFICAR SI YA EXISTE OTRO PRODUCTO CON EL MISMO NOMBRE
        $sql = "SELECT * FROM productos WHERE nombre = '{$jsonOBJ->nombre}' AND id != {$jsonOBJ->id} AND eliminado = 0";
        $result = $this->conexion->query($sql);
        
        if ($result->num_rows == 0) {
            $this->conexion->set_charset("utf8");
            $sql = "UPDATE productos SET 
                    nombre = '{$jsonOBJ->nombre}',
                    marca = '{$jsonOBJ->marca}',
                    modelo = '{$jsonOBJ->modelo}',
                    precio = {$jsonOBJ->precio},
                    detalles = '{$jsonOBJ->detalles}',
                    unidades = {$jsonOBJ->unidades},
                    imagen = '{$jsonOBJ->imagen}'
                    WHERE id = {$jsonOBJ->id}";
            
            if ($this->conexion->query($sql)) {
                $this->response['status'] = "success";
                $this->response['message'] = "Producto actualizado correctamente";
            } else {
                $this->response['message'] = "ERROR: No se ejecutó $sql. " . mysqli_error($this->conexion);
            }
        } else {
            $this->response['message'] = "Ya existe un producto con ese nombre";
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