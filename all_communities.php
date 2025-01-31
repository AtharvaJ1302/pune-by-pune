<?php
include('navbar.php');
include('connection.php');

$sql = "SELECT communities.community_id, communities.community_name, communities.community_description, communities.image_path, 
        COUNT(community_members.user_id) AS member_count
        FROM communities
        LEFT JOIN community_members ON communities.community_id = community_members.community_id
        WHERE communities.status = 1
        GROUP BY communities.community_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./CSS/home.css">
</head>
<style>
    .card-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
</style>
<body>
    <div class="container my-4">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="mb-0">All Communities</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4 d-flex">
                                <a href="community_info.php?community_id=<?php echo $row['community_id']; ?>" class="community-card text-decoration-none">
                                    <div class="card h-100 w-100">
                                        <img src="<?php echo $row['image_path']; ?>" 
                                             class="card-img-top community-image" 
                                             alt="Community Image" 
                                             style="object-fit: contain; width: 100%; height: 200px; border-radius: 15px;">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['community_name']); ?></h5>
                                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($row['community_description']); ?></p>
                                            <div>
                                                <span class="badge bg-primary"><?php echo $row['member_count']; ?> members</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-center">No communities are available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include('footer.php') ?>
</body>
</html>