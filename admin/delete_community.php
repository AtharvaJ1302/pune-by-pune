<?php
include('connection.php');

if (isset($_POST['delete_community'])) {
    $community_id = $_POST['community_id'];

    $conn->begin_transaction();

    try {
        $deleteMembersQuery = "DELETE FROM community_members WHERE community_id = ?";
        $stmt = $conn->prepare($deleteMembersQuery);
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $stmt->close();

        $deleteCommunityQuery = "DELETE FROM communities WHERE community_id = ?";
        $stmt = $conn->prepare($deleteCommunityQuery);
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        header("Location: admin_dashboard.php?message=Community deleted successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error deleting community: " . $e->getMessage();
    }
}
?>
