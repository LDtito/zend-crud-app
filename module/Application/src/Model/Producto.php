<?php

namespace Application\Model;

/**
 * Modelo para la entidad Producto
 * Incluye todos los tipos de campos requeridos en la evaluación
 */
class Producto
{
    public $id;
    public $nombre;                    // Texto simple
    public $codigo;                    // Alfanumérico
    public $emailContacto;             // Correo electrónico
    public $fechaLanzamiento;          // Fecha
    public $horaDisponible;            // Hora
    public $fechaHoraCreacion;         // Fecha y hora
    public $categoriaId;               // Lista desplegable (FK)
    public $telefonoSoporte;           // Teléfono
    public $imagen;                    // Carga de imagen
    public $precio;                    // Decimal (monto)
    public $descuentoPorcentaje;       // Decimal (porcentaje)
    public $createdAt;
    public $updatedAt;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->exchangeArray($data);
        }
    }

    public function exchangeArray(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? '';
        $this->codigo = $data['codigo'] ?? '';
        $this->emailContacto = $data['email_contacto'] ?? '';
        $this->fechaLanzamiento = $data['fecha_lanzamiento'] ?? '';
        
        // Normalizar hora (agregar segundos si no los tiene)
        $horaDisponible = $data['hora_disponible'] ?? '';
        if ($horaDisponible && !preg_match('/:\d{2}$/', $horaDisponible)) {
            $horaDisponible .= ':00';
        }
        $this->horaDisponible = $horaDisponible;
        
        // Normalizar fecha-hora (convertir T a espacio y agregar segundos si es necesario)
        $fechaHoraCreacion = $data['fecha_hora_creacion'] ?? '';
        if ($fechaHoraCreacion) {
            $fechaHoraCreacion = str_replace('T', ' ', $fechaHoraCreacion);
            if (!preg_match('/:\d{2}$/', $fechaHoraCreacion)) {
                $fechaHoraCreacion .= ':00';
            }
        }
        $this->fechaHoraCreacion = $fechaHoraCreacion;
        
        $this->categoriaId = $data['categoria_id'] ?? null;
        $this->telefonoSoporte = $data['telefono_soporte'] ?? '';
        
        // Manejar imagen BYTEA
        if (isset($data['imagen'])) {
            if (is_resource($data['imagen'])) {
                // Si es un recurso de PostgreSQL, leer el contenido
                $this->imagen = stream_get_contents($data['imagen']);
            } else {
                // Si ya es un string (datos binarios)
                $this->imagen = $data['imagen'];
            }
        } else {
            $this->imagen = null;
        }
        
        $this->precio = $data['precio'] ?? 0;
        $this->descuentoPorcentaje = $data['descuento_porcentaje'] ?? 0;
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    public function toArray()
    {
        // Asegurar que la hora tenga el formato correcto para la base de datos
        $horaDisponible = $this->horaDisponible;
        if ($horaDisponible && !preg_match('/:\d{2}$/', $horaDisponible)) {
            $horaDisponible .= ':00';
        }
        
        // Asegurar que la fecha-hora tenga el formato correcto para la base de datos
        $fechaHoraCreacion = $this->fechaHoraCreacion;
        if ($fechaHoraCreacion) {
            $fechaHoraCreacion = str_replace('T', ' ', $fechaHoraCreacion);
            if (!preg_match('/:\d{2}$/', $fechaHoraCreacion)) {
                $fechaHoraCreacion .= ':00';
            }
        }
        
        return [
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'email_contacto' => $this->emailContacto,
            'fecha_lanzamiento' => $this->fechaLanzamiento,
            'hora_disponible' => $horaDisponible,
            'fecha_hora_creacion' => $fechaHoraCreacion,
            'categoria_id' => $this->categoriaId,
            'telefono_soporte' => $this->telefonoSoporte,
            'imagen' => $this->imagen, // Datos binarios para BYTEA
            'precio' => $this->precio,
            'descuento_porcentaje' => $this->descuentoPorcentaje,
        ];
    }

    /**
     * Validaciones backend requeridas
     */
    public function isValid()
    {
        $errors = [];

        // Validación texto simple
        if (empty($this->nombre)) {
            $errors[] = 'El nombre del producto es requerido';
        } elseif (strlen($this->nombre) > 255) {
            $errors[] = 'El nombre no puede exceder 255 caracteres';
        }

        // Validación alfanumérico
        if (empty($this->codigo)) {
            $errors[] = 'El código del producto es requerido';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $this->codigo)) {
            $errors[] = 'El código solo puede contener letras y números';
        }

        // Validación correo electrónico
        if (empty($this->emailContacto)) {
            $errors[] = 'El email de contacto es requerido';
        } elseif (!filter_var($this->emailContacto, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email de contacto no es válido';
        }

        // Validación fecha
        if (empty($this->fechaLanzamiento)) {
            $errors[] = 'La fecha de lanzamiento es requerida';
        } elseif (!$this->isValidDate($this->fechaLanzamiento)) {
            $errors[] = 'La fecha de lanzamiento no es válida';
        }

        // Validación hora
        if (empty($this->horaDisponible)) {
            $errors[] = 'La hora disponible es requerida';
        } elseif (!$this->isValidTime($this->horaDisponible)) {
            $errors[] = 'La hora disponible no es válida';
        }

        // Validación fecha y hora
        if (empty($this->fechaHoraCreacion)) {
            $errors[] = 'La fecha y hora de creación es requerida';
        } elseif (!$this->isValidDateTime($this->fechaHoraCreacion)) {
            $errors[] = 'La fecha y hora de creación no es válida';
        }

        // Validación lista desplegable (categoría)
        if (empty($this->categoriaId) || !is_numeric($this->categoriaId)) {
            $errors[] = 'Debe seleccionar una categoría válida';
        }

        // Validación teléfono
        if (empty($this->telefonoSoporte)) {
            $errors[] = 'El teléfono de soporte es requerido';
        } elseif (!preg_match('/^[\+]?[0-9\-\(\)\s]+$/', $this->telefonoSoporte)) {
            $errors[] = 'El teléfono de soporte no es válido';
        }

        // Validación decimal (precio)
        if (!is_numeric($this->precio) || $this->precio < 0) {
            $errors[] = 'El precio debe ser un número válido mayor o igual a 0';
        }

        // Validación decimal (porcentaje)
        if (!is_numeric($this->descuentoPorcentaje) || 
            $this->descuentoPorcentaje < 0 || 
            $this->descuentoPorcentaje > 100) {
            $errors[] = 'El descuento debe ser un porcentaje entre 0 y 100';
        }

        return empty($errors) ? true : $errors;
    }

    private function isValidDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    private function isValidTime($time)
    {
        // Intentar diferentes formatos de hora comunes
        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
        
        foreach ($formats as $format) {
            $t = \DateTime::createFromFormat($format, $time);
            if ($t && $t->format($format) === $time) {
                return true;
            }
        }
        
        // Validar con expresión regular como respaldo
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
    }

    private function isValidDateTime($datetime)
    {
        // Formatos comunes de fecha-hora
        $formats = [
            'Y-m-d H:i:s',     // Formato de base de datos
            'Y-m-d\TH:i:s',    // Formato ISO 8601
            'Y-m-d\TH:i',      // Formato datetime-local HTML5
            'Y-m-d H:i',       // Formato común sin segundos
        ];
        
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $datetime);
            if ($dt && $dt->format($format) === $datetime) {
                return true;
            }
        }
        
        // Validar con expresión regular como respaldo
        return preg_match('/^\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}(:\d{2})?$/', $datetime);
    }
}