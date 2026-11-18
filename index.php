<?php

require_once __DIR__ . '/includes/auth.php';

$isLoggedIn = isLoggedIn();
$isAdmin = $isLoggedIn && ($_SESSION['role'] ?? '') === 'admin';

if ($isLoggedIn) {
    $dashboardLink = $isAdmin
        ? 'admin/dashboard.php'
        : 'dashboard.php';

    $dashboardLabel = 'Go to Dashboard';
} else {
    $dashboardLink = 'login.php';
    $dashboardLabel = 'Login';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LocalFix | Municipal Fault Reporting</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .hero {
            background: linear-gradient(135deg, #173d73, #2457a6);
            color: white;
            padding: 100px 0;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.15rem;
            color: #e5edf8;
            max-width: 650px;
        }

        .section-padding {
            padding: 70px 0;
        }

        .feature-card {
            height: 100%;
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .category-card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            height: 100%;
            border: 1px solid #e9ecef;
        }

        .cta-section {
            background-color: #e8eef7;
            padding: 65px 0;
        }

        footer {
            background-color: #173d73;
            color: white;
            padding: 25px 0;
        }

        footer p {
            margin-bottom: 0;
            color: #dce6f3;
        }

        @media (max-width: 768px) {
            .hero {
                padding: 70px 0;
            }

            .hero h1 {
                font-size: 2.2rem;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            LocalFix
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarContent"
            aria-controls="navbarContent"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">

            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">

                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">
                        How It Works
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#fault-categories">
                        Fault Categories
                    </a>
                </li>

                <li class="nav-item">
                    <a class="btn btn-light btn-sm px-3"
                       href="<?= htmlspecialchars($dashboardLink) ?>">
                        <?= htmlspecialchars($dashboardLabel) ?>
                    </a>
                </li>

                <?php if (!$isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm px-3"
                           href="register.php">
                            Register
                        </a>
                    </li>
                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>

<section class="hero">
    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <h1>
                    Making Municipal Fault Reporting Simpler
                </h1>

                <p class="mt-4">
                    LocalFix provides a central platform where residents
                    can report municipal faults, receive unique reference
                    numbers and track the progress of their reports.
                </p>

                <div class="mt-4 d-flex flex-wrap gap-3">

                    <?php if ($isLoggedIn): ?>

                        <a href="<?= htmlspecialchars($dashboardLink) ?>"
                           class="btn btn-light btn-lg">
                            Go to Dashboard
                        </a>

                    <?php else: ?>

                        <a href="register.php"
                           class="btn btn-light btn-lg">
                            Get Started
                        </a>

                        <a href="login.php"
                           class="btn btn-outline-light btn-lg">
                            Login
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>
</section>

<section class="section-padding" id="how-it-works">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">How LocalFix Works</h2>

            <p class="text-muted">
                A straightforward process for reporting and tracking faults.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">

                        <div class="feature-icon">📝</div>

                        <h5 class="fw-bold">1. Submit a Report</h5>

                        <p class="text-muted mb-0">
                            Register and submit a municipal fault by
                            selecting a category, providing its location
                            and describing the problem.
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">

                        <div class="feature-icon">🔖</div>

                        <h5 class="fw-bold">2. Receive a Reference</h5>

                        <p class="text-muted mb-0">
                            Each submitted report receives a unique
                            reference number that residents can use
                            to identify and track it.
                        </p>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card feature-card shadow-sm">
                    <div class="card-body p-4">

                        <div class="feature-icon">📊</div>

                        <h5 class="fw-bold">3. Track Progress</h5>

                        <p class="text-muted mb-0">
                            Residents can view their reports and check
                            the latest status updates through their
                            LocalFix account.
                        </p>

                    </div>
                </div>
            </div>

        </div>

    </div>

</section>

<section class="section-padding bg-white" id="fault-categories">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">What Can You Report?</h2>

            <p class="text-muted">
                LocalFix supports several categories of municipal faults.
            </p>

        </div>

        <div class="row g-3">

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Potholes</h5>
                    <p class="text-muted mb-0">
                        Damaged roads and potholes.
                    </p>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Streetlights</h5>
                    <p class="text-muted mb-0">
                        Broken or faulty streetlights.
                    </p>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Water Leaks</h5>
                    <p class="text-muted mb-0">
                        Water leaks and burst pipes.
                    </p>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Blocked Drains</h5>
                    <p class="text-muted mb-0">
                        Blocked or damaged drainage systems.
                    </p>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Illegal Dumping</h5>
                    <p class="text-muted mb-0">
                        Unauthorised waste disposal.
                    </p>
                </div>
            </div>

            <div class="col-6 col-md-4">
                <div class="category-card">
                    <h5>Traffic Lights</h5>
                    <p class="text-muted mb-0">
                        Faulty or damaged traffic lights.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>

<section class="cta-section">

    <div class="container text-center">

        <h2 class="fw-bold">Help Make Your Community Better</h2>

        <p class="text-muted mt-3">
            Use LocalFix to submit and monitor municipal fault reports
            through one central platform.
        </p>

        <?php if (!$isLoggedIn): ?>

            <a href="register.php" class="btn btn-primary btn-lg mt-3">
                Create an Account
            </a>

        <?php else: ?>

            <a href="<?= htmlspecialchars($dashboardLink) ?>"
               class="btn btn-primary btn-lg mt-3">
                Open Your Dashboard
            </a>

        <?php endif; ?>

    </div>

</section>

<footer>

    <div class="container text-center">

        <h5 class="fw-bold">LocalFix</h5>

        <p>
            Web-Based Municipal Fault Reporting and Service Request
            Management System
        </p>

        <p class="mt-2 small">
            Academic project prototype | 2026
        </p>

    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
