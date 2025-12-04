-- =============================================
-- BASE DE DATOS: ResourceHub
-- Dashboard para Gestión de Recursos Digitales
-- =============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS resourcehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE resourcehub;

-- =============================================
-- TABLA: usuarios
-- =============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: recursos
-- =============================================
CREATE TABLE recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo_recurso ENUM('codigo', 'documentacion', 'biblioteca', 'herramienta', 'tutorial', 'otro') NOT NULL,
    lenguaje VARCHAR(50),
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_ruta VARCHAR(500) NOT NULL,
    archivo_tamanio INT,
    tags VARCHAR(255),
    usuario_id INT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo_recurso),
    INDEX idx_lenguaje (lenguaje),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: bitacora_acceso
-- =============================================
CREATE TABLE bitacora_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- TABLA: bitacora_descargas (OPCIONAL - +10 pts)
-- =============================================
CREATE TABLE bitacora_descargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recurso_id INT NOT NULL,
    usuario_id INT,
    ip_address VARCHAR(45),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dia_semana VARCHAR(10),
    hora_descarga TIME,
    FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_recurso (recurso_id),
    INDEX idx_fecha (fecha_hora),
    INDEX idx_dia (dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATOS DE PRUEBA
-- =============================================

-- Usuario administrador (contraseña: admin123)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@resourcehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Usuario Demo', 'usuario@resourcehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');

-- Recursos de ejemplo
INSERT INTO recursos (titulo, descripcion, tipo_recurso, lenguaje, archivo_nombre, archivo_ruta, archivo_tamanio, tags, usuario_id) VALUES
('Librería de Validación de Formularios', 'Librería JavaScript para validar formularios de manera sencilla', 'biblioteca', 'JavaScript', 'form-validator.js', 'uploads/form-validator.js', 15420, 'javascript,validacion,formularios', 1),
('Tutorial de PHP OOP', 'Guía completa de programación orientada a objetos en PHP', 'tutorial', 'PHP', 'php-oop-tutorial.pdf', 'uploads/php-oop-tutorial.pdf', 2456789, 'php,oop,tutorial', 1),
('API REST con Slim Framework', 'Código ejemplo de API REST usando Slim Framework', 'codigo', 'PHP', 'slim-api-example.zip', 'uploads/slim-api-example.zip', 856320, 'php,api,rest,slim', 1),
('Documentación de Chart.js', 'Documentación oficial de Chart.js en español', 'documentacion', 'JavaScript', 'chartjs-docs.pdf', 'uploads/chartjs-docs.pdf', 1234567, 'javascript,charts,visualizacion', 1),
('Herramienta de Debug MySQL', 'Script para debugging de consultas MySQL', 'herramienta', 'PHP', 'mysql-debugger.php', 'uploads/mysql-debugger.php', 45678, 'php,mysql,debug', 1);

-- Bitácora de accesos de ejemplo
INSERT INTO bitacora_acceso (usuario_id, accion, ip_address, user_agent) VALUES
(1, 'login', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(2, 'login', '192.168.1.101', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)');

-- Bitácora de descargas de ejemplo (OPCIONAL)
INSERT INTO bitacora_descargas (recurso_id, usuario_id, ip_address, dia_semana, hora_descarga) VALUES
(1, 1, '192.168.1.100', 'Lunes', '10:30:00'),
(2, 1, '192.168.1.100', 'Lunes', '11:45:00'),
(3, 2, '192.168.1.101', 'Martes', '14:20:00'),
(1, 2, '192.168.1.101', 'Miércoles', '09:15:00'),
(4, 1, '192.168.1.100', 'Jueves', '16:30:00');

-- =============================================
-- VISTAS ÚTILES PARA ESTADÍSTICAS
-- =============================================

-- Vista: Descargas por tipo de recurso
CREATE VIEW v_descargas_por_tipo AS
SELECT 
    r.tipo_recurso,
    COUNT(bd.id) as total_descargas
FROM recursos r
LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id
GROUP BY r.tipo_recurso;

-- Vista: Descargas por lenguaje
CREATE VIEW v_descargas_por_lenguaje AS
SELECT 
    r.lenguaje,
    COUNT(bd.id) as total_descargas
FROM recursos r
LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id
WHERE r.lenguaje IS NOT NULL
GROUP BY r.lenguaje
ORDER BY total_descargas DESC;

-- Vista: Descargas por día de la semana
CREATE VIEW v_descargas_por_dia AS
SELECT 
    dia_semana,
    COUNT(*) as total_descargas
FROM bitacora_descargas
GROUP BY dia_semana
ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');

-- Vista: Recursos más descargados
CREATE VIEW v_recursos_populares AS
SELECT 
    r.id,
    r.titulo,
    r.tipo_recurso,
    r.lenguaje,
    COUNT(bd.id) as total_descargas
FROM recursos r
LEFT JOIN bitacora_descargas bd ON r.id = bd.recurso_id
GROUP BY r.id
ORDER BY total_descargas DESC
LIMIT 10;

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================

-- Procedimiento: Registrar descarga
DELIMITER //
CREATE PROCEDURE sp_registrar_descarga(
    IN p_recurso_id INT,
    IN p_usuario_id INT,
    IN p_ip_address VARCHAR(45)
)
BEGIN
    DECLARE v_dia_semana VARCHAR(10);
    DECLARE v_hora TIME;
    
    -- Obtener día de la semana en español
    SET v_dia_semana = CASE DAYOFWEEK(NOW())
        WHEN 1 THEN 'Domingo'
        WHEN 2 THEN 'Lunes'
        WHEN 3 THEN 'Martes'
        WHEN 4 THEN 'Miércoles'
        WHEN 5 THEN 'Jueves'
        WHEN 6 THEN 'Viernes'
        WHEN 7 THEN 'Sábado'
    END;
    
    SET v_hora = TIME(NOW());
    
    -- Insertar en bitácora
    INSERT INTO bitacora_descargas (recurso_id, usuario_id, ip_address, dia_semana, hora_descarga)
    VALUES (p_recurso_id, p_usuario_id, p_ip_address, v_dia_semana, v_hora);
END //
DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger: Registrar acceso al crear usuario
DELIMITER //
CREATE TRIGGER tr_usuario_registro
AFTER INSERT ON usuarios
FOR EACH ROW
BEGIN
    INSERT INTO bitacora_acceso (usuario_id, accion)
    VALUES (NEW.id, 'registro');
END //
DELIMITER ;

-- =============================================
-- INFORMACIÓN DEL SISTEMA
-- =============================================
SELECT 'Base de datos ResourceHub creada exitosamente' as mensaje;
SELECT 'Tablas creadas: usuarios, recursos, bitacora_acceso, bitacora_descargas' as info;
SELECT 'Usuario admin creado - Email: admin@resourcehub.com - Password: admin123' as credenciales;