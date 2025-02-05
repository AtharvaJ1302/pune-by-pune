<?php
include 'connection.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id === null) {
    $_SESSION['show_popup'] = true;
    header("Location: community_info.php?community_id=" . $_POST['community_id']);
    exit;
}

if (!isset($_POST['community_id'])) {
    echo "Community not found.";
    exit;
}

$community_id = $_POST['community_id'];

$sql_check = "SELECT * FROM request WHERE user_id = '$user_id' AND community_id = '$community_id'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    echo "You have already sent a request to join this community.";
    exit;
}

$sql_user = "SELECT city_id, pincode_id FROM users WHERE user_id = '$user_id'";
$result_user = $conn->query($sql_user);

if ($result_user->num_rows === 0) {
    echo "User details not found.";
    exit;
}

$user_data = $result_user->fetch_assoc();
$city_id = $user_data['city_id'];
$pincode_id = $user_data['pincode_id'];

$sql_skills = "SELECT skill_ids FROM user_skills WHERE user_id = '$user_id'";
$result_skills = $conn->query($sql_skills);

$skills_array = [];
while ($row = $result_skills->fetch_assoc()) {
    $skills_array[] = $row['skill_ids']; 
}

$skills_str = implode(',', $skills_array); 


$document_path = null;
$upload_dir = "uploads/verification_docs/";

if (isset($_FILES["document"]) && $_FILES["document"]["error"] === 0) {
    $file_name = time() . "_" . basename($_FILES["document"]["name"]);
    $target_file = $upload_dir . $file_name;

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ["pdf", "jpg", "png"];
    $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        echo "Invalid file type. Only PDF, JPG, and PNG are allowed.";
        exit;
    }

    if (move_uploaded_file($_FILES["document"]["tmp_name"], $target_file)) {
        $document_path = $target_file;
    } else {
        echo "Error uploading the file.";
        exit;
    }
}

$sql_insert = "INSERT INTO request (user_id, community_id, skill_ids, city_id, pincode_id, status, document_path) 
               VALUES ('$user_id', '$community_id', '$skills_str', '$city_id', '$pincode_id', 0, '$document_path')";

if ($conn->query($sql_insert) === TRUE) {
    echo "<script>
        alert('Your request to join the community has been sent for approval.');
        window.location.href = 'community_info.php?community_id=$community_id';
    </script>";
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
