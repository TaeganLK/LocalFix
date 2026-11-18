<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$csrfToken = generateCsrfToken();

// Dashboard statistics
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

// All reports
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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .admin-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #ffffff;
            border-radius: 16px;
            padding: 32px;
        }

        .admin-header h2 {
            color: #ffffff;
            font-weight: 700;
        }

        .admin-header p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        .admin-stat {
            background: #ffffff;
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(22, 50, 79, 0.06);
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 18px rgba(22, 50, 79, 0.10);
        }

        .admin-stat .card-body {
            padding: 23px;
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

        .admin-table-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(22, 50, 79, 0.06);
            background: #ffffff;
        }

        .admin-table-card .card-body {
            padding: 25px;
        }

        .admin-section-title {
            color: #16324F;
            font-weight: 700;
        }

        .admin-table {
            margin-bottom: 0;
        }

        .admin-table thead th {
            background: #16324F;
            color: #ffffff;
            font-weight: 600;
            white-space: nowrap;
            padding: 14px;
            border: none;
        }

        .admin-table tbody td {
            padding: 14px;
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: #E8F3F1;
        }

        .reference-text {
            color: #1B625F;
            font-weight: 600;
            white-space: nowrap;
        }

        .admin-status {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-submitted {
            background: #FFF3D8;
            color: #8A5A0A;
        }

        .status-progress {
            background: #DDEFEE;
            color: #1B625F;
        }

        .status-resolved {
            background: #E1F1E5;
            color: #28623B;
        }

        .status-default {
            background: #E9ECEF;
            color: #495057;
        }

        .admin-manage-btn {
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .admin-empty {
            background: #F4F6F8;
            border-radius: 12px;
            padding: 35px 20px;
        }

        @media (max-width: 576px) {
            .admin-header {
                padding: 24px;
            }

            .admin-stat .card-body {
                padding: 18px;
            }

            .stat-number {
                font-size: 1.7rem;
            }

            .admin-table-card .card-body {
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            LocalFix Admin
        </a>

        <div class="d-flex align-items-center gap-2">

            <span class="text-white d-none d-sm-inline">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="../logout.php" class="m-0">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <button type="submit" class="btn btn-outline-light btn-sm">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<main class="container py-5">

    <!-- Header -->
    <section class="admin-header mb-4">

        <h2>Administrator Dashboard</h2>

        <p class="mt-2">
            Monitor municipal faults, review submissions, and manage report progress.
        </p>

    </section>

    <!-- Statistics -->
    <section class="mb-5">

        <h4 class="admin-section-title mb-3">
            System Overview
        </h4>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="card admin-stat stat-total">
                    <div class="card-body">
                        <p class="stat-label">Total Reports</p>
                        <h3 class="stat-number"><?= $totalReports ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card admin-stat stat-submitted">
                    <div class="card-body">
                        <p class="stat-label">Submitted</p>
                        <h3 class="stat-number"><?= $submittedReports ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card admin-stat stat-progress">
                    <div class="card-body">
                        <p class="stat-label">In Progress</p>
                        <h3 class="stat-number"><?= $inProgressReports ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card admin-stat stat-resolved">
                    <div class="card-body">
                        <p class="stat-label">Resolved</p>
                        <h3 class="stat-number"><?= $resolvedReports ?></h3>
                    </div>
                </div>
            </div>

        </div>

    </section>

    <!-- Reports management -->
    <section class="card admin-table-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

                <div>
                    <h4 class="admin-section-title mb-1">
                        All Municipal Fault Reports
                    </h4>

                    <p class="text-muted small mb-0">
                        Review and manage reports submitted by residents.
                    </p>
                </div>

                <span class="text-muted small">
                    <?= count($reports) ?> report(s)
                </span>

            </div>

            <?php if (empty($reports)): ?>

                <div class="admin-empty text-center">

                    <h5 class="admin-section-title">
                        No Reports Available
                    </h5>

                    <p class="text-muted mb-0">
                        Reports submitted by residents will appear here.
                    </p>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle admin-table">

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
                                $statusClass = 'status-default';

                                if ($report['status'] === 'Submitted') {
                                    $statusClass = 'status-submitted';
                                } elseif ($report['status'] === 'In Progress') {
                                    $statusClass = 'status-progress';
                                } elseif ($report['status'] === 'Resolved') {
                                    $statusClass = 'status-resolved';
                                }
                                ?>

                                <tr>

                                    <td>
                                        <span class="reference-text">
                                            <?= htmlspecialchars($report['reference_number']) ?>
                                        </span>
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
                                        <span class="admin-status <?= $statusClass ?>">
                                            <?= htmlspecialchars($report['status']) ?>
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= date('d M Y', strtotime($report['created_at'])) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="update_report.php?id=<?= (int)$report['report_id'] ?>"
                                            class="btn btn-primary btn-sm admin-manage-btn"
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

    </section>

</main>

</body>
</html>
