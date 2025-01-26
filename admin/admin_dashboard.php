<?php
session_start();
include('connection.php');

if (!isset($_SESSION['username'])) {
    header('location: admin_login.php');
}

$domain = "";
$skill = "";
$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['addDomain'])) {
        $domain = trim($_POST['addDomain']);
        if (!empty($domain)) {
            $domainQuery = "INSERT INTO interest (interest_name) VALUES ('$domain')";
            if ($conn->query($domainQuery) === TRUE) {
                $successMessage = "Domain '$domain' added successfully!";
                header('location: admin_dashboard.php');
                exit();
            } else {
                $errorMessage = "Error adding domain: " . $conn->error;
            }
        } else {
            $errorMessage = "Domain name cannot be empty.";
        }
    }

    if (isset($_POST['addSkill'])) {
        $skill = $_POST['addSkill'];
        if (!empty($skill)) {
            $skillQuery = "INSERT INTO skills (skill_name) VALUES ('$skill')";
            if ($conn->query($skillQuery) === TRUE) {
                $successMessage = "Skill '$skill' added successfully!";
                header('location: admin_dashboard.php');
                exit();
            } else {
                $errorMessage = "Error adding skill: " . $conn->error;
            }
        } else {
            $errorMessage = "Skill name cannot be empty.";
        }
    }
}

// Get the count of registered users
$userQuery = "SELECT COUNT(user_id) AS user_count FROM users";
$userResult = $conn->query($userQuery);
$userCount = $userResult->fetch_assoc()['user_count'];

// Get the count of communities
$communityQuery = "SELECT COUNT(community_id) AS community_count FROM communities";
$communityResult = $conn->query($communityQuery);
$communityCount = $communityResult->fetch_assoc()['community_count'];

//Get the interests 
$interest = "SELECT interest_id, interest_name FROM interest";
$interestResult = $conn->query($interest);

if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $delete_query = "DELETE FROM interest WHERE interest_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit;
}

if (isset($_POST['update_id']) && isset($_POST['update_name'])) {
    $update_id = $_POST['update_id'];
    $update_name = $_POST['update_name'];
    $update_query = "UPDATE interest SET interest_name = ? WHERE interest_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $update_name, $update_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit;
}

//Get the skills
$skills = "SELECT skill_id, skill_name FROM skills";
$skillResult = $conn->query($skills);

if (isset($_POST['delete_skill_id'])) {
    $delete_skill_id = $_POST['delete_skill_id'];
    $delete_query = "DELETE FROM skills WHERE skill_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_skill_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit;
}

if (isset($_POST['update_skill_id']) && isset($_POST['update_skill_name'])) {
    $update_skill_id = $_POST['update_skill_id'];
    $update_skill_name = $_POST['update_skill_name'];
    $update_query = "UPDATE skills SET skill_name = ? WHERE skill_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $update_skill_name, $update_skill_id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
    exit;
}

$showCommunity = "SELECT c.community_id, c.community_name, c.image_path, c.user_id AS admin_id, c.organized_by, 
        (SELECT COUNT(*) FROM community_members cm WHERE cm.community_id = c.community_id) AS member_count,
        (SELECT name FROM users u WHERE u.user_id = c.user_id) AS admin_name,
        c.status
        FROM communities c";

$showCommunityResult =  $conn->query($showCommunity);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* Sidebar styles */
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #343a40;
        padding-top: 20px;
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

