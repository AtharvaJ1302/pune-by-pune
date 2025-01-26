<?php
// Include database connection
include('connection.php');

// Check if form is submitted
if (isset($_POST['delete_community'])) {
    // Get the community ID from the form
    $community_id = $_POST['community_id'];

    // Start the transaction
    $conn->begin_transaction();

    try {
        // Step 1: Delete members from the community_members table
        $deleteMembersQuery = "DELETE FROM community_members WHERE community_id = ?";
        $stmt = $conn->prepare($deleteMembersQuery);
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $stmt->close();

        // Step 2: Delete the community from the communities table
        $deleteCommunityQuery = "DELETE FROM communities WHERE community_id = ?";
        $stmt = $conn->prepare($deleteCommunityQuery);
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $stmt->close();

        // Commit the transaction
        $conn->commit();

        // Redirect to the admin dashboard with success message
        header("Location: admin_dashboard.php?message=Community deleted successfully");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo "Error deleting community: " . $e->getMessage();
    }
}
?>
