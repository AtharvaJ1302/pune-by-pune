<?php
include 'connection.php';
session_start();

$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;

if ($community_id) {
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
    $community_id = intval($_POST['community_id']);
    $event_name = $_POST['event_name'];
    $event_description = $_POST['event_description'];
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $event_location = $_POST['event_location'];

    $state_id = intval($_POST['state_id']);
    $city_id = intval($_POST['city_id']);
    $pincode_id = intval($_POST['pincode_id']);

    $sql = "INSERT INTO events (community_id, event_name, event_description, event_time, location, state_id, city_id, pincode_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the parameters (including state_id, city_id, pincode_id)
        $stmt->bind_param("issssiii", $community_id, $event_name, $event_description, $event_time, $event_location, $state_id, $city_id, $pincode_id);

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

if (isset($_GET['community_id'])) {
    $community_id = intval($_GET['community_id']);

    $community_sql = "SELECT community_name FROM communities WHERE community_id = ?";
    $stmt = $conn->prepare($community_sql);
    $stmt->bind_param("i", $community_id);
    $stmt->execute();
    $community_result = $stmt->get_result();

    if ($community_result->num_rows > 0) {
        $community = $community_result->fetch_assoc();
    } else {
        die("Community not found.");
    }

    $event_sql = "SELECT event_id, event_name, event_time, event_description FROM events WHERE community_id = ?";
    $event_stmt = $conn->prepare($event_sql);
    $event_stmt->bind_param("i", $community_id);
    $event_stmt->execute();
    $event_result = $event_stmt->get_result();
} else {
    die("Invalid community ID.");
}

$uploadPostSql = "SELECT event_id, event_name FROM events WHERE community_id = ?";
$stmt = $conn->prepare($uploadPostSql);
$stmt->bind_param("i", $community_id);
$stmt->execute();
$uploadPostResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photos']) && isset($_POST['event_id'])) {
    // Get community_id from the URL
    if (isset($_GET['community_id'])) {
        $community_id = intval($_GET['community_id']);
    } else {
        die("Community ID not provided!");
    }

    $event_id = intval($_POST['event_id']); // Get event ID from the form
    $uploadDir = "uploads/community_event/photos/";
    $uploadedFiles = [];

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['photos']['name'][$key]);
        $filePath = $uploadDir . time() . "_" . $fileName; // Unique filename

        if (move_uploaded_file($tmp_name, $filePath)) {
            $uploadedFiles[] = $filePath; // Store the file path
        }
    }

    if (!empty($uploadedFiles)) {
        $photoString = implode(',', $uploadedFiles); // Convert array to comma-separated string

        // Check if a record already exists for the same community and event
        $sql = "SELECT photos FROM event_photos WHERE community_id = ? AND event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $community_id, $event_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Fetch existing photos and append new ones
            $stmt->bind_result($existingPhotos);
            $stmt->fetch();
            $updatedPhotos = empty($existingPhotos) ? $photoString : $existingPhotos . ',' . $photoString;

            // Update existing record
            $updateSql = "UPDATE event_photos SET photos = ? WHERE community_id = ? AND event_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sii", $updatedPhotos, $community_id, $event_id);
            $updateStmt->execute();
        } else {
            // Insert new record
            $insertSql = "INSERT INTO event_photos (community_id, event_id, photos) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iis", $community_id, $event_id, $photoString);
            $insertStmt->execute();
        }
    }

    header("Location: community_admin_dashboard.php?community_id=" . $community_id); // Redirect back with community_id
    exit();
}
$sql_requests = "SELECT r.request_id, u.name, u.email, s.skill_name, c.city_name, p.pincode 
FROM request r
JOIN users u ON r.user_id = u.user_id
JOIN skills s ON r.skill_ids = s.skill_id
JOIN cities c ON r.city_id = c.city_id
JOIN pincodes p ON r.pincode_id = p.pincode_id
WHERE r.community_id = '$community_id' AND r.status = 0";

$result_requests = $conn->query($sql_requests);
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
    <script src="./ckeditor/ckeditor.js"></script>
</head>

