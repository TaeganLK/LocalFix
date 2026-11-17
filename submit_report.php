<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

requireLogin();

if (($_SESSION['role'] ?? '') !== 'resident') {
    header('Location: admin/dashboard.php');
    exit;
}

$errors = [];
$location = '';
$description = '';
$selectedCategory = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken()) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $selectedCategory = $_POST['category_id'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!ctype_digit((string)$selectedCategory)) {
        $errors[] = 'Please select a valid fault category.';
    }

    if ($location === '' || strlen($location) > 255) {
        $errors[] = 'Please enter a valid location (maximum 255 characters).';
    }

    if ($description === '' || strlen($description) > 5000) {
        $errors[] = 'Please enter a description (maximum 5000 characters).';
    }

    if (empty($errors)) {

        $categoryCheck = $pdo->prepare(
            'SELECT category_id FROM categories WHERE category_id = ?'
        );
        $categoryCheck->execute([$selectedCategory]);

        if (!$categoryCheck->fetch()) {
            $errors[] = 'The selected category does not exist.';
        } else {

            $referenceNumber = 'LF-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(6)));

            $stmt = $pdo->prepare(
                'INSERT INTO reports
                (reference_number, user_id, category_id, location, description, status)
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $referenceNumber,
                $_SESSION['user_id'],
                $selectedCategory,
                $location,
                $description,
                'Submitted'
            ]);

            header('Location: my_reports.php?submitted=1');
            exit;
        }
    }
}

$categories = $pdo->query(
    'SELECT category_id, category_name FROM categories ORDER BY category_name'
)->fetchAll();

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Fault | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container py-5">

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="mb-2">Report a Municipal Fault</h2>

                    <p class="text-muted mb-4">
                        Provide the details of the fault you would like to report.
                    </p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="submit_report.php">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-3">
                            <label class="form-label">Fault Category</label>

                            <select name="category_id" class="form-select" required>
                                <option value="">Select a category</option>

                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= htmlspecialchars((string)$category['category_id']) ?>"
                                        <?= $selectedCategory == $category['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fault Location</label>

                            <input type="text" name="location"
                                   class="form-control"
                                   maxlength="255"
                                   value="<?= htmlspecialchars($location) ?>"
                                   placeholder="Enter the street or area"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fault Description</label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="5"
                                      maxlength="5000"
                                      placeholder="Describe the problem..."
                                      required><?= htmlspecialchars($description) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Submit Fault Report
                        </button>

                    </form>

                    <div class="text-center mt-3">
                        <a href="dashboard.php">Back to Dashboard</a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
