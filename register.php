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

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 15px;
            position: relative;
            overflow: hidden;
        }

        .register-wrapper::before {
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

        .register-wrapper::after {
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

        .register-card {
            background: #FFFFFF;
            border: 1px solid #E1E8F0;
            border-radius: 18px;
            box-shadow: 0 18px 50px rgba(16, 29, 50, 0.10);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .register-header {
            background: linear-gradient(120deg, #101D32, #1B3652);
            color: #FFFFFF;
            padding: 34px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-header::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border: 1px solid rgba(53, 191, 234, 0.2);
            border-radius: 50%;
            top: -100px;
            right: -45px;
        }

        .register-header::after {
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

        .register-header p {
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
        .register-body {
            padding: 34px;
        }

        .register-title {
            color: #101D32;
            font-weight: 800;
            letter-spacing: -0.6px;
            margin-bottom: 9px;
        }

        .register-description {
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

        .form-note {
            color: #8491A3;
            font-size: 0.78rem;
            margin-top: 7px;
        }

        /* Button */
        .btn-register {
            background: linear-gradient(135deg, #35BFEA, #00BFA6);
            color: #101D32;
            border: none;
            border-radius: 9px;
            padding: 13px;
            font-weight: 800;
            font-size: 0.95rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #27ACD7, #00A990);
            color: #101D32;
            transform: translateY(-2px);
            box-shadow: 0 7px 18px rgba(0, 191, 166, 0.18);
        }

        /* Links and alerts */
        .login-link {
            color: #1689AD;
            font-weight: 700;
            text-decoration: none;
        }

        .login-link:hover {
            color: #00A990;
            text-decoration: underline;
        }

        .alert-danger {
            background: #FFF0F0;
            border: 1px solid #F5D0D0;
            border-left: 4px solid #D9534F;
            color: #8B3434;
            border-radius: 9px;
            font-size: 0.87rem;
        }

        .alert-danger ul {
            padding-left: 20px;
        }

        /* Footer */
        .register-footer {
            text-align: center;
            padding: 0 25px 26px;
            color: #8491A3;
            font-size: 0.78rem;
            line-height: 1.6;
        }

        @media (max-width: 576px) {
            .register-body {
                padding: 27px 21px;
            }

            .register-header {
                padding: 29px 20px;
            }

            .register-title {
                font-size: 1.5rem;
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

                    <!-- Header -->
                    <div class="register-header">

                        <div class="header-label">
                            Civic Technology Platform
                        </div>

                        <div class="brand-name">
                            Local<span class="brand-accent">Fix</span>
                        </div>

                        <p>Municipal Fault Reporting System</p>

                    </div>

                    <!-- Registration form -->
                    <div class="register-body">

                        <h2 class="register-title text-center">
                            Create an Account
                        </h2>

                        <p class="register-description text-center">
                            Join LocalFix to report municipal faults and
                            track their progress.
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

                                <div class="form-note">
                                    Your password must contain at least
                                    8 characters.
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

                            <button type="submit" class="btn btn-register w-100">
                                Create Account
                            </button>

                        </form>

                        <p class="text-center mt-4 mb-0">
                            Already have an account?
                            <a href="login.php" class="login-link">Log in</a>
                        </p>

                    </div>

                    <!-- Footer -->
                    <div class="register-footer">
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
