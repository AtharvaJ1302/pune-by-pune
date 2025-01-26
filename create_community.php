<?php
include 'connection.php'; 

// Check if the user is logged in and get the user_id from the session
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get community details from the form
    $community_name = $_POST['name'];
    $community_description = $_POST['description'];
    $organized_by = $_POST['organized_by']; // Get the "Organized By" field

    // Handling file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageFolder = "uploads/"; // Directory to store uploaded images
        $imagePath = $imageFolder . $imageName;

        if (move_uploaded_file($imageTmpName, $imagePath)) {
            // Insert the community into the database
            $sql = "INSERT INTO communities (community_name, community_description, image_path, user_id, admin, organized_by)
                    VALUES ('$community_name', '$community_description', '$imagePath', '$user_id', TRUE, '$organized_by')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>alert('Community Created');</script>";
                header('Location: home.php');
            } else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Failed to upload image.');</script>";
        }
    } else {
        echo "<script>alert('No image file selected or an error occurred.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Community</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card shadow-lg p-4" style="width: 400px;">
            <h1 class="text-center mb-4">Create Community</h1>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Community Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Community Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="organized_by" class="form-label">Organized By</label>
                    <input type="text" name="organized_by" id="organized_by" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Community Image</label>
                    <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
                </div>
                
                <button type="submit" class="btn btn-danger w-100">Create</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
