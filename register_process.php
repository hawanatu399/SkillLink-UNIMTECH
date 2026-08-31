<?php
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name  = trim($_POST['full_name']);
    $student_id = trim($_POST['student_id']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $department = trim($_POST['department']);
    $programme  = trim($_POST['programme']);
    $level      = trim($_POST['level']);

    $sql = "INSERT INTO users
    (full_name, student_id, email, password, department, programme, level)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL Prepare Error: " . mysqli_error($conn));
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

        echo "<h3 style='color:red'>Database Error</h3>";
        echo mysqli_error($conn);

    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {

    die("Invalid Request");

}
?>