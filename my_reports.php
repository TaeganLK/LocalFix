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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .reports-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #ffffff;
            border-radius: 16px;
            padding: 30px;
        }

        .reports-header h2 {
            color: #ffffff;
            font-weight: 700;
        }

        .reports-header p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        .report-card {
            background: #ffffff;
            border: 1px solid #DCE3E8;
            border-radius: 14px;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(22, 50, 79, 0.08);
        }

        .report-card .card-body {
            padding: 26px;
        }

        .report-category {
            color: #16324F;
            font-weight: 700;
        }

        .report-reference {
            display: inline-block;
            background: #E8F3F1;
            color: #1B625F;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            word-break: break-word;
        }

        .report-status {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
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

        .report-detail-label {
            color: #16324F;
            font-weight: 600;
        }

        .report-description {
            line-height: 1.7;
            color: #39495A;
            overflow-wrap: anywhere;
        }

        .report-dates {
            background: #F4F6F8;
            border-radius: 10px;
            padding: 14px 16px;
        }

        .report-dates p {
            margin-bottom: 5px;
        }

        .report-dates p:last-child {
            margin-bottom: 0;
        }

        .empty-reports {
            background: #ffffff;
            border: 1px solid #DCE3E8;
            border-radius: 16px;
            padding: 50px 25px;
        }

        .empty-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #E8F3F1;
            color: #237C78;
            font-size: 1.8rem;
        }

        @media (max-width: 576px) {
            .reports-header {
                padding: 24px;
            }

            .report-card .card-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            LocalFix
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="dashboard.php" class="btn btn-light btn-sm">
                Dashboard
            </a>

            <form method="POST" action="logout.php" class="m-0">
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

    <!-- Page header -->
    <section class="reports-header mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h2>My Reports</h2>

                <p class="mt-2">
                    View your municipal fault reports and track their progress.
                </p>
            </div>

            <a href="submit_report.php" class="btn btn-light">
                + Submit New Report
            </a>

        </div>

    </section>

    <!-- Submission success message -->
    <?php if (isset($_GET['submitted'])): ?>

        <div class="alert alert-success border-0 shadow-sm mb-4">
            <strong>Report submitted successfully!</strong>
            Your fault report has been received and added to your reports.
        </div>

    <?php endif; ?>

    <!-- No reports -->
    <?php if (empty($reports)): ?>

        <div class="empty-reports text-center">

            <div class="empty-icon">
                &#9776;
            </div>

            <h4 class="mb-2">No Reports Yet</h4>

            <p class="text-muted mb-4">
                You haven't submitted any municipal fault reports yet.
                Once you submit a report, you can track its progress here.
            </p>

            <a href="submit_report.php" class="btn btn-primary">
                Submit Your First Report
            </a>

        </div>

    <?php else: ?>

        <!-- Report count -->
        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0" style="color: #16324F;">
                Your Submitted Reports
            </h5>

            <span class="text-muted small">
                <?= count($reports) ?> report(s)
            </span>

        </div>

        <!-- Report cards -->
        <div class="row g-3">

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

                <div class="col-12">

                    <div class="card report-card">

                        <div class="card-body">

                            <!-- Card heading -->
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">

                                <div>
                                    <h5 class="report-category mb-2">
                                        <?= htmlspecialchars($report['category_name']) ?>
                                    </h5>

                                    <span class="report-reference">
                                        <?= htmlspecialchars($report['reference_number']) ?>
                                    </span>
                                </div>

                                <span class="report-status <?= $statusClass ?>">
                                    <?= htmlspecialchars($report['status']) ?>
                                </span>

                            </div>

                            <!-- Location -->
                            <div class="mb-3">

                                <span class="report-detail-label">
                                    Fault Location
                                </span>

                                <p class="mb-0 mt-1">
                                    <?= htmlspecialchars($report['location']) ?>
                                </p>

                            </div>

                            <!-- Description -->
                            <div class="mb-4">

                                <span class="report-detail-label">
                                    Description
                                </span>

                                <p class="report-description mb-0 mt-1">
                                    <?= nl2br(htmlspecialchars($report['description'])) ?>
                                </p>

                            </div>

                            <!-- Dates -->
                            <div class="report-dates small text-muted">

                                <p>
                                    <strong>Date Submitted:</strong>
                                    <?= date('d M Y, H:i', strtotime($report['created_at'])) ?>
                                </p>

                                <p>
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

</main>

</body>
</html>
