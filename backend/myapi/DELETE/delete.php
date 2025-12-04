<?php
namespace TECWEB\MYAPI\Delete;

use TECWEB\MYAPI\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Delete extends DataBase {
    private $response;
    
    public function __construct($db = 'marketzone', $user = 'root', $pass = 'JoshelinLun407') {
        $this->response = array();
        parent::__construct($db, $user, $pass);
    }

    /**
     * Elimina lógicamente un producto (marca como eliminado)
     * @param int $id - ID del producto a eliminar
     */
    public function delete($id) {
        // SE INICIALIZA LA RESPUESTA
        $this->response = array(
            'status'  => 'error',
            'message' => 'La consulta falló'
        );
        
        // SE REALIZA LA QUERY DE ELIMINACIÓN (LÓGICA)
        $sql = "UPDATE productos SET eliminado=1 WHERE id = {$id}";
        
        if ($this->conexion->query($sql)) {
            $this->response['status'] = "success";
            $this->response['message'] = "Producto eliminado";
        } else {
            $this->response['message'] = "ERROR: No se ejecutó $sql. " . mysqli_error($this->conexion);
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