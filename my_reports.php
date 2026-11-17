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

$stmt = $pdo->prepare(
    'SELECT
        reports.report_id,
        reports.reference_number,
        categories.category_name,
        reports.location,
        reports.description,
        reports.status,
        reports.created_at,
        reports.updated_at
     FROM reports
     INNER JOIN categories
        ON reports.category_id = categories.category_id
     WHERE reports.user_id = ?
     ORDER BY reports.created_at DESC'
);

$stmt->execute([$_SESSION['user_id']]);
$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            LocalFix
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="dashboard.php" class="btn btn-light btn-sm">
                Dashboard
            </a>

            <form method="POST" action="logout.php" class="m-0">
                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrfToken) ?>">

                <button type="submit" class="btn btn-outline-light btn-sm">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My Reports</h2>
            <p class="text-muted mb-0">
                View and track your municipal fault reports.
            </p>
        </div>

        <a href="submit_report.php" class="btn btn-primary">
            Submit New Report
        </a>
    </div>

    <?php if (isset($_GET['submitted'])): ?>
        <div class="alert alert-success">
            Your report has been submitted successfully!
        </div>
    <?php endif; ?>

    <?php if (empty($reports)): ?>

        <div class="card shadow-sm border-0">
            <div class="card-body text-center p-5">
                <h4>No Reports Yet</h4>

                <p class="text-muted">
                    You haven't submitted any municipal fault reports.
                </p>

                <a href="submit_report.php" class="btn btn-primary">
                    Submit Your First Report
                </a>
            </div>
        </div>

    <?php else: ?>

        <div class="row g-3">

            <?php foreach ($reports as $report): ?>

                <?php
                $statusClass = 'bg-secondary';

                if ($report['status'] === 'Submitted') {
                    $statusClass = 'bg-primary';
                } elseif ($report['status'] === 'In Progress') {
                    $statusClass = 'bg-warning text-dark';
                } elseif ($report['status'] === 'Resolved') {
                    $statusClass = 'bg-success';
                }
                ?>

                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">

                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">

                                <div>
                                    <h5 class="mb-1">
                                        <?= htmlspecialchars($report['category_name']) ?>
                                    </h5>

                                    <small class="text-muted">
                                        Reference:
                                        <?= htmlspecialchars($report['reference_number']) ?>
                                    </small>
                                </div>

                                <span class="badge <?= $statusClass ?>">
                                    <?= htmlspecialchars($report['status']) ?>
                                </span>

                            </div>

                            <p class="mb-2">
                                <strong>Location:</strong>
                                <?= htmlspecialchars($report['location']) ?>
                            </p>

                            <p class="mb-3">
                                <strong>Description:</strong><br>
                                <?= nl2br(htmlspecialchars($report['description'])) ?>
                            </p>

                            <div class="border-top pt-3 text-muted small">

                                <p class="mb-1">
                                    <strong>Date Submitted:</strong>
                                    <?= date('d M Y, H:i', strtotime($report['created_at'])) ?>
                                </p>

                                <p class="mb-0">
                                    <strong>Last Updated:</strong>
                                    <?= date('d M Y, H:i', strtotime($report['updated_at'])) ?>
                                </p>

                            </div>

                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

</body>
</html>
