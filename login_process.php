<?php

require_once "config/database.php";
require_once "config/session.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    verify_csrf();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        if (password_verify($password, $user['password'])) {

            if (isset($user['status']) && $user['status'] === 'Suspended') {

                echo "

<div style='font-family:Arial;text-align:center;margin-top:50px;'>

<h2 style='color:red;'>

Your account has been suspended.

</h2>

<p>Please contact an administrator if you believe this is a mistake.</p>

<a href='login.php'>

Back to Login

</a>

</div>

";
                exit();

            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == "student") {

                header("Location: student/dashboard.php");
                exit();

            }

            if ($user['role'] == "lecturer") {

                header("Location: lecturer/dashboard.php");
                exit();

            }

            if ($user['role'] == "admin") {

                header("Location: admin/dashboard.php");
                exit();

            }

        }

    }

    echo "

<div style='font-family:Arial;text-align:center;margin-top:50px;'>

<h2 style='color:red;'>

Invalid Email or Password

</h2>

<a href='login.php'>

Try Again

</a>

</div>

";

}