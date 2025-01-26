<?php
include('connection.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete user from database
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: admin_dashboard.php"); // Redirect back to the dashboard after deletion
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No user ID specified.";
}
?>
