<?php
include 'connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:registration.php');
}

$user_id = $_SESSION['user_id'];

$query = "SELECT skill_id, skill_name FROM skills";
$skills = $conn->query($query);

if (!$skills) {
    die("Query failed: " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['skill']) && is_array($_POST['skill'])) {
        $selected_skills = implode(',', $_POST['skill']); 
        $checkQuery = "SELECT * FROM user_skills WHERE user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $updateQuery = "UPDATE user_skills SET skill_ids = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $selected_skills, $user_id);
        } else {
            $insertQuery = "INSERT INTO user_skills (user_id, skill_ids) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("is", $user_id, $selected_skills);
        }

        if ($stmt->execute()) {
            header('location: home.php');
            exit();
        } else {
            echo "<script>alert('Error saving skills.');</script>";
        }
    } else {
        echo "<script>alert('Please select at least one skill.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Interests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h5 {
            text-align: center;
            margin-bottom: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .list-group-item {
            font-size: 14px;
        }

        button {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h5>Select Skills</h5>
        <form method="POST">
            <ul class="list-group">
                <?php while ($skill = $skills->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <input type="checkbox" name="skill[]" value="<?php echo $skill['skill_id']; ?>">
                        <?php echo $skill['skill_name']; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
            <button type="submit" class="btn btn-danger mt-3">Save Interest</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>