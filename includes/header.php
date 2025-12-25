<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderly Diet AI Platform</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS for Accessibility -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fs-3" href="/pages/dashboard.php">Diet AI for Elderly</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning" href="/pages/admin/dashboard.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] !== 'Admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="/pages/dashboard.php">Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/pages/settings.php">Settings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/pages/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/pages/index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="/pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">