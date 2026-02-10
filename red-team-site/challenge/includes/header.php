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
                    <li class="nav-item"><a class="nav-link" href="index.php">Overview</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="newsletter.php">Newsletter</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>

                    <?php if (isset($_SESSION['user_id'])):
                        $my_credits = getUserCredits($_SESSION['user_id']);
                        ?>
                        <li class="nav-item d-flex align-items-center gap-3 ms-3">
                            <span class="text-secondary small">$<?php echo $my_credits; ?></span>
                            <div class="dropdown">
                                <a href="#" class="btn btn-primary btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown">Account</a>
                                <ul class="dropdown-menu dropdown-menu-dark"
                                    style="background: #1d1d1f; border: 1px solid #333;">
                                    <li><a class="dropdown-item text-secondary hover-white" href="profile.php">Profile
                                            Settings</a></li>
                                    <li><a class="dropdown-item text-secondary hover-white" href="dashboard.php">Internal
                                            Dashboard</a></li>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <li><a class="dropdown-item text-secondary hover-white" href="admin.php">Admin Panel</a>
                                        </li>
                                    <?php endif; ?>
                                    <li>
                                        <hr class="dropdown-divider border-secondary">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="logout.php">Sign Out</a></li>
                                </ul>
                            </div>
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