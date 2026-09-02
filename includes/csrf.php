<?php

/*
|--------------------------------------------------------------------------
| CSRF Protection Helpers
|--------------------------------------------------------------------------
|
| generate_csrf_field() should be echoed inside every <form> that changes
| data (POST). verify_csrf() should be called at the top of every script
| that processes a POST request, before touching the database.
|
*/

function generate_csrf_token() {

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function generate_csrf_field() {

    $token = generate_csrf_token();

    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function verify_csrf() {

    $submitted = $_POST['csrf_token'] ?? '';

    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $submitted)
    ) {
        http_response_code(403);
        die("Security check failed (invalid or missing CSRF token). Please go back and try again.");
    }
}
