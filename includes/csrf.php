<?php

require_once __DIR__ . '/auth.php';

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    return is_string($submittedToken)
        && is_string($sessionToken)
        && $sessionToken !== ''
        && hash_equals($sessionToken, $submittedToken);
}
