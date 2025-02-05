<?php
include('navbar.php');
include 'connection.php';

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

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

$event_time_ist = new DateTime($event['event_time']);
$formatted_event_time = $event_time_ist->format('h:i A');
$formatted_event_date = $event_time_ist->format('l, jS F Y');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = intval($_POST['event_id']);
    $user_id = intval($_SESSION['user_id']); 
    $domain = trim($_POST['domain']);
    $stream = trim($_POST['stream']);

    $sql = "SELECT profile_picture, pincode_id, phone_number FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!$user_data) {
        die("User not found.");
    }

    $profile_photo = $user_data['profile_picture'];
    $pincode_id = $user_data['pincode_id'];
    $mobile_number = $user_data['phone_number']; 

    $insert_sql = "INSERT INTO event_attendees (event_id, user_id, domain, stream, profile_photo, pincode_id, mobile_number) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisssis", $event_id, $user_id, $domain, $stream, $profile_photo, $pincode_id, $mobile_number);

    if ($insert_stmt->execute()) {
        echo "<script>alert('Successfully registered for the event!'); window.location.href='event_info.php?event_id=$event_id';</script>";
    } else {
        echo "Error: " . $insert_stmt->error;
    }
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$event_id = intval($_GET['event_id']);

// Check if the user has already registered
$sql_registrationCheck = "SELECT * FROM event_attendees WHERE event_id = ? AND user_id = ?";
$stmt_check = $conn->prepare($sql_registrationCheck);
$stmt_check->bind_param("ii", $event_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$is_registered = $result_check->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./CSS/home.css">
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
    <div class="container my-4 border shadow-sm ps-4" style="padding-bottom: 50px;background: linear-gradient(to right, #ff7e5f, #feb47b); color: black;">
        <div class="row">
            <div class="col-md-8 left-column">
                <img src="path_to_event_image.jpg" alt="Event Image" class="img-fluid rounded mb-3">
                <h2 class="fw-bold"><?php echo htmlspecialchars($event['event_name']); ?></h2>
                <p><?php echo $event['event_description']; ?></p>
            </div>

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

    <div class="sticky-bottom-nav d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold"><?php echo htmlspecialchars($formatted_event_date); ?> <?php echo htmlspecialchars($formatted_event_time); ?></span><br>
            <span><?php echo htmlspecialchars($event['event_name']); ?></span>
        </div>

        <?php
        $current_time = date('Y-m-d H:i:s');
        $event_time = $event['event_time'];

        if ($event_time > $current_time) {
            if ($is_registered): ?>
                <button class="" style="background-color: green; color: black; font-weight: bold; border: none; padding: 10px 20px;">
                    Registered for Event
                </button>
            <?php else: ?>
                <a href="#" class="btn" style="background-color: green; color: red; font-weight: bold; border: none; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#attendModal">
                    Attend
                </a>
        <?php endif;
        } else {
            echo '<button class="btn btn-secondary" style="color: black !important;" disabled>Event Ended</button>';;
        }
        ?>
    </div>


    <div class="modal fade" id="attendModal" tabindex="-1" aria-labelledby="attendModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="attendModalLabel">Attend Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain</label>
                            <input type="text" class="form-control" id="domain" name="domain" required>
                        </div>
                        <div class="mb-3">
                            <label for="stream" class="form-label">Stream</label>
                            <input type="text" class="form-control" id="stream" name="stream" required>
                        </div>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>