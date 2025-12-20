<?php
// pages/index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderly Diet AI Assistant</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1544654268-1a778cc91b93?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center/cover;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .feature-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 20px;
        }

        .about-section {
            background-color: #f8f9fa;
            padding: 80px 0;
        }

        .cta-btn {
            padding: 15px 30px;
            font-size: 1.2rem;
            border-radius: 30px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fs-4" href="index.php"><i class="bi bi-heart-pulse-fill"></i> Elderly Diet AI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2 px-3" href="register.php">Get Started</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4">Smart Nutrition for Golden Years</h1>
            <p class="lead mb-5">Your personalized AI companion for healthy aging. Custom diet plans, easy food
                tracking, and professional dietitian support.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-primary cta-btn me-3">Create Free Account</a>
                <a href="login.php" class="btn btn-outline-light cta-btn">Login</a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-success cta-btn">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose Us?</h2>
                <p class="text-muted">Designed specifically for elderly health needs.</p>
            </div>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100">
                        <i class="bi bi-robot feature-icon"></i>
                        <h4>AI-Powered Plans</h4>
                        <p>Our advanced AI generates weekly meal plans tailored to your health profile, allergies, and
                            dietary restrictions.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100">
                        <i class="bi bi-camera feature-icon"></i>
                        <h4>Snap & Log</h4>
                        <p>Simply take a photo of your meal. Our Image Analyzer identifies the food and logs nutrients
                            automatically.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border rounded shadow-sm h-100">
                        <i class="bi bi-person-check feature-icon"></i>
                        <h4>Dietitian Connected</h4>
                        <p>You are assigned a professional dietitian who reviews your progress and answers your
                            questions via chat.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80"
                        alt="Healthy Elderly" class="img-fluid rounded shadow">
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-3">About Us</h2>
                    <p class="lead text-muted">Empowering independence through better nutrition.</p>
                    <p>
                        The Elderly Diet Platform was born from a simple mission: to make healthy eating accessible and
                        simple for our elderly community.
                        We understand that maintaining a balanced diet becomes challenging with age, whether due to
                        health conditions, complexity of meal prep, or lack of guidance.
                    </p>
                    <p>
                        Our team combines cutting-edge AI technology with the compassionate care of real certified
                        dietitians.
                        We believe that technology should serve people, bridging the gap between medical advice and
                        daily life.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 text-center">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> Elderly Diet AI Platform. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>