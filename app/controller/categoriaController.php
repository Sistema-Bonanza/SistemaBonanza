<?php
require_once __DIR__ . '/../model/categoriaModel.php';
require_once __DIR__ . '/../../config/database.php';

class CategoriaController {
    private $categoriaModel;

    public function __construct() {
        $database = new Database();
        $pdo = $database->getConnection();
        $this->categoriaModel = new CategoriaModel($pdo);
    }

    public function formcategoria() {
        $categorias = $this->categoriaModel->getAll();
        require_once __DIR__ . '/../../app/views/categorias/categorias.php';
    }

    public function fromcrearCategoria() {
        require_once __DIR__ . '/../../app/views/categorias/crearCategoria.php';
    }

    public function fromeditarCategoria() {
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            $categoria = $this->categoriaModel->getById($id);
            if ($categoria) {
                require_once __DIR__ . '/../../app/views/categorias/editarCategoria.php';
                return;
            }
        }
        // Si no se encuentra la categoría, redirigir a la lista de categorías
        header("Location: index.php?controller=categoria&action=formcategoria");
        exit;
    }

    public function guardarCategoria() {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($nombre)) {
            $this->categoriaModel->createCategoria($nombre, $descripcion);
        }

        header("Location: index.php?controller=categoria&action=formcategoria");
        exit;
    }

    public function actualizarCategoria() {
        $id = (int)$_POST['id'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($id) && !empty($nombre) && !empty($descripcion)) {
            $this->categoriaModel->updateCategoria($id, $nombre, $descripcion);
        }

        header("Location: index.php?controller=categoria&action=formcategoria");
        exit;
    }

    

}
?>