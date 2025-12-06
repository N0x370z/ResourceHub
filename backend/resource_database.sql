-- =============================================
-- BASE DE DATOS: ResourceHub
-- Script de instalación
-- =============================================

-- 1. Crear y usar la base de datos
CREATE DATABASE IF NOT EXISTS resourcehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE resourcehub;

-- 2. Tabla de Usuarios (Administradores y usuarios normales)
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabla de Recursos (El núcleo del sistema)
DROP TABLE IF EXISTS recursos;
CREATE TABLE recursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    tipo_recurso ENUM('codigo', 'documentacion', 'biblioteca', 'herramienta', 'tutorial', 'otro') NOT NULL,
    lenguaje VARCHAR(50),
    archivo_nombre VARCHAR(255) NOT NULL,
    archivo_ruta VARCHAR(500) NOT NULL,
    archivo_tamanio INT DEFAULT 0,
    tags VARCHAR(255),
    usuario_id INT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE COMMENT '1 = Visible, 0 = Eliminado logicamente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabla para Bitácora de Acceso (Logins)
DROP TABLE IF EXISTS bitacora_acceso;
CREATE TABLE bitacora_acceso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabla para Bitácora de Descargas (Estadísticas)
DROP TABLE IF EXISTS bitacora_descargas;
CREATE TABLE bitacora_descargas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recurso_id INT NOT NULL,
    usuario_id INT,
    ip_address VARCHAR(45),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dia_semana VARCHAR(10),
    hora_descarga TIME,
    FOREIGN KEY (recurso_id) REFERENCES recursos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- DATOS INICIALES (SEMILLA)
-- =============================================

-- Usuarios por defecto
-- La contraseña para ambos es: admin123
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Super Admin', 'admin@resourcehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Usuario Test', 'test@resourcehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');

-- Recursos de prueba para que el catálogo no esté vacío
INSERT INTO recursos (titulo, descripcion, tipo_recurso, lenguaje, archivo_nombre, archivo_ruta, tags, usuario_id) VALUES
('Sistema de Login PHP', 'Código base para autenticación de usuarios', 'codigo', 'PHP', 'login-system.zip', 'uploads/login-system.zip', 'auth,login,php', 1),
('Guía de Instalación MySQL', 'PDF con pasos para instalar el servidor', 'documentacion', 'SQL', 'manual-mysql.pdf', 'uploads/manual-mysql.pdf', 'mysql,db,guia', 1),
('Librería ChartJS', 'Versión estable de la librería de gráficos', 'biblioteca', 'JavaScript', 'chart.min.js', 'uploads/chart.min.js', 'frontend,graficos', 1),
('Postman Portable', 'Herramienta para probar APIs', 'herramienta', 'N/A', 'postman.exe', 'uploads/postman.exe', 'api,testing', 1);

-- =============================================
-- PROCEDIMIENTOS ALMACENADOS (Requerido por backend)
-- =============================================

DROP PROCEDURE IF EXISTS sp_registrar_descarga;
DELIMITER //
CREATE PROCEDURE sp_registrar_descarga(
    IN p_recurso_id INT,
    IN p_usuario_id INT,
    IN p_ip_address VARCHAR(45)
)
BEGIN
    DECLARE v_dia_semana VARCHAR(10);
    
    -- Calcular día en español
    SET v_dia_semana = CASE DAYOFWEEK(NOW())
        WHEN 1 THEN 'Domingo'
        WHEN 2 THEN 'Lunes'
        WHEN 3 THEN 'Martes'
        WHEN 4 THEN 'Miércoles'
        WHEN 5 THEN 'Jueves'
        WHEN 6 THEN 'Viernes'
        WHEN 7 THEN 'Sábado'
    END;
    
    INSERT INTO bitacora_descargas (recurso_id, usuario_id, ip_address, dia_semana, hora_descarga)
    VALUES (p_recurso_id, p_usuario_id, p_ip_address, v_dia_semana, CURTIME());
END //
DELIMITER ;

-- =============================================
-- VISTAS (Para reportes futuros)
-- =============================================

CREATE OR REPLACE VIEW v_estadisticas_generales AS
SELECT 
    (SELECT COUNT(*) FROM recursos WHERE activo = 1) as total_recursos,
    (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as total_usuarios,
    (SELECT COUNT(*) FROM bitacora_descargas) as total_descargas;