<body>
    <div class="sidebar">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="admin_dashboard.php" class="nav-link d-flex align-items-center">
                    Admin Dashboard
                </a>
            </li><br><br><br>
            <li>
                <a href="javascript:void(0)" onclick="showSection('dashboard')" class="nav-link d-flex align-items-center">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" onclick="showSection('skills')" class="nav-link d-flex align-items-center">
                    Skills
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" onclick="showSection('community_domain')" class="nav-link d-flex align-items-center">
                    Community Domain
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" onclick="showSection('user_details')" class="nav-link d-flex align-items-center">
                    User Details
                </a>
            </li>
            <li>
                <a href="javascript:void(0)" onclick="showSection('all_communities')" class="nav-link d-flex align-items-center">
                    All Communities
                </a>
            </li>
        </ul>

        <a href="admin_logout.php" class="nav-link text-dark mt-auto">
            <button class="btn btn-danger ms-5 mt-5">
                Logout
            </button>
        </a>
    </div>

    <div class="main-content">

        <div id="dashboard" class="section active-section">
            <div class="row text-center mb-5">
                <div class="col-md-6 mb-4">
                    <div class="rounded-circle bg-primary d-flex justify-content-center align-items-center text-white mx-auto shadow" style="width: 150px; height: 150px;">
                        <p class="mb-0 fs-2"><?php echo $userCount; ?></p>
                    </div>
                    <span class="d-block mt-3 fw-bold">Registered Users</span>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="rounded-circle bg-primary d-flex justify-content-center align-items-center text-white mx-auto shadow" style="width: 150px; height: 150px;">
                        <p class="mb-0 fs-2"><?php echo $communityCount; ?></p>
                    </div>
                    <span class="d-block mt-3 fw-bold">Total Communities</span>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="fw-bold text-center mb-3">Communities</h5>
                    <label for="addDomain" class="form-label fw-semibold">Add Community Domain</label>
                    <form action="" method="POST">
                        <div class="input-group">
                            <input type="text" id="addDomain" name="addDomain" class="form-control" placeholder="Enter Domain" aria-label="Enter Domain" value="">
                            <button type="submit" class="btn btn-primary">Add</button>

                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold text-center mb-3">Skills</h5>
                    <label for="addSkill" class="form-label fw-semibold">Add Skill</label>
                    <form action="" method="POST">
                        <div class="input-group">
                            <input type="text" id="addSkill" name="addSkill" class="form-control" placeholder="Enter Skill" aria-label="Enter Skill" value="">
                            <button type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="user_details" class="section">
            <form method="GET" action="" class="mb-4">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="categoryFilter" class="form-label">Select Category:</label>
                        <select name="categoryFilter" id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            <?php
                            // Fetch categories dynamically
                            $categories = $conn->query("SELECT DISTINCT interest_name FROM Interest");
                            while ($category = $categories->fetch_assoc()) {
                                // Pre-select the filter option if it matches the GET parameter
                                $selected = isset($_GET['categoryFilter']) && $_GET['categoryFilter'] === $category['interest_name'] ? 'selected' : '';
                                echo "<option value='" . $category['interest_name'] . "' $selected>" . $category['interest_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="skillFilter" class="form-label">Select Skill:</label>
                        <select name="skillFilter" id="skillFilter" class="form-select">
                            <option value="">All Skills</option>
                            <?php
                            // Fetch skills dynamically
                            $skills = $conn->query("SELECT DISTINCT skill_name FROM Skills");
                            while ($skill = $skills->fetch_assoc()) {
                                // Pre-select the filter option if it matches the GET parameter
                                $selected = isset($_GET['skillFilter']) && $_GET['skillFilter'] === $skill['skill_name'] ? 'selected' : '';
                                echo "<option value='" . $skill['skill_name'] . "' $selected>" . $skill['skill_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </form>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Pincode</th>
                        <th>Categories</th>
                        <th>Skills</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch filter values from the GET request
                    $categoryFilter = isset($_GET['categoryFilter']) && !empty($_GET['categoryFilter']) ? $_GET['categoryFilter'] : '';
                    $skillFilter = isset($_GET['skillFilter']) && !empty($_GET['skillFilter']) ? $_GET['skillFilter'] : '';

                    // Base query to fetch all users
                    $sql = "SELECT 
                            users.name, 
                            users.email, 
                            users.age, 
                            States.state_name, 
                            Cities.city_name, 
                            Pincodes.pincode, 
                            GROUP_CONCAT(DISTINCT Interest.interest_name SEPARATOR ', ') AS user_interests, 
                            GROUP_CONCAT(DISTINCT Skills.skill_name SEPARATOR ', ') AS user_skills,
                            users.user_id AS user_id
                        FROM users
                        JOIN States ON users.state_id = States.state_id
                        JOIN Cities ON users.city_id = Cities.city_id
                        JOIN Pincodes ON users.pincode_id = Pincodes.pincode_id
                        LEFT JOIN user_interests ON users.user_id = user_interests.user_id
                        LEFT JOIN Interest ON FIND_IN_SET(Interest.interest_id, user_interests.interest_ids) > 0
                        LEFT JOIN user_skills ON users.user_id = user_skills.user_id
                        LEFT JOIN Skills ON FIND_IN_SET(Skills.skill_id, user_skills.skill_ids) > 0
                        WHERE 1=1"; // Default condition

                    // If category filter is applied
                    if (!empty($categoryFilter)) {
                        $sql .= " AND FIND_IN_SET((SELECT interest_id FROM Interest WHERE interest_name = '$categoryFilter'), user_interests.interest_ids)";
                    }

                    // If skill filter is applied
                    if (!empty($skillFilter)) {
                        $sql .= " AND FIND_IN_SET((SELECT skill_id FROM Skills WHERE skill_name = '$skillFilter'), user_skills.skill_ids)";
                    }

                    // Group by user ID
                    $sql .= " GROUP BY users.user_id";

                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['age']}</td>
                                <td>{$row['state_name']}</td>
                                <td>{$row['city_name']}</td>
                                <td>{$row['pincode']}</td>
                                <td>" . (!empty($row['user_interests']) ? $row['user_interests'] : 'No interests selected') . "</td>
                                <td>" . (!empty($row['user_skills']) ? $row['user_skills'] : 'No skills selected') . "</td>
                                <td>
                                   <div class= 'btn-group' role='group'>
                                        <a href='edit_user.php?id={$row['user_id']}' class='btn btn-primary'>Edit</a>
                                        <a href='delete_user.php?id={$row['user_id']}' class='btn btn-danger' onclick='return confirm('Are you sure you want to delete this user?')'>Delete</a>
                                    </div>
                                </td>
                              </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="skills" class="section mt-5">
            <h3 class="display-4 mb-4 text-center">Skills</h3>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Skills</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNumber = 1;
                        if ($skillResult->num_rows > 0) {
                            while ($row = $skillResult->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $serialNumber . "</td>";
                                echo "<td>" . htmlspecialchars($row['skill_name']) . "</td>";
                                echo "<td>";
                                echo "<form method='POST' style='display: inline-block;'>";
                                echo "<input type='hidden' name='delete_skill_id' value='" . $row['skill_id'] . "'>";
                                echo "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>";
                                echo "</form>";
                                echo " ";
                                echo "<button class='btn btn-warning btn-sm' onclick='showSkillUpdateForm(" . $row['skill_id'] . ", `" . htmlspecialchars($row['skill_name']) . "`)'>Edit</button>";
                                echo "</td>";
                                echo "</tr>";
                                $serialNumber++;
                            }
                        } else {
                            echo "<tr><td colspan='3'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="community_domain" class="section mt-5">
            <h3 class="display-4 mb-4 text-center">Domains</h3>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Domains</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serialNumber = 1;
                        if ($interestResult->num_rows > 0) {
                            while ($row = $interestResult->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $serialNumber . "</td>";
                                echo "<td>" . htmlspecialchars($row['interest_name']) . "</td>";
                                echo "<td>";
                                echo "<form method='POST' style='display: inline-block;'>";
                                echo "<input type='hidden' name='delete_id' value='" . $row['interest_id'] . "'>";
                                echo "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>";
                                echo "</form>";
                                echo " ";
                                echo "<button class='btn btn-warning btn-sm' onclick='showUpdateForm(" . $row['interest_id'] . ", `" . htmlspecialchars($row['interest_name']) . "`)'>Edit</button>";
                                echo "</td>";
                                echo "</tr>";
                                $serialNumber++;
                            }
                        } else {
                            echo "<tr><td colspan='3'>No records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="all_communities" class="section mt-5">
            <h3 class="display-4 mb-4 text-center">All Communities</h3>
            <div class="container">
                <div class="row">
                    <?php
                    if ($showCommunityResult->num_rows > 0) {
                        while ($row = $showCommunityResult->fetch_assoc()) {
                    ?>
                            <div class="col-md-6 mb-4">
                                <div class="card shadow">
                                    <div class="row g-0">
                                        <!-- Left Column: Community Image -->
                                        <div class="col-md-4">
                                            <img src="../<?php echo htmlspecialchars($row['image_path']); ?>"
                                                class="img-fluid rounded-start" alt="Community Image">
                                        </div>
                                        <!-- Right Column: Community Details -->
                                        <div class="col-md-8">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($row['community_name']); ?></h5>
                                                <p class="card-text">
                                                    <strong>Admin:</strong> <?php echo htmlspecialchars($row['admin_name']); ?><br>
                                                    <strong>Members:</strong> <?php echo $row['member_count']; ?><br>
                                                    <strong>Organized By:</strong> <?php echo htmlspecialchars($row['organized_by']); ?><br>
                                                    <strong>Status:</strong>
                                                    <span class="badge <?php echo $row['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $row['status'] ? 'Active' : 'Disabled'; ?>
                                                    </span>
                                                </p>
                                                <!-- Disable/Enable Form -->
                                                <form action="community_status.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="community_id" value="<?php echo $row['community_id']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $row['status']; ?>">
                                                    <button type="submit" class="btn btn-<?php echo $row['status'] ? 'warning' : 'success'; ?>">
                                                        <?php echo $row['status'] ? 'Disable' : 'Enable'; ?> Community
                                                    </button>
                                                </form>
                                                <!-- Delete Community Form -->
                                                <form action="delete_community.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="community_id" value="<?php echo $row['community_id']; ?>">
                                                    <button type="submit" class="btn btn-danger" name="delete_community">Delete Community</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-center'>No communities found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>



    </div>



    <!-- Update form for interest -->
    <div id="updateModal" style="display: none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; padding: 20px; z-index: 1000;">
        <h4>Update Interest</h4>
        <form method="POST">
            <input type="hidden" id="update_id" name="update_id">
            <div class="mb-3">
                <label for="update_name" class="form-label">Domain Name</label>
                <input type="text" id="update_name" name="update_name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" onclick="closeUpdateForm()">Cancel</button>
        </form>
    </div>

    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;" onclick="closeUpdateForm()"></div>
    <!-- Update form for interest -->


    <!-- Update form for skills -->
    <div id="skillUpdateModal" style="display: none; position: fixed; top: 20%; left: 50%; transform: translate(-50%, -50%); background: white; border: 1px solid #ccc; padding: 20px; z-index: 1000;">
        <h4>Update Skill</h4>
        <form method="POST">
            <input type="hidden" id="update_skill_id" name="update_skill_id">
            <div class="mb-3">
                <label for="update_skill_name" class="form-label">Skill Name</label>
                <input type="text" id="update_skill_name" name="update_skill_name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-secondary" onclick="closeSkillUpdateForm()">Cancel</button>
        </form>
    </div>

    <div id="skillModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 999;" onclick="closeSkillUpdateForm()"></div>
    <!-- Update form for skills -->


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSection(sectionId) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active-section');
            });

            var activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active-section');

            localStorage.setItem('activeSection', sectionId);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var savedSection = localStorage.getItem('activeSection');
            if (savedSection) {
                showSection(savedSection);
            } else {
                showSection('dashboard');
            }
        });

        function confirmDeleteCommunity(community_id) {
            var confirmDelete = confirm("Are you sure you want to delete this community? This action cannot be undone.");
            if (confirmDelete) {
                window.location.href = 'delete_community.php?community_id=' + community_id;
            }
        }

        // popup interest form
        function showUpdateForm(id, name) {
            document.getElementById('update_id').value = id;
            document.getElementById('update_name').value = name;
            document.getElementById('updateModal').style.display = 'block';
            document.getElementById('modalOverlay').style.display = 'block';
        }

        function closeUpdateForm() {
            document.getElementById('updateModal').style.display = 'none';
            document.getElementById('modalOverlay').style.display = 'none';
        }
        // popup interest form

        // popup skills form
        function showSkillUpdateForm(id, name) {
            document.getElementById('update_skill_id').value = id;
            document.getElementById('update_skill_name').value = name;
            document.getElementById('skillUpdateModal').style.display = 'block';
            document.getElementById('skillModalOverlay').style.display = 'block';
        }

        function closeSkillUpdateForm() {
            document.getElementById('skillUpdateModal').style.display = 'none';
            document.getElementById('skillModalOverlay').style.display = 'none';
        }
        // popup skills form

        <?php if ($successMessage): ?>
            alert("<?php echo $successMessage; ?>");
        <?php elseif ($errorMessage): ?>
            alert("<?php echo $errorMessage; ?>");
        <?php endif; ?>
    </script>
</body>

</html>