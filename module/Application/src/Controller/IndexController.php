<?php

namespace Application\Controller;

use Application\Model\Producto;
use Application\Model\ProductoTable;
use Application\Model\CategoriaTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use PDO;
use PDOException;

class IndexController extends AbstractActionController
{
    private function getDbConnection()
    {
        $config = $this->getEvent()->getApplication()->getServiceManager()->get('Config');
        $dbConfig = $config['database'];
        
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', 
            $dbConfig['host'], $dbConfig['port'], $dbConfig['database']);

        return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    private function getProductoTable()
    {
        return new ProductoTable($this->getDbConnection());
    }

    private function getCategoriaTable()
    {
        return new CategoriaTable($this->getDbConnection());
    }

    // Listar productos (página principal)
    public function indexAction()
    {
        $search = $this->params()->fromQuery('search', '');
        $productoTable = $this->getProductoTable();
        
        $productos = empty($search) 
            ? $productoTable->fetchAll()
            : $productoTable->searchProductos($search);

        return new ViewModel([
            'productos' => $productos,
            'search' => $search
        ]);
    }

    // Mostrar formulario para crear producto
    public function createAction()
    {
        $categoriaTable = $this->getCategoriaTable();
        $categorias = $categoriaTable->getCategoriasParaSelect();
        $producto = new Producto();

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            
            // Manejar carga de imagen como BYTEA
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imageData = $this->processImageToBytea($_FILES['imagen']);
                if ($imageData !== false) {
                    $data['imagen'] = $imageData;
                }
            }
            
            $producto->exchangeArray($data);
            
