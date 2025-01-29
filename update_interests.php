<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['interests'])) {
    $user_id = $_SESSION['user_id'];
    $selected_interests = array_slice($_POST['interests'], 0, 5); 
    $interest_ids = implode(',', $selected_interests); 

    $sql = "INSERT INTO user_interests (user_id, interest_ids) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE interest_ids = VALUES(interest_ids)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $interest_ids);
    $stmt->execute();

    header("Location: profile.php"); 
    exit();
} else {
    header("Location: profile.php"); 
    exit();
}
?>
