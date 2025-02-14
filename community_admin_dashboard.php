<?php
include 'connection.php';
session_start();

$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;

if ($community_id) {
    $sql = "
        SELECT u.name, u.age, s.state_name, c.city_name, p.pincode, 
               GROUP_CONCAT(sk.skill_name ORDER BY sk.skill_name SEPARATOR ', ') AS skills
        FROM users u
        INNER JOIN cities c ON u.city_id = c.city_id
        INNER JOIN states s ON u.state_id = s.state_id
        INNER JOIN pincodes p ON u.pincode_id = p.pincode_id
        LEFT JOIN user_skills us ON u.user_id = us.user_id
        LEFT JOIN skills sk ON FIND_IN_SET(sk.skill_id, us.skill_ids)
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
    if (isset($_GET['community_id'])) {
        $community_id = intval($_GET['community_id']);
    } else {
        die("Community ID not provided!");
    }

    $event_id = intval($_POST['event_id']);
    $uploadDir = "uploads/community_event/photos/";
    $uploadedFiles = [];

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['photos']['name'][$key]);
        $filePath = $uploadDir . time() . "_" . $fileName;

        if (move_uploaded_file($tmp_name, $filePath)) {
            $uploadedFiles[] = $filePath;
        }
    }

    if (!empty($uploadedFiles)) {
        $photoString = implode(',', $uploadedFiles);

        $sql = "SELECT photos FROM event_photos WHERE community_id = ? AND event_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $community_id, $event_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($existingPhotos);
            $stmt->fetch();
            $updatedPhotos = empty($existingPhotos) ? $photoString : $existingPhotos . ',' . $photoString;

            $updateSql = "UPDATE event_photos SET photos = ? WHERE community_id = ? AND event_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sii", $updatedPhotos, $community_id, $event_id);
            $updateStmt->execute();
        } else {
            $insertSql = "INSERT INTO event_photos (community_id, event_id, photos) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("iis", $community_id, $event_id, $photoString);
            $insertStmt->execute();
        }
    }

    header("Location: community_admin_dashboard.php?community_id=" . $community_id);
    exit();
}

$sql_requests = "SELECT r.request_id, u.name, u.email, r.skill_ids, c.city_name, p.pincode, r.document_path
FROM request r
JOIN users u ON r.user_id = u.user_id
JOIN cities c ON r.city_id = c.city_id
JOIN pincodes p ON r.pincode_id = p.pincode_id
WHERE r.community_id = '$community_id' AND r.status = 0";

$result_requests = $conn->query($sql_requests);

$requests = [];

while ($row = $result_requests->fetch_assoc()) {
    // Fetch all skill names using the comma-separated skill_ids
    $skill_ids = explode(',', $row['skill_ids']);
    $skill_names = [];

    if (!empty($skill_ids)) {
        $sql_skills = "SELECT skill_name FROM skills WHERE skill_id IN (" . implode(',', array_map('intval', $skill_ids)) . ")";
        $result_skills = $conn->query($sql_skills);

        while ($skill = $result_skills->fetch_assoc()) {
            $skill_names[] = $skill['skill_name'];
        }
    }

    // Store request data
    $row['skill_names'] = implode(', ', $skill_names);
    $requests[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_role'])) {
    $user_id = intval($_POST['user_id']);
    $role = $conn->real_escape_string($_POST['id']);

    // Check if the role already exists for the user in the community
    $check_sql = "SELECT * FROM community_members WHERE user_id = ? AND community_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $community_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing role
        $update_sql = "UPDATE community_members SET role_id = ? WHERE user_id = ? AND community_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $role, $user_id, $community_id);
        $update_stmt->execute();
        echo "<script>alert('Role updated successfully.');</script>";
    } else {
        // Insert new role
        $insert_sql = "INSERT INTO community_members (user_id, community_id, role_id) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sii", $user_id, $community_id, $role);
        $insert_stmt->execute();
        echo "<script>alert('Role assigned successfully.');</script>";
    }
}

$eventPhotosSql = "SELECT ep.event_id, ep.photos, e.event_name 
        FROM event_photos ep
        JOIN events e ON ep.event_id = e.event_id
        WHERE ep.community_id = ?";