            $validation = $producto->isValid();
            if ($validation === true) {
                try {
                    $this->getProductoTable()->saveProducto($producto);
                    // Producto creado exitosamente
                    return $this->redirect()->toRoute('home');
                } catch (\Exception $e) {
                    // Error al crear producto
                }
            } else {
                return new ViewModel([
                    'producto' => $producto,
                    'categorias' => $categorias,
                    'errors' => $validation
                ]);
            }
        }

        return new ViewModel([
            'producto' => $producto,
            'categorias' => $categorias
        ]);
    }

    // Editar producto
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('home');
        }

        try {
            $productoTable = $this->getProductoTable();
            $producto = $productoTable->getProducto($id);
            $categorias = $this->getCategoriaTable()->getCategoriasParaSelect();

            if ($this->getRequest()->isPost()) {
                $data = $this->params()->fromPost();
                
                // Mantener imagen anterior si no se sube una nueva
                $imagenActual = $producto->imagen;
                
                // Manejar carga de imagen como BYTEA (solo si se subió una nueva)
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $imageData = $this->processImageToBytea($_FILES['imagen']);
                    if ($imageData !== false) {
                        $data['imagen'] = $imageData;
                    } else {
                        $data['imagen'] = $imagenActual; // Mantener imagen anterior si falla la subida
                    }
                } else {
                    $data['imagen'] = $imagenActual; // Mantener imagen anterior si no se sube nueva
                }
                
                $producto->exchangeArray($data);
                
                $validation = $producto->isValid();
                if ($validation === true) {
                    $productoTable->saveProducto($producto);
                    // Producto actualizado exitosamente
                    return $this->redirect()->toRoute('home');
                } else {
                    return new ViewModel([
                        'producto' => $producto,
                        'categorias' => $categorias,
                        'errors' => $validation
                    ]);
                }
            }

            return new ViewModel([
                'producto' => $producto,
                'categorias' => $categorias
            ]);
        } catch (\Exception $e) {
            // Error al editar producto
            return $this->redirect()->toRoute('home');
        }
    }

    // Eliminar producto
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('home');
        }

        try {
            $this->getProductoTable()->deleteProducto($id);
            // Producto eliminado exitosamente
        } catch (\Exception $e) {
            // Error al eliminar producto
        }

        return $this->redirect()->toRoute('home');
    }

    // Ver detalles del producto
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('home');
        }

        try {
            $producto = $this->getProductoTable()->getProducto($id);
            $categoria = $this->getCategoriaTable()->getCategoria($producto->categoriaId);
            
            return new ViewModel([
                'producto' => $producto,
                'categoria' => $categoria
            ]);
        } catch (\Exception $e) {
            // Error al obtener producto
            return $this->redirect()->toRoute('home');
        }
    }

    /**
     * Procesar imagen y convertir a BYTEA
     */
    private function processImageToBytea($file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        // Verificar tipo de archivo
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Verificar tamaño
        if ($file['size'] > $maxFileSize) {
            return false;
        }
        
        // Leer el contenido del archivo
        $imageData = file_get_contents($file['tmp_name']);
        
        if ($imageData === false) {
            return false;
        }
        
        return $imageData;
    }
    
    /**
     * Generar imagen desde BYTEA para mostrar en navegador
     */
    public function imagenAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        
        try {
            $producto = $this->getProductoTable()->getProducto($id);
            
            if (!$producto->imagen) {
                header('HTTP/1.0 404 Not Found');
                exit;
            }
            
            // Detectar tipo de imagen
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($producto->imagen);
            
            // Configurar headers y mostrar imagen
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . strlen($producto->imagen));
            echo $producto->imagen;
            exit;
            
        } catch (\Exception $e) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }

    // Listar categorías
    public function categoriasAction()
    {
        $categoriaTable = $this->getCategoriaTable();
        $categorias = $categoriaTable->fetchAll();

        return new ViewModel([
            'categorias' => $categorias
        ]);
    }

    // Crear categoría
    public function createcategoriaAction()
    {
        $categoria = new \Application\Model\Categoria();

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            
            // Convertir checkbox activo
            $data['activo'] = isset($data['activo']) ? 1 : 0;
            
            $categoria->exchangeArray($data);
            
            $validation = $categoria->isValid();
            if ($validation === true) {
                try {
                    // Verificar si el nombre ya existe
                    if ($this->getCategoriaTable()->nombreExists($categoria->nombre)) {
                        return new ViewModel([
                            'categoria' => $categoria,
                            'errors' => ['nombre' => 'Ya existe una categoría con este nombre']
                        ]);
                    }
                    
                    $this->getCategoriaTable()->saveCategoria($categoria);
                    return $this->redirect()->toRoute('categorias');
                } catch (\Exception $e) {
                    return new ViewModel([
                        'categoria' => $categoria,
                        'errors' => ['general' => 'Error al crear la categoría']
                    ]);
                }
            } else {
                return new ViewModel([
                    'categoria' => $categoria,
                    'errors' => $validation
                ]);
            }
        }

        return new ViewModel([
            'categoria' => $categoria
        ]);
    }

    // Editar categoría
    public function editcategoriaAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('categorias');
        }

        try {
            $categoriaTable = $this->getCategoriaTable();
            $categoria = $categoriaTable->getCategoria($id);

            if ($this->getRequest()->isPost()) {
                $data = $this->params()->fromPost();
                
                // Convertir checkbox activo
                $data['activo'] = isset($data['activo']) ? 1 : 0;
                
                $categoria->exchangeArray($data);
                $categoria->id = $id; // Asegurar el ID
                
                $validation = $categoria->isValid();
                if ($validation === true) {
                    // Verificar si el nombre ya existe (excluyendo el actual)
                    if ($categoriaTable->nombreExists($categoria->nombre, $id)) {
                        return new ViewModel([
                            'categoria' => $categoria,
                            'errors' => ['nombre' => 'Ya existe otra categoría con este nombre']
                        ]);
                    }
                    
                    $categoriaTable->saveCategoria($categoria);
                    return $this->redirect()->toRoute('categorias');
                } else {
                    return new ViewModel([
                        'categoria' => $categoria,
                        'errors' => $validation
                    ]);
                }
            }

            return new ViewModel([
                'categoria' => $categoria
            ]);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('categorias');
        }
    }

    // Eliminar categoría
    public function deletecategoriaAction()
    {
        $id = $this->params()->fromRoute('id');
        if (!$id) {
            return $this->redirect()->toRoute('categorias');
        }

        try {
            $this->getCategoriaTable()->deleteCategoria($id);
        } catch (\Exception $e) {
            // Error al eliminar (puede tener productos asociados)
        }

        return $this->redirect()->toRoute('categorias');
    }


}
