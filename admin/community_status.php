<?php
include 'connection.php'; // Include your database connection script

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $community_id = $_POST['community_id'];
    $current_status = $_POST['current_status'];

    // Toggle the status
    $new_status = $current_status ? 0 : 1;

    // Update the status in the database
    $updateQuery = "UPDATE communities SET status = ? WHERE community_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ii', $new_status, $community_id);

    if ($stmt->execute()) {
        // Redirect back with a success message
        header('Location: admin_dashboard.php?status_updated=true');
        exit;
    } else {
        // Redirect back with an error message
        header('Location: admin_dashboard.php?status_updated=true');
        exit;
    }
    $stmt->close();
}
?>
