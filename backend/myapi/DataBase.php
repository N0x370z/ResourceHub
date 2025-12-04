<?php
namespace TECWEB\MYAPI;

abstract class DataBase {
    protected $conexion;

    public function __construct($db, $user = 'root', $pass = 'JoshelinLun407', $host = 'localhost') {
        // SE CREA LA CONEXIÓN A LA BASE DE DATOS
        $this->conexion = new \mysqli($host, $user, $pass, $db);
        
        // VERIFICA SI HAY ERROR EN LA CONEXIÓN
        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
        
        // ESTABLECE EL CHARSET A UTF-8
        $this->conexion->set_charset("utf8");
    }

    public function __destruct() {
        // CIERRA LA CONEXIÓN CUANDO EL OBJETO SE DESTRUYE
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>