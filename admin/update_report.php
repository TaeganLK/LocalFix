<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$reportId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$reportId || $reportId < 1) {
    exit('Invalid report ID.');
}

$stmt = $pdo->prepare(
    'SELECT reports.*, categories.category_name, users.full_name
     FROM reports
     JOIN categories ON reports.category_id = categories.category_id
     JOIN users ON reports.user_id = users.user_id
     WHERE reports.report_id = ?'
);

$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    exit('Report not found.');
}

$errors = [];
$allowedStatuses = ['Submitted', 'In Progress', 'Resolved'];
$notesInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken()) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $newStatus = $_POST['status'] ?? '';
    $notesInput = trim($_POST['notes'] ?? '');

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (strlen($notesInput) > 5000) {
        $errors[] = 'The update notes cannot exceed 5000 characters.';
    }

    if (empty($errors)) {

        try {
            $pdo->beginTransaction();

            $oldStatus = $report['status'];

            if ($newStatus !== $oldStatus) {
                $update = $pdo->prepare(
                    'UPDATE reports
                     SET status = ?
                     WHERE report_id = ?'
                );

                $update->execute([$newStatus, $reportId]);
            }

            if ($newStatus !== $oldStatus || $notesInput !== '') {
                $history = $pdo->prepare(
                    'INSERT INTO report_updates
                     (report_id, admin_id, status, notes)
                     VALUES (?, ?, ?, ?)'
                );

                $history->execute([
                    $reportId,
                    $_SESSION['user_id'],
                    $newStatus,
                    $notesInput !== '' ? $notesInput : null
                ]);
            }

            $pdo->commit();

            header('Location: update_report.php?id=' . $reportId . '&updated=1');
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($e->getMessage());
            $errors[] = 'The report could not be updated. Please try again.';
        }
    }

    if (in_array($newStatus, $allowedStatuses, true)) {
        $report['status'] = $newStatus;
    }
}

$historyStmt = $pdo->prepare(
    'SELECT report_updates.*, users.full_name
     FROM report_updates
     JOIN users ON report_updates.admin_id = users.user_id
     WHERE report_updates.report_id = ?
     ORDER BY report_updates.created_at DESC'
);

$historyStmt->execute([$reportId]);
$history = $historyStmt->fetchAll();

$csrfToken = generateCsrfToken();

