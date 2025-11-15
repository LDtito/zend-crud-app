-- Script SQL para crear la base de datos y tabla del CRUD
-- Base de datos: zend_crud_db

-- Crear la base de datos (ejecutar primero si no existe)
-- CREATE DATABASE zend_crud_db;

-- Conectar a la base de datos
-- \c zend_crud_db;

-- Crear tabla con todos los campos requeridos
CREATE TABLE IF NOT EXISTS registros (
    id SERIAL PRIMARY KEY,
    texto_simple VARCHAR(255) NOT NULL,
    alfanumerico VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    lista_desplegable VARCHAR(50) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    imagen VARCHAR(255), -- Ruta del archivo de imagen
    decimal_monto DECIMAL(10,2) NOT NULL,
    decimal_porcentaje DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_correo ON registros(correo_electronico);
CREATE INDEX IF NOT EXISTS idx_fecha ON registros(fecha);
CREATE INDEX IF NOT EXISTS idx_lista ON registros(lista_desplegable);

-- Insertar algunos datos de prueba
INSERT INTO registros (
    texto_simple, 
    alfanumerico, 
    correo_electronico, 
    fecha, 
    hora, 
    fecha_hora, 
    lista_desplegable, 
    telefono, 
    imagen, 
    decimal_monto, 
    decimal_porcentaje
) VALUES 
(
    'Ejemplo de texto', 
    'ABC123', 
    'ejemplo@correo.com', 
    '2025-01-15', 
    '14:30:00', 
    '2025-01-15 14:30:00', 
    'opcion1', 
    '+51987654321', 
    NULL, 
    1250.75, 
    15.50
),
(
    'Segundo registro', 
    'XYZ789', 
    'segundo@test.com', 
    '2025-02-20', 
    '09:15:00', 
    '2025-02-20 09:15:00', 
    'opcion3', 
    '+51123456789', 
    NULL, 
    890.00, 
    22.75
);