<?php
include('connection.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $age = $_POST['age'];
        $state_id = $_POST['state_id']; 
        $city_id = $_POST['city_id'];   
        $pincode_id = $_POST['pincode_id']; 

        $update_sql = "UPDATE users SET name=?, email=?, age=?, state_id=?, city_id=?, pincode_id=? WHERE user_id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssiiiii", $name, $email, $age, $state_id, $city_id, $pincode_id, $user_id);

        if ($update_stmt->execute()) {
            header("Location: admin_dashboard.php"); 
            exit;
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
} else {
    echo "No user ID specified.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../CSS/regStyle.css">
</head>
<body>
    <div class="container">
    <h1>Edit User</h1>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label>Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required><br>

        
        <label>State:</label>
        <select name="state_id" required>
            <?php
            $states_sql = "SELECT * FROM States";
            $states_result = $conn->query($states_sql);
            while ($state = $states_result->fetch_assoc()) {
                echo "<option value='{$state['state_id']}' " . ($user['state_id'] == $state['state_id'] ? 'selected' : '') . ">{$state['state_name']}</option>";
            }
            ?>
        </select><br>

        <label>City:</label>
        <select name="city_id" required>
            <?php
            $cities_sql = "SELECT * FROM Cities";
            $cities_result = $conn->query($cities_sql);
            while ($city = $cities_result->fetch_assoc()) {
                echo "<option value='{$city['city_id']}' " . ($user['city_id'] == $city['city_id'] ? 'selected' : '') . ">{$city['city_name']}</option>";
            }
            ?>
        </select><br>

        <label>Pincode:</label>
        <select name="pincode_id" required>
            <?php
            $pincodes_sql = "SELECT * FROM Pincodes";
            $pincodes_result = $conn->query($pincodes_sql);
            while ($pincode = $pincodes_result->fetch_assoc()) {
                echo "<option value='{$pincode['pincode_id']}' " . ($user['pincode_id'] == $pincode['pincode_id'] ? 'selected' : '') . ">{$pincode['pincode']}</option>";
            }
            ?>
        </select><br>

        <input type="submit" value="Update User">
    </form>
    </div>
</body>
</html>
