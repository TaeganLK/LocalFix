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

    if (!ctype_digit((string) $selectedCategory)) {
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

        .navbar .btn {
            background: rgba(255, 255, 255, 0.08);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 8px 15px;
        }

        .navbar .btn:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #FFFFFF;
        }

        .main-container {
            max-width: 1050px;
            padding-top: 38px;
            padding-bottom: 50px;
        }

        .report-header {
            position: relative;
            overflow: hidden;
            background: linear-gradient(120deg, var(--lf-navy), var(--lf-navy-light));
            color: #FFFFFF;
            border-radius: 18px;
            padding: 38px;
            margin-bottom: 27px;
            box-shadow: 0 12px 30px rgba(16, 29, 50, 0.12);
        }

        .report-header::after {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            border: 1px solid rgba(53, 191, 234, 0.18);
            border-radius: 50%;
            right: -65px;
            top: -125px;
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
            margin-bottom: 11px;
        }

        .report-header h1 {
            color: #FFFFFF;
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 12px;
        }

        .report-header p {
            color: #C5D2E2;
            line-height: 1.7;
            max-width: 650px;
            margin-bottom: 0;
        }

        .report-card {
            border: 1px solid var(--lf-border);
            border-radius: 15px;
            background: var(--lf-white);
            box-shadow: 0 5px 18px rgba(16, 29, 50, 0.045);
            overflow: hidden;
        }

        .report-card .card-body {
            padding: 32px;
        }

        .section-title {
            color: var(--lf-navy);
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            margin-bottom: 8px;
        }

        .section-description {
            color: var(--lf-muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 27px;
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

        .form-control::placeholder {
            color: #9AA8B8;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--lf-cyan);
            box-shadow: 0 0 0 3px rgba(53, 191, 234, 0.14);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 155px;
        }

        .form-help {
            color: var(--lf-muted);
            font-size: 0.78rem;
            line-height: 1.5;
            margin-top: 7px;
        }

        .report-submit {
            background: linear-gradient(135deg, #159BC8, #00AFA0);
            border: none;
            border-radius: 9px;
            color: #FFFFFF;
            padding: 13px 18px;
            font-size: 0.9rem;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .report-submit:hover {
            background: linear-gradient(135deg, #1189B3, #009B8D);
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(0, 174, 160, 0.2);
        }

        .back-link {
            color: #168DB8;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .back-link:hover {
            color: #087B9F;
            text-decoration: underline;
        }

        .alert {
            border: none;
            border-radius: 10px;
            padding: 16px 19px;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: #FDE8E8;
            color: #A52A2A;
        }

        .footer {
            border-top: 1px solid var(--lf-border);
            padding: 23px 0;
            margin-top: 35px;
            color: var(--lf-muted);
            font-size: 0.78rem;
            line-height: 1.6;
        }

        .footer strong {
            color: var(--lf-navy);
        }

        @media (max-width: 576px) {
            .main-container {
                padding-top: 23px;
            }

            .report-header {
                padding: 27px 22px;
                border-radius: 14px;
            }

            .report-card .card-body {
                padding: 22px;
            }

            .report-submit {
                padding: 13px;
            }
        }
    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar">
    <div class="container">

        <a class="navbar-brand" href="dashboard.php">
            <span class="brand-mark">L</span>
            LocalFix
        </a>

        <a href="dashboard.php" class="btn">
            Dashboard
        </a>

    </div>
</nav>

<main class="container main-container">

    <div class="row justify-content-center">

        <div class="col-lg-9 col-xl-8">

            <!-- Page Header -->
            <div class="report-header">
                <div class="header-label">Resident Portal / New Report</div>

                <h1>Report a Municipal Fault</h1>

                <p>
                    Help improve your community by reporting municipal problems.
                    Provide accurate information so the fault can be identified
                    and addressed.
                </p>
            </div>

            <!-- Report Form -->
            <div class="card report-card">
                <div class="card-body">

                    <h4 class="section-title">Fault Details</h4>

                    <p class="section-description">
                        Complete the fields below to submit your municipal fault report.
                    </p>

                    <!-- Validation Errors -->
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
                                        value="<?= htmlspecialchars((string) $category['category_id']) ?>"
                                        <?= $selectedCategory == $category['category_id'] ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($category['category_name']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <div class="form-help">
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

                            <div class="form-help">
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

                            <div class="form-help">
                                Provide as much detail as possible to help identify the issue.
                            </div>

                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="btn report-submit w-100"
                        >
                            Submit Fault Report
                        </button>

                    </form>

                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="back-link">
                            &larr; Back to Dashboard
                        </a>
                    </div>

                </div>
            </div>

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

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
