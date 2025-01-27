<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./CSS/home.css">
</head>

<body>
    
    <nav class="navbar navbar-expand-lg ">
        <div class="container">
            <a class="navbar-brand" href="home.php"><img src="./assets/pune-logo3.png" style="height:150px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="all_communities.php">Communities</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="all_events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#profile">Profile</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="about_us.php">About Us</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link btn btn-danger ms-2 fw-bold text-dark" href="user_logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary ms-2 fw-bold text-dark" href="user_login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-secondary ms-2 fw-bold text-dark" href="signup.php">Signup</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

</body>

</html>