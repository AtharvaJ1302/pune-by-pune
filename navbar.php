<?php
session_start();
?> <!-- Start session at the top -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./CSS/home.css">
    <style>
        /* Center and resize Login/Signup buttons on small screens */
        @media (max-width: 768px) {
            .auth-buttons {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
            }

            .auth-buttons .nav-link {
                font-size: 18px;
                padding: 10px 15px;
                margin: 10px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg bg-light">
        <div class="container">
            <a class="navbar-brand" href="home.php">
                <img src="./assets/pune-logo3.png" style="height:150px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="all_communities.php">Communities</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="all_events.php">Events</a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="profile.php">Profile</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Profile</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="about_us.php">About Us</a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link btn btn-danger ms-2 fw-bold text-dark" href="user_logout.php">Logout</a></li>
                    <?php else: ?>
                        <div class="auth-buttons d-md-flex">
                            <li class="nav-item"><a class="nav-link btn btn-primary btn-sm fw-bold text-white me-2" href="user_login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link btn btn-secondary btn-sm fw-bold text-white" href="signup.php">Signup</a></li>
                        </div>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap Modal for Login Alert -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="loginModalLabel">Oops !!!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>You need to log in to view your profile.</strong></p>
                </div>
                <div class="modal-footer">
                    <a href="user_login.php" class="btn btn-primary">Login</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>