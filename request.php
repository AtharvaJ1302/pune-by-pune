<?php
include 'connection.php';

if (!isset($_GET['request_id']) || !isset($_GET['action'])) {
    exit("Invalid request.");
}

$request_id = $_GET['request_id'];
$action = $_GET['action'];

// Fetch request details, including the document path
$sql_request = "SELECT * FROM request WHERE request_id = '$request_id'";
$result_request = $conn->query($sql_request);

if ($result_request->num_rows === 0) {
    exit("Request not found.");
}

$request_data = $result_request->fetch_assoc();
$user_id = $request_data['user_id'];
$community_id = $request_data['community_id'];
$document_path = $request_data['document_path']; // Ensure this column exists in the database

if ($action === "approve") {
    $sql_add_member = "INSERT INTO community_members (user_id, community_id) VALUES ('$user_id', '$community_id')";
    if ($conn->query($sql_add_member) === TRUE) {
        // Delete the verification document if it exists
        if (!empty($document_path) && file_exists($document_path)) {
            unlink($document_path); // Deletes the file from the server
        }

        // Remove the request from the request table
        $sql_delete_request = "DELETE FROM request WHERE request_id = '$request_id'";
        $conn->query($sql_delete_request);
        
        $message = "User has been approved and added to the community. Verification document deleted.";
    } else {
        exit("Error adding user to the community.");
    }
} elseif ($action === "reject") {
    // If rejected, delete the verification document
    if (!empty($document_path) && file_exists($document_path)) {
        unlink($document_path);
    }

    // Delete the request from the request table
    $sql_delete = "DELETE FROM request WHERE request_id = '$request_id'";
    $conn->query($sql_delete);

    $message = "User's request has been rejected and verification document removed.";
} else {
    exit("Invalid action.");
}

// Redirect with a message
echo "<script>
    alert('$message');
    window.location.href = 'community_admin_dashboard.php?community_id=$community_id';
</script>";
exit();
?>
