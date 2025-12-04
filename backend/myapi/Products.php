<?php
namespace TECWEB\MYAPI;

require_once __DIR__ . '/DataBase.php';

class Products extends DataBase {
    private $response;

    public function __construct($db = 'marketzone', $user = 'root', $pass = 'JoshelinLun407') {
        // INICIALIZA EL ARREGLO DE RESPUESTA
        $this->response = array();
        
        // LLAMA AL CONSTRUCTOR DE LA CLASE PADRE
        parent::__construct($db, $user, $pass);
    }

    // LISTAR TODOS LOS PRODUCTOS
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

    // BUSCAR PRODUCTOS
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

    // OBTENER UN SOLO PRODUCTO POR ID
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

    // OBTENER UN SOLO PRODUCTO POR NOMBRE
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

    // AGREGAR PRODUCTO
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

    // ELIMINAR PRODUCTO
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

    // EDITAR PRODUCTO
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

    // OBTENER LA RESPUESTA EN FORMATO JSON
    public function getData() {
        // SE HACE LA CONVERSIÓN DE ARRAY A JSON
        return json_encode($this->response, JSON_PRETTY_PRINT);
    }
}
?>