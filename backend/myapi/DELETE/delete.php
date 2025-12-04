<?php
namespace ResourceHub\API\Delete;

use ResourceHub\API\DataBase;
require_once __DIR__ . '/../DataBase.php';

class Delete extends DataBase {
    private $response;
    
    public function __construct($db = 'resourcehub', $user = 'root', $pass = 'JoshelinLun407') {
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
        
        // SE REALIZA LA QUERY DE ELIMINACIÓN (LÓGICA) usando prepared statement
        $sql = "UPDATE productos SET eliminado=1 WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $this->response['status'] = "success";
            $this->response['message'] = "Producto eliminado";
        } else {
            $this->response['message'] = "ERROR: No se ejecutó la consulta. " . $stmt->error;
        }
        
        $stmt->close();
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