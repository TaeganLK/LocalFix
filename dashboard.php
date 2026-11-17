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

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports WHERE user_id = ?'
);
$stmt->execute([$userId]);
$totalReports = $stmt->fetch()['total'];

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports
     WHERE user_id = ? AND status = ?'
);
$stmt->execute([$userId, 'Submitted']);
$submittedReports = $stmt->fetch()['total'];

$stmt = $pdo->prepare(
    'SELECT COUNT(*) AS total FROM reports
     WHERE user_id = ? AND status = ?'
);
$stmt->execute([$userId, 'In Progress']);
$inProgressReports = $stmt->fetch()['total'];

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
    <title>Dashboard | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            LocalFix
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="text-white">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="logout.php" class="m-0">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrfToken) ?>">

                <button type="submit" class="btn btn-light btn-sm">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<div class="container py-5">

    <div class="mb-4">
        <h2>
            Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!
        </h2>

        <p class="text-muted">
            Manage your municipal fault reports from your dashboard.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Reports</p>
                    <h3><?= $totalReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Submitted</p>
                    <h3><?= $submittedReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">In Progress</p>
                    <h3><?= $inProgressReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolved</p>
                    <h3><?= $resolvedReports ?></h3>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">

                    <h4>Report a Fault</h4>

                    <p class="text-muted">
                        Submit a new municipal fault or service request.
                    </p>

                    <a href="submit_report.php" class="btn btn-primary">
                        Submit a Report
                    </a>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">

                    <h4>My Reports</h4>

                    <p class="text-muted">
                        View your submitted reports and track their progress.
                    </p>

                    <a href="my_reports.php" class="btn btn-primary">
                        View My Reports
                    </a>

                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
