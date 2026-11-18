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

    <style>
        body {
            background: #F4F6F8;
            color: #263445;
        }

        .page-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #FFFFFF;
            padding: 32px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-header p {
            margin-bottom: 0;
            color: #E8F3F1;
        }

        .content-card {
            background: #FFFFFF;
            border: 1px solid #DCE3E8;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(22, 50, 79, 0.05);
            margin-bottom: 24px;
        }

        .content-card .card-body {
            padding: 28px;
        }

        .section-title {
            color: #16324F;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .detail-label {
            color: #6B7785;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #263445;
            font-weight: 500;
            margin-bottom: 20px;
            overflow-wrap: anywhere;
        }

        .description-box {
            background: #F4F6F8;
            border: 1px solid #DCE3E8;
            border-radius: 8px;
            padding: 16px;
            line-height: 1.6;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 13px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-submitted {
            background: #FFF3D9;
            color: #93600A;
        }

        .status-progress {
            background: #E8F3F1;
            color: #1B625F;
        }

        .status-resolved {
            background: #E2F3E8;
            color: #21683B;
        }

        .status-default {
            background: #E9ECEF;
            color: #495057;
        }

        .form-label {
            color: #263445;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border: 1px solid #DCE3E8;
            border-radius: 8px;
            padding: 11px 13px;
            color: #263445;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #237C78;
            box-shadow: 0 0 0 0.2rem rgba(35, 124, 120, 0.15);
        }

        .btn-primary {
            background: #237C78;
            border-color: #237C78;
            font-weight: 600;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background: #1B625F;
            border-color: #1B625F;
        }

        .btn-secondary {
            background: #16324F;
            border-color: #16324F;
            font-weight: 600;
            padding: 10px 20px;
        }

        .btn-secondary:hover {
            background: #10263D;
            border-color: #10263D;
        }

        .history-entry {
            border-left: 4px solid #237C78;
            background: #F8FAFB;
            border-radius: 0 8px 8px 0;
            padding: 18px;
            margin-bottom: 16px;
        }

        .history-entry:last-child {
            margin-bottom: 0;
        }

        .history-date {
            color: #6B7785;
            font-size: 0.85rem;
        }

        .history-notes {
            background: #FFFFFF;
            border: 1px solid #DCE3E8;
            border-radius: 7px;
            padding: 12px;
            margin-top: 12px;
            overflow-wrap: anywhere;
        }

        .empty-history {
            background: #F4F6F8;
            border: 1px dashed #DCE3E8;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            color: #6B7785;
        }

        @media (max-width: 576px) {
            .page-header {
                padding: 24px 20px;
            }

            .content-card .card-body {
                padding: 20px;
            }

            .action-buttons .btn {
                width: 100%;
                margin-bottom: 8px;
            }
        }
    </style>
</head>

<body>

<div class="container py-5">

    <div class="page-header">
        <h1>Manage Fault Report</h1>
        <p>Review report information, update its status and monitor administrative activity.</p>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">
            Report updated successfully.
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
                    <div class="detail-value">
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

    <div class="content-card">
        <div class="card-body">

            <h4 class="section-title">Update Report Status</h4>

            <p class="text-muted mb-4">
                Select the appropriate status and add notes if there is additional information
                about the report.
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

                    <small class="text-muted">
                        Optional. Maximum 5000 characters.
                    </small>
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

    <div class="content-card">
        <div class="card-body">

            <h4 class="section-title">Update History</h4>

            <p class="text-muted mb-4">
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

                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">

                            <span class="status-badge <?= getStatusClass($entry['status']) ?>">
                                <?= htmlspecialchars($entry['status']) ?>
                            </span>

                            <span class="history-date">
                                <?= htmlspecialchars($entry['created_at']) ?>
                            </span>

                        </div>

                        <p class="mb-1">
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

</div>

</body>
</html>
