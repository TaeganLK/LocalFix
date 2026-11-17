<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$csrfToken = generateCsrfToken();

$totalReports = $pdo->query(
    'SELECT COUNT(*) FROM reports'
)->fetchColumn();

$submittedReports = $pdo->query(
    "SELECT COUNT(*) FROM reports WHERE status = 'Submitted'"
)->fetchColumn();

$inProgressReports = $pdo->query(
    "SELECT COUNT(*) FROM reports WHERE status = 'In Progress'"
)->fetchColumn();

$resolvedReports = $pdo->query(
    "SELECT COUNT(*) FROM reports WHERE status = 'Resolved'"
)->fetchColumn();

$stmt = $pdo->query(
    'SELECT
        reports.report_id,
        reports.reference_number,
        users.full_name,
        categories.category_name,
        reports.location,
        reports.status,
        reports.created_at
     FROM reports
     INNER JOIN users ON reports.user_id = users.user_id
     INNER JOIN categories ON reports.category_id = categories.category_id
     ORDER BY reports.created_at DESC'
);

$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            LocalFix Admin
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="text-white">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="../logout.php" class="m-0">
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

    <div class="mb-4">
        <h2>Administrator Dashboard</h2>
        <p class="text-muted">
            Manage and monitor municipal fault reports.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Reports</p>
                    <h3><?= $totalReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Submitted</p>
                    <h3><?= $submittedReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">In Progress</p>
                    <h3><?= $inProgressReports ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Resolved</p>
                    <h3><?= $resolvedReports ?></h3>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h4 class="mb-3">All Municipal Fault Reports</h4>

            <?php if (empty($reports)): ?>

                <p class="text-muted">No reports have been submitted yet.</p>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Resident</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

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

                                <tr>
                                    <td>
                                        <?= htmlspecialchars($report['reference_number']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($report['full_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($report['category_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($report['location']) ?>
                                    </td>

                                    <td>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($report['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= date('d M Y', strtotime($report['created_at'])) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="update_report.php?id=<?= (int)$report['report_id'] ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Manage
                                        </a>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>
                </div>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>
