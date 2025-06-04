<?php
include '../includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT id, password, role FROM users WHERE username = ? AND role = 'admin'";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $id, $hashed_password, $role);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;
            header("Location: dashboard.html"); // o dashboard.php si tiene lógica
            exit();
        } else {
            echo "<div class='error'>Contraseña incorrecta.</div>";
        }
    } else {
        echo "<div class='error'>Credenciales inválidas o sin permisos.</div>";
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conexion);
?>
