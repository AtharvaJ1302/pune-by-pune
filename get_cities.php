<?php
include('connection.php');

$state_id = $_GET['state_id'];
$query = "SELECT city_id, city_name FROM Cities WHERE state_id = $state_id";
$result = $conn->query($query);

$cities = [];
while ($row = $result->fetch_assoc()) {
    $cities[] = $row;
}

echo json_encode($cities);
?>
