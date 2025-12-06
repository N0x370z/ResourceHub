<?php
/**
 * ResourceHub - Clase Base de Conexión a BD
 * 
 * Esta clase abstracta proporciona la conexión base
 * para todas las operaciones CRUD del sistema
 */

namespace ResourceHub\API;

abstract class DataBase {
    protected $conexion;

    /**
     * Constructor - Establece conexión a la base de datos
     * 
     * @param string $db Nombre de la base de datos
     * @param string $user Usuario de MySQL
     * @param string $pass Contraseña de MySQL
     * @param string $host Host del servidor MySQL
     */
    public function __construct(
        $db = 'resourcehub', 
        $user = 'root', 
        $pass = '', 
        $host = 'localhost'
    ) {
        // Crear conexión
        $this->conexion = new \mysqli($host, $user, $pass, $db);
        
        // Verificar errores de conexión
        if ($this->conexion->connect_error) {
            die(json_encode([
                'status' => 'error',
                'message' => 'Error de conexión: ' . $this->conexion->connect_error
            ]));
        }
        
        // Establecer charset UTF-8
        $this->conexion->set_charset("utf8mb4");
    }

    /**
     * Destructor - Cierra la conexión al destruir el objeto
     */
    public function __destruct() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }

    /**
     * Escapa caracteres especiales en una cadena para prevenir SQL injection
     * 
     * @param string $string Cadena a escapar
     * @return string Cadena escapada
     */
    protected function escape($string) {
        return $this->conexion->real_escape_string($string);
    }

    /**
     * Ejecuta una consulta preparada de manera segura
     * 
     * @param string $sql Consulta SQL con marcadores ?
     * @param string $types Tipos de datos (i=integer, d=double, s=string, b=blob)
     * @param array $params Parámetros a vincular
     * @return mysqli_stmt|false
     */
    protected function ejecutar_consulta($sql, $types = '', $params = []) {
        $stmt = $this->conexion->prepare($sql);
        
        if (!$stmt) {
            $this->log_error('Error al preparar consulta', [
                'sql' => $sql,
                'error' => $this->conexion->error
            ]);
            return false;
        }
        
        if (!empty($types) && !empty($params)) {
            if (strlen($types) !== count($params)) {
                $this->log_error('Error: tipos y parámetros no coinciden', [
                    'types' => $types,
                    'params_count' => count($params)
                ]);
                $stmt->close();
                return false;
            }
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            $this->log_error('Error al ejecutar consulta', [
                'sql' => $sql,
                'error' => $stmt->error
            ]);
            $stmt->close();
            return false;
        }
        
        return $stmt;
    }

    /**
     * Obtiene el último ID insertado
     * 
     * @return int
     */
    protected function ultimo_id() {
        return $this->conexion->insert_id;
    }

    /**
     * Inicia una transacción
     */
    protected function iniciar_transaccion() {
        $this->conexion->begin_transaction();
    }

    /**
     * Confirma una transacción
     */
    protected function confirmar_transaccion() {
        $this->conexion->commit();
    }

    /**
     * Revierte una transacción
     */
    protected function revertir_transaccion() {
        $this->conexion->rollback();
    }

    /**
     * Verifica si una tabla existe
     * 
     * @param string $table Nombre de la tabla
     * @return bool
     */
    protected function tabla_existe($table) {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $table);
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    /**
     * Registra un error en el log del sistema
     * 
     * @param string $mensaje Mensaje de error
     * @param array $contexto Contexto adicional
     */
    protected function log_error($mensaje, $contexto = []) {
        $log = [
            'fecha' => date('Y-m-d H:i:s'),
            'mensaje' => $mensaje,
            'contexto' => $contexto,
            'archivo' => debug_backtrace()[0]['file'] ?? 'desconocido',
            'linea' => debug_backtrace()[0]['line'] ?? 0
        ];
        
        // En producción, esto debería escribir a un archivo de log
        error_log(json_encode($log));
    }

    /**
     * Método abstracto que debe implementar cada clase hija
     * para retornar los datos en formato JSON
     * 
     * @return string JSON con los datos
     */
    abstract public function getData();
}
?>