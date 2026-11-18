<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

requireLogin();

if (($_SESSION['role'] ?? '') !== 'resident') {
    header('Location: /LocalFix/admin/dashboard.php');
    exit;
}

$csrfToken = generateCsrfToken();
$userId = $_SESSION['user_id'];

// Total reports
$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports WHERE user_id = ?'
);
$stmt->execute([$userId]);
$totalReports = $stmt->fetch()['total'];

// Submitted reports
$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports
     WHERE user_id = ? AND status = ?'
);
$stmt->execute([$userId, 'Submitted']);
$submittedReports = $stmt->fetch()['total'];

// In progress reports
$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports
     WHERE user_id = ? AND status = ?'
);
$stmt->execute([$userId, 'In Progress']);
$inProgressReports = $stmt->fetch()['total'];

// Resolved reports
$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports
     WHERE user_id = ? AND status = ?'
);
$stmt->execute([$userId, 'Resolved']);
$resolvedReports = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard | LocalFix</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #F3F6FA;
            color: #263445;
        }

        /* Navigation */
        .navbar {
            background: #101D32 !important;
            padding: 16px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff !important;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            margin-right: 9px;
            border-radius: 9px;
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            font-weight: 800;
        }

        .nav-user {
            color: #DCE6F2;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .btn-logout {
            background: rgba(255,255,255,0.10);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 7px 16px;
            font-weight: 600;
        }

        .btn-logout:hover {
            background: #35BFEA;
            color: #101D32;
            border-color: #35BFEA;
        }

        /* Main layout */
        .dashboard-container {
            max-width: 1200px;
        }

        /* Welcome panel */
        .dashboard-welcome {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #101D32 0%, #1B2B43 70%, #163B52 100%);
            color: #ffffff;
            border-radius: 18px;
            padding: 42px;
            box-shadow: 0 12px 30px rgba(16, 29, 50, 0.12);
        }

        .dashboard-welcome::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            border: 1px solid rgba(53,191,234,0.18);
            border-radius: 50%;
            right: -75px;
            top: -150px;
            box-shadow:
                0 0 0 35px rgba(53,191,234,0.04),
                0 0 0 75px rgba(53,191,234,0.03);
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-label {
            color: #35BFEA;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .dashboard-welcome h2 {
            color: #ffffff;
            font-size: clamp(1.7rem, 4vw, 2.3rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }

        .dashboard-welcome p {
            color: #C3D0DF;
            font-size: 0.98rem;
            max-width: 600px;
            margin-bottom: 0;
            line-height: 1.7;
        }

        .welcome-accent {
            color: #35BFEA;
        }

        /* Section headings */
        .dashboard-section {
            margin-bottom: 38px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .dashboard-section-title {
            color: #101D32;
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 0;
            letter-spacing: -0.4px;
        }

        .section-subtitle {
            color: #7A8798;
            font-size: 0.85rem;
            margin-top: 5px;
            margin-bottom: 0;
        }

        /* Statistics cards */
        .dashboard-stat {
            height: 100%;
            border: 1px solid #E4EAF1;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(16,29,50,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(16,29,50,0.09);
        }

        .dashboard-stat .card-body {
            padding: 23px;
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .stat-label {
            color: #758296;
            font-size: 0.83rem;
            font-weight: 600;
            margin-bottom: 0;
        }

        .stat-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 39px;
            height: 39px;
            border-radius: 11px;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .stat-number {
            color: #101D32;
            font-size: 2.15rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 0;
        }

        .stat-total .stat-icon {
            background: #E9F4FB;
            color: #278BB8;
        }

        .stat-submitted .stat-icon {
            background: #FFF4DF;
            color: #C58A26;
        }

        .stat-progress .stat-icon {
            background: #E6F8F6;
            color: #009F8C;
        }

        .stat-resolved .stat-icon {
            background: #E8F5EC;
            color: #3D9660;
        }

        /* Quick action cards */
        .dashboard-action {
            height: 100%;
            border: 1px solid #E4EAF1;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(16,29,50,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-action:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(16,29,50,0.09);
        }

        .dashboard-action .card-body {
            padding: 30px;
        }

        .action-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #E7F7FC, #E5F8F5);
            color: #078FAD;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 21px;
        }

        .dashboard-action h4 {
            color: #101D32;
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .dashboard-action p {
            color: #758296;
            font-size: 0.9rem;
            line-height: 1.7;
            min-height: 50px;
            margin-bottom: 22px;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            border: none;
            border-radius: 9px;
            padding: 10px 19px;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #23ACD8, #00A991);
            color: #101D32;
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(0,191,166,0.2);
        }

        /* Footer */
        .dashboard-footer {
            border-top: 1px solid #E1E7EF;
            padding: 22px 0;
            margin-top: 35px;
            color: #8793A3;
            font-size: 0.8rem;
            text-align: center;
        }

        @media (max-width: 576px) {
            .dashboard-welcome {
                padding: 28px 23px;
            }

            .dashboard-stat .card-body {
                padding: 17px;
            }

            .stat-number {
                font-size: 1.8rem;
            }

            .stat-label {
                font-size: 0.76rem;
            }

            .dashboard-action .card-body {
                padding: 23px;
            }

            .nav-user {
                display: none;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <span class="brand-mark">L</span>LocalFix
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="nav-user">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="logout.php" class="m-0">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <button type="submit" class="btn btn-logout btn-sm">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<main class="container dashboard-container py-5">

    <!-- Welcome section -->
    <section class="dashboard-welcome mb-5">
        <div class="welcome-content">

            <div class="welcome-label">Resident Portal</div>

            <h2>
                Welcome back,
                <span class="welcome-accent">
                    <?= htmlspecialchars($_SESSION['full_name']) ?>
                </span>!
            </h2>

            <p>
                Your community matters. Submit municipal faults, monitor
                your reports, and stay informed as issues move towards resolution.
            </p>

        </div>
    </section>

    <!-- Statistics -->
    <section class="dashboard-section">

        <div class="section-heading">
            <div>
                <h4 class="dashboard-section-title">Report Overview</h4>
                <p class="section-subtitle">
                    A summary of your submitted municipal reports.
                </p>
            </div>
        </div>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-total">
                    <div class="card-body">

                        <div class="stat-top">
                            <p class="stat-label">Total Reports</p>
                            <div class="stat-icon">#</div>
                        </div>

                        <h3 class="stat-number">
                            <?= $totalReports ?>
                        </h3>

                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-submitted">
                    <div class="card-body">

                        <div class="stat-top">
                            <p class="stat-label">Submitted</p>
                            <div class="stat-icon">↑</div>
                        </div>

                        <h3 class="stat-number">
                            <?= $submittedReports ?>
                        </h3>

                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-progress">
                    <div class="card-body">

                        <div class="stat-top">
                            <p class="stat-label">In Progress</p>
                            <div class="stat-icon">↻</div>
                        </div>

                        <h3 class="stat-number">
                            <?= $inProgressReports ?>
                        </h3>

                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-resolved">
                    <div class="card-body">

                        <div class="stat-top">
                            <p class="stat-label">Resolved</p>
                            <div class="stat-icon">✓</div>
                        </div>

                        <h3 class="stat-number">
                            <?= $resolvedReports ?>
                        </h3>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Quick actions -->
    <section class="dashboard-section">

        <div class="section-heading">
            <div>
                <h4 class="dashboard-section-title">Quick Actions</h4>
                <p class="section-subtitle">
                    Choose what you would like to do next.
                </p>
            </div>
        </div>

        <div class="row g-4">

            <div class="col-md-6">
                <div class="card dashboard-action">
                    <div class="card-body">

                        <div class="action-icon">+</div>

                        <h4>Report a Fault</h4>

                        <p>
                            Report a pothole, broken streetlight, water leak,
                            or another municipal problem in your area.
                        </p>

                        <a href="submit_report.php" class="btn btn-primary">
                            Submit a Report →
                        </a>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-action">
                    <div class="card-body">

                        <div class="action-icon">&#9776;</div>

                        <h4>My Reports</h4>

                        <p>
                            View your submitted reports, reference numbers,
                            status updates, and current progress.
                        </p>

                        <a href="my_reports.php" class="btn btn-primary">
                            View My Reports →
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <footer class="dashboard-footer">
        LocalFix is an academic project developed as part of the
        Bachelor of Science in Information Technology programme at
        Richfield Graduate Institute of Technology.
    </footer>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
