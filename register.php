<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

$errors = [];
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken()) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || strlen($fullName) > 100) {
        $errors[] = 'Please enter a valid full name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Your password must be at least 8 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Your passwords do not match.';
    }

    if (empty($errors)) {

        $check = $pdo->prepare(
            'SELECT user_id FROM users WHERE email = ?'
        );

        $check->execute([$email]);

        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, role)
                 VALUES (?, ?, ?, ?)'
            );

            $insert->execute([
                $fullName,
                $email,
                $passwordHash,
                'resident'
            ]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | LocalFix</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #F4F6F8;
            color: #263445;
            min-height: 100vh;
        }

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 15px;
        }

        .register-card {
            background: #FFFFFF;
            border: 1px solid #DCE3E8;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(22, 50, 79, 0.08);
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #FFFFFF;
            padding: 30px;
            text-align: center;
        }

        .brand-name {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .register-header p {
            color: #E8F3F1;
            margin-bottom: 0;
        }

        .register-body {
            padding: 32px;
        }

        .register-title {
            color: #16324F;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .register-description {
            color: #6B7785;
            margin-bottom: 25px;
        }

        .form-label {
            color: #263445;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .form-control {
            border: 1px solid #DCE3E8;
            border-radius: 8px;
            padding: 11px 13px;
            color: #263445;
        }

        .form-control:focus {
            border-color: #237C78;
            box-shadow: 0 0 0 0.2rem rgba(35, 124, 120, 0.15);
        }

        .btn-primary {
            background: #237C78;
            border-color: #237C78;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background: #1B625F;
            border-color: #1B625F;
        }

        .login-link {
            color: #237C78;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link:hover {
            color: #1B625F;
            text-decoration: underline;
        }

        .form-note {
            color: #6B7785;
            font-size: 0.85rem;
        }

        .register-footer {
            text-align: center;
            padding: 0 25px 25px;
            color: #6B7785;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .register-body {
                padding: 25px 20px;
            }

            .register-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<div class="register-wrapper">

    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-5">

                <div class="register-card">

                    <div class="register-header">
                        <div class="brand-name">LocalFix</div>
                        <p>Municipal Fault Reporting System</p>
                    </div>

                    <div class="register-body">

                        <h2 class="register-title text-center">
                            Create an Account
                        </h2>

                        <p class="register-description text-center">
                            Join LocalFix to report municipal faults and track their progress.
                        </p>

                        <?php if (!empty($errors)): ?>

                            <div class="alert alert-danger">
                                <strong>Registration unsuccessful.</strong>

                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                        <?php endif; ?>

                        <form method="POST" action="register.php">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken) ?>"
                            >

                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control"
                                    placeholder="Enter your full name"
                                    value="<?= htmlspecialchars($fullName) ?>"
                                    maxlength="100"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email address"
                                    value="<?= htmlspecialchars($email) ?>"
                                    maxlength="150"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Create a password"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >

                                <div class="form-note mt-1">
                                    Your password must contain at least 8 characters.
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    Confirm Password
                                </label>

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Enter your password again"
                                    minlength="8"
                                    autocomplete="new-password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Create Account
                            </button>

                        </form>

                        <p class="text-center mt-4 mb-0">
                            Already have an account?
                            <a href="login.php" class="login-link">Log in</a>
                        </p>

                    </div>

                    <div class="register-footer">
                        LocalFix &copy; <?= date('Y') ?>. All rights reserved.
                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>
