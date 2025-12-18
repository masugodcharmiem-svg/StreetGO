<!-- Footer Section -->
    <footer class="footer bg-dark text-white pt-5 pb-3 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-primary"><i class="fas fa-motorcycle"></i> StreetGo</h5>
                    <p class="small">Bringing the authentic taste of Filipino street food right to your doorstep. Experience the flavors of the Philippines!</p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <!-- Note: Gigamit nako ang relative paths para safe maskin usbon ang folder name -->
                        <li><a href="index.php" class="text-decoration-none text-white-50">Home</a></li>
                        <li><a href="menu.php" class="text-decoration-none text-white-50">Menu</a></li>
                        <li><a href="about.php" class="text-decoration-none text-white-50">About Us</a></li>
                        <li><a href="services.php" class="text-decoration-none text-white-50">Services</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="fw-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled contact-info small text-white-50">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> X628+6H7, City of Mati, Davao Oriental, Philippines</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i> +63 912 345 6789</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i> hello@streetgo.ph</li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h6 class="fw-bold mb-3">Operating Hours</h6>
                    <ul class="list-unstyled small text-white-50">
                        <li><i class="fas fa-clock me-2"></i> Open Everyday 3PM - 10PM</li>
                    </ul>
                    <div class="mt-3">
                        <img src="https://img.shields.io/badge/Cash%20on%20Delivery-Available-green" alt="COD">
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row small">
                <div class="col-md-6">
                    <p class="mb-0 text-white-50">&copy; <?php echo date('Y'); ?> StreetGo. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-white-50">Made with <i class="fas fa-heart text-danger"></i> in the Philippines</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 (IMPORTANTE: Kini ang magpahigayon sa popup alert) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FUNCTION PARA MOGANA ANG ADD TO CART -->
    <script>
        function addToCart(itemId) {
            // I-check kung sakto ba ang path sa add_to_cart.php
            // Kung naa sa root folder, 'add_to_cart.php' lang
            const formData = new FormData();
            formData.append('menu_item_id', itemId);
            formData.append('quantity', 1);

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Success Popup
                    Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: 'Item added to your cart.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Optional: I-reload ang page kung gusto nimo ma-update dayon ang badge sa taas
                        location.reload(); 
                    });
                } else {
                    // Error Popup (e.g. Please login first)
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        confirmButtonText: (data.message.includes('login') ? 'Login Now' : 'Okay')
                    }).then((result) => {
                        if (data.message.includes('login') && result.isConfirmed) {
                            window.location.href = 'login.php';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again.'
                });
            });
        }
    </script>
</body>
</html>