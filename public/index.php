<?php

// Inicia la sesión global
session_start();

// Obtener el controlador y la acción desde la URL
$controllerParam = $_GET['controller'] ?? 'sistema';
$actionParam     = $_GET['action'] ?? 'index';

// Formateamos el nombre de la clase (ej: 'sistema' -> 'SistemaController')
$controllerName = ucfirst($controllerParam) . 'Controller';
$action         = $actionParam;

// Ruta del archivo: Desde /public sube un nivel (/..) y entra a /app/controller/
$controllerFile = __DIR__ . "/../app/controller/{$controllerName}.php";

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Instanciamos el controlador
    if (class_exists($controllerName)) {
        $controllerInstance = new $controllerName();

        // Verificamos si existe el método en la clase
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            echo "404 - Acción '{$action}' no encontrada en {$controllerName}";
        }
    } else {
        echo "404 - La clase '{$controllerName}' no está definida dentro del archivo.";
    }
} else {
    echo "404 - Controlador no encontrado en: " . $controllerFile;
}

?>