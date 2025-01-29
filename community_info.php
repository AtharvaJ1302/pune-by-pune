<?php
session_start();
include 'connection.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!isset($_GET['community_id'])) {
    echo "Community not found.";
    exit;
}

$community_id = $_GET['community_id'];

$sql = "SELECT communities.*, COUNT(community_members.user_id) AS member_count
        FROM communities
        LEFT JOIN community_members ON communities.community_id = community_members.community_id
        WHERE communities.community_id = '$community_id'
        GROUP BY communities.community_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $community = $result->fetch_assoc();
    $is_creator = $community['user_id'] == $user_id;

    $admin_id = $community['user_id'];
    $admin_sql = "SELECT name FROM users WHERE user_id = '$admin_id'";
    $admin_result = $conn->query($admin_sql);
    $admin_name = ($admin_result->num_rows > 0) ? $admin_result->fetch_assoc()['name'] : 'Unknown Admin';

    $check_membership = "SELECT * FROM community_members WHERE user_id = '$user_id' AND community_id = '$community_id'";
    $membership_result = $conn->query($check_membership);
    $is_member = $membership_result->num_rows > 0;
} else {
    echo "Community not found.";
    exit;
}

$members_sql = "
    SELECT 
    u.name, 
    u.age, 
    s.state_name, 
    c.city_name, 
    p.pincode, 
    GROUP_CONCAT(sk.skill_name ORDER BY sk.skill_name SEPARATOR ', ') AS skills
FROM users u
INNER JOIN cities c ON u.city_id = c.city_id
INNER JOIN states s ON u.state_id = s.state_id
INNER JOIN pincodes p ON u.pincode_id = p.pincode_id
LEFT JOIN user_skills us ON u.user_id = us.user_id
LEFT JOIN skills sk ON FIND_IN_SET(sk.skill_id, us.skill_ids)
WHERE u.user_id IN (
    SELECT user_id 
    FROM community_members 
    WHERE community_id = '$community_id'
) AND u.user_id != '$admin_id'
GROUP BY u.user_id, u.name, u.age, s.state_name, c.city_name, p.pincode
";


$members_result = $conn->query($members_sql);


$current_time = date('Y-m-d H:i:s'); 
$eventQuery = "SELECT e.event_id, e.event_name, e.event_description, e.event_time, 
                             (SELECT COUNT(*) FROM event_participants WHERE event_participants.event_id = e.event_id) AS attendees_count
                      FROM events e
                      WHERE e.event_time > ? AND e.community_id = ?
                      ORDER BY e.event_time ASC";

