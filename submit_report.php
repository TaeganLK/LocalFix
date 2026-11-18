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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .report-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: white;
            border-radius: 16px;
            padding: 30px;
        }

        .report-header h2 {
            color: white;
            font-weight: 700;
        }

        .report-header p {
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 0;
        }

        .report-card {
            border: none;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 5px 20px rgba(22, 50, 79, 0.07);
        }

        .report-card .card-body {
            padding: 32px;
        }

        .report-card .form-label {
            color: #16324F;
            font-weight: 600;
        }

        .report-card .form-control,
        .report-card .form-select {
            padding: 12px 14px;
            border: 1px solid #DCE3E8;
            border-radius: 9px;
        }

        .report-card .form-control:focus,
        .report-card .form-select:focus {
            border-color: #237C78;
            box-shadow: 0 0 0 0.2rem rgba(35, 124, 120, 0.15);
        }

        .report-card textarea {
            resize: vertical;
            min-height: 150px;
        }

        .form-help {
            color: #6B7785;
            font-size: 0.85rem;
        }

        .report-submit {
            padding: 13px;
            font-weight: 600;
            border-radius: 9px;
        }

        .back-link {
            color: #237C78;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: #1B625F;
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .report-header {
                padding: 24px;
            }

            .report-card .card-body {
                padding: 22px;
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

        <a href="dashboard.php" class="btn btn-light btn-sm">
            Dashboard
        </a>

    </div>
</nav>

<main class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-8 col-xl-7">

            <!-- Header -->
            <div class="report-header mb-4">
                <h2>Report a Municipal Fault</h2>

                <p class="mt-2">
                    Help improve your community by reporting municipal problems.
                    Provide accurate information so the fault can be identified.
                </p>
            </div>

            <!-- Form card -->
            <div class="card report-card">
                <div class="card-body">

                    <h4 class="mb-2">Fault Details</h4>

                    <p class="text-muted mb-4">
                        Complete all fields below to submit your report.
                    </p>

                    <!-- Validation errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <strong>Please correct the following:</strong>

                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="submit_report.php">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($csrfToken) ?>"
                        >

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="form-label">
                                Fault Category
                            </label>

                            <select
                                name="category_id"
                                id="category_id"
                                class="form-select"
                                required
                            >
                                <option value="">Select a category</option>

                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= htmlspecialchars((string)$category['category_id']) ?>"
                                        <?= $selectedCategory == $category['category_id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>

                            <div class="form-help mt-1">
                                Select the category that best describes the problem.
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label for="location" class="form-label">
                                Fault Location
                            </label>

                            <input
                                type="text"
                                name="location"
                                id="location"
                                class="form-control"
                                maxlength="255"
                                value="<?= htmlspecialchars($location) ?>"
                                placeholder="e.g. Main Road, near the shopping centre"
                                required
                            >

                            <div class="form-help mt-1">
                                Include the street name, suburb, or a nearby landmark.
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label">
                                Fault Description
                            </label>

                            <textarea
                                name="description"
                                id="description"
                                class="form-control"
                                rows="6"
                                maxlength="5000"
                                placeholder="Describe the fault, what you noticed, and any relevant details..."
                                required
                            ><?= htmlspecialchars($description) ?></textarea>

                            <div class="form-help mt-1">
                                Provide as much detail as possible to help identify the issue.
                            </div>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="btn btn-primary w-100 report-submit"
                        >
                            Submit Fault Report
                        </button>

                    </form>

                    <!-- Back -->
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="back-link">
                            &larr; Back to Dashboard
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

</body>
</html>
