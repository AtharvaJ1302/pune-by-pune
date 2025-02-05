<?php
session_start();
include 'connection.php'; 

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to attend an event.");
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id > 0) {
    $checkQuery = "SELECT * FROM event_participants WHERE event_id = ? AND user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $insertQuery = "INSERT INTO event_participants (event_id, user_id, joined_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $event_id, $user_id);

        if ($stmt->execute()) {
            echo "<script>
            alert('Successfuly joined the event');
            window.location.href = 'event_info.php?event_id=$event_id';
        </script>";
            exit();
        } else {
            header("Location: events.php?error=failed");
            exit();
        }
    } else {
        echo "<script>
        alert('Already joined the event');
        window.location.href = 'event_info.php?event_id=$event_id';
    </script>";
        exit();
    }
} else {
    header("Location: events.php?error=invalid_event");
    exit();
}
?>
