
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="text-center mb-2">LocalFix Login</h2>
                    <p class="text-center text-muted mb-4">
                        Sign in to access your account.
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

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($email) ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Login
                        </button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Don't have an account?
                        <a href="register.php">Register here</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
