<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>
            alert('Please login to view your profile.');
            window.location.href = 'home.php';
          </script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// edit profile section
$profileQuery = "
    SELECT 
        users.profile_picture,
        users.name,
        users.email,
        users.age,
        states.state_name,
        cities.city_name,
        pincodes.pincode
    FROM users
    LEFT JOIN states ON users.state_id = states.state_id
    LEFT JOIN cities ON users.city_id = cities.city_id
    LEFT JOIN pincodes ON users.pincode_id = pincodes.pincode_id
    WHERE users.user_id = ?";
$stmt = $conn->prepare($profileQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profileResult = $stmt->get_result();
$user = $profileResult->fetch_assoc();

$profile_photo = !empty($user['profile_picture'])
    ? htmlspecialchars($user['profile_picture'])
    : 'https://via.placeholder.com/150';
$name = $user['name'] ?? 'N/A';
$email = $user['email'] ?? 'N/A';
$age = $user['age'] ?? 'N/A';
$state = $user['state_name'] ?? 'N/A';
$city = $user['city_name'] ?? 'N/A';
$pincode = $user['pincode'] ?? 'N/A';

//update profile 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = intval($_POST['age']);
    $state_id = isset($_POST['state']) ? $_POST['state'] : null; 
    $city_id = isset($_POST['city']) ? $_POST['city'] : null; 
    $pincode_id = isset($_POST['pincode']) ? $_POST['pincode'] : null; 

    $profile_picture = $_FILES['profile_picture'];
    $upload_dir = 'uploads/profile_pictures/';
    $file_name = $user_id . '_' . basename($profile_picture['name']);
    $upload_path = $upload_dir . $file_name;

    $fields_to_update = [];
    $params = [];

    $query = "UPDATE users SET ";

    if (!empty($name)) {
        $fields_to_update[] = "name = ?";
        $params[] = $name;
    }

    if (!empty($email)) {
        $fields_to_update[] = "email = ?";
        $params[] = $email;
    }

    if (!empty($age)) {
        $fields_to_update[] = "age = ?";
        $params[] = $age;
    }

    if ($state_id !== null) {
        $fields_to_update[] = "state_id = ?";
        $params[] = $state_id;
    }

    if ($city_id !== null) {
        $fields_to_update[] = "city_id = ?";
        $params[] = $city_id;
    }

    if ($pincode_id !== null) {
        $fields_to_update[] = "pincode_id = ?";
        $params[] = $pincode_id;
    }

    if (!empty($profile_picture['name'])) {
        if (move_uploaded_file($profile_picture['tmp_name'], $upload_path)) {
            $fields_to_update[] = "profile_picture = ?";
            $params[] = $upload_path;
        } else {
            $_SESSION['error'] = "Failed to upload profile picture.";
            header('Location: profile.php');
            exit;
        }
    }

    $query .= implode(", ", $fields_to_update) . " WHERE user_id = ?";

    $params[] = $user_id;

    if ($stmt = mysqli_prepare($conn, $query)) {
        $param_types = str_repeat('s', count($params) - 1); 
        $param_types .= 'i'; 
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Profile updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating profile: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Error preparing the update query: " . mysqli_error($conn);
    }

    header('Location: profile.php');
    exit;
}
//update profile

// edit profile section


