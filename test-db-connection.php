<?php
/**
 * Script de prueba de conexión a PostgreSQL
 * Ejecutar desde la raíz del proyecto: php test-db-connection.php
 */

// Cargar autoloader de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuraciones
$globalConfig = require __DIR__ . '/config/autoload/global.php';
$localConfig = require __DIR__ . '/config/autoload/local.php';

// Combinar configuraciones (local.php sobrescribe global.php)
$dbConfig = array_merge($globalConfig['database'], $localConfig['database']);

echo "=== PRUEBA DE CONEXIÓN A POSTGRESQL ===\n";
echo "Host: " . $dbConfig['host'] . ":" . $dbConfig['port'] . "\n";
echo "Database: " . $dbConfig['database'] . "\n";
echo "Usuario: " . $dbConfig['username'] . "\n";
echo "==========================================\n\n";

try {
    // Crear DSN
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['database']
    );

    echo "Intentando conectar...\n";
    
    // Crear conexión PDO
    $pdo = new PDO(
        $dsn,
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ CONEXIÓN EXITOSA!\n\n";
    
    // Probar una consulta simple
    echo "Probando consulta de versión...\n";
    $stmt = $pdo->query('SELECT version() as version');
    $result = $stmt->fetch();
    
    echo "✅ Consulta ejecutada correctamente!\n";
    echo "Versión PostgreSQL: " . $result['version'] . "\n\n";
    
    // Probar listado de tablas
    echo "Probando listado de tablas...\n";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    echo "✅ Tablas en la base de datos (" . count($tables) . "):\n";
    if (empty($tables)) {
        echo "  - No hay tablas definidas aún\n";
    } else {
        foreach ($tables as $table) {
            echo "  - " . $table['table_name'] . "\n";
        }
    }
    
    echo "\n🎉 TODAS LAS PRUEBAS PASARON CORRECTAMENTE!\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN PDO:\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n\n";
    
    // Ayuda para errores comunes
    if (strpos($e->getMessage(), 'could not connect') !== false) {
        echo "💡 POSIBLES SOLUCIONES:\n";
        echo "1. Verificar que PostgreSQL esté ejecutándose\n";
        echo "2. Verificar host y puerto en la configuración\n";
        echo "3. Verificar que el servidor acepte conexiones TCP/IP\n";
    } elseif (strpos($e->getMessage(), 'password authentication') !== false) {
        echo "💡 POSIBLES SOLUCIONES:\n";
        echo "1. Verificar usuario y contraseña en config/autoload/local.php\n";
        echo "2. Verificar que el usuario existe en PostgreSQL\n";
    } elseif (strpos($e->getMessage(), 'database') !== false && strpos($e->getMessage(), 'does not exist') !== false) {
        echo "💡 POSIBLES SOLUCIONES:\n";
        echo "1. Crear la base de datos: CREATE DATABASE " . $dbConfig['database'] . ";\n";
        echo "2. Verificar el nombre de la base de datos en la configuración\n";
    }
    
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR GENERAL:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    exit(1);
}