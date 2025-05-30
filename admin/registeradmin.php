<?php
// admin/registeradmin.php
include '../includes/db.php';

// Cambiar estas credenciales si es necesario
$username = "administrador";
$password = "admin";

// Encriptar la contraseña
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$sql = "INSERT INTO users (username, password, role) 
        VALUES (?, ?, 'admin') 
        ON DUPLICATE KEY UPDATE password = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    echo "✅ Admin creado:<br>Usuario: $username<br>Contraseña: $password";
} else {
    echo "❌ Error: " . mysqli_error($conexion);
}
