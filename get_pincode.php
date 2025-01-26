<?php
include('connection.php');

$city_id = intval($_GET['city_id']);
$query = "SELECT pincode_id, pincode FROM Pincodes WHERE city_id = $city_id";
$result = $conn->query($query);

$pincodes = [];
while ($row = $result->fetch_assoc()) {
    $pincodes[] = $row;
}

echo json_encode($pincodes);
?>
