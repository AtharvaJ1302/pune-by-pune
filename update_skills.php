<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['skills'])) {
    $user_id = $_SESSION['user_id'];
    $selected_skills = array_slice($_POST['skills'], 0, 10); 
    $skill_ids = implode(',', $selected_skills);

    $sql = "INSERT INTO user_skills (user_id, skill_ids) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE skill_ids = VALUES(skill_ids)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $skill_ids);
    $stmt->execute();

    header("Location: profile.php"); 
    exit();
} else {
    header("Location: profile.php"); 
    exit();
}
?>
