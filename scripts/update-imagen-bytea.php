<?php
/**
 * Script para actualizar la estructura de la base de datos
 * Cambiar el campo imagen de VARCHAR a BYTEA
 */

// Configuración de base de datos directa
$dsn = 'pgsql:host=localhost;port=5432;dbname=zend_crud_db';
$username = 'postgres';
$password = '12345';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    echo "Actualizando estructura de la base de datos...\n";
    
    // Cambiar el tipo de columna imagen a BYTEA
    $sql = "ALTER TABLE productos ALTER COLUMN imagen TYPE BYTEA USING NULL";
    $pdo->exec($sql);
    
    echo "✅ Campo imagen actualizado a tipo BYTEA\n";
    echo "✅ Estructura de base de datos actualizada correctamente\n";
    
} catch (PDOException $e) {
    echo "❌ Error al actualizar la base de datos: " . $e->getMessage() . "\n";
}
?>