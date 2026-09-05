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

    public function crearCategoria() {
        require_once __DIR__ . '/../../app/views/categorias/crearCategoria.php';
    }

    public function guardarCategoria() {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($nombre)) {
            $this->categoriaModel->crearCategoria($nombre, $descripcion);
        }

        header("Location: index.php?controller=categoria&action=formcategoria");
        exit;
    }
}
?>