
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
    <title>Register | LocalFix</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="text-center mb-2">Create an Account</h2>
                    <p class="text-center text-muted mb-4">
                        Register with LocalFix to report municipal faults.
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

                    <form method="POST" action="register.php">

                        <input type="hidden" name="csrf_token"
                               value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($fullName) ?>"
                                   maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($email) ?>"
                                   maxlength="150" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password"
                                   class="form-control"
                                   minlength="8" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password"
                                   class="form-control"
                                   minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Register
                        </button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Already have an account?
                        <a href="login.php">Log in</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
