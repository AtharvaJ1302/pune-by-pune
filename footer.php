<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<footer class="bg-dark text-light pt-4" style=" bottom: 0; width: 100%;">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <h5>Create your own Meetup group.</h5>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="create_community.php" class="btn btn-outline-light">Get Started</a>
                    <?php else: ?>
                        <button class="btn btn-outline-light" onclick="alert('Please log in to create a community!');">Create Community</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                
                <!-- Your Account Column -->
                <div class="col-6 col-md-3 mb-3">
                    <h6>Your Account</h6>
                    <ul class="list-unstyled">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="registration.php" class="text-light text-decoration-none">Logout</a></li>
                        <?php else: ?>
                            <li><a href="signup.php" class="text-light text-decoration-none">Sign up</a></li>
                            <li><a href="user_login.php" class="text-light text-decoration-none">Log in</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Discover Column -->
                <div class="col-6 col-md-3 mb-3">
                    <h6>Discover</h6>
                    <ul class="list-unstyled">
                        <li><a href="all_communities.php" class="text-light text-decoration-none">Communities</a></li>
                        <li><a href="all_events.php" class="text-light text-decoration-none">Events</a></li>
                    </ul>
                </div>

                <!-- Meetup Column -->
                <div class="col-6 col-md-3 mb-3">
                    <h6>Pune By Pune</h6>
                    <ul class="list-unstyled">
                        <li><a href="about_us.php" class="text-light text-decoration-none">About</a></li>
                        <li><a href="contact_us.php" class="text-light text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Social and App Links Column -->
                <div class="col-6 col-md-3 text-center">
                    <h6>Follow us</h6>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <ul class="list-inline">
                            <li class="list-inline-item"><a href="#" class="text-light"><i class="fab fa-facebook"></i> Facebook</a></li>
                            <li class="list-inline-item"><a href="#" class="text-light"><i class="fab fa-twitter"></i> Twitter</a></li>
                            <li class="list-inline-item"><a href="#" class="text-light"><i class="fab fa-youtube"></i> YouTube</a></li>
                            <li class="list-inline-item"><a href="#" class="text-light"><i class="fab fa-instagram"></i> Instagram</a></li>
                        </ul>

                    </div>
                </div>
            </div>
            <div class="row mt-4 border-top pt-3">
                <div class="col-12 text-center">
                    <p>&copy; 2025 Pune By Pune | <a href="#" class="text-light text-decoration-none">Terms of Service</a> | <a href="#" class="text-light text-decoration-none">Privacy Policy</a> | <a href="#" class="text-light text-decoration-none">Cookie Policy</a></p>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>