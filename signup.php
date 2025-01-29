<?php
include('connection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $age = $_POST['age'];
    $state_id = $_POST['state'];
    $city_id = $_POST['city'];
    $pincode_id = $_POST['pincode'];

    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $uploads_dir = 'uploads/profile_pictures';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']); 
        $target_file = "$uploads_dir/$file_name";

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = $file_name;
        } else {
            echo "<div class='alert alert-danger'>Error uploading profile picture.</div>";
        }
    }

    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $emailResult = $conn->query($checkEmailQuery);

    if ($emailResult->num_rows > 0) {
        $result = "<div class='alert alert-danger'>Error: Email already exists. Please use a different email.</div>";
    } else {
        $sql = "INSERT INTO users (name, email, password, age, state_id, city_id, pincode_id, profile_picture) 
                VALUES ('$name', '$email', '$password', $age, $state_id, $city_id, $pincode_id, '$profile_picture')";

        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id; 
            $_SESSION['user_id'] = $user_id; 
            header('Location: categories.php');
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        async function fetchCities(stateId) {
            if (stateId) {
                const response = await fetch(`get_cities.php?state_id=${stateId}`);
                const cities = await response.json();
                const cityDropdown = document.getElementById('city');
                cityDropdown.innerHTML = '<option value="">Select City</option>';
                cities.forEach(city => {
                    cityDropdown.innerHTML += `<option value="${city.city_id}">${city.city_name}</option>`;
                });
                document.getElementById('pincode').innerHTML = '<option value="">Select Pincode</option>';
            }
        }

        async function fetchPincodes(cityId) {
            if (cityId) {
                const response = await fetch(`get_pincode.php?city_id=${cityId}`);
                const pincodes = await response.json();
                const pincodeDropdown = document.getElementById('pincode');
                pincodeDropdown.innerHTML = '<option value="">Select Pincode</option>';
                pincodes.forEach(pincode => {
                    pincodeDropdown.innerHTML += `<option value="${pincode.pincode_id}">${pincode.pincode}</option>`;
                });
            }
        }
    </script>
</head>

<body>
    <div class="container mt-5 mb-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-sm" style="max-width: 400px; width: 100%;">
            <h1 class="text-center mb-4">User Registration</h1>
            <?php if (isset($result)) echo $result; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="age" class="form-label">Age:</label>
                    <input type="number" id="age" name="age" class="form-control" min="0">
                </div>

                <div class="mb-3">
                    <label for="state" class="form-label">State:</label>
                    <select id="state" name="state" class="form-select" onchange="fetchCities(this.value)" required>
                        <option value="">Select State</option>
                        <?php
                        $states = $conn->query("SELECT state_id, state_name FROM States");
                        while ($row = $states->fetch_assoc()) {
                            echo "<option value='{$row['state_id']}'>{$row['state_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label">City:</label>
                    <select id="city" name="city" class="form-select" onchange="fetchPincodes(this.value)" required>
                        <option value="">Select City</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="pincode" class="form-label">Pincode:</label>
                    <select id="pincode" name="pincode" class="form-select" required>
                        <option value="">Select Pincode</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">
                Don't have an account? <a href="user_login.php">Login</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>