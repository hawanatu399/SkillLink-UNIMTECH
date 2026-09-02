<?php
require_once "config/session.php";
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    verify_csrf();

    $full_name  = trim($_POST['full_name']);
    $student_id = trim($_POST['student_id']);
    $email      = trim($_POST['email']);
    $raw_password = $_POST['password'] ?? '';
    $department = trim($_POST['department']);
    $programme  = trim($_POST['programme']);
    $level      = trim($_POST['level']);

    /*
    |----------------------------------------------------------------
    | Basic Server-Side Validation
    |----------------------------------------------------------------
    */

    if ($full_name === '' || $student_id === '' || $email === '' || $raw_password === '') {
        die("Please fill in all required fields. <a href='register.php'>Go back</a>");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address. <a href='register.php'>Go back</a>");
    }

    if (strlen($raw_password) < 6) {
        die("Password must be at least 6 characters long. <a href='register.php'>Go back</a>");
    }

    $password = password_hash($raw_password, PASSWORD_DEFAULT);

    /*
    |----------------------------------------------------------------
    | Reject Duplicate Email Before Insert (friendlier than a raw
    | DB constraint error, and avoids leaking DB internals)
    |----------------------------------------------------------------
    */

    $check_sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        mysqli_stmt_close($check_stmt);
        die("An account with that email already exists. <a href='login.php'>Login instead</a>");
    }

    mysqli_stmt_close($check_stmt);

    $sql = "INSERT INTO users
    (full_name, student_id, email, password, department, programme, level)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log("Register prepare failed: " . mysqli_error($conn));
        die("Something went wrong. Please try again later.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $full_name,
        $student_id,
        $email,
        $password,
        $department,
        $programme,
        $level
    );

    if (mysqli_stmt_execute($stmt)) {

        echo "<h2 style='color:green'>🎉 Registration Successful!</h2>";
        echo "<p>Welcome to SkillLink UNIMTECH.</p>";
        echo "<a href='login.php'>Proceed to Login</a>";

    } else {

        error_log("Register insert failed: " . mysqli_stmt_error($stmt));
        echo "<h3 style='color:red'>Something went wrong. Please try again.</h3>";

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {

    die("Invalid Request");

}
?>