$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("si", $current_time, $community_id);
$stmt->execute();
$eventResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CommunityHub - Connect & Engage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./CSS/home.css">
</head>
<style>
    .attendees-images img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: -10px;
        border: 2px solid white;
    }

    .attendees-count {
        font-weight: 600;
        background-color: blue;
        color: white;
        border-radius: 15px;
        padding-left: 15px;
        padding-right: 15px;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>

<body>
    <?php include('navbar.php') ?>
    <div class="container mt-4">
        <div class="row g-0">
            <div class="col-md-4 bg-warning text-center d-flex align-items-center justify-content-center position-relative p-4" style="background: url('<?php echo $community['image_path']; ?>'); background-size: cover; background-position: center; background-repeat: no-repeat; height:auto;">
                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
                <div class="bg-opacity-75 text-center rounded position-relative">
                    <h1 class="text-white fw-bold"><?php echo $community['community_name']; ?></h1>
                </div>
            </div>

            <div class="col-md-8 bg-white">
                <div class="p-4">
                    <h3 class="fw-bold"><?php echo $community['community_name']; ?></h3>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-2"><?php echo $community['location']; ?></i>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-people-fill text-primary me-2"><?php echo $community['member_count']; ?> members</i>
                        </li>
                        <li>
                            <i class="bi bi-person-circle me-2">Organized by <strong><?php echo $community['organized_by'] ?></strong></i>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <?php if ($is_creator): ?>
                            <a href="./community_admin_dashboard.php?community_id=<?php echo $community_id; ?>" class="btn btn-danger fw-bold">Admin</a>
                        <?php elseif (!$is_member): ?>
                            <a href="join_community.php?community_id=<?php echo $community_id; ?>" class="btn btn-primary fw-bold">Join this group</a>
                        <?php else: ?>
                            <p class="text-success fw-bold">You are a member of this community</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="about-tab" data-bs-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">About</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="events-tab" data-bs-toggle="tab" href="#events" role="tab" aria-controls="events" aria-selected="false">Events</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="members-tab" data-bs-toggle="tab" href="#members" role="tab" aria-controls="members" aria-selected="false">Members</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="photos-tab" data-bs-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">Photos</a>
                </li>
            </ul>
            <div class="tab-content mt-3" id="myTabContent">
                <!-- About Section -->
                <div class="tab-pane fade show active" id="about" role="tabpanel" aria-labelledby="about-tab">
                    <h5 class="fw-bold">What we’re about</h5>
                    <p><?php echo $community['community_description']; ?></p>
                </div>

                <!-- Events Section -->
                <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                    <?php
                    $eventCountQuery = "SELECT COUNT(*) AS event_count FROM events WHERE community_id = ?";
                    $stmt = $conn->prepare($eventCountQuery);

                    if ($stmt) {
                        $stmt->bind_param("i", $community_id); 
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();
                        $eventCount = $row['event_count'];
                        $stmt->close();
                    } else {
                        $eventCount = 0;
                    }
                    ?>

                    <h5 class="fw-bold">Upcoming events (<?php echo $eventCount; ?>)</h5>

                    <div class="container mt-5 mb-5">
                        <?php if ($eventResult->num_rows > 0) {
                            while ($row = $eventResult->fetch_assoc()) {
                                $formatted_time = date('D, M j, Y, g:i A T', strtotime($row['event_time']));
                                $short_description = substr($row['event_description'], 0, 200) . (strlen($row['event_description']) > 200 ? "..." : "");
                        ?>
                                <div class="card shadow-lg p-3 mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h6 class="text-uppercase text-muted mb-1"><?php echo $formatted_time; ?></h6>
                                                <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="event_info.php?event_id=<?php echo $row['event_id']; ?>" class="btn btn-outline-primary">View Event</a>
                                            </div>
                                        </div>
                                        <p class="card-text mb-3">
                                            <?php echo $short_description; ?>
                                        </p>
                                        <div class="d-flex align-items-center">
                                            <span class="ms-3 attendees-count"><?php echo $row['attendees_count']; ?> attendees</span>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p class='text-center text-muted'>No upcoming events found.</p>";
                        }
                        ?>
                    </div>
                </div>


                <!-- Members Section -->
                <div class="tab-pane fade mb-5" id="members" role="tabpanel" aria-labelledby="members-tab">
                    <h5 class="fw-bold">Members (<?php echo $community['member_count']; ?>)</h5>

                    <ul class="list-group">
                        <li class="list-group-item">
                            <h6 class="fw-bold"><?php echo htmlspecialchars($admin_name); ?> <span class="badge bg-danger">Community Admin</span></h6>
                        </li>

                        <?php if ($members_result->num_rows > 0): ?>
                            <?php while ($member = $members_result->fetch_assoc()): ?>
                                <li class="list-group-item">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($member['name']); ?></h6>
                                    <p class="mb-0">
                                        <strong>Age:</strong> <?php echo htmlspecialchars($member['age']); ?><br>
                                        <strong>City:</strong> <?php echo htmlspecialchars($member['city_name']); ?>, <?php echo htmlspecialchars($member['state_name']); ?><br>
                                        <strong>Pincode:</strong> <?php echo htmlspecialchars($member['pincode']); ?><br>
                                        <strong>Skills:</strong>
                                        <?php
                                        $skills = explode(',', $member['skills']); 
                                        echo implode(', ', array_map('htmlspecialchars', $skills)); 
                                        ?>
                                    </p>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No members have joined this community yet.</p>
                        <?php endif; ?>


                    </ul>
                </div>


                <!-- Photos Section -->
                <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                    <h5 class="fw-bold">Photos</h5>
                    <p>Content for photos will go here.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            if (window.history.pushState) {
                window.history.pushState(null, null, window.location.href);
            }
        };

        window.onpopstate = function() {
            window.location.href = "home.php";
        }
    </script>
</body>

</html>