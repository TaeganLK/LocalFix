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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        body {
            background: #F3F6FA;
            font-family: 'Inter', sans-serif;
            color: #263445;
        }

        /* Navigation */
        .navbar {
            background: #101D32;
            padding: 15px 0;
            box-shadow: 0 4px 18px rgba(16, 29, 50, 0.12);
        }

        .navbar-brand {
            color: #ffffff !important;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            font-weight: 800;
        }

        .btn-dashboard {
            background: #1B2B43;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
        }

        .btn-dashboard:hover {
            background: #263C59;
            color: #ffffff;
        }

        .btn-logout {
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 600;
        }

        /* Page heading */
        .reports-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #101D32, #1B3652);
            color: #ffffff;
            border-radius: 18px;
            padding: 38px;
            box-shadow: 0 12px 30px rgba(16, 29, 50, 0.12);
        }

        .reports-header::before {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            border: 1px solid rgba(53, 191, 234, 0.2);
            border-radius: 50%;
            right: -65px;
            top: -130px;
        }

        .reports-header::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border: 1px solid rgba(0, 191, 166, 0.2);
            border-radius: 50%;
            right: 55px;
            bottom: -125px;
        }

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-label {
            color: #35BFEA;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .reports-header h2 {
            color: #ffffff;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .reports-header p {
            color: #C5D3E2;
            margin-bottom: 0;
            max-width: 550px;
            line-height: 1.7;
        }

        .btn-new-report {
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            border: none;
            border-radius: 9px;
            padding: 12px 20px;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-new-report:hover {
            color: #101D32;
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(53, 191, 234, 0.2);
        }

        /* Success message */
        .success-message {
            background: #E5F7F2;
            border: 1px solid #B9E9D9;
            border-left: 4px solid #00BFA6;
            color: #176B5E;
            border-radius: 10px;
            padding: 16px 20px;
        }

        /* Report section heading */
        .section-heading {
            color: #101D32;
            font-weight: 800;
            letter-spacing: -0.4px;
        }

        .report-count {
            background: #E4EDF5;
            color: #31516F;
            padding: 7px 12px;
            border-radius: 7px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Report cards */
        .report-card {
            background: #ffffff;
            border: 1px solid #E1E8F0;
            border-radius: 14px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .report-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(16, 29, 50, 0.08);
        }

        .report-card .card-body {
            padding: 28px;
        }

        .report-category {
            color: #101D32;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .report-reference {
            display: inline-block;
            background: #E8F6FB;
            color: #18799A;
            border: 1px solid #C8EAF5;
            padding: 6px 11px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            word-break: break-word;
        }

        /* Status badges */
        .report-status {
            display: inline-block;
            padding: 8px 13px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-submitted {
            background: #FFF3D8;
            color: #93610D;
        }

        .status-progress {
            background: #E1F4FB;
            color: #16799A;
        }

        .status-resolved {
            background: #DDF5EB;
            color: #19734F;
        }

        .status-default {
            background: #E9EDF2;
            color: #49576A;
        }

        /* Report details */
        .report-detail-label {
            display: block;
            color: #101D32;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .report-detail-value {
            color: #536174;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .report-description {
            white-space: normal;
        }

        .report-dates {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 30px;
            background: #F3F6FA;
            border: 1px solid #E8EDF3;
            border-radius: 10px;
            padding: 15px 18px;
            color: #64748B;
        }

        .report-dates p {
            margin-bottom: 0;
        }

        .report-dates strong {
            color: #33445B;
        }

        /* Empty state */
        .empty-reports {
            background: #ffffff;
            border: 1px solid #E1E8F0;
            border-radius: 16px;
            padding: 55px 25px;
            text-align: center;
        }

        .empty-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 20px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #E8F6FB;
            color: #1595BC;
            font-size: 1.9rem;
        }

        .empty-reports h4 {
            color: #101D32;
            font-weight: 800;
        }

        .empty-reports p {
            color: #718096;
            max-width: 480px;
            margin: 0 auto 24px;
            line-height: 1.7;
        }

        /* Footer */
        .site-footer {
            border-top: 1px solid #E1E8F0;
            margin-top: 60px;
            padding: 25px 0;
            color: #8491A3;
            font-size: 0.8rem;
            line-height: 1.6;
        }

        .site-footer strong {
            color: #42536A;
        }

        @media (max-width: 576px) {
            .reports-header {
                padding: 26px 22px;
            }

            .reports-header h2 {
                font-size: 1.65rem;
            }

            .report-card .card-body {
                padding: 21px;
            }

            .report-dates {
                flex-direction: column;
                gap: 7px;
            }

            .btn-new-report {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-mark">L</span>
            LocalFix
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="dashboard.php" class="btn btn-dashboard btn-sm">
                Dashboard
            </a>

            <form method="POST" action="logout.php" class="m-0">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <button type="submit" class="btn btn-outline-light btn-sm btn-logout">
                    Logout
                </button>
            </form>

        </div>
    </div>
</nav>

<main class="container py-5">

    <!-- Page header -->
    <section class="reports-header mb-4">

        <div class="header-content d-flex justify-content-between align-items-center flex-wrap gap-4">

            <div>
                <div class="header-label">Resident Portal</div>

                <h2>My Reports</h2>

                <p class="mt-2">
                    Keep track of the municipal faults you've reported.
                    View report details and monitor their progress.
                </p>
            </div>

            <a href="submit_report.php" class="btn btn-new-report">
                + Submit New Report
            </a>

        </div>

    </section>

    <!-- Submission success message -->
    <?php if (isset($_GET['submitted'])): ?>

        <div class="success-message mb-4">
            <strong>Report submitted successfully!</strong>
            Your fault report has been received and added to your reports.
        </div>

    <?php endif; ?>

    <!-- No reports -->
    <?php if (empty($reports)): ?>

        <div class="empty-reports">

            <div class="empty-icon">
                &#9776;
            </div>

            <h4 class="mb-2">No Reports Yet</h4>

            <p>
                You haven't submitted any municipal fault reports yet.
                Once you submit a report, you can track its progress here.
            </p>

            <a href="submit_report.php" class="btn btn-new-report">
                Submit Your First Report
            </a>

        </div>

    <?php else: ?>

        <!-- Report count -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

            <h5 class="section-heading mb-0">
                Your Submitted Reports
            </h5>

            <span class="report-count">
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
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

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

                                <p class="report-detail-value mb-0">
                                    <?= htmlspecialchars($report['location']) ?>
                                </p>

                            </div>

                            <!-- Description -->
                            <div class="mb-4">

                                <span class="report-detail-label">
                                    Description
                                </span>

                                <p class="report-detail-value report-description mb-0">
                                    <?= nl2br(htmlspecialchars($report['description'])) ?>
                                </p>

                            </div>

                            <!-- Dates -->
                            <div class="report-dates small">

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

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <strong>LocalFix</strong> | Municipal Fault Reporting System
            </div>

            <div class="col-md-6 text-md-end">
                Developed as an academic project for the Bachelor of Science
                in Information Technology programme at Richfield Graduate
                Institute of Technology.
            </div>
        </div>
    </div>
</footer>

</body>
</html>