$stmt = $conn->prepare($eventPhotosSql);
$stmt->bind_param("i", $community_id);
$stmt->execute();
$eventPhotosResult = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.7.1/jszip.min.js"></script>


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

        .id-card {
            width: 250px;
            border: 2px solid #007bff;
            /* border-radius: 10px; */
            padding: 15px;
            text-align: center;
            background: #f9f9f9;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }

        .id-card img {
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .id-card h5 {
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: bold;
        }

        .id-card p {
            margin: 2px 0;
            font-size: 14px;
        }

        #event_id,
        #event_select {
            width: 200px;
        }

        #photos {
            width: 250px;
        }

        .photo-container {
            width: 100%;
            height: 250px;
            /* overflow: hidden; */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .event-section {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .photo-row {
            gap: 10px;
            padding-bottom: 10px;
        }

        .photo-card {
            width: 300px;
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
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="javascript:void(0)" onclick="showSection('id_cards')">
                    <i class="bi bi-pencil-square me-2"></i> ID Cards
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

        <div id="create_event" class="section mt-4">
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
                                    <!-- <div class="ms-3 mb-3">
                                        <a href="" class="btn btn-danger">End Event</a>
                                    </div> -->
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

        <div id="assign_roles" class="section">
            <h1>Assign Roles</h1>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="user_id" class="form-label">Select User:</label>
                    <select name="user_id" id="user_id" class="form-select" required>
                        <option value="">-- Select a User --</option>
                        <?php
                        // Fetch users in the community
                        $user_query = "SELECT u.user_id, u.name FROM users u 
                                    INNER JOIN community_members cm ON u.user_id = cm.user_id 
                                    WHERE cm.community_id = ?";
                        $stmt = $conn->prepare($user_query);
                        $stmt->bind_param("i", $community_id);
                        $stmt->execute();
                        $user_result = $stmt->get_result();

                        while ($user = $user_result->fetch_assoc()) {
                            echo "<option value='{$user['user_id']}'>" . htmlspecialchars($user['name']) . "</option>";
                        }
                        ?>
                    </select>

                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Select Role:</label>
                    <select name="id" id="id" class="form-select" required>
                        <option value="">-- Select a Role --</option>
                        <?php
                        $role_query = "SELECT id, role_name FROM roles";
                        $role_result = $conn->query($role_query);
                        while ($role = $role_result->fetch_assoc()) {
                            echo "<option value='{$role['id']}'>{$role['role_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" name="assign_role" class="btn btn-primary">Assign Role</button>
            </form>

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

            <div class="tab-pane mb-5" role="tabpanel" aria-labelledby="photos-tab">
                <h5 class="fw-bold">Photos</h5>

                <div class="d-flex flex-column gap-3">
                    <?php
                    $events = [];
                    while ($row = $eventPhotosResult->fetch_assoc()) {
                        $events[$row['event_name']][] = explode(',', $row['photos']);
                    }

                    foreach ($events as $eventName => $photoGroups) {
                    ?>
                        <div class="event-section">
                            <h5 class="text-center fw-bold"><?php echo htmlspecialchars($eventName); ?></h5>
                            <div class="d-flex overflow-auto photo-row" style="scroll-snap-type: x mandatory;">
                                <?php
                                foreach ($photoGroups as $photos) {
                                    foreach ($photos as $photo) {
                                ?>
                                        <div class="photo-card" style="flex: 0 0 auto; margin-right: 10px; scroll-snap-align: start;">
                                            <div class="card mb-3">
                                                <div class="photo-container">
                                                    <img src="<?php echo htmlspecialchars($photo); ?>" class="card-img-top" alt="Event Photo" data-bs-toggle="modal" data-bs-target="#photoModal" data-bs-img-src="<?php echo htmlspecialchars($photo); ?>">
                                                </div>
                                            </div>
                                        </div>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>

        </div>

        <div id="manage_requests" class="section mt-4">
            <h3>Pending Requests</h3>
            <?php if (!empty($requests)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Skills</th>
                            <th>City</th>
                            <th>Pincode</th>
                            <th>Verification Document</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['skill_names']); ?></td>
                                <td><?php echo htmlspecialchars($row['city_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['pincode']); ?></td>
                                <td>
                                    <?php if (!empty($row['document_path'])): ?>
                                        <button type="button" class="btn btn-info view-document"
                                            data-bs-toggle="modal" data-bs-target="#documentModal"
                                            data-document="<?php echo htmlspecialchars($row['document_path']); ?>">
                                            View
                                        </button>
                                    <?php else: ?>
                                        No document uploaded
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="request.php?request_id=<?php echo $row['request_id']; ?>&action=approve" class="btn btn-success m-2">Approve</a>
                                    <a href="request.php?request_id=<?php echo $row['request_id']; ?>&action=reject" class="btn btn-danger m-2">Reject</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending requests.</p>
            <?php endif; ?>
        </div>


        <div id="id_cards" class="section mt-4">
            <label for="event_select">Select Event:</label>
            <select id="event_select" class="form-control" onchange="filterByEvent()">
                <option value="all">All Events</option>
            </select>
            <button class="btn btn-primary download-btn mt-2" onclick="downloadAllIDCards()">Download All ID Cards</button>
            <div id="id_card_container" class="d-flex flex-wrap gap-3 mt-3"></div>
        </div>

    </div>

    <!-- modal form for viewing verification document -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Verification Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <iframe id="documentFrame" src="" width="100%" height="500px" style="border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="modal-photo" src="<?php echo htmlspecialchars($photo); ?>" class="img-fluid" alt="Full-size Event Photo">
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var documentModal = document.getElementById("documentModal");
            documentModal.addEventListener("show.bs.modal", function(event) {
                var button = event.relatedTarget;
                var documentPath = button.getAttribute("data-document");
                var documentFrame = document.getElementById("documentFrame");

                if (documentPath) {
                    documentFrame.src = documentPath;
                } else {
                    documentFrame.src = "";
                }
            });
        });
    </script>

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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchData();
        });

        function fetchData() {
            const urlParams = new URLSearchParams(window.location.search);
            const communityId = urlParams.get("community_id");

            fetch(`fetch_id_cards.php?community_id=${communityId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateEventDropdown(data.events);
                        displayIDCards(data.users);
                    } else {
                        document.getElementById("id_card_container").innerHTML = "<p>No users found</p>";
                    }
                })
                .catch(error => console.error("Error fetching data:", error));
        }

        function populateEventDropdown(events) {
            let eventSelect = document.getElementById("event_select");
            eventSelect.innerHTML = "<option value='all'>All Events</option>";

            events.forEach(event => {
                let option = document.createElement("option");
                option.value = event.event_id;
                option.textContent = event.event_name;
                eventSelect.appendChild(option);
            });
        }

        function displayIDCards(users) {
            let container = document.getElementById("id_card_container");
            container.innerHTML = "";
            users.forEach(user => {
                let card = document.createElement("div");
                card.classList.add("id-card", "card", "p-3", "shadow-sm");
                card.innerHTML = `
                <img src="${user.photo}" alt="Profile Picture" class="card-img-top rounded-circle" style="width: 100%; height: 200px; object-fit: cover;">
                <h5>${user.name}</h5>
                <p><strong>City:</strong> ${user.city}</p>
                <p><strong>Domain:</strong> ${user.domain}</p>
                <p><strong>Stream:</strong> ${user.stream}</p>
                <p><strong>Phone:</strong> ${user.phone_number}</p>
            `;
                card.setAttribute("data-event", user.event_id);
                container.appendChild(card);
            });
        }

        function filterByEvent() {
            let selectedEvent = document.getElementById("event_select").value;
            document.querySelectorAll(".id-card").forEach(card => {
                if (selectedEvent === "all" || card.getAttribute("data-event") === selectedEvent) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        }

        function downloadAllIDCards() {
            const selectedEvent = document.getElementById("event_select").value;
            const cards = document.querySelectorAll(".id-card");

            let filteredCards = Array.from(cards).filter(card => {
                return selectedEvent === "all" || card.getAttribute("data-event") === selectedEvent;
            });

            if (filteredCards.length === 0) {
                alert("No ID cards found for the selected event.");
                return;
            }

            let images = [];
            let captureCard = (index) => {
                if (index >= filteredCards.length) {
                    zipDownload(images);
                    return;
                }

                html2canvas(filteredCards[index], {
                    useCORS: true
                }).then(canvas => {
                    images.push({
                        name: `id_card_${index + 1}.png`,
                        data: canvas.toDataURL("image/png")
                    });
                    captureCard(index + 1);
                }).catch(error => console.error(`Error capturing ID card ${index + 1}:`, error));
            };

            captureCard(0);
        }


        function zipDownload(images) {
            if (images.length === 0) {
                alert("No images captured.");
                return;
            }

            let zip = new JSZip();
            images.forEach(img => {
                let imgData = img.data.split(',')[1];
                zip.file(img.name, imgData, {
                    base64: true
                });
            });

            zip.generateAsync({
                type: "blob"
            }).then(content => {
                let blobUrl = URL.createObjectURL(content);
                let link = document.createElement("a");
                link.href = blobUrl;
                link.download = "ID_Cards.zip";
                document.body.appendChild(link); // Ensure the link is added to the DOM
                link.click();
                document.body.removeChild(link); // Remove it after click
                URL.revokeObjectURL(blobUrl); // Free up memory
            }).catch(error => console.error("Error generating ZIP file:", error));
        }
    </script>
    <script>
        const photoModal = document.getElementById('photoModal');
        const modalImage = document.getElementById('modal-photo');

        photoModal.addEventListener('show.bs.modal', function(event) {
            const imgSrc = event.relatedTarget.getAttribute('data-bs-img-src');
            modalImage.src = imgSrc;
        });
    </script>
</body>

</html>