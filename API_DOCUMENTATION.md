# ResourceHub API - Documentación

API REST para gestión de recursos digitales para programadores.

## Base URL
```
http://tu-dominio.com/backend/
```

## Autenticación

La mayoría de los endpoints requieren autenticación mediante sesiones PHP. Los endpoints protegidos requieren que el usuario esté autenticado.

### Endpoints de Autenticación
- `POST /backend/auth-login.php` - Iniciar sesión
- `POST /backend/auth-signup.php` - Registrar nuevo usuario
- `POST /backend/auth-logout.php` - Cerrar sesión

## Endpoints de Recursos

### 1. Listar Recursos
**GET** `/backend/resource-list.php`

Lista todos los recursos activos.

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "titulo": "Librería de Validación",
            "descripcion": "...",
            "tipo_recurso": "biblioteca",
            "lenguaje": "JavaScript",
            ...
        }
    ],
    "count": 10
}
```

---

### 2. Buscar Recursos
**GET** `/backend/resource-search.php?search=termino`

Busca recursos por término de búsqueda.

**Parámetros:**
- `search` (requerido): Término de búsqueda (mínimo 2 caracteres)

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": [...],
    "count": 5,
    "search_term": "javascript"
}
```

---

### 3. Obtener Recurso Individual
**GET** `/backend/resource-single.php?id=1`

Obtiene un recurso específico por ID.

**Parámetros:**
- `id` (requerido): ID del recurso

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "titulo": "...",
        ...
    }
}
```

---

### 4. Agregar Recurso
**POST** `/backend/resource-add.php`

Crea un nuevo recurso. Requiere autenticación.

**Body (JSON):**
```json
{
    "titulo": "Mi Recurso",
    "descripcion": "Descripción del recurso",
    "tipo_recurso": "codigo",
    "lenguaje": "PHP",
    "archivo_nombre": "archivo.php",
    "archivo_ruta": "uploads/archivo.php",
    "archivo_tamanio": 1024,
    "tags": "php,api,rest"
}
```

**Tipos de recurso válidos:**
- `codigo`
- `documentacion`
- `biblioteca`
- `herramienta`
- `tutorial`
- `otro`

**Respuesta exitosa (201):**
```json
{
    "status": "success",
    "message": "Recurso agregado exitosamente",
    "data": {
        "id": 5,
        "titulo": "Mi Recurso"
    }
}
```

---

### 5. Editar Recurso
**PUT/PATCH** `/backend/resource-edit.php`

Actualiza un recurso existente. Requiere autenticación. Solo el propietario o admin puede editar.

**Body (JSON):**
```json
{
    "id": 1,
    "titulo": "Título actualizado",
    "descripcion": "Nueva descripción",
    ...
}
```

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "message": "Recurso actualizado exitosamente",
    "data": {
        "id": 1
    }
}
```

---

### 6. Eliminar Recurso
**DELETE** `/backend/resource-delete.php?id=1`

Elimina un recurso (eliminación lógica). Requiere autenticación. Solo el propietario o admin puede eliminar.

**Parámetros:**
- `id` (requerido): ID del recurso (puede venir en query string o body)

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "message": "Recurso eliminado exitosamente",
    "data": {
        "id": 1,
        "titulo": "..."
    }
}
```

---

### 7. Registrar Descarga
**GET** `/backend/resource-download.php?id=1`

Registra una descarga y retorna información del archivo.

**Parámetros:**
- `id` (requerido): ID del recurso

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "message": "Descarga registrada exitosamente",
    "data": {
        "id": 1,
        "titulo": "...",
        "archivo_nombre": "...",
        "archivo_ruta": "...",
        "archivo_url": "...",
        ...
    }
}
```

---

### 8. Filtrar por Tipo
**GET** `/backend/resource-filter-type.php?type=codigo`

Filtra recursos por tipo.

**Parámetros:**
- `type` (requerido): Tipo de recurso

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": [...],
    "count": 5,
    "filter": {
        "type": "codigo"
    }
}
```

---

### 9. Filtrar por Lenguaje
**GET** `/backend/resource-filter-language.php?language=PHP`

Filtra recursos por lenguaje de programación.

**Parámetros:**
- `language` (requerido): Lenguaje de programación

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": [...],
    "count": 3,
    "filter": {
        "language": "PHP"
    }
}
```

---

## Endpoints de Estadísticas

### 10. Estadísticas de Recursos
**GET** `/backend/resource-stats.php`

Obtiene estadísticas generales de recursos.

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "data": {
        "total_recursos": 50,
        "por_tipo": {
            "codigo": 20,
            "documentacion": 15,
            ...
        },
        "por_lenguaje": {
            "PHP": 10,
            "JavaScript": 8,
            ...
        }
    }
}
```

---

### 11. Estadísticas de Descargas
**GET** `/backend/stats-download.php`

Obtiene estadísticas detalladas de descargas.

**Respuesta exitosa (200):**
```json
{
    "status": "success",
    "total_descargas": 150,
    "por_tipo": {...},
    "por_lenguaje": {...},
    "por_dia": {
        "Lunes": 20,
        "Martes": 25,
        ...
    },
    "por_hora": [0, 1, 2, ...],
    "mas_descargados": [...],
    "por_usuario": [...]
}
```

---

## Códigos de Estado HTTP

- `200` - OK (Operación exitosa)
- `201` - Created (Recurso creado)
- `400` - Bad Request (Datos inválidos)
- `401` - Unauthorized (No autenticado)
- `403` - Forbidden (Sin permisos)
- `404` - Not Found (Recurso no encontrado)
- `405` - Method Not Allowed (Método HTTP no permitido)
- `409` - Conflict (Recurso duplicado)
- `500` - Internal Server Error (Error del servidor)

## Manejo de Errores

Todas las respuestas de error siguen este formato:

```json
{
    "status": "error",
    "message": "Descripción del error"
}
```

## Headers Requeridos

Para operaciones que modifican datos (POST, PUT, PATCH, DELETE):
```
Content-Type: application/json
```

## Ejemplos de Uso

### JavaScript (Fetch API)

```javascript
// Listar recursos
fetch('http://tu-dominio.com/backend/resource-list.php')
    .then(response => response.json())
    .then(data => console.log(data));

// Buscar recursos
fetch('http://tu-dominio.com/backend/resource-search.php?search=javascript')
    .then(response => response.json())
    .then(data => console.log(data));

// Agregar recurso (requiere autenticación)
fetch('http://tu-dominio.com/backend/resource-add.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        titulo: 'Mi Recurso',
        tipo_recurso: 'codigo',
        archivo_nombre: 'archivo.php',
        archivo_ruta: 'uploads/archivo.php'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

### PHP (cURL)

```php
// Listar recursos
$ch = curl_init('http://tu-dominio.com/backend/resource-list.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

## Notas Importantes

1. Todos los endpoints retornan JSON
2. Las fechas están en formato ISO 8601
3. Los tamaños de archivo están en bytes
4. Todos los textos están codificados en UTF-8
5. La eliminación es lógica (soft delete), no física
6. Los recursos inactivos no aparecen en las listas

## Seguridad

- Todos los endpoints usan prepared statements para prevenir SQL injection
- Las contraseñas se almacenan con hash bcrypt
- Las sesiones están configuradas de forma segura
- Se validan todos los datos de entrada
- Se verifica la propiedad de recursos antes de editar/eliminar




