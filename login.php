<?php
    include "db.php";
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        // Modifica la consulta para filtrar solo por rol 'admin'
        $sql = "SELECT id, password, role FROM users WHERE username = ? AND role = 'admin'";
        $stmt = mysqli_prepare($con, $sql);
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
                header("Location: Reserva.php");
                exit();
            } else {
                echo "<div class='error'>Contraseña incorrecta.</div>";
            }
        } else {
            // Mensaje genérico por seguridad (no revelar si el usuario existe)
            echo "<div class='error'>Credenciales inválidas o no tienes permisos de administrador.</div>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($con);
?>

<!-- Formulario de inicio de sesión (sin cambios) -->
<form method="post">
    <input type="text" name="username" required placeholder="Usuario">
    <input type="password" name="password" required placeholder="Contraseña">
    <button type="submit">Iniciar Sesión</button>
</form>