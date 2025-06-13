<?php
    include "../public/db.php";
    session_start();
    
    // Si ya está logueado, redirigir al calendario de reservas
    if (isset($_SESSION["admin_logueado"]) && $_SESSION["admin_logueado"] === true) {
        header("Location: reservas/calendario_reserva.php");
        exit();
    }
    
    $error = "";
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        // Modifica la consulta para usar la tabla administradores
        $sql = "SELECT id_admin, nombre, contraseña FROM administradores WHERE email = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $id, $nombre, $hashed_password);
            mysqli_stmt_fetch($stmt);
            
            if (password_verify($password, $hashed_password)) {
                // Establecer variables de sesión consistentes
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $nombre;
                $_SESSION["admin_logueado"] = true; // Variable clave para verificación
                
                // Redirigir al calendario de reservas
                header("Location: reservas/calendario_reserva.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            // Mensaje genérico por seguridad (no revelar si el usuario existe)
            $error = "Credenciales inválidas o no tienes permisos de administrador.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - La Casona</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo img {
            max-width: 120px;
            border-radius: 50%;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        }
        .login-title {
            color: #495057;
            font-size: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-primary {
            padding: 0.75rem 1rem;
            background-color: #0d6efd;
            border-color: #0d6efd;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .error {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            border-radius: 5px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
        .back-link a {
            color: #6c757d;
            text-decoration: none;
        }
        .back-link a:hover {
            color: #0d6efd;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="../USUARIO/MULTIMEDIA/LOGO/LOGO.jpg" alt="La Casona">
        </div>
        <h4 class="login-title">Acceso al Sistema</h4>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Email</label>
                <input type="email" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </div>
        </form>
        <div class="back-link">
            <a href="../Index.html">Volver al sitio principal</a>
        </div>
    </div>
</body>
</html>
