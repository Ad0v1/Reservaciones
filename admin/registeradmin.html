<?php // create_admin.php (ejecutar una vez y luego eliminar)
include "db.php";

// 1. Datos del administrador (MODIFICAR ESTOS VALORES)
$username = "administrador";
$password = "admin";

// 2. Encriptación segura
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// 3. Query inteligente
$sql = "INSERT INTO users (username, password, role) 
        VALUES (?, ?, 'admin') 
        ON DUPLICATE KEY UPDATE password = ?";

// 4. Ejecución segura
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin creado. Credenciales:\nUsuario: $username\nContraseña: $password";
} else {
    echo "Error: " . mysqli_error($con);
}