<?php

require_once "../config/session.php";

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

/*
|--------------------------------------------------------------------------
| Role-Based Access Control
|--------------------------------------------------------------------------
|
| Call require_role('student') or require_role('lecturer') at the top of
| any page that should only be reachable by that role. Without this,
| any logged-in user (regardless of role) could open the URL directly.
|
*/

function require_role($role) {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {

        http_response_code(403);

        die(
            "<div style='font-family:Arial;text-align:center;margin-top:50px;'>" .
            "<h2 style='color:red;'>Access Denied</h2>" .
            "<p>This page is restricted to the '" . htmlspecialchars($role) . "' role.</p>" .
            "<a href='../login.php'>Back to Login</a>" .
            "</div>"
        );

    }

}