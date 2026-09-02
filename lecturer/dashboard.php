<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'lecturer'
) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Lecturer Information
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            full_name,
            department,
            programme

        FROM users

        WHERE id = ?

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$lecturer = mysqli_fetch_assoc($result);

if (!$lecturer) {
    die("Lecturer account not found.");
}


/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/

function getCount($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int) ($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/


/*
| Total Students
*/

$student_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'student'"
);


/*
| Total Skills
*/

$skill_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM skills"
);


/*
| Verified Skills
*/

$verified_skill_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM skills
     WHERE verified = 1"
);


/*
| Pending Skills
*/

$pending_skill_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM skills
     WHERE verified = 0"
);


/*
| Study Groups
*/

$group_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM study_groups"
);


/*
| Learning Resources
*/

$resource_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM resources"
);


/*
| Pending Collaboration Requests
*/

$pending_collaboration_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collaboration_requests
     WHERE status = 'Pending'"
);


/*
| Accepted Collaborations
*/

$accepted_collaboration_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM collaboration_requests
     WHERE status = 'Accepted'"
);


/*
| Total Reviews
*/

$review_count = getCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM reviews"
);


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Lecturer Dashboard | SkillLink UNIMTECH
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

            👨‍🏫

            <?= htmlspecialchars(
                $lecturer['full_name']
            ); ?>

        </span>

    </div>

</nav>


<!-- =========================================================
     PAGE LAYOUT
========================================================= -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
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
                     HEADER
                ================================================== -->

                <h2>

                    👨‍🏫 Lecturer Dashboard

                </h2>


                <p class="text-muted">

                    Welcome,

                    <strong>

                        <?= htmlspecialchars(
                            $lecturer['full_name']
                        ); ?>

                    </strong>.

                    Monitor and manage student
                    activities on SkillLink UNIMTECH.

                </p>


                <hr>


                <!-- =================================================
                     STATISTICS
                ================================================== -->

                <div class="row g-4">


                    <!-- =================================================
                         STUDENTS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="students.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        👨‍🎓 Students

                                    </h5>


                                    <h2 class="text-primary">

                                        <?= $student_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Registered students

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Students →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         SKILLS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        💡 Student Skills

                                    </h5>


                                    <h2 class="text-primary">

                                        <?= $skill_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Skills submitted

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        Review Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         VERIFIED SKILLS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🏅 Verified Skills

                                    </h5>


                                    <h2 class="text-success">

                                        <?= $verified_skill_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Lecturer verified

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Verified Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         PENDING SKILLS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⏳ Pending Skills

                                    </h5>


                                    <h2 class="text-warning">

                                        <?= $pending_skill_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Awaiting verification

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        Verify Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         STUDY GROUPS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="study_groups.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        📚 Study Groups

                                    </h5>


                                    <h2 class="text-primary">

                                        <?= $group_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Available study groups

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Study Groups →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         RESOURCES
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="resources.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        📁 Resources

                                    </h5>


                                    <h2 class="text-primary">

                                        <?= $resource_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Learning resources

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Resources →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         PENDING COLLABORATIONS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="collaboration_requests.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⏳ Pending Collaborations

                                    </h5>


                                    <h2 class="text-warning">

                                        <?= $pending_collaboration_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Awaiting response

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Requests →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         ACCEPTED COLLABORATIONS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="collaborations.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🤝 Accepted Collaborations

                                    </h5>


                                    <h2 class="text-success">

                                        <?= $accepted_collaboration_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Active collaborations

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Collaborations →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- =================================================
                         REVIEWS
                    ================================================== -->

                    <div class="col-md-4">

                        <a
                            href="reviews.php"
                            class="text-decoration-none">

                            <div
                                class="card shadow-sm h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⭐ Reviews

                                    </h5>


                                    <h2 class="text-primary">

                                        <?= $review_count; ?>

                                    </h2>


                                    <p class="text-muted mb-0">

                                        Student reviews

                                    </p>


                                    <small
                                        class="text-primary d-block mt-2">

                                        View Reviews →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                </div>


                <!-- =================================================
                     QUICK MANAGEMENT
                ================================================== -->

                <div class="card mt-4 shadow-sm">

                    <div class="card-body">

                        <h4>

                            ⚡ Quick Management

                        </h4>


                        <p class="text-muted">

                            Quickly access the main lecturer
                            management functions.

                        </p>


                        <hr>


                        <div class="row g-3">


                            <div class="col-md-4">

                                <a
                                    href="students.php"
                                    class="btn btn-outline-primary w-100">

                                    👨‍🎓 Manage Students

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="skills.php"
                                    class="btn btn-outline-success w-100">

                                    🏅 Verify Skills

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="study_groups.php"
                                    class="btn btn-outline-primary w-100">

                                    📚 Study Groups

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="resources.php"
                                    class="btn btn-outline-primary w-100">

                                    📁 Learning Resources

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="collaboration_requests.php"
                                    class="btn btn-outline-warning w-100">

                                    🤝 Collaboration Requests

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="reviews.php"
                                    class="btn btn-outline-secondary w-100">

                                    ⭐ Student Reviews

                                </a>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     LECTURER INFORMATION
                ================================================== -->

                <div class="card mt-4 shadow-sm">

                    <div class="card-body">

                        <h4>

                            👨‍🏫 Lecturer Information

                        </h4>


                        <hr>


                        <p>

                            <strong>
                                Name:
                            </strong>

                            <?= htmlspecialchars(
                                $lecturer['full_name']
                            ); ?>

                        </p>


                        <p>

                            <strong>
                                Department:
                            </strong>

                            <?= htmlspecialchars(
                                $lecturer['department']
                            ); ?>

                        </p>


                        <p>

                            <strong>
                                Programme:
                            </strong>

                            <?= htmlspecialchars(
                                $lecturer['programme']
                            ); ?>

                        </p>

                    </div>

                </div>


                <!-- =================================================
                     SYSTEM SUMMARY
                ================================================== -->

                <div class="alert alert-info mt-4">

                    <strong>

                        📌 SkillLink UNIMTECH Lecturer Portal

                    </strong>

                    <br><br>

                    The lecturer portal provides tools for
                    monitoring registered students, reviewing
                    and verifying skills, managing study groups,
                    reviewing learning resources and monitoring
                    student collaboration activity.

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