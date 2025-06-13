<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurante KAWAI - Auténtica Comida Peruana</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/imagenes/favicon.ico" type="image/x-icon">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="assets/css/normalize.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Barra superior con información de contacto -->
    <div class="top-bar">
        <div class="container">
            <div class="contact-info">
                <span><i class="fas fa-phone"></i> 980 436 234</span>
                <span><i class="fas fa-envelope"></i> info@restaurantekawai.com</span>
            </div>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>

    <!-- Encabezado principal con logo y navegación -->
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/imagenes/logo-kawai.png" alt="Restaurante KAWAI">
                </a>
            </div>
            
            <button class="mobile-menu-toggle" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="menu.php">Menú</a></li>
                    <li><a href="nosotros.php">Nosotros</a></li>
                    <li><a href="reserva.php" class="active">Reservas</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <!-- Banner de página (personalizable por página) -->
    <?php if (!isset($no_banner)): ?>
    <div class="page-banner" style="background-image: url('assets/imagenes/banner-reservas.jpg');">
        <div class="container">
            <h1 class="banner-title"><?php echo isset($page_title) ? $page_title : 'Reserva tu Mesa'; ?></h1>
            <div class="breadcrumbs">
                <a href="index.php">Inicio</a> &gt; 
                <span><?php echo isset($page_title) ? $page_title : 'Reservas'; ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>