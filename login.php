<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/csrf.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!validateCsrfToken()) {
        http_response_code(403);
        exit('Invalid security token. Please refresh the page and try again.');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email address and password.';
    } else {

        $stmt = $pdo->prepare(
            'SELECT user_id, full_name, email, password_hash, role
             FROM users
             WHERE email = ?'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Generate a fresh CSRF token after login.
            unset($_SESSION['csrf_token']);

            if ($user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: dashboard.php');
            }

            exit;

        } else {
            $error = 'Invalid email address or password.';
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

    <title>Login | LocalFix</title>

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

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 15px;
        }

        .login-card {
            background: #FFFFFF;
            border: 1px solid #DCE3E8;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(22, 50, 79, 0.08);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #16324F, #237C78);
            color: #FFFFFF;
            padding: 32px;
            text-align: center;
        }

        .brand-name {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #E8F3F1;
            margin-bottom: 0;
        }

        .login-body {
            padding: 32px;
        }

        .login-title {
            color: #16324F;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .login-description {
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

        .register-link {
            color: #237C78;
            font-weight: 600;
            text-decoration: none;
        }

        .register-link:hover {
            color: #1B625F;
            text-decoration: underline;
        }

        .login-footer {
            text-align: center;
            padding: 0 25px 25px;
            color: #6B7785;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .login-body {
                padding: 25px 20px;
            }

            .login-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-7 col-lg-5">

                <div class="login-card">

                    <div class="login-header">
                        <div class="brand-name">LocalFix</div>
                        <p>Municipal Fault Reporting System</p>
                    </div>

                    <div class="login-body">

                        <h2 class="login-title text-center">
                            Welcome Back
                        </h2>

                        <p class="login-description text-center">
                            Sign in to access your LocalFix account.
                        </p>

                        <?php if (isset($_GET['registered'])): ?>

                            <div class="alert alert-success">
                                Registration successful. You can now log in.
                            </div>

                        <?php endif; ?>

                        <?php if ($error !== ''): ?>

                            <div class="alert alert-danger">
                                <?= htmlspecialchars($error) ?>
                            </div>

                        <?php endif; ?>

                        <form method="POST" action="login.php">

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= htmlspecialchars($csrfToken) ?>"
                            >

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
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Sign In
                            </button>

                        </form>

                        <p class="text-center mt-4 mb-0">
                            Don't have an account?
                            <a href="register.php" class="register-link">
                                Register here
                            </a>
                        </p>

                    </div>

                    <div class="login-footer">
                        LocalFix &copy; <?= date('Y') ?>. All rights reserved.
                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>
