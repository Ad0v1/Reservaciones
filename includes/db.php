<?php
$host = "localhost"; // Servidor donde se encuentra la base de datos
$user = "root"; // Usuario de la base de datos
$password = ""; // Contraseña del usuario (vacía en servidores locales)
$database = "reservacioneskawai"; // Nombre de la base de datos
// Crear conexión con MySQL usando mysqli
$con = mysqli_connect($host, $user, $password, $database);
// Verificar si la conexión fue exitosa 
if (!$con) {
die("Error de conexión: " . mysqli_connect_error()); // Termina el script si hay error
}
?>