function getStatusClass(string $status): string
{
    switch ($status) {
        case 'Submitted':
            return 'status-submitted';

        case 'In Progress':
            return 'status-progress';

        case 'Resolved':
            return 'status-resolved';

        default:
            return 'status-default';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Report | LocalFix</title>

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
        :root {
            --lf-navy: #101D32;
            --lf-navy-light: #1B2B43;
            --lf-cyan: #35BFEA;
            --lf-teal: #00BFA6;
            --lf-background: #F3F6FA;
            --lf-white: #FFFFFF;
            --lf-text: #263445;
            --lf-muted: #718096;
            --lf-border: #E1E8F0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            background: var(--lf-background);
            color: var(--lf-text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        .navbar {
            background: var(--lf-navy);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(16, 29, 50, 0.12);
        }

        .navbar-brand {
            color: #FFFFFF !important;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--lf-cyan), var(--lf-teal));
            color: var(--lf-navy);
            border-radius: 10px;
            font-weight: 800;
        }

        .navbar-text {
            color: #B8C7D9 !important;
            font-size: 0.88rem;
        }

        .main-container {
            max-width: 1140px;
            padding-top: 38px;
            padding-bottom: 50px;
        }

        .page-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, var(--lf-navy), var(--lf-navy-light));
            color: #FFFFFF;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 28px;
            box-shadow: 0 12px 30px rgba(16, 29, 50, 0.12);
        }

        .page-header::after {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            border: 1px solid rgba(53, 191, 234, 0.18);
            border-radius: 50%;
            right: -75px;
            top: -130px;
            box-shadow:
                0 0 0 30px rgba(53, 191, 234, 0.04),
                0 0 0 60px rgba(53, 191, 234, 0.03);
            pointer-events: none;
        }

        .header-label {
            color: var(--lf-cyan);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .page-header h1 {
            font-size: clamp(1.8rem, 4vw, 2.35rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #C5D2E2;
            margin-bottom: 0;
            max-width: 650px;
            line-height: 1.7;
        }

        .content-card {
            background: var(--lf-white);
            border: 1px solid var(--lf-border);
            border-radius: 15px;
            box-shadow: 0 5px 18px rgba(16, 29, 50, 0.045);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .card-body {
            padding: 28px;
        }

        .section-title {
            color: var(--lf-navy);
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin-bottom: 8px;
        }

        .section-description {
            color: var(--lf-muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 24px;
        }

        .detail-label {
            color: var(--lf-muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .detail-value {
            color: var(--lf-text);
            font-size: 0.96rem;
            font-weight: 600;
            margin-bottom: 23px;
            overflow-wrap: anywhere;
        }

        .reference-value {
            color: #168DB8;
            font-weight: 800;
        }

        .description-box {
            background: #F5F8FC;
            border: 1px solid var(--lf-border);
            border-radius: 10px;
            padding: 18px;
            line-height: 1.75;
            white-space: normal;
            overflow-wrap: anywhere;
            color: #405168;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 7px 13px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-submitted {
            background: #FFF3D9;
            color: #93600A;
        }

        .status-progress {
            background: #DDF5FC;
            color: #087B9F;
        }

        .status-resolved {
            background: #DDF7EF;
            color: #087B61;
        }

        .status-default {
            background: #E9EEF4;
            color: #49576A;
        }

        .form-label {
            color: var(--lf-navy);
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .form-control,
        .form-select {
            border: 1px solid #D8E1EC;
            border-radius: 9px;
            padding: 12px 14px;
            color: var(--lf-text);
            background-color: #FFFFFF;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--lf-cyan);
            box-shadow: 0 0 0 3px rgba(53, 191, 234, 0.14);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 115px;
        }

        .form-hint {
            color: var(--lf-muted);
            font-size: 0.78rem;
            margin-top: 7px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #159BC8, #00AFA0);
            border: none;
            border-radius: 9px;
            color: #FFFFFF;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 11px 21px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1189B3, #009B8D);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(0, 174, 160, 0.2);
        }

        .btn-secondary {
            background: var(--lf-navy);
            border: 1px solid var(--lf-navy);
            border-radius: 9px;
            color: #FFFFFF;
            font-size: 0.88rem;
            font-weight: 700;
            padding: 11px 21px;
        }

        .btn-secondary:hover {
            background: var(--lf-navy-light);
            border-color: var(--lf-navy-light);
            color: #FFFFFF;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 16px 19px;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #DDF7EF;
            color: #087B61;
        }

        .alert-danger {
            background: #FDE8E8;
            color: #A52A2A;
        }

        .history-entry {
            position: relative;
            border-left: 3px solid var(--lf-cyan);
            background: #F6F9FC;
            border-radius: 0 10px 10px 0;
            padding: 19px 21px;
            margin-bottom: 16px;
        }

        .history-entry:last-child {
            margin-bottom: 0;
        }

        .history-date {
            color: var(--lf-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .history-person {
            color: var(--lf-text);
            font-size: 0.88rem;
            margin-bottom: 0;
        }

        .history-notes {
            background: #FFFFFF;
            border: 1px solid var(--lf-border);
            border-radius: 8px;
            padding: 13px 15px;
            margin-top: 13px;
            overflow-wrap: anywhere;
            color: #405168;
            font-size: 0.88rem;
            line-height: 1.65;
        }

        .history-notes strong {
            color: var(--lf-navy);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .empty-history {
            background: #F6F9FC;
            border: 1px dashed #CBD7E5;
            border-radius: 10px;
            padding: 35px 20px;
            text-align: center;
            color: var(--lf-muted);
        }

        .empty-history h6 {
            color: var(--lf-navy);
            font-weight: 700;
        }

        .footer {
            border-top: 1px solid var(--lf-border);
            padding: 23px 0;
            color: var(--lf-muted);
            font-size: 0.78rem;
            line-height: 1.6;
        }

        .footer strong {
            color: var(--lf-navy);
        }

        @media (max-width: 576px) {
            .main-container {
                padding-top: 22px;
            }

            .page-header {
                padding: 26px 22px;
                border-radius: 14px;
            }

            .card-body {
                padding: 21px;
            }

            .action-buttons .btn {
                width: 100%;
                margin-bottom: 9px;
            }

            .history-entry {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-mark">L</span>
            LocalFix
        </a>

        <span class="navbar-text d-none d-sm-inline">
            Administration Portal
        </span>
    </div>
</nav>

<div class="container main-container">

    <div class="page-header">
        <div class="header-label">Administration / Report Management</div>

        <h1>Manage Fault Report</h1>

        <p>
            Review submitted fault information, update its status and
            monitor the history of administrative activity.
        </p>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            <strong>Update successful.</strong>
            The report has been updated successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Please review the following:</strong>

            <ul class="mb-0 mt-2">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Report Information -->
    <div class="content-card">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h4 class="section-title mb-0">Report Information</h4>

                <span class="status-badge <?= getStatusClass($report['status']) ?>">
                    <?= htmlspecialchars($report['status']) ?>
                </span>
            </div>

            <div class="row">

                <div class="col-md-6">
                    <div class="detail-label">Reference Number</div>

                    <div class="detail-value reference-value">
                        <?= htmlspecialchars($report['reference_number']) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="detail-label">Submitted By</div>

                    <div class="detail-value">
                        <?= htmlspecialchars($report['full_name']) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="detail-label">Fault Category</div>

                    <div class="detail-value">
                        <?= htmlspecialchars($report['category_name']) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="detail-label">Reported Location</div>

                    <div class="detail-value">
                        <?= htmlspecialchars($report['location']) ?>
                    </div>
                </div>

                <div class="col-12">
                    <div class="detail-label">Report Description</div>

                    <div class="description-box">
                        <?= nl2br(htmlspecialchars($report['description'])) ?>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Update Report Status -->
    <div class="content-card">
        <div class="card-body">

            <h4 class="section-title">Update Report Status</h4>

            <p class="section-description">
                Select the appropriate status and add notes if there is
                additional information about the report.
            </p>

            <form method="POST" action="update_report.php?id=<?= (int) $reportId ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrfToken) ?>"
                >

                <div class="mb-3">
                    <label for="status" class="form-label">New Status</label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                        required
                    >
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option
                                value="<?= htmlspecialchars($status) ?>"
                                <?= $report['status'] === $status ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label">
                        Administrative Update Notes
                    </label>

                    <textarea
                        name="notes"
                        id="notes"
                        class="form-control"
                        rows="4"
                        maxlength="5000"
                        placeholder="Enter additional information about this update..."
                    ><?= htmlspecialchars($notesInput) ?></textarea>

                    <div class="form-hint">
                        Optional. Maximum 5000 characters.
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        Save Update
                    </button>

                    <a href="dashboard.php" class="btn btn-secondary">
                        Back to Dashboard
                    </a>
                </div>

            </form>

        </div>
    </div>

    <!-- Update History -->
    <div class="content-card">
        <div class="card-body">

            <h4 class="section-title">Update History</h4>

            <p class="section-description">
                A record of previous administrative status changes and notes.
            </p>

            <?php if (empty($history)): ?>

                <div class="empty-history">
                    <h6 class="mb-2">No Updates Recorded</h6>

                    <p class="mb-0">
                        Administrative updates for this report will appear here.
                    </p>
                </div>

            <?php else: ?>

                <?php foreach ($history as $entry): ?>

                    <div class="history-entry">

                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">

                            <span class="status-badge <?= getStatusClass($entry['status']) ?>">
                                <?= htmlspecialchars($entry['status']) ?>
                            </span>

                            <span class="history-date">
                                <?= htmlspecialchars($entry['created_at']) ?>
                            </span>

                        </div>

                        <p class="history-person">
                            <strong>Updated by:</strong>
                            <?= htmlspecialchars($entry['full_name']) ?>
                        </p>

                        <?php if (!empty($entry['notes'])): ?>

                            <div class="history-notes">
                                <strong>Administrative Notes</strong>

                                <p class="mb-0 mt-2">
                                    <?= nl2br(htmlspecialchars($entry['notes'])) ?>
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>

    <footer class="footer">
        <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <strong>LocalFix</strong> — Municipal Fault Reporting System
            </div>

            <div class="col-md-6 text-md-end">
                LocalFix is an academic project developed as part of the
                Bachelor of Science in Information Technology programme at
                Richfield Graduate Institute of Technology.
            </div>
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
