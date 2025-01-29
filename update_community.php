<?php
include 'connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $community_id = $_POST['community_id'];
    $community_name = $_POST['community_name'];
    $description = $_POST['description'];
    $organized_by = $_POST['organized_by'];
    $banner_image = null;

    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $banner_image = $target_dir . basename($_FILES['banner_image']['name']);
        move_uploaded_file($_FILES['banner_image']['tmp_name'], $banner_image);
    }
    

    $update_sql = "
        UPDATE communities
        SET community_name = ?, community_description = ?, organized_by = ?, image_path = IFNULL(?, image_path)
        WHERE community_id = ?
    ";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $community_name, $description, $organized_by, $banner_image, $community_id);

    if ($stmt->execute()) {
        header("Location: community_admin_dashboard.php?community_id=$community_id");
    } else {
        echo "Error updating community: " . $conn->error;
    }
}
?>
