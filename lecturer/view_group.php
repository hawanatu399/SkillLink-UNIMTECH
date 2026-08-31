<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Validate Group ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid study group.");
}

$group_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Study Group Information
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            study_groups.id,
            study_groups.group_name,
            study_groups.description,
            study_groups.category,
            study_groups.created_at,

            users.full_name AS creator_name,
            users.student_id AS creator_student_id,
            users.email AS creator_email

        FROM study_groups

        INNER JOIN users
            ON study_groups.creator_id = users.id

        WHERE study_groups.id = ?

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die(
        "Database error: " .
        mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $group_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$group = mysqli_fetch_assoc($result);

if (!$group) {
    die("Study group not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        View Study Group | Lecturer | SkillLink UNIMTECH
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


<!-- =========================================================
     TOP NAVIGATION
========================================================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <span class="navbar-brand">
            🎓 SkillLink UNIMTECH
        </span>

        <span class="text-white">
            👨‍🏫 Lecturer Portal
        </span>

    </div>

</nav>


<!-- =========================================================
     PAGE LAYOUT
========================================================= -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             REUSABLE LECTURER SIDEBAR
        ================================================== -->

        <div class="col-md-3">

            <?php include "../templates/lecturer_sidebar.php"; ?>

        </div>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="col-md-9">

            <div class="p-4">


                <!-- =================================================
                     PAGE TITLE
                ================================================== -->

                <h2>
                    📚 Study Group Details
                </h2>

                <p class="text-muted">
                    Review the details of this student-created
                    study group.
                </p>

                <hr>


                <!-- =================================================
                     GROUP INFORMATION
                ================================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-body">


                        <h3 class="mb-3">

                            📚
                            <?= htmlspecialchars(
                                $group['group_name']
                            ); ?>

                        </h3>


                        <!-- CATEGORY -->

                        <p>

                            <strong>
                                Category:
                            </strong>

                            <?php if (
                                !empty(
                                    $group['category']
                                )
                            ): ?>

                                <span
                                    class="badge bg-primary">

                                    <?= htmlspecialchars(
                                        $group['category']
                                    ); ?>

                                </span>

                            <?php else: ?>

                                <span
                                    class="text-muted">

                                    Not specified

                                </span>

                            <?php endif; ?>

                        </p>


                        <!-- DESCRIPTION -->

                        <div class="mb-4">

                            <h5>
                                📝 Description
                            </h5>

                            <div
                                class="border rounded p-3 bg-light">

                                <?= nl2br(
                                    htmlspecialchars(
                                        $group['description']
                                        ?: 'No description provided.'
                                    )
                                ); ?>

                            </div>

                        </div>


                        <!-- CREATOR -->

                        <div class="mb-4">

                            <h5>
                                👤 Created By
                            </h5>

                            <p class="mb-1">

                                <strong>
                                    Name:
                                </strong>

                                <?= htmlspecialchars(
                                    $group['creator_name']
                                ); ?>

                            </p>


                            <p class="mb-1">

                                <strong>
                                    Student ID:
                                </strong>

                                <?= htmlspecialchars(
                                    $group['creator_student_id']
                                ); ?>

                            </p>


                            <p class="mb-1">

                                <strong>
                                    Email:
                                </strong>

                                <?= htmlspecialchars(
                                    $group['creator_email']
                                ); ?>

                            </p>

                        </div>


                        <!-- DATE -->

                        <div>

                            <h5>
                                📅 Date Created
                            </h5>

                            <p>

                                <?= htmlspecialchars(
                                    $group['created_at']
                                ); ?>

                            </p>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="text-center mb-4">

                    <a
                        href="study_groups.php"
                        class="btn btn-secondary">

                        ← Back to Study Groups

                    </a>

                </div>


            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     BOOTSTRAP JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>