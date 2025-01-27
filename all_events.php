<?php
include 'connection.php';

// Fetch all events from the database
$sql = "SELECT event_id, event_name, event_description, event_time FROM events ORDER BY event_time DESC";
$eventResult = $conn->query($sql);

// Predefined set of light colors (avoiding dark colors)
$colors = [
    '#FFDDC1', '#FFABAB', '#FFC3A0', '#FF677D', '#D4A5A5',
    '#392F5A', '#31A2AC', '#61C0BF', '#6B4226', '#D9BF77',
    '#F1F1F1', '#F7C8D4', '#B0D0D3', '#F2A7B3', '#6A0572',
    '#B8D8D8', '#E3A6B9', '#FF9F1C', '#F4A261', '#2A9D8F'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include('navbar.php'); ?>
    <div class="container my-5">
        <h1 class="mb-4 text-center">All Events</h1>

        <div class="row gy-4">
            <?php
            if ($eventResult->num_rows > 0):
                while ($row = $eventResult->fetch_assoc()):
                    // Format the event date
                    $formatted_time = date('D, M j, Y, g:i A T', strtotime($row['event_time']));

                    $random_color = $colors[array_rand($colors)];
            ?>
                    <div class="col-md-4">
                        <a href="event_info.php?event_id=<?php echo $row['event_id']; ?>" class="text-decoration-none">
                            <div class="card shadow-lg h-100" style="background-color: <?php echo $random_color; ?>;">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                                    <p class="card-text" style="overflow: hidden; text-overflow: ellipsis; max-height: 3em;">
                                        <?php echo htmlspecialchars($row['event_description']); ?>
                                    </p>
                                    <span class="badge bg-info text-dark"><?php echo $formatted_time; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
            <?php
                endwhile;
            else:
            ?>
                <p class="text-center">No events found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
