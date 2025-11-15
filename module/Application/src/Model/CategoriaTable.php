<?php

namespace Application\Model;

use PDO;
use PDOException;
use RuntimeException;

class CategoriaTable
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchAll()
    {
        $sql = 'SELECT * FROM categorias ORDER BY nombre ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $categorias = [];
        while ($row = $stmt->fetch()) {
            $categoria = new Categoria();
            $categoria->exchangeArray($row);
            $categorias[] = $categoria;
        }
        
        return $categorias;
    }

    public function getCategoria($id)
    {
        $sql = 'SELECT * FROM categorias WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch();
        if (!$row) {
            throw new RuntimeException(sprintf('No se pudo encontrar la categoría con ID %d', $id));
        }
        
        $categoria = new Categoria();
        $categoria->exchangeArray($row);
        return $categoria;
    }

    public function saveCategoria(Categoria $categoria)
    {
        $data = $categoria->getArrayCopy();
        unset($data['id']);
        
        if ($categoria->id) {
            // Update
            $data['updated_at'] = date('Y-m-d H:i:s');
            $sql = 'UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, activo = :activo, updated_at = :updated_at WHERE id = :id';
            $data['id'] = $categoria->id;
        } else {
            // Insert
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $sql = 'INSERT INTO categorias (nombre, descripcion, activo, created_at, updated_at) VALUES (:nombre, :descripcion, :activo, :created_at, :updated_at)';
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        if (!$categoria->id) {
            $categoria->id = $this->pdo->lastInsertId();
        }
    }

    public function deleteCategoria($id)
    {
        // Verificar si hay productos asociados
        $checkSql = 'SELECT COUNT(*) FROM productos WHERE categoria_id = :id';
        $checkStmt = $this->pdo->prepare($checkSql);
        $checkStmt->execute(['id' => $id]);
        $count = $checkStmt->fetchColumn();
        
        if ($count > 0) {
            throw new RuntimeException('No se puede eliminar la categoría porque tiene productos asociados');
        }
        
        $sql = 'DELETE FROM categorias WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('No se pudo eliminar la categoría');
        }
    }

    public function nombreExists($nombre, $excludeId = null)
    {
        $sql = 'SELECT COUNT(*) FROM categorias WHERE LOWER(nombre) = LOWER(:nombre)';
        $params = ['nombre' => $nombre];
        
        if ($excludeId) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }

    public function getCategoriasParaSelect()
    {
        $sql = 'SELECT id, nombre FROM categorias WHERE activo = true ORDER BY nombre ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $categorias = [];
        while ($row = $stmt->fetch()) {
            $categorias[$row['id']] = $row['nombre'];
        }
        
        return $categorias;
    }
}