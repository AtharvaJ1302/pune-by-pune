<?php
session_start();
include('connection.php');

$sql = "SELECT communities.community_id, communities.community_name, communities.community_description, communities.image_path, 
        COUNT(community_members.user_id) AS member_count
        FROM communities
        LEFT JOIN community_members ON communities.community_id = community_members.community_id
        WHERE communities.status = 1
        GROUP BY communities.community_id";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CommunityHub - Connect & Engage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./CSS/home.css">
    <style>
        .category-slider {
            display: flex;
            overflow-x: auto;
            padding: 10px 0;
        }

        .category-box {
            background-color: #f0f0f0;
            padding: 20px;
            margin-right: 15px;
            text-align: center;
            border-radius: 8px;
            min-width: 150px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .category-box:hover {
            transform: scale(1.05);
        }

        .category-slider::-webkit-scrollbar {
            display: none;
        }

        .category-slider {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg ">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="./assets/pune-logo3.png" style="height:150px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="home.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#communities">Communities</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#events">Events</a></li>
                    <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#profile">Profile</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link btn btn-danger ms-2 fw-bold text-dark" href="user_logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-primary fw-bold text-dark" href="user_login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-secondary ms-2 fw-bold text-dark" href="signup.php">Signup</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <aside class="col-md-3">
                <h3 class="mt-4">My Communities</h3>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2">🎨 Art Enthusiasts</li>
                    <li class="mb-2">💻 Tech Innovators</li>
                    <li class="mb-2">🌱 Eco Warriors</li>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create_community.php" class="btn btn-primary w-100">Create Community</a>
                <?php else: ?>
                    <button class="btn btn-primary w-100" onclick="alert('Please log in to create a community!');">Create Community</button>
                <?php endif; ?>
            </aside>

            <main class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Popular Communities</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <div class="col-md-4 mb-4 d-flex">
                                        <a href="community_info.php?community_id=<?php echo $row['community_id']; ?>" class="community-card">
                                            <div class="card h-100 w-100">
                                                <img src="<?php echo $row['image_path']; ?>" class="card-img-top community-image" alt="Community Image">
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title"><?php echo $row['community_name']; ?></h5>
                                                    <p class="card-text flex-grow-1"><?php echo $row['community_description']; ?></p>
                                                    <div>
                                                        <span class="badge bg-primary"><?php echo $row['member_count']; ?> members</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No communities are available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="container my-4">
                    <h2 class="mb-4 text-center">Explore Categories</h2>

                    <!-- Scrollable Category Slider -->
                    <div class="category-slider">
                        <div class="category-box">
                            <h5 class="category-title">Dance</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Music</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Education</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Sports</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">E-Sports</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Fitness</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Travel</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Art & Crafts</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Food</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Career</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Theatre</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Pets & Animals</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Health & Medical</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Book Clubs</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Gardening</h5>
                        </div>
                        <div class="category-box">
                            <h5 class="category-title">Fashion</h5>
                        </div>
                    </div>

                </div>
            </main>

        </div>
    </div>


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
                        <li><a href="#" class="text-light text-decoration-none">Groups</a></li>
                    </ul>
                </div>

                <!-- Meetup Column -->
                <div class="col-6 col-md-3 mb-3">
                    <h6>Meetup</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">About</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Contact Us</a></li>
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
                    <div class="d-flex justify-content-center gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm">Get it on Google Play</a>
                        <a href="#" class="btn btn-outline-light btn-sm">Download on the App Store</a>
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


    <script>
        const checkboxes = document.querySelectorAll('.interest-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedCount = document.querySelectorAll('.interest-checkbox:checked').length;

                if (checkedCount > 5) {
                    checkbox.checked = false;
                    alert('You can select up to 5 categories only.');
                }
            });
        });
    </script>
</body>

</html>