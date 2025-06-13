<?php
// admin/registeradmin.php
include '../public/db.php';

// Datos a registrar
$nombre = "root";
$email = "root@gmail.com";
$password = "root";

// Encriptar la contraseña
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verifica si el email ya existe
$sql = "INSERT INTO administradores (nombre, email, contraseña) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE contraseña = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $hashed_password, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Admin registrado o actualizado:<br>Email: $email<br>Contraseña: $password";
} else {
    echo "❌ Error: " . mysqli_error($con);
}
?>