<body>
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

                <div class="mb-3">
                    <label for="event_location" class="form-label">Event Location:</label>
                    <div class="mb-3">
                        <label for="event_location" class="form-label">Area:</label>
                        <input type="text" id="event_location" name="event_location" class="form-control" rows="4" required>
                    </div>
                    <div class="mb-3">
                        <label for="state_id" class="form-label">State:</label>
                        <select name="state_id" id="state_id" class="form-select" required>
                            <option value="">Select State</option>
                            <?php
                            $state_query = "SELECT state_id, state_name FROM States";
                            $state_result = $conn->query($state_query);
                            while ($state = $state_result->fetch_assoc()) {
                                echo "<option value='{$state['state_id']}'>{$state['state_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="city_id" class="form-label">City:</label>
                        <select name="city_id" id="city_id" class="form-select" required>
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pincode_id" class="form-label">Pincode:</label>
                        <select name="pincode_id" id="pincode_id" class="form-select" required>
                            <option value="">Select Pincode</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
                </div>
            </form>

            <h3 class="display-4 mb-4 text-center">Events for <?php echo htmlspecialchars($community['community_name']); ?></h3>
            <div class="container-sm mt-4" style="max-width: 720px;">
                <div class="row">
                    <?php
                    if ($event_result->num_rows > 0) {
                        while ($event = $event_result->fetch_assoc()) {
                    ?>

                            <div class="col-md-6 mb-4">
                                <div class="card shadow">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                        <p><strong>Event Time:</strong> <?php echo date('d M Y h:i A', strtotime($event['event_time'])); ?></p>
                                    </div>
                                    <div class="ms-3 mb-3">
                                        <a href="" class="btn btn-danger">End Event</a>
                                    </div>
                                </div>

                            </div>

                    <?php
                        }
                    } else {
                        echo "<div class='col-md-12 mb-4'><p class='text-center'>No events found for this community.</p></div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div id="create_post" class="section mt-4">
            <h4>Create Event Photos</h4>
            <form action="" method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label for="event_id" class="form-label">Select Event:</label>
                    <select name="event_id" id="event_id" class="form-select" required>
                        <option value="">-- Select an Event --</option>
                        <?php while ($row = $uploadPostResult->fetch_assoc()) { ?>
                            <option value="<?php echo $row['event_id']; ?>"><?php echo htmlspecialchars($row['event_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="photos" class="form-label">Select Event Photos:</label>
                    <input type="file" name="photos[]" id="photos" class="form-control" multiple accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>

        <div id="manage_requests" class="section mt-4">
            <h3>Pending Requests</h3>
            <?php if ($result_requests->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Skill</th>
                            <th>City</th>
                            <th>Pincode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_requests->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['skill_name']; ?></td>
                                <td><?php echo $row['city_name']; ?></td>
                                <td><?php echo $row['pincode']; ?></td>
                                <td>
                                    <a href="request.php?request_id=<?php echo $row['request_id']; ?>&action=approve" class="btn btn-success">Approve</a>
                                    <a href="request.php?request_id=<?php echo $row['request_id']; ?>&action=reject" class="btn btn-danger">Reject</a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending requests.</p>
            <?php endif; ?>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        CKEDITOR.replace('event_description');
    </script>
    <script>
        function showSection(sectionId) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active-section');
            });

            var activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active-section');
        }

        document.getElementById('state_id').addEventListener('change', function() {
            const stateId = this.value;
            const cityDropdown = document.getElementById('city_id');

            cityDropdown.innerHTML = '<option value="">Select City</option>';

            if (stateId) {
                fetch(`get_cities.php?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.city_id;
                            option.textContent = city.city_name;
                            cityDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching cities:', error));
            }
        });

        document.getElementById('city_id').addEventListener('change', function() {
            const cityId = this.value;
            const pincodeDropdown = document.getElementById('pincode_id');

            pincodeDropdown.innerHTML = '<option value="">Select Pincode</option>';

            if (cityId) {
                fetch(`get_pincode.php?city_id=${cityId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(pincode => {
                            const option = document.createElement('option');
                            option.value = pincode.pincode_id;
                            option.textContent = pincode.pincode;
                            pincodeDropdown.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching pincodes:', error));
            }
        });



        window.onbeforeunload = function() {
            window.location.href = "community_info.php";
        };
    </script>
</body>

</html>