<?php

$db_host = getenv('DB_HOST') ? getenv('DB_HOST') : "localhost";
$db_user = "redteam_user";
$db_pass = "root";
$db_name = "cybertech_db";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function getTeamScore($team_id)
{
    global $conn;
    $query = "SELECT SUM(c.points) as score FROM ctf_solves s 
              JOIN challenges c ON s.challenge_id = c.id 
              WHERE s.team_id = $team_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['score'] ? $row['score'] : 0;
}
?>