// edit interest section
$interestSql = "SELECT interest_ids FROM user_interests WHERE user_id = ?";
$stmt = $conn->prepare($interestSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$interestResult = $stmt->get_result();

$interests = [];

if ($row = $interestResult->fetch_assoc()) {
    $interest_ids = explode(',', $row['interest_ids']);

    if (!empty($interest_ids)) {
        $placeholders = implode(',', array_fill(0, count($interest_ids), '?'));
        $query = "SELECT interest_name FROM interest WHERE interest_id IN ($placeholders)";
        $stmt = $conn->prepare($query);

        $stmt->bind_param(str_repeat('i', count($interest_ids)), ...$interest_ids);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($interest = $res->fetch_assoc()) {
            $interests[] = $interest['interest_name'];
        }
    }
}
$interest_text = !empty($interests) ? implode(', ', $interests) : "No interests available";

$available_interests = [];
$availableInterestSql = "SELECT interest_id, interest_name FROM interest";
$availableInterestResult = $conn->query($availableInterestSql);
while ($row = $availableInterestResult->fetch_assoc()) {
    $available_interests[$row['interest_id']] = $row['interest_name'];
}

// Fetch user's selected interests
$user_interests = [];
$userInterstSql = "SELECT interest_ids FROM user_interests WHERE user_id = ?";
$stmt = $conn->prepare($userInterstSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userInterstResult = $stmt->get_result();
if ($row = $userInterstResult->fetch_assoc()) {
    $user_interests = explode(',', $row['interest_ids']);
}

// edit interest section



// edit skills
$skillSql = "SELECT skill_ids FROM user_skills WHERE user_id = ?";
$stmt = $conn->prepare($skillSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$skillResult = $stmt->get_result();

$skills = [];

if ($row = $skillResult->fetch_assoc()) {
    $skill_ids = explode(',', $row['skill_ids']);
    if (!empty($skill_ids)) {
        $placeholders = implode(',', array_fill(0, count($skill_ids), '?'));
        $query = "SELECT skill_name FROM skills WHERE skill_id IN ($placeholders)";
        $stmt = $conn->prepare($query);

        $stmt->bind_param(str_repeat('i', count($skill_ids)), ...$skill_ids);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($skill = $res->fetch_assoc()) {
            $skills[] = $skill['skill_name'];
        }
    }
}
$skill_text = !empty($skills) ? implode(', ', $skills) : "No skills available";

$available_skills = [];
$availableSkillsSql = "SELECT skill_id, skill_name FROM skills";
$availableSkills = $conn->query($availableSkillsSql);
while ($row = $availableSkills->fetch_assoc()) {
    $available_skills[$row['skill_id']] = $row['skill_name'];
}

// Fetch user's selected skills
$user_skills = [];
$userSkillsSql = "SELECT skill_ids FROM user_skills WHERE user_id = ?";
$stmt = $conn->prepare($userSkillsSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userSkillResult = $stmt->get_result();
if ($row = $userSkillResult->fetch_assoc()) {
    $user_skills = explode(',', $row['skill_ids']);
}
// edit skills
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    .profile-picture {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #ddd;
    }
</style>

<body>
    <?php include('navbar.php'); ?>
    <!-- Profile Section -->
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body text-center">
                        <img src="<?php echo ($profile_photo); ?>" alt="Profile Picture" class="profile-picture mb-3">
                        <h2><?php echo htmlspecialchars($name); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($email); ?></p>
                        <p class="text-muted">Age: <?php echo htmlspecialchars($age); ?></p>
                        <p class="text-muted">State: <?php echo htmlspecialchars($state); ?></p>
                        <p class="text-muted">City: <?php echo htmlspecialchars($city); ?></p>
                        <p class="text-muted">Pincode: <?php echo htmlspecialchars($pincode); ?></p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
                        <hr>
                        <h5>Interests</h5>
                        <p class="text-muted" id="interests-text"><?php echo htmlspecialchars($interest_text); ?></p>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editInterestsModal">Edit Interests</button>
                        <hr>
                        <h5>Skills</h5>
                        <p class="text-muted" id="skills-text"><?php echo htmlspecialchars($skill_text); ?></p>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#editSkillsModal">Edit Skills</button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" value="<?php echo $name ?>">
                        </div>
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" value="<?php echo $email ?>">
                        </div>
                        <div class="mb-3">
                            <label for="edit-age" class="form-label">Age</label>
                            <input type="number" class="form-control" id="edit-age" name="age" value="<?php echo $age ?>">
                        </div>
                        <div class="mb-3">
                            <label for="edit-state" class="form-label">State</label>
                            <select class="form-control" id="edit-state" name="state">
                                <option value="" disabled selected><?php echo $state ?></option>
                                <?php
                                $query = "SELECT state_id, state_name FROM States";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['state_id'] . "'>" . $row['state_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-city" class="form-label">City</label>
                            <select class="form-control" id="edit-city" name="city">
                                <option value="" disabled selected><?php echo $city ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-pincode" class="form-label">Pincode</label>
                            <select class="form-control" id="edit-pincode" name="pincode">
                                <option value="" disabled selected><?php echo $pincode ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-profile_picture" class="form-label">Profile Picture</label>
                            <div>
                                <img src="<?php echo $profile_photo; ?>" alt="Profile Picture" id="current-profile-picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                            </div>

                            <input type="file" class="form-control" id="edit-profile_picture" name="profile_picture" accept="image/*" value="">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Interests Modal -->
    <div class="modal fade" id="editInterestsModal" tabindex="-1" aria-labelledby="editInterestsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInterestsModalLabel">Edit Interests</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="update_interests.php">
                        <div class="mb-3">
                            <label for="edit-interests" class="form-label">Choose up to 5 Interests:</label>
                            <div id="interests-checkbox-group">
                                <?php foreach ($available_interests as $id => $name): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="interests[]" value="<?php echo $id; ?>"
                                            <?php echo in_array($id, $user_interests) ? 'checked' : ''; ?>>
                                        <label class="form-check-label"><?php echo htmlspecialchars($name); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="save-interests">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Skills Modal -->
    <div class="modal fade" id="editSkillsModal" tabindex="-1" aria-labelledby="editSkillsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSkillsModalLabel">Edit Skills</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="update_skills.php">
                        <div class="mb-3">
                            <label for="edit-skills" class="form-label">Choose up to 10 Skills:</label>
                            <div id="skills-checkbox-group">
                                <?php foreach ($available_skills as $id => $name): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="skills[]" value="<?php echo $id; ?>"
                                            <?php echo in_array($id, $user_skills) ? 'checked' : ''; ?>>
                                        <label class="form-check-label"><?php echo htmlspecialchars($name); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="save-skills">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php include('footer.php') ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('edit-state').addEventListener('change', function() {
            var stateId = this.value;
            if (stateId) {
                fetch('get_cities.php?state_id=' + stateId)
                    .then(response => response.json())
                    .then(data => {
                        var citySelect = document.getElementById('edit-city');
                        citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
                        data.forEach(city => {
                            citySelect.innerHTML += '<option value="' + city.city_id + '">' + city.city_name + '</option>';
                        });
                    });
            }
        });

        document.getElementById('edit-city').addEventListener('change', function() {
            var cityId = this.value;
            if (cityId) {
                fetch('get_pincode.php?city_id=' + cityId)
                    .then(response => response.json())
                    .then(data => {
                        var pincodeSelect = document.getElementById('edit-pincode');
                        pincodeSelect.innerHTML = '<option value="" disabled selected>Select Pincode</option>';
                        data.forEach(pincode => {
                            pincodeSelect.innerHTML += '<option value="' + pincode.pincode_id + '">' + pincode.pincode + '</option>';
                        });
                    });
            }
        });
    </script>
</body>

</html>