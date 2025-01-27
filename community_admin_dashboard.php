<?php
include 'connection.php';
session_start();

// Get the community_id from the query string
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;

if ($community_id) {
    // Query to get the users who have joined the community
    $sql = "
        SELECT u.name, u.age, s.state_name, c.city_name, p.pincode, GROUP_CONCAT(sk.skill_name) AS skills
        FROM users u
        INNER JOIN cities c ON u.city_id = c.city_id
        INNER JOIN states s ON u.state_id = s.state_id
        INNER JOIN pincodes p ON u.pincode_id = p.pincode_id
        LEFT JOIN user_skills us ON u.user_id = us.user_id
        LEFT JOIN skills sk ON us.skill_ids = sk.skill_id
        WHERE u.user_id IN (
            SELECT user_id FROM community_members WHERE community_id = '$community_id'
        )
        GROUP BY u.user_id
    ";

    $result = $conn->query($sql);
} else {
    echo "Community ID is missing.";
    exit;
}

if ($community_id) {
    // Fetch community details
    $community_sql = "
        SELECT community_name, community_description, organized_by, image_path
        FROM communities
        WHERE community_id = '$community_id'
    ";
    $community_result = $conn->query($community_sql);

    if ($community_result->num_rows > 0) {
        $community_info = $community_result->fetch_assoc();
    } else {
        echo "Community not found.";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_event'])) {
    // Sanitize user input
    $community_id = intval($_POST['community_id']);
    $event_name = $conn->real_escape_string($_POST['event_name']);
    $event_description = $conn->real_escape_string($_POST['event_description']);
    $event_time = $conn->real_escape_string($_POST['event_time']);

    // Insert event data into the events table
    $sql = "INSERT INTO events (community_id, event_name, event_description, event_time) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("isss", $community_id, $event_name, $event_description, $event_time);

        if ($stmt->execute()) {
            echo "<script>alert('Event created successfully.');</script>";
        } else {
            echo "<script>alert('Error: Unable to create the event.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Database error: Unable to prepare statement.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <style>
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding: 15px;
        }

        .sidebar a.navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
        }

        .sidebar .nav-link {
            color: #fff;
        }

        .sidebar .nav-link:hover {
            color: #adb5bd;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .section {
            display: none;
        }

        .active-section {
            display: block;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a class="navbar-brand" href="javascript:void(0)" onclick="showSection('dashboard')">Admin Dashboard</a>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('community_info')">
                    <i class="bi bi-info-circle-fill me-2"></i> Community Information
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('list_users')">
                    <i class="bi bi-people-fill me-2"></i> List of Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('assign_roles')">
                    <i class="bi bi-person-check-fill me-2"></i> Assign Roles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('create_post')">
                    <i class="bi bi-pencil-square me-2"></i> Create Post
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('manage_requests')">
                    <i class="bi bi-check-circle-fill me-2"></i> Manage Requests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('create_event')">
                    <i class="bi bi-pencil-square me-2"></i> Create Event
                </a>
            </li>
            <br><br>
            <a href="./community_info.php?community_id=<?php echo $community_id; ?>"><button class=" btn btn-danger">Return</button></a>
        </ul>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div id="community_info" class="section active-section">
            <h1 class="display-4 mb-4">Community Information</h1>

            <?php if (isset($community_info)): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h3 class="card-title">Community Name: <?php echo htmlspecialchars($community_info['community_name']); ?></h3>
                        <h4 class="card-subtitle mb-2 text-muted">Description:</h4>
                        <p><?php echo htmlspecialchars($community_info['community_description']); ?></p>
                        <h4 class="card-subtitle mb-2 text-muted">Organized By:</h4>
                        <p><?php echo htmlspecialchars($community_info['organized_by']); ?></p>
                        <h4 class="card-subtitle mb-2 text-muted">Banner Image:</h4>
                        <img src="<?php echo htmlspecialchars($community_info['image_path']); ?>" alt="Community Banner" class="img-fluid rounded">
                    </div>
                </div>

                <h4 class="mb-3">Update Community Information</h4>
                <form action="./update_community.php" method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
                    <input type="hidden" name="community_id" value="<?php echo $community_id; ?>">

                    <div class="mb-3">
                        <label for="community_name" class="form-label">Community Name</label>
                        <input type="text" class="form-control" id="community_name" name="community_name" value="<?php echo htmlspecialchars($community_info['community_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="community_description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($community_info['community_description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="organized_by" class="form-label">Organized By</label>
                        <input type="text" class="form-control" id="organized_by" name="organized_by" value="<?php echo htmlspecialchars($community_info['organized_by']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="banner_image" class="form-label">Banner Image</label>
                        <input type="file" class="form-control" id="banner_image" name="banner_image">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Information</button>
                </form>
            <?php else: ?>
                <p class="alert alert-warning mt-4">Community details not available.</p>
            <?php endif; ?>
        </div>


        <div id="list_users" class="section">
            <h1>List of Users</h1>
            <?php if ($result->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Skills</th>
                            <th>State</th>
                            <th>City</th>
                            <th>Pincode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['skills']); ?></td>
                                <td><?php echo htmlspecialchars($user['state_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['city_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['pincode']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users have joined this community yet.</p>
            <?php endif; ?>
        </div>

        <div id="create_event" class="section">
            <h1 class="mb-4">Event Creation</h1>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="community_id" class="form-label">Community:</label>
                    <select name="community_id" id="community_id" class="form-select" required>
                        <?php
                        if ($community_id) {
                            $query = "SELECT community_id, community_name FROM communities WHERE community_id = ?";
                            $stmt = $conn->prepare($query);

                            if ($stmt) {
                                $stmt->bind_param("i", $community_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['community_id']}' selected>{$row['community_name']}</option>";
                                    }
                                } else {
                                    echo "<option value=''>Community not found</option>";
                                }

                                $stmt->close();
                            } else {
                                echo "<option value=''>Database error</option>";
                            }
                        } else {
                            echo "<option value=''>Invalid Community ID</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="event_name" class="form-label">Event Name:</label>
                    <input type="text" id="event_name" name="event_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="event_description" class="form-label">Event Description:</label>
                    <textarea id="event_description" name="event_description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="event_time" class="form-label">Event Time:</label>
                    <input type="datetime-local" id="event_time" name="event_time" class="form-control" required>
                </div>

                <div>
                    <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>



        <!-- Other sections -->

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active-section');
            });

            // Show the clicked section
            var activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active-section');
        }

        function confirmDeleteCommunity(community_id) {
            // Ask for confirmation
            var confirmDelete = confirm("Are you sure you want to delete this community? This action cannot be undone.");
            if (confirmDelete) {
                // Redirect to the delete_community.php with the community_id
                window.location.href = 'delete_community.php?community_id=' + community_id;
            }
        }

        window.onbeforeunload = function() {
            window.location.href = "community_info.php";
        };
    </script>
</body>

</html>