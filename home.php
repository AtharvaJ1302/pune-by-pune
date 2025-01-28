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

$domains = "SELECT interest_id, interest_name FROM interest";
$resultDomain = $conn->query($domains);

$eventsQuery = "
    SELECT event_id, event_name, event_description, event_time
    FROM events
    ORDER BY event_time DESC 
    LIMIT 6 -- Limit the results to 6
";

// Execute the query
$eventResult = $conn->query($eventsQuery);

if (!$eventResult) {
    echo "Error fetching events: " . $conn->error;
    exit;
}
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
            overflow-y: hidden;
            white-space: nowrap;
            padding: 10px;
            gap: 10px;
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

        .category-slider::-webkit-scrollbar {
            height: 8px;
        }

        .category-slider::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .category-slider::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .category-box {
            flex: 0 0 auto;
            border-radius: 5px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s, background-color 0.2s;
            color: #000000;
        }

        .category-box:hover {
            transform: scale(1.05);
        }

        .category-title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        #eventSlider {
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: thin;
            /* Firefox scrollbar */
            scrollbar-color: #888 transparent;
        }

        #eventSlider::-webkit-scrollbar {
            height: 8px;
            /* Height of the scrollbar */
        }

        #eventSlider::-webkit-scrollbar-thumb {
            background-color: #888;
            /* Scrollbar thumb color */
            border-radius: 10px;
        }

        #eventSlider::-webkit-scrollbar-thumb:hover {
            background-color: #555;
        }

        #eventSlider::-webkit-scrollbar-track {
            background-color: transparent;
            /* Scrollbar track color */
        }
    </style>
</head>

<body>
    <?php include('navbar.php') ?>

    <div class="container mt-4">
        <div class="row">
            <aside class="col-md-3 mb-5">
                <h3 class="mt-4">My Communities</h3>
                <hr>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div>
                        <h5>Created Communities</h5>
                        <hr>
                        <?php
                        // Fetch created communities
                        $userId = $_SESSION['user_id'];
                        $createdQuery = "SELECT community_id, community_name FROM communities WHERE user_id = $userId AND status = 1";
                        $createdResult = $conn->query($createdQuery);
                        if ($createdResult->num_rows > 0):
                            while ($row = $createdResult->fetch_assoc()):
                        ?>
                                <p><a href="community_info.php?community_id=<?php echo $row['community_id']; ?>"><?php echo htmlspecialchars($row['community_name']); ?></a></p>
                        <?php
                            endwhile;
                        else:
                            echo '<p>No created communities.</p>';
                        endif;
                        ?>
                    </div>

                    <div class="mt-4">
                        <h5>Joined Communities</h5>
                        <hr>
                        <?php
                        // Fetch joined communities
                        $joinedQuery = "SELECT communities.community_id, communities.community_name FROM community_members 
                            JOIN communities ON community_members.community_id = communities.community_id 
                            WHERE community_members.user_id = $userId";
                        $joinedResult = $conn->query($joinedQuery);
                        if ($joinedResult->num_rows > 0):
                            while ($row = $joinedResult->fetch_assoc()):
                        ?>
                                <p><a href="community_info.php?community_id=<?php echo $row['community_id']; ?>"><?php echo htmlspecialchars($row['community_name']); ?></a></p>
                        <?php
                            endwhile;
                        else:
                            echo '<p>No joined communities.</p>';
                        endif;
                        ?>
                    </div>

                    <a href="create_community.php" class="btn btn-primary w-100 mt-4">Create Community</a>
                <?php else: ?>
                    <p>Please log in to view your communities.</p>
                <?php endif; ?>
            </aside>


            <main class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">Communities</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $limit = 6; // Set the maximum number of cards to display
                            $counter = 0;
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()) :
                                    if ($counter >= $limit) break; // Stop after 6 communities
                                    $counter++;
                            ?>
                                    <div class="col-md-4 mb-4 d-flex">
                                        <a href="community_info.php?community_id=<?php echo $row['community_id']; ?>" class="community-card">
                                            <div class="card h-100 w-100">
                                                <img src="<?php echo $row['image_path']; ?>" class="card-img-top community-image" alt="Community Image" style="object-fit: contain; width: 90%; height: auto; border-radius: 15px;">
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
                                <div class="col-12 text-center">
                                    <a href="all_communities.php" class="btn btn-primary">Explore</a>
                                </div>
                            <?php else: ?>
                                <p>No communities are available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Upcoming Events</h2>
                    </div>
                    <div class="card-body">
                        <div id="eventSlider" class="d-flex overflow-auto gap-3" style="white-space: nowrap; scroll-behavior: smooth;">
                            <?php
                            if ($eventResult->num_rows > 0):
                                while ($row = $eventResult->fetch_assoc()):
                                    $formatted_time = date('D, M j, Y, g:i A T', strtotime($row['event_time']));
                                    $colors = ['#FFEB3B', '#8BC34A', '#00BCD4', '#FF5722', '#FFC107', '#4CAF50', '#FF9800'];
                                    $random_color = $colors[array_rand($colors)];
                            ?>
                                    <a href="event_info.php?event_id=<?php echo $row['event_id']; ?>" class="text-decoration-none">
                                        <div class="card" style="min-width: 250px; max-width: 250px; display: inline-block; background-color: <?php echo $random_color; ?>;">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                                                <p class="card-text text-truncate" style="overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                                    <?php echo strip_tags($row['event_description'], '<b><i><strong>'); ?>
                                                </p>
                                                <span class="badge bg-info text-dark"><?php echo $formatted_time; ?></span>
                                            </div>
                                        </div>
                                    </a>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <p class="text-center">No upcoming events are available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>




                <div class="container my-4">
                    <h2 class="mb-4 text-center">Explore Categories</h2>

                    <div class="category-slider">
                        <?php
                        if ($resultDomain->num_rows > 0) {
                            while ($row = $resultDomain->fetch_assoc()) {
                                echo '<div class="category-box">';
                                echo '<h5 class="category-title">' . htmlspecialchars($row['interest_name']) . '</h5>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No categories available.</p>';
                        }
                        ?>
                    </div>

                </div>
            </main>

        </div>
    </div>


    <?php include('footer.php') ?>

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

        function getRandomColor() {
            const letters = "89ABCDEF"; // Restrict to higher values for softer colors
            let color = "#";
            for (let i = 0; i < 6; i++) {
                color += letters[Math.floor(Math.random() * letters.length)];
            }
            return color;
        }

        document.addEventListener("DOMContentLoaded", () => {
            const categoryBoxes = document.querySelectorAll(".category-box");
            categoryBoxes.forEach(box => {
                box.style.backgroundColor = getRandomColor();
            });
        });

        const slider = document.getElementById("eventSlider");


        function autoScroll() {
            if (slider.scrollLeft >= slider.scrollWidth / 2) {
                slider.scrollLeft = 0;
            }

            slider.scrollBy({
                left: 1,
                behavior: "smooth"
            });
        }

        let autoScrollInterval = setInterval(autoScroll, 30);

        slider.addEventListener("mouseenter", () => clearInterval(autoScrollInterval));
        slider.addEventListener("mouseleave", () => autoScrollInterval = setInterval(autoScroll, 30));
    </script>
</body>

</html>