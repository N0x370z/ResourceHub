<?php
/**
 * ResourceHub - Clase Delete
 * 
 * Maneja la eliminación lógica de recursos
 */

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
     * Elimina lógicamente un recurso (marca como inactivo)
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
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);
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
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);

            if ($stmt && $stmt->affected_rows > 0) {
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

            if ($stmt) {
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en delete()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Elimina físicamente un recurso de la base de datos
     * (Usar con precaución)
     * 
     * @param int $id - ID del recurso a eliminar
     * @return void
     */
    public function deletePermanent($id) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al eliminar permanentemente el recurso'
        );

        try {
            $id = (int)$id;

            // Verificar que el recurso existe
            $sql = "SELECT id, titulo FROM recursos WHERE id = ?";
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->response['message'] = 'Recurso no encontrado';
                $stmt->close();
                return;
            }

            $recurso = $result->fetch_assoc();
            $stmt->close();

            // Eliminar permanentemente
            $sql = "DELETE FROM recursos WHERE id = ?";
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);

            if ($stmt && $stmt->affected_rows > 0) {
                $this->response = array(
                    'status' => 'success',
                    'message' => 'Recurso eliminado permanentemente',
                    'data' => array(
                        'id' => $id,
                        'titulo' => utf8_encode($recurso['titulo'])
                    )
                );
            } else {
                $this->response['message'] = 'No se pudo eliminar el recurso';
            }

            if ($stmt) {
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en deletePermanent()', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Restaura un recurso eliminado lógicamente
     * 
     * @param int $id - ID del recurso a restaurar
     * @return void
     */
    public function restore($id) {
        $this->response = array(
            'status'  => 'error',
            'message' => 'Error al restaurar el recurso'
        );

        try {
            $id = (int)$id;

            // Verificar que el recurso existe y está inactivo
            $sql = "SELECT id, titulo FROM recursos WHERE id = ? AND activo = 0";
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $this->response['message'] = 'Recurso no encontrado o ya está activo';
                $stmt->close();
                return;
            }

            $recurso = $result->fetch_assoc();
            $stmt->close();

            // Restaurar el recurso
            $sql = "UPDATE recursos SET activo = 1 WHERE id = ?";
            $stmt = $this->ejecutar_consulta($sql, 'i', [$id]);

            if ($stmt && $stmt->affected_rows > 0) {
                $this->response = array(
                    'status' => 'success',
                    'message' => 'Recurso restaurado exitosamente',
                    'data' => array(
                        'id' => $id,
                        'titulo' => utf8_encode($recurso['titulo'])
                    )
                );
            } else {
                $this->response['message'] = 'No se pudo restaurar el recurso';
            }

            if ($stmt) {
                $stmt->close();
            }

        } catch (\Exception $e) {
            $this->response['message'] = 'Excepción: ' . $e->getMessage();
            $this->log_error('Error en restore()', ['exception' => $e->getMessage()]);
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