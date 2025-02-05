<?php
include 'connection.php'; 

$community_id = isset($_GET['community_id']) ? intval($_GET['community_id']) : 0;

$response = ["success" => false];

$sql_users = "SELECT 
                users.profile_picture AS photo, 
                users.name, 
                cities.city_name AS city, 
                event_attendees.event_id,
                event_attendees.domain, 
                event_attendees.stream, 
                users.phone_number
            FROM users 
            JOIN cities ON users.city_id = cities.city_id
            JOIN event_attendees ON users.user_id = event_attendees.user_id
            JOIN events ON event_attendees.event_id = events.event_id
            WHERE events.community_id = ?";

$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $community_id);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

$users = [];
while ($row = $result_users->fetch_assoc()) {
    $users[] = $row;
}

$sql_events = "SELECT event_id, event_name FROM events WHERE community_id = ?";
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("i", $community_id);
$stmt_events->execute();
$result_events = $stmt_events->get_result();

$events = [];
while ($row = $result_events->fetch_assoc()) {
    $events[] = $row;
}

$response["success"] = true;
$response["users"] = $users;
$response["events"] = $events;

echo json_encode($response);
?>
