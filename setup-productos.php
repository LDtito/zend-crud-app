<?php
/**
 * Script para crear base de datos con productos y categorías
 */

require_once __DIR__ . '/vendor/autoload.php';

$config = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'zend_crud_db',
    'username' => 'postgres',
    'password' => '12345'
];

echo "=== SETUP BASE DE DATOS PRODUCTOS ===\n\n";

try {
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $config['host'], $config['port'], $config['database']);
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Conectado a la base de datos...\n";
    
    // 1. Crear tabla categorías
    echo "2. Creando tabla categorías...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL UNIQUE,
            descripcion TEXT,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // 2. Insertar categorías
    echo "3. Insertando categorías...\n";
    $pdo->exec("
        INSERT INTO categorias (nombre, descripcion) VALUES 
        ('Electrónicos', 'Dispositivos electrónicos y gadgets'),
        ('Ropa', 'Prendas de vestir y accesorios'),
        ('Hogar', 'Artículos para el hogar y decoración'),
        ('Deportes', 'Equipamiento deportivo y fitness'),
        ('Libros', 'Literatura, educación y entretenimiento'),
        ('Salud', 'Productos de salud y bienestar')
        ON CONFLICT (nombre) DO NOTHING
    ");
    
    // 3. Eliminar tabla anterior y crear nueva
    echo "4. Creando tabla productos...\n";
    $pdo->exec("DROP TABLE IF EXISTS registros");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            codigo VARCHAR(100) NOT NULL UNIQUE,
            email_contacto VARCHAR(255) NOT NULL,
            fecha_lanzamiento DATE NOT NULL,
            hora_disponible TIME NOT NULL,
            fecha_hora_creacion TIMESTAMP NOT NULL,
            categoria_id INTEGER NOT NULL,
            telefono_soporte VARCHAR(20) NOT NULL,
            imagen VARCHAR(255),
            precio DECIMAL(10,2) NOT NULL,
            descuento_porcentaje DECIMAL(5,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_producto_categoria 
                FOREIGN KEY (categoria_id) REFERENCES categorias(id)
                ON DELETE RESTRICT ON UPDATE CASCADE
        )
    ");
    
    // 4. Crear índices
    echo "5. Creando índices...\n";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_producto_categoria ON productos(categoria_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_producto_codigo ON productos(codigo)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_producto_email ON productos(email_contacto)");
    
    // 5. Insertar productos de ejemplo
    echo "6. Insertando productos de ejemplo...\n";
    $pdo->exec("
        INSERT INTO productos (
            nombre, codigo, email_contacto, fecha_lanzamiento, hora_disponible, 
            fecha_hora_creacion, categoria_id, telefono_soporte, imagen, 
            precio, descuento_porcentaje
        ) VALUES 
        ('Smartphone Galaxy Pro', 'SGP2025001', 'soporte@galaxy.com', '2025-03-15', '09:00:00', '2025-01-15 14:30:00', 1, '+51987654321', NULL, 1299.99, 15.00),
        ('Camiseta Deportiva Nike', 'CDN2025002', 'ayuda@nike.com', '2025-02-01', '08:30:00', '2025-01-20 10:15:00', 2, '+51123456789', NULL, 89.90, 20.00),
        ('Lámpara LED Inteligente', 'LLI2025003', 'info@hogar.com', '2025-04-10', '12:00:00', '2025-01-25 16:45:00', 3, '+51555123456', NULL, 149.50, 10.00)
        ON CONFLICT (codigo) DO NOTHING
    ");
    
    // 6. Verificar resultados
    echo "7. Verificando resultados...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias");
    $categorias = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productos = $stmt->fetch()['total'];
    
    echo "✅ Categorías creadas: $categorias\n";
    echo "✅ Productos creados: $productos\n";
    echo "\n🎉 SETUP COMPLETADO!\n";
    echo "Base de datos lista para el CRUD de productos.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}