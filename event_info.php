<?php
// Include the database connection file
include 'connection.php';

// Fetch the event ID from the URL
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Fetch event details from the database
    $sql = "SELECT e.event_name, e.event_description, e.event_time, e.community_id, c.community_name, e.location 
            FROM events e
            JOIN communities c ON e.community_id = c.community_id
            WHERE e.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        die("Event not found.");
    }
} else {
    die("Invalid event ID.");
}

date_default_timezone_set('Asia/Kolkata');

// Format the event_time to 12-hour format with AM/PM
$event_time_ist = new DateTime($event['event_time']);
$formatted_event_time = $event_time_ist->format('h:i A'); // 12-hour format with AM/PM
$formatted_event_date = $event_time_ist->format('l, jS F Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sticky-bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 -1px 5px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            z-index: 10;
        }

        .left-column {
            width: 70%;
        }

        .right-column {
            width: 30%;
        }
    </style>
</head>

<body>
    <?php include('navbar.php') ?>
    <div class="container my-4 bg-body-tertiary" style="padding-bottom: 40px;"> <!-- Add margin-bottom here -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8 left-column">
                <img src="path_to_event_image.jpg" alt="Event Image" class="img-fluid rounded mb-3">
                <h2 class="fw-bold"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                <p><?php echo $event['event_description']; ?></p>
            </div>

            <!-- Right Column -->
            <div class="col-md-4 right-column">
                <!-- Community Card -->
                <div class="card mb-3 mt-3 border">
                    <div class="card-body">
                        <h5 class="card-title">Community</h5>
                        <p class="card-text"><?php echo htmlspecialchars($event['community_name']); ?></p>
                    </div>
                </div>

                <!-- Event Time and Location Card -->
                <div class="card mb-3 mt-3 border">
                    <div class="card-body">
                        <h5 class="card-title">Event Details</h5>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($formatted_event_date); ?></p>
                        <p><strong>Time:</strong> <?php echo htmlspecialchars($formatted_event_time); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Bottom Navbar -->
    <div class="sticky-bottom-nav d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold"><?php echo htmlspecialchars($formatted_event_date); ?> <?php echo htmlspecialchars($formatted_event_time); ?></span><br>
            <span><?php echo htmlspecialchars($event['event_name']); ?></span>
        </div>
        <a href="attend_event.php?event_id=<?php echo $event_id; ?>" class="btn btn-primary">Attend</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>