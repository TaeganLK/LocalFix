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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken()) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $newStatus = $_POST['status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (!in_array($newStatus, $allowedStatuses, true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (strlen($notes) > 5000) {
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

            if ($newStatus !== $oldStatus || $notes !== '') {
                $history = $pdo->prepare(
                    'INSERT INTO report_updates
                    (report_id, admin_id, status, notes)
                    VALUES (?, ?, ?, ?)'
                );

                $history->execute([
                    $reportId,
                    $_SESSION['user_id'],
                    $newStatus,
                    $notes !== '' ? $notes : null
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

    $report['status'] = $newStatus;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Report | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container py-5">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">

            <h2 class="mb-3">Manage Fault Report</h2>

            <p><strong>Reference:</strong>
                <?= htmlspecialchars($report['reference_number']) ?>
            </p>

            <p><strong>Resident:</strong>
                <?= htmlspecialchars($report['full_name']) ?>
            </p>

            <p><strong>Category:</strong>
                <?= htmlspecialchars($report['category_name']) ?>
            </p>

            <p><strong>Location:</strong>
                <?= htmlspecialchars($report['location']) ?>
            </p>

            <p><strong>Description:</strong><br>
                <?= nl2br(htmlspecialchars($report['description'])) ?>
            </p>

            <p><strong>Current Status:</strong>
                <?= htmlspecialchars($report['status']) ?>
            </p>

            <?php if (isset($_GET['updated'])): ?>
                <div class="alert alert-success">
                    Report updated successfully.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <hr>

            <h4>Update Report Status</h4>

            <form method="POST" action="update_report.php?id=<?= $reportId ?>">

                <input type="hidden" name="csrf_token"
                       value="<?= htmlspecialchars($csrfToken) ?>">

                <div class="mb-3">
                    <label class="form-label">New Status</label>

                    <select name="status" class="form-select" required>
                        <?php foreach ($allowedStatuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>"
                                <?= $report['status'] === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Update Notes (Optional)</label>

                    <textarea name="notes" class="form-control"
                              rows="4" maxlength="5000"
                              placeholder="Enter any additional information..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Update
                </button>

                <a href="dashboard.php" class="btn btn-secondary">
                    Back to Dashboard
                </a>

            </form>

        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <h4 class="mb-3">Update History</h4>

            <?php if (empty($history)): ?>

                <p class="text-muted">No updates have been recorded yet.</p>

            <?php else: ?>

                <?php foreach ($history as $entry): ?>
                    <div class="border-bottom pb-3 mb-3">

                        <p class="mb-1">
                            <strong>Status:</strong>
                            <?= htmlspecialchars($entry['status']) ?>
                        </p>

                        <p class="mb-1">
                            <strong>Updated by:</strong>
                            <?= htmlspecialchars($entry['full_name']) ?>
                        </p>

                        <p class="mb-1">
                            <strong>Date:</strong>
                            <?= htmlspecialchars($entry['created_at']) ?>
                        </p>

                        <?php if (!empty($entry['notes'])): ?>
                            <p class="mb-0">
                                <strong>Notes:</strong><br>
                                <?= nl2br(htmlspecialchars($entry['notes'])) ?>
                            </p>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>
