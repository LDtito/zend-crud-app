<?php

namespace Application\Model;

class Categoria
{
    public $id;
    public $nombre;
    public $descripcion;
    public $activo;
    public $createdAt;
    public $updatedAt;

    public function exchangeArray(array $data)
    {
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->nombre = !empty($data['nombre']) ? $data['nombre'] : '';
        $this->descripcion = !empty($data['descripcion']) ? $data['descripcion'] : '';
        $this->activo = isset($data['activo']) ? (bool) $data['activo'] : true;
        $this->createdAt = !empty($data['created_at']) ? $data['created_at'] : null;
        $this->updatedAt = !empty($data['updated_at']) ? $data['updated_at'] : null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function isValid()
    {
        $errors = [];
        
        if (empty($this->nombre)) {
            $errors['nombre'] = 'El nombre es requerido';
        }
        
        if (empty($this->descripcion)) {
            $errors['descripcion'] = 'La descripción es requerida';
        }
        
        return empty($errors) ? true : $errors;
    }
}