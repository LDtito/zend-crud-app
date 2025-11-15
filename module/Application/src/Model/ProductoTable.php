<?php

namespace Application\Model;

use PDO;
use PDOException;

/**
 * Table Gateway para la tabla productos
 */
class ProductoTable
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Obtener todos los productos con información de categoría
     */
    public function fetchAll()
    {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            ORDER BY p.created_at DESC
        ";
        $stmt = $this->pdo->query($sql);
        
        $productos = [];
        while ($row = $stmt->fetch()) {
            $producto = new Producto($row);
            $producto->categoriaNombre = $row['categoria_nombre'];
            $productos[] = $producto;
        }
        
        return $productos;
    }

    /**
     * Obtener un producto por ID
     */
    public function getProducto($id)
    {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            throw new \Exception("No se encontró el producto con ID $id");
        }
        
        return new Producto($row);
    }

    /**
     * Guardar un producto (crear o actualizar)
     */
    public function saveProducto(Producto $producto)
    {
        $data = $producto->toArray();
        
        if (empty($producto->id)) {
            return $this->insertProducto($data);
        } else {
            return $this->updateProducto($producto->id, $data);
        }
    }

    /**
     * Insertar un nuevo producto
     */
    private function insertProducto($data)
    {
        $sql = "
            INSERT INTO productos (
                nombre, codigo, email_contacto, fecha_lanzamiento, hora_disponible, 
                fecha_hora_creacion, categoria_id, telefono_soporte, imagen, 
                precio, descuento_porcentaje
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            // Preparar parámetros con tipos específicos para BYTEA
            $stmt->bindParam(1, $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(2, $data['codigo'], PDO::PARAM_STR);
            $stmt->bindParam(3, $data['email_contacto'], PDO::PARAM_STR);
            $stmt->bindParam(4, $data['fecha_lanzamiento'], PDO::PARAM_STR);
            $stmt->bindParam(5, $data['hora_disponible'], PDO::PARAM_STR);
            $stmt->bindParam(6, $data['fecha_hora_creacion'], PDO::PARAM_STR);
            $stmt->bindParam(7, $data['categoria_id'], PDO::PARAM_INT);
            $stmt->bindParam(8, $data['telefono_soporte'], PDO::PARAM_STR);
            $stmt->bindParam(9, $data['imagen'], PDO::PARAM_LOB);
            $stmt->bindParam(10, $data['precio'], PDO::PARAM_STR);
            $stmt->bindParam(11, $data['descuento_porcentaje'], PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'duplicate key')) {
                throw new \Exception("Ya existe un producto con ese código");
            }
            throw new \Exception("Error al insertar producto: " . $e->getMessage());
        }
    }

    /**
     * Actualizar un producto existente
     */
    private function updateProducto($id, $data)
    {
        $sql = "
            UPDATE productos SET 
                nombre = ?, codigo = ?, email_contacto = ?, fecha_lanzamiento = ?, 
                hora_disponible = ?, fecha_hora_creacion = ?, categoria_id = ?, 
                telefono_soporte = ?, imagen = ?, precio = ?, descuento_porcentaje = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            // Preparar parámetros con tipos específicos para BYTEA
            $stmt->bindParam(1, $data['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(2, $data['codigo'], PDO::PARAM_STR);
            $stmt->bindParam(3, $data['email_contacto'], PDO::PARAM_STR);
            $stmt->bindParam(4, $data['fecha_lanzamiento'], PDO::PARAM_STR);
            $stmt->bindParam(5, $data['hora_disponible'], PDO::PARAM_STR);
            $stmt->bindParam(6, $data['fecha_hora_creacion'], PDO::PARAM_STR);
            $stmt->bindParam(7, $data['categoria_id'], PDO::PARAM_INT);
            $stmt->bindParam(8, $data['telefono_soporte'], PDO::PARAM_STR);
            $stmt->bindParam(9, $data['imagen'], PDO::PARAM_LOB);
            $stmt->bindParam(10, $data['precio'], PDO::PARAM_STR);
            $stmt->bindParam(11, $data['descuento_porcentaje'], PDO::PARAM_STR);
            $stmt->bindParam(12, $id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'duplicate key')) {
                throw new \Exception("Ya existe un producto con ese código");
            }
            throw new \Exception("Error al actualizar producto: " . $e->getMessage());
        }
    }

    /**
     * Eliminar un producto
     */
    public function deleteProducto($id)
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$id]);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new \Exception("Error al eliminar producto: " . $e->getMessage());
        }
    }

    /**
     * Buscar productos
     */
    public function searchProductos($searchTerm)
    {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.nombre ILIKE ? 
               OR p.codigo ILIKE ? 
               OR p.email_contacto ILIKE ?
               OR c.nombre ILIKE ?
            ORDER BY p.created_at DESC
        ";
        
        $searchPattern = "%$searchTerm%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        
        $productos = [];
        while ($row = $stmt->fetch()) {
            $producto = new Producto($row);
            $producto->categoriaNombre = $row['categoria_nombre'];
            $productos[] = $producto;
        }
        
        return $productos;
    }

    /**
     * Obtener productos por categoría
     */
    public function getProductosPorCategoria($categoriaId)
    {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.categoria_id = ?
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoriaId]);
        
        $productos = [];
        while ($row = $stmt->fetch()) {
            $producto = new Producto($row);
            $producto->categoriaNombre = $row['categoria_nombre'];
            $productos[] = $producto;
        }
        
        return $productos;
    }
}