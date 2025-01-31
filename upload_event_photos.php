<?php
include 'connection.php'; // Database connection

// Get community_id from URL
if (isset($_GET['community_id'])) {
    $community_id = intval($_GET['community_id']);
} else {
    die("Community ID not provided!");
}

// Fetch available events for the selected community
$sql = "SELECT event_id, event_name FROM events WHERE community_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $community_id);
$stmt->execute();
$result = $stmt->get_result();
?>