<?php
include '../includes/config.php';

header('Content-Type: application/json');


$query = "SELECT u.username, SUM(f.points) as score, MAX(s.timestamp) as last_solve 
          FROM users u 
          JOIN solves s ON u.id = s.user_id 
          JOIN flags f ON s.flag_id = f.id 
          GROUP BY u.id 
          ORDER BY score DESC, last_solve ASC";

$result = mysqli_query($conn, $query);

$scores = [];
while ($row = mysqli_fetch_assoc($result)) {
    $scores[] = [
        'username' => $row['username'],
        'score' => (int) $row['score'],
        'last_update' => $row['last_solve']
    ];
}

echo json_encode($scores, JSON_PRETTY_PRINT);
?>