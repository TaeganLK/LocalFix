
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

    <style>
        .dashboard-welcome {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #ffffff;
            border-radius: 16px;
            padding: 35px;
        }

        .dashboard-welcome h2 {
            color: #ffffff;
            font-weight: 700;
        }

        .dashboard-welcome p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        .dashboard-stat {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(22, 50, 79, 0.06);
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 18px rgba(22, 50, 79, 0.10);
        }

        .dashboard-stat .card-body {
            padding: 24px;
        }

        .stat-label {
            color: #6B7785;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .stat-number {
            color: #16324F;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-total {
            border-top: 4px solid #16324F;
        }

        .stat-submitted {
            border-top: 4px solid #D99A36;
        }

        .stat-progress {
            border-top: 4px solid #237C78;
        }

        .stat-resolved {
            border-top: 4px solid #4B8F67;
        }

        .dashboard-action {
            border: none;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(22, 50, 79, 0.06);
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 18px rgba(22, 50, 79, 0.10);
        }

        .dashboard-action .card-body {
            padding: 28px;
        }

        .action-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #E8F3F1;
            color: #237C78;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 18px;
        }

        .dashboard-action h4 {
            color: #16324F;
            font-weight: 700;
        }

        .dashboard-action p {
            color: #6B7785;
            min-height: 48px;
        }

        .dashboard-section-title {
            color: #16324F;
            font-weight: 700;
        }

        @media (max-width: 576px) {
            .dashboard-welcome {
                padding: 25px;
            }

            .dashboard-stat .card-body {
                padding: 18px;
            }

            .stat-number {
                font-size: 1.7rem;
            }

            .dashboard-action .card-body {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            LocalFix
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="text-white">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="logout.php" class="m-0">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <button type="submit" class="btn btn-light btn-sm">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<main class="container py-5">

    <!-- Welcome section -->
    <section class="dashboard-welcome mb-4">
        <h2>
            Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!
        </h2>

        <p class="mt-2">
            Manage your municipal fault reports and keep track of their progress.
        </p>
    </section>

    <!-- Statistics -->
    <section class="mb-5">

        <h4 class="dashboard-section-title mb-3">
            Report Overview
        </h4>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-total">
                    <div class="card-body">
                        <p class="stat-label">Total Reports</p>
                        <h3 class="stat-number">
                            <?= $totalReports ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-submitted">
                    <div class="card-body">
                        <p class="stat-label">Submitted</p>
                        <h3 class="stat-number">
                            <?= $submittedReports ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-progress">
                    <div class="card-body">
                        <p class="stat-label">In Progress</p>
                        <h3 class="stat-number">
                            <?= $inProgressReports ?>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card dashboard-stat stat-resolved">
                    <div class="card-body">
                        <p class="stat-label">Resolved</p>
                        <h3 class="stat-number">
                            <?= $resolvedReports ?>
                        </h3>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Dashboard actions -->
    <section>

        <h4 class="dashboard-section-title mb-3">
            Quick Actions
        </h4>

        <div class="row g-3">

            <div class="col-md-6">
                <div class="card dashboard-action">
                    <div class="card-body">

                        <div class="action-icon">
                            +
                        </div>

                        <h4>Report a Fault</h4>

                        <p>
                            Report a pothole, broken streetlight, water leak,
                            or other municipal problem in your area.
                        </p>

                        <a href="submit_report.php" class="btn btn-primary">
                            Submit a Report
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card dashboard-action">
                <div class="card-body">

                    <div class="action-icon">
                        &#9776;
                    </div>

                    <h4>My Reports</h4>

                    <p>
                        View your submitted reports, reference numbers,
                        and current progress.
                    </p>

                    <a href="my_reports.php" class="btn btn-primary">
                        View My Reports
                    </a>

                </div>
            </div>
        </div>
    </div>

    </section>

</main>

</body>
</html>
