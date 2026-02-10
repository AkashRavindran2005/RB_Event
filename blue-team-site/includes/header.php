<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberTech Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-custom w-100 d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="index.php">CyberTech</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse flex-grow-0" id="navbarNav">
                <ul class="navbar-nav gap-3 align-items-center">
                    <!-- Public links removed for Blue Team Platform -->

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                             <li class="nav-item"><a class="nav-link text-secondary" href="admin.php">Admin</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-3">
                             <span class="text-secondary me-3 small">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Analyst'); ?></span>
                             <a class="btn btn-outline-danger btn-sm" href="logout.php">Sign Out</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-3">
                            <a class="btn btn-primary btn-sm" style="font-size: 14px; padding: 8px 16px;"
                                href="login.php">Sign In</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>