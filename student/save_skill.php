<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];
    $skill_name = trim($_POST['skill_name']);
    $skill_level = trim($_POST['skill_level']);
    $description = trim($_POST['description']);

    $sql = "INSERT INTO skills (user_id, skill_name, skill_level, description)
            VALUES (?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "isss",
        $user_id,
        $skill_name,
        $skill_level,
        $description
    );

    if (mysqli_stmt_execute($stmt)) {

        header("Location: skills.php?success=1");
        exit();

    } else {

        die("Error saving skill: " . mysqli_error($conn));

    }

}
?>