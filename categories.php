<?php
include 'connection.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: registration.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT interest_id, interest_name FROM interest";
$interests = $conn->query($query);

if (!$interests) {
    die("Query failed: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['interest']) && count($_POST['interest']) <= 5) {
        $selected_interests = implode(',', $_POST['interest']);

        $checkQuery = "SELECT * FROM user_interests WHERE user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $updateQuery = "UPDATE user_interests SET interest_ids = ? WHERE user_id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $selected_interests, $user_id);
        } else {
            $insertQuery = "INSERT INTO user_interests (user_id, interest_ids) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("is", $user_id, $selected_interests);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Interests saved successfully.');</script>";
            header('Location: skills.php');
            exit();
        } else {
            echo "<script>alert('Error saving interests.');</script>";
        }
    } else {
        echo "<script>alert('Please select up to 3 interests only.');</script>";
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
            background-color: #f8f9fa;
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            margin-top: 50px;
            margin-bottom: 50px;
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

        .max {
            color: grey;
            font-size: 15px;

            display: table;
            margin-bottom: 20px;
            margin-left: 95px;

        }
    </style>
</head>

<body>
    <div class="form-container">
        <h5>Select Categories</h5>
        <span class="max">(Select Maximum of 5)</span>
        <form method="POST">
            <ul class="list-group">
                <?php while ($interest = $interests->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <input class="interest-checkbox" type="checkbox" name="interest[]" value="<?php echo $interest['interest_id']; ?>">
                        <?php echo $interest['interest_name']; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
            <button type="submit" class="btn btn-danger mt-3">Save Interest</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        const checkboxes = document.querySelectorAll('.interest-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedCount = document.querySelectorAll('.interest-checkbox:checked').length;

                if (checkedCount > 5) {
                    checkbox.checked = false;
                    alert('You can select up to 5 options only.');
                }
            });
        });
    </script>
</body>

</html>