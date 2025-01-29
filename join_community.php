<?php
include 'connection.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id === null) {
    echo "<script>
        alert('Please log in to join a community.');
        window.location.href = 'user_login.php'; // Redirect to the login page
    </script>";
    exit;
}

if (!isset($_GET['community_id'])) {
    echo "Community not found.";
    exit;
}

$community_id = $_GET['community_id'];

$sql_check = "SELECT * FROM community_members WHERE user_id = '$user_id' AND community_id = '$community_id'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    echo "You are already a member of this community.";
    exit;
}

$sql_join = "INSERT INTO community_members (user_id, community_id) VALUES ('$user_id', '$community_id')";
if ($conn->query($sql_join) === TRUE) {
    echo "You have successfully joined the community!";
    header("Location: community_info.php?community_id=" . $community_id); 
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
