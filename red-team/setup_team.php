<?php
include 'includes/db_ctf.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['team_id']) && $_SESSION['team_id']) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'join'; // 'join' or 'create'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];

    if (isset($_POST['create_team'])) {
        $team_name = trim($_POST['team_name']);

        // Generate random 6 char code
        $join_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        $check = mysqli_query($conn, "SELECT id FROM ctf_teams WHERE name = '$team_name'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Team name taken.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO ctf_teams (name, join_code) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $team_name, $join_code);
            if (mysqli_stmt_execute($stmt)) {
                $team_id = mysqli_insert_id($conn);
                mysqli_query($conn, "UPDATE ctf_users SET team_id = $team_id WHERE id = $user_id");

                $_SESSION['team_id'] = $team_id;
                $_SESSION['team_name'] = $team_name;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Error creating team.";
            }
        }
    } elseif (isset($_POST['join_team'])) {
        $code = trim($_POST['join_code']);

        $stmt = mysqli_prepare($conn, "SELECT id, name FROM ctf_teams WHERE join_code = ?");
        mysqli_stmt_bind_param($stmt, "s", $code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $team_id = $row['id'];
            mysqli_query($conn, "UPDATE ctf_users SET team_id = $team_id WHERE id = $user_id");

            $_SESSION['team_id'] = $team_id;
            $_SESSION['team_name'] = $row['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid Team Code.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Team Setup</title>
    <link href="challenge/css/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-black text-white d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="container text-center" style="max-width: 800px;">
        <h2 class="mb-5">Welcome,
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Join Team -->
            <div class="col-md-6">
                <div class="p-5 rounded h-100 d-flex flex-column justify-content-center"
                    style="background: #111; border: 1px solid #333;">
                    <h4 class="mb-4">Join Existing Team</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="join_code"
                                class="form-control bg-dark text-white text-center border-secondary"
                                placeholder="Enter 6-Char Code" required maxlength="6">
                        </div>
                        <button type="submit" name="join_team" class="btn btn-outline-primary w-100 rounded-pill">Join
                            Team</button>
                    </form>
                </div>
            </div>

            <!-- Create Team -->
            <div class="col-md-6">
                <div class="p-5 rounded h-100 d-flex flex-column justify-content-center"
                    style="background: #1d1d1f; border: 1px solid #444;">
                    <h4 class="mb-4">Create New Team</h4>
                    <form method="POST">
                        <div class="mb-3">
                            <input type="text" name="team_name"
                                class="form-control bg-dark text-white text-center border-secondary"
                                placeholder="Team Name" required>
                        </div>
                        <button type="submit" name="create_team" class="btn btn-primary w-100 rounded-pill">Create
                            Team</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="logout_ctf.php" class="text-secondary small">Logout</a>
        </div>
    </div>

</body>

</html>