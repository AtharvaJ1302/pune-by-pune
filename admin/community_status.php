<?php
include 'connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $community_id = $_POST['community_id'];
    $current_status = $_POST['current_status'];

    $new_status = $current_status ? 0 : 1;

    $updateQuery = "UPDATE communities SET status = ? WHERE community_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ii', $new_status, $community_id);

    if ($stmt->execute()) {
        header('Location: admin_dashboard.php?status_updated=true');
        exit;
    } else {
        header('Location: admin_dashboard.php?status_updated=true');
        exit;
    }
    $stmt->close();
}
?>
