<?php
// Mostrar todos los errores por pantalla (muy útil para pruebas)
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Iniciando prueba del Modelo...</h2>";

// 1. Llama a tu archivo de conexión a la Base de Datos.
// ¡OJO! Asegúrate de que esta ruta sea la correcta en tu proyecto.
require_once 'config/database.php'; 

// 2. Llama a tu nuevo modelo.
require_once 'app/model/UsuarioModel.php';

try {
    echo "<p>1. Archivos cargados correctamente.</p>";

    // 3. Crear una instancia de la conexión a la base de datos.
    // NOTA: Ajusta esto dependiendo de cómo se llame tu clase de conexión.
    // Si tu conexión es una función, llámala. Aquí asumo que devuelve un objeto $pdo.
    $conexion = new Database(); // Cambia 'Conexion' por el nombre real de tu clase
    $pdo = $conexion->getConnection(); // Cambia 'conectar' por el método real de tu clase

    echo "<p>2. Conexión a la BD establecida.</p>";

    // 4. Instanciar el modelo pasándole la conexión.
    $modelo = new usuarioModel($pdo);

    // 5. Los datos de prueba ("Dummies")
    $user_prueba = "miguel_test";
    $pass_hash_prueba = password_hash("123456", PASSWORD_DEFAULT); // Simulamos una clave encriptada
    $rol_prueba = "Administrador"; 

    // 6. Ejecutar el método que acabas de crear.
    $resultado = $modelo->crearUsuario($user_prueba, $pass_hash_prueba, $rol_prueba);

    if ($resultado) {
        echo "<h3 style='color:green;'>¡PRUEBA EXITOSA! 🎉</h3>";
        echo "Ve a phpMyAdmin y revisa la tabla 'usuarios'. Debería aparecer 'miguel_test'.";
    } else {
        echo "<h3 style='color:red;'>La consulta falló, pero no devolvió error de código.</h3>";
    }

} catch (Exception $e) {
    echo "<h3 style='color:red;'>Error crítico en la prueba:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>