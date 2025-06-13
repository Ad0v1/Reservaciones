    <!-- Sección de llamado a la acción -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¿Listo para disfrutar de la mejor comida peruana?</h2>
                <p>Haz tu reserva ahora y vive una experiencia gastronómica inolvidable</p>
                <a href="reserva.php" class="btn btn-primary">Reservar Mesa</a>
            </div>
        </div>
    </section>

    <!-- Pie de página -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-widgets">
                <div class="footer-widget">
                    <h3>Sobre Nosotros</h3>
                    <p>Restaurante KAWAI ofrece la auténtica experiencia de la gastronomía peruana en un ambiente acogedor y familiar. Nuestros platos son preparados con ingredientes frescos y de la mejor calidad.</p>
                </div>
                
                <div class="footer-widget">
                    <h3>Horario de Atención</h3>
                    <ul class="opening-hours">
                        <li><span>Lunes - Viernes:</span> 7:00 AM - 11:00 PM</li>
                        <li><span>Sábados:</span> 8:00 AM - 11:00 PM</li>
                        <li><span>Domingos:</span> 8:00 AM - 10:00 PM</li>
                    </ul>
                </div>
                
                <div class="footer-widget">
                    <h3>Información de Contacto</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> Av. La Marina 2355, San Miguel, Lima</li>
                        <li><i class="fas fa-phone"></i> 980 436 234</li>
                        <li><i class="fas fa-envelope"></i> info@restaurantekawai.com</li>
                    </ul>
                </div>
                
                <div class="footer-widget">
                    <h3>Enlaces Rápidos</h3>
                    <ul class="quick-links">
                        <li><a href="menu.php">Nuestro Menú</a></li>
                        <li><a href="reserva.php">Reservaciones</a></li>
                        <li><a href="registrar_pago.php">Registrar Pago</a></li>
                        <li><a href="politicas.php">Políticas de Reserva</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> Restaurante KAWAI. Todos los derechos reservados.</p>
                </div>
                
                <div class="payment-methods">
                    <span>Métodos de pago:</span>
                    <img src="assets/imagenes/payment-methods.png" alt="Métodos de pago">
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Botón de WhatsApp flotante -->
    <a href="https://wa.me/51980436234" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Scripts JS -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Script para marcar la página actual en el menú -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener la URL actual
            const currentLocation = window.location.href;
            
            // Obtener todos los enlaces del menú principal
            const navLinks = document.querySelectorAll('.nav-list a');
            
            // Recorrer los enlaces y marcar el activo
            navLinks.forEach(function(link) {
                if (currentLocation.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Menú móvil
            const menuToggle = document.querySelector('.mobile-menu-toggle');
            const mainNav = document.querySelector('.main-nav');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mainNav.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>