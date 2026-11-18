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
            color: #ffffff !important;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
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
            font-size: 0.88rem;
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
        .admin-container {
            max-width: 1350px;
        }

        /* Header */
        .admin-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, #101D32 0%, #1B2B43 70%, #163B52 100%);
            color: #ffffff;
            border-radius: 18px;
            padding: 40px;
            box-shadow: 0 12px 30px rgba(16,29,50,0.12);
        }

        .admin-header::before {
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

        .header-content {
            position: relative;
            z-index: 1;
        }

        .header-label {
            color: #35BFEA;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .admin-header h2 {
            color: #ffffff;
            font-size: clamp(1.7rem, 4vw, 2.3rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }

        .admin-header p {
            color: #C3D0DF;
            font-size: 0.95rem;
            max-width: 650px;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .header-accent {
            color: #35BFEA;
        }

        /* Section headings */
        .admin-section {
            margin-bottom: 38px;
        }

        .section-heading {
            margin-bottom: 18px;
        }

        .admin-section-title {
            color: #101D32;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.4px;
        }

        .section-subtitle {
            color: #7A8798;
            font-size: 0.85rem;
            margin-top: 5px;
            margin-bottom: 0;
        }

        /* Statistics */
        .admin-stat {
            height: 100%;
            border: 1px solid #E4EAF1;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(16,29,50,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .admin-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 24px rgba(16,29,50,0.09);
        }

        .admin-stat .card-body {
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

        /* Reports table card */
        .admin-table-card {
            border: 1px solid #E4EAF1;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(16,29,50,0.04);
            overflow: hidden;
        }

        .admin-table-card .card-body {
            padding: 28px;
        }

        .report-count {
            display: inline-flex;
            align-items: center;
            background: #E9F4FB;
            color: #278BB8;
            border-radius: 30px;
            padding: 7px 13px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* Table */
        .admin-table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 850px;
        }

        .admin-table thead th {
            background: #101D32;
            color: #EAF2FA;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 16px 14px;
            border: none;
        }

        .admin-table thead th:first-child {
            border-top-left-radius: 9px;
        }

        .admin-table thead th:last-child {
            border-top-right-radius: 9px;
        }

        .admin-table tbody td {
            padding: 16px 14px;
            vertical-align: middle;
            color: #526174;
            font-size: 0.86rem;
            border-bottom: 1px solid #EDF1F5;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .admin-table tbody tr:hover td {
            background: #F3F9FC;
        }

        .reference-text {
            color: #078FAD;
            font-weight: 700;
            white-space: nowrap;
        }

        .resident-name {
            color: #263445;
            font-weight: 600;
        }

        .admin-status {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-submitted {
            background: #FFF4DF;
            color: #986313;
        }

        .status-progress {
            background: #E6F8F6;
            color: #008B7A;
        }

        .status-resolved {
            background: #E8F5EC;
            color: #347C4F;
        }

        .status-default {
            background: #EDF0F4;
            color: #596579;
        }

        /* Manage button */
        .btn-manage {
            display: inline-block;
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.8rem;
            font-weight: 700;
            white-space: nowrap;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-manage:hover {
            background: linear-gradient(135deg, #23ACD8, #00A991);
            color: #101D32;
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(0,191,166,0.2);
        }

        /* Empty state */
        .admin-empty {
            background: #F5F8FB;
            border: 1px dashed #D5DEE8;
            border-radius: 12px;
            padding: 45px 20px;
            text-align: center;
        }

        .empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 55px;
            height: 55px;
            margin: 0 auto 17px;
            border-radius: 15px;
            background: #E9F4FB;
            color: #278BB8;
            font-size: 1.5rem;
        }

        .admin-empty h5 {
            color: #101D32;
            font-weight: 800;
        }

        .admin-empty p {
            color: #7A8798;
            font-size: 0.9rem;
        }

        /* Footer */
        .admin-footer {
            border-top: 1px solid #E1E7EF;
            padding: 22px 0;
            margin-top: 35px;
            color: #8793A3;
            font-size: 0.8rem;
            text-align: center;
        }

        @media (max-width: 576px) {
            .admin-header {
                padding: 28px 23px;
            }

            .admin-stat .card-body {
                padding: 17px;
            }

            .stat-number {
                font-size: 1.8rem;
            }

            .stat-label {
                font-size: 0.76rem;
            }

            .admin-table-card .card-body {
                padding: 19px;
            }

            .nav-user {
                display: none;
            }
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-mark">L</span>LocalFix
            <span class="d-none d-sm-inline">Admin</span>
        </a>

        <div class="d-flex align-items-center gap-3">

            <span class="nav-user d-none d-sm-inline">
                <?= htmlspecialchars($_SESSION['full_name']) ?>
            </span>

            <form method="POST" action="../logout.php" class="m-0">
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

<main class="container admin-container py-5">

    <!-- Header -->
    <section class="admin-header mb-5">

        <div class="header-content">

            <div class="header-label">System Administration</div>

            <h2>
                Administrator <span class="header-accent">Dashboard</span>
            </h2>

            <p>
                Monitor municipal faults, review resident submissions,
                and manage report progress from one central dashboard.
            </p>

        </div>

    </section>

    <!-- Statistics -->
    <section class="admin-section">

        <div class="section-heading">
            <h4 class="admin-section-title mb-1">System Overview</h4>
            <p class="section-subtitle">
                A summary of all municipal fault reports in the system.
            </p>
        </div>

        <div class="row g-3">

            <div class="col-6 col-lg-3">
                <div class="card admin-stat stat-total">
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
                <div class="card admin-stat stat-submitted">
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
                <div class="card admin-stat stat-progress">
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
                <div class="card admin-stat stat-resolved">
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

    <!-- Reports management -->
    <section class="card admin-table-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

                <div>
                    <h4 class="admin-section-title mb-1">
                        All Municipal Fault Reports
                    </h4>

                    <p class="section-subtitle">
                        Review and manage reports submitted by residents.
                    </p>
                </div>

                <span class="report-count">
                    <?= count($reports) ?> report(s)
                </span>

            </div>

            <?php if (empty($reports)): ?>

                <div class="admin-empty">

                    <div class="empty-icon">☷</div>

                    <h5>No Reports Available</h5>

                    <p class="mb-0">
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
                                        <span class="resident-name">
                                            <?= htmlspecialchars($report['full_name']) ?>
                                        </span>
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
                                            class="btn-manage"
                                        >
                                            Manage →
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

    <footer class="admin-footer">
        LocalFix is an academic project developed as part of the
        Bachelor of Science in Information Technology programme at
        Richfield Graduate Institute of Technology.
    </footer>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
