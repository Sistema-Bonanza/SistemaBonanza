<?php
// Escribe aquí la contraseña que quieres usar
$mi_contrasena = "Admin123456*"; 

// Generamos el hash seguro usando el algoritmo por defecto (Bcrypt)
$hash = password_hash($mi_contrasena, PASSWORD_DEFAULT);

echo "<h3>Generador de Hash</h3>";
echo "Tu contraseña original: " . $mi_contrasena . "<br><br>";
echo "<strong>Copia este Hash y pégalo en tu base de datos:</strong><br>";
echo "<div style='background:#eee; padding:10px; margin-top:10px;'>";
echo $hash;
echo "</div>";
?>