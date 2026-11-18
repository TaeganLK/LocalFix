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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #F3F6FA;
            color: #263445;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 15px;
            position: relative;
            overflow: hidden;
        }

        .login-wrapper::before {
            content: '';
            position: fixed;
            width: 450px;
            height: 450px;
            border: 1px solid rgba(53, 191, 234, 0.12);
            border-radius: 50%;
            top: -180px;
            right: -150px;
            pointer-events: none;
        }

        .login-wrapper::after {
            content: '';
            position: fixed;
            width: 350px;
            height: 350px;
            border: 1px solid rgba(0, 191, 166, 0.12);
            border-radius: 50%;
            bottom: -150px;
            left: -100px;
            pointer-events: none;
        }

        .login-card {
            background: #FFFFFF;
            border: 1px solid #E1E8F0;
            border-radius: 18px;
            box-shadow: 0 18px 50px rgba(16, 29, 50, 0.10);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .login-header {
            background: linear-gradient(120deg, #101D32, #1B3652);
            color: #FFFFFF;
            padding: 34px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border: 1px solid rgba(53, 191, 234, 0.2);
            border-radius: 50%;
            top: -100px;
            right: -45px;
        }

        .login-header::after {
            content: '';
            position: absolute;
            width: 130px;
            height: 130px;
            border: 1px solid rgba(0, 191, 166, 0.2);
            border-radius: 50%;
            bottom: -90px;
            left: -30px;
        }

        .brand-name {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.7px;
            margin-bottom: 9px;
            position: relative;
            z-index: 1;
        }

        .brand-accent {
            color: #35BFEA;
        }

        .login-header p {
            color: #C5D3E2;
            margin-bottom: 0;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }

        .header-label {
            color: #35BFEA;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        /* Form */
        .login-body {
            padding: 34px;
        }

        .login-title {
            color: #101D32;
            font-weight: 800;
            letter-spacing: -0.6px;
            margin-bottom: 9px;
        }

        .login-description {
            color: #718096;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .form-label {
            color: #263445;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .form-control {
            background: #FAFCFE;
            border: 1px solid #DCE5EE;
            border-radius: 9px;
            padding: 12px 14px;
            color: #263445;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control::placeholder {
            color: #9AA7B6;
        }

        .form-control:focus {
            background: #FFFFFF;
            border-color: #35BFEA;
            color: #263445;
            box-shadow: 0 0 0 3px rgba(53, 191, 234, 0.13);
        }

        /* Button */
        .btn-login {
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            border: none;
            border-radius: 9px;
            padding: 13px;
            font-weight: 800;
            font-size: 0.95rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #27ACD7, #00A990);
            color: #101D32;
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(0, 191, 166, 0.18);
        }

        /* Links and alerts */
        .register-link {
            color: #1689AD;
            font-weight: 700;
            text-decoration: none;
        }

        .register-link:hover {
            color: #00A990;
            text-decoration: underline;
        }

        .alert-success {
            background: #E5F7F2;
            border: 1px solid #B9E9D9;
            border-left: 4px solid #00BFA6;
            color: #176B5E;
            border-radius: 9px;
            font-size: 0.87rem;
        }

        .alert-danger {
            background: #FFF0F0;
            border: 1px solid #F5D0D0;
            border-left: 4px solid #D9534F;
            color: #8B3434;
            border-radius: 9px;
            font-size: 0.87rem;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding: 0 25px 26px;
            color: #8491A3;
            font-size: 0.78rem;
            line-height: 1.6;
        }

        @media (max-width: 576px) {
            .login-body {
                padding: 27px 21px;
            }

            .login-header {
                padding: 29px 20px;
            }

            .login-title {
                font-size: 1.5rem;
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

                    <!-- Header -->
                    <div class="login-header">

                        <div class="header-label">
                            Civic Technology Platform
                        </div>

                        <div class="brand-name">
                            Local<span class="brand-accent">Fix</span>
                        </div>

                        <p>Municipal Fault Reporting System</p>

                    </div>

                    <!-- Login form -->
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

                            <button type="submit" class="btn btn-login w-100">
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

                    <!-- Footer -->
                    <div class="login-footer">
                        LocalFix &copy; <?= date('Y') ?>.
                        Developed as an academic project at
                        Richfield Graduate Institute of Technology.
                    </div>

                </div>

            </div>

        </div>
    </div>

</div>

</body>
</html>
