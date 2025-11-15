<?php
/**
 * Script para crear la base de datos y tabla
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configuración para conectar a postgres (para crear la BD)
$adminConfig = [
    'host' => 'localhost',
    'port' => '5432',
    'database' => 'postgres', // Base por defecto para crear otras BDs
    'username' => 'postgres',
    'password' => '12345' // Actualizar con tu contraseña
];

echo "=== SETUP DE BASE DE DATOS ===\n\n";

try {
    // Conectar a PostgreSQL para crear la base de datos
    $adminDsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $adminConfig['host'],
        $adminConfig['port'],
        $adminConfig['database']
    );
    
    $adminPdo = new PDO($adminDsn, $adminConfig['username'], $adminConfig['password']);
    $adminPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "1. Conectado a PostgreSQL...\n";
    
    // Verificar si la base de datos existe
    $stmt = $adminPdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    $stmt->execute(['zend_crud_db']);
    
    if (!$stmt->fetchColumn()) {
        echo "2. Creando base de datos 'zend_crud_db'...\n";
        $adminPdo->exec("CREATE DATABASE zend_crud_db");
        echo "✅ Base de datos creada exitosamente!\n";
    } else {
        echo "2. Base de datos 'zend_crud_db' ya existe.\n";
    }
    
    // Cerrar conexión admin
    $adminPdo = null;
    
    // Ahora conectar a la nueva base de datos
    echo "3. Conectando a la base de datos del proyecto...\n";
    
    $projectDsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $adminConfig['host'],
        $adminConfig['port'],
        'zend_crud_db'
    );
    
    $projectPdo = new PDO($projectDsn, $adminConfig['username'], $adminConfig['password']);
    $projectPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "4. Ejecutando script SQL...\n";
    
    // Leer y ejecutar el archivo SQL
    $sql = file_get_contents(__DIR__ . '/database/setup.sql');
    
    // Ejecutar comandos SQL uno por uno
    
    // 1. Crear tabla
    $createTableSQL = "
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
        imagen VARCHAR(255),
        decimal_monto DECIMAL(10,2) NOT NULL,
        decimal_porcentaje DECIMAL(5,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $projectPdo->exec($createTableSQL);
    
    // 2. Crear índices
    $projectPdo->exec("CREATE INDEX IF NOT EXISTS idx_correo ON registros(correo_electronico)");
    $projectPdo->exec("CREATE INDEX IF NOT EXISTS idx_fecha ON registros(fecha)");
    $projectPdo->exec("CREATE INDEX IF NOT EXISTS idx_lista ON registros(lista_desplegable)");
    
    // 3. Verificar si ya hay datos
    $stmt = $projectPdo->query("SELECT COUNT(*) as count FROM registros");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        // 4. Insertar datos de prueba
        $insertSQL = "
        INSERT INTO registros (
            texto_simple, alfanumerico, correo_electronico, fecha, hora, fecha_hora, 
            lista_desplegable, telefono, imagen, decimal_monto, decimal_porcentaje
        ) VALUES 
        ('Ejemplo de texto', 'ABC123', 'ejemplo@correo.com', '2025-01-15', '14:30:00', 
         '2025-01-15 14:30:00', 'opcion1', '+51987654321', NULL, 1250.75, 15.50),
        ('Segundo registro', 'XYZ789', 'segundo@test.com', '2025-02-20', '09:15:00', 
         '2025-02-20 09:15:00', 'opcion3', '+51123456789', NULL, 890.00, 22.75)
        ";
        
        $projectPdo->exec($insertSQL);
        echo "✅ Datos de prueba insertados!\n";
    } else {
        echo "ℹ️  Datos ya existen, omitiendo inserción.\n";
    }
    
    echo "✅ Tabla 'registros' creada exitosamente!\n";
    echo "✅ Datos de prueba insertados!\n\n";
    
    // Verificar que todo esté bien
    echo "5. Verificando estructura de la tabla...\n";
    $stmt = $projectPdo->query("SELECT COUNT(*) as total FROM registros");
    $result = $stmt->fetch();
    
    echo "✅ Total de registros: " . $result['total'] . "\n";
    
    echo "\n🎉 SETUP COMPLETADO EXITOSAMENTE!\n";
    echo "Ya puedes usar la aplicación CRUD.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}