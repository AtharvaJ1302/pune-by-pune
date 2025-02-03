<?php
include 'connection.php';

if (!isset($_GET['request_id']) || !isset($_GET['action'])) {
    exit("Invalid request.");
}

$request_id = $_GET['request_id'];
$action = $_GET['action'];

// Fetch request details
$sql_request = "SELECT * FROM request WHERE request_id = '$request_id'";
$result_request = $conn->query($sql_request);

if ($result_request->num_rows === 0) {
    exit("Request not found.");
}

$request_data = $result_request->fetch_assoc();
$user_id = $request_data['user_id'];
$community_id = $request_data['community_id'];

if ($action === "approve") {
    // Add user to community_members
    $sql_add_member = "INSERT INTO community_members (user_id, community_id) VALUES ('$user_id', '$community_id')";
    $conn->query($sql_add_member);

    // Update request status to approved (1)
    $sql_update_request = "UPDATE request SET status = 1 WHERE request_id = '$request_id'";
    $conn->query($sql_update_request);

    $message = "User has been approved and added to the community.";
} elseif ($action === "reject") {
    // Delete request from request table
    $sql_delete = "DELETE FROM request WHERE request_id = '$request_id'";
    $conn->query($sql_delete);

    $message = "User's request has been rejected and removed from the list.";
} else {
    exit("Invalid action.");
}

// Redirect back to the admin dashboard with a success message
echo "<script>
    alert('$message');
    window.location.href = 'community_admin_dashboard.php?community_id=$community_id';
</script>";
exit();
?>
