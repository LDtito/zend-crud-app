-- Script SQL para crear base de datos con relación productos-categorías
-- Diseño realista para sistema de productos

-- Crear tabla de categorías (tabla relacionada)
CREATE TABLE IF NOT EXISTS categorias (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar categorías de ejemplo
INSERT INTO categorias (nombre, descripcion) VALUES 
('Electrónicos', 'Dispositivos electrónicos y gadgets'),
('Ropa', 'Prendas de vestir y accesorios'),
('Hogar', 'Artículos para el hogar y decoración'),
('Deportes', 'Equipamiento deportivo y fitness'),
('Libros', 'Literatura, educación y entretenimiento'),
('Salud', 'Productos de salud y bienestar')
ON CONFLICT (nombre) DO NOTHING;

-- Eliminar tabla anterior si existe
DROP TABLE IF EXISTS registros;

-- Crear tabla de productos (tabla principal)
CREATE TABLE IF NOT EXISTS productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,                    -- Texto simple
    codigo VARCHAR(100) NOT NULL UNIQUE,            -- Alfanumérico
    email_contacto VARCHAR(255) NOT NULL,           -- Correo electrónico
    fecha_lanzamiento DATE NOT NULL,                -- Fecha
    hora_disponible TIME NOT NULL,                  -- Hora
    fecha_hora_creacion TIMESTAMP NOT NULL,         -- Fecha y hora
    categoria_id INTEGER NOT NULL,                  -- Lista desplegable (FK)
    telefono_soporte VARCHAR(20) NOT NULL,          -- Teléfono
    imagen VARCHAR(255),                            -- Carga de imagen
    precio DECIMAL(10,2) NOT NULL,                  -- Decimal monto
    descuento_porcentaje DECIMAL(5,2) NOT NULL,     -- Decimal porcentaje
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraint de clave foránea
    CONSTRAINT fk_producto_categoria 
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Crear índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_producto_categoria ON productos(categoria_id);
CREATE INDEX IF NOT EXISTS idx_producto_codigo ON productos(codigo);
CREATE INDEX IF NOT EXISTS idx_producto_email ON productos(email_contacto);
CREATE INDEX IF NOT EXISTS idx_producto_fecha ON productos(fecha_lanzamiento);

-- Insertar productos de ejemplo
INSERT INTO productos (
    nombre, codigo, email_contacto, fecha_lanzamiento, hora_disponible, 
    fecha_hora_creacion, categoria_id, telefono_soporte, imagen, 
    precio, descuento_porcentaje
) VALUES 
(
    'Smartphone Galaxy Pro', 
    'SGP2025001', 
    'soporte@galaxy.com', 
    '2025-03-15', 
    '09:00:00', 
    '2025-01-15 14:30:00', 
    1, -- Electrónicos
    '+51987654321', 
    NULL, 
    1299.99, 
    15.00
),
(
    'Camiseta Deportiva Nike', 
    'CDN2025002', 
    'ayuda@nike.com', 
    '2025-02-01', 
    '08:30:00', 
    '2025-01-20 10:15:00', 
    2, -- Ropa
    '+51123456789', 
    NULL, 
    89.90, 
    20.00
),
(
    'Lámpara LED Inteligente', 
    'LLI2025003', 
    'info@hogar.com', 
    '2025-04-10', 
    '12:00:00', 
    '2025-01-25 16:45:00', 
    3, -- Hogar
    '+51555123456', 
    NULL, 
    149.50, 
    10.00
),
(
    'Bicicleta Mountain Bike', 
    'BMB2025004', 
    'ventas@bikes.com', 
    '2025-05-20', 
    '07:00:00', 
    '2025-02-01 09:30:00', 
    4, -- Deportes
    '+51444987654', 
    NULL, 
    899.00, 
    5.00
),
(
    'Libro: Programación Web', 
    'LPW2025005', 
    'editorial@libros.com', 
    '2025-01-30', 
    '10:30:00', 
    '2025-01-10 11:20:00', 
    5, -- Libros
    '+51333654321', 
    NULL, 
    45.90, 
    0.00
);