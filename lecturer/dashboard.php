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


if (!$stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


$lecturer =
    mysqli_fetch_assoc($result);


if (!$lecturer) {

    die(
        "Lecturer account not found."
    );

}


/*
|--------------------------------------------------------------------------
| Count Helper
|--------------------------------------------------------------------------
*/

function getCount($conn, $sql)
{

    $result =
        mysqli_query(
            $conn,
            $sql
        );


    if (!$result) {

        return 0;

    }


    $row =
        mysqli_fetch_assoc(
            $result
        );


    return (int)
        ($row['total'] ?? 0);

}


/*
|--------------------------------------------------------------------------
| ACADEMIC STATISTICS
|--------------------------------------------------------------------------
*/


/*
| Total Students
*/

$student_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM users
         WHERE role = 'student'"
    );


/*
| Total Skills
*/

$skill_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM skills"
    );


/*
| Verified Skills
*/

$verified_skill_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM skills
         WHERE verified = 1"
    );


/*
| Pending Skills
*/

$pending_skill_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM skills
         WHERE verified = 0"
    );


/*
| Study Groups
*/

$group_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM study_groups"
    );


/*
| Learning Resources
*/

$resource_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM resources"
    );


/*
|--------------------------------------------------------------------------
| COLLABORATION STATISTICS
|--------------------------------------------------------------------------
*/


/*
| Pending Collaborations
*/

$pending_collaboration_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM collaboration_requests
         WHERE status = 'Pending'"
    );


/*
| Accepted Collaborations
*/

$accepted_collaboration_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM collaboration_requests
         WHERE status = 'Accepted'"
    );


/*
|--------------------------------------------------------------------------
| REVIEW STATISTICS
|--------------------------------------------------------------------------
*/


$review_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM reviews"
    );


/*
|--------------------------------------------------------------------------
| MARKETPLACE STATISTICS
|--------------------------------------------------------------------------
*/


/*
| Active Marketplace Services
*/

$marketplace_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM marketplace_services
         WHERE status = 'Active'"
    );


/*
| Available Marketplace Services
*/

$available_services_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM marketplace_services
         WHERE status = 'Active'
         AND availability = 'Available'"
    );


/*
| Lecturer's Services
*/

$my_services_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM marketplace_services
         WHERE provider_id = $user_id
         AND status = 'Active'"
    );


/*
|--------------------------------------------------------------------------
| SERVICE REQUEST STATISTICS
|--------------------------------------------------------------------------
*/


/*
| Total Requests
*/

$total_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests"
    );


/*
| Pending Requests
*/

$pending_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE status = 'Pending'"
    );


/*
| Accepted Requests
*/

$accepted_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE status = 'Accepted'"
    );


/*
| In Progress
*/

$in_progress_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE status = 'In Progress'"
    );


/*
| Completed
*/

$completed_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE status = 'Completed'"
    );


/*
| Confirmed
*/

$confirmed_service_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE status = 'Confirmed'"
    );


/*
|--------------------------------------------------------------------------
| LECTURER-SPECIFIC SERVICE REQUESTS
|--------------------------------------------------------------------------
*/


/*
| Requests Received by Lecturer
*/

$lecturer_requests_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE provider_id = $user_id"
    );


/*
| Pending Requests Received
*/

$lecturer_pending_requests =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE provider_id = $user_id
         AND status = 'Pending'"
    );


/*
| Lecturer In Progress
*/

$lecturer_in_progress =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE provider_id = $user_id
         AND status = 'In Progress'"
    );


/*
| Lecturer Completed
*/

$lecturer_completed =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM service_requests
         WHERE provider_id = $user_id
         AND status = 'Completed'"
    );


/*
|--------------------------------------------------------------------------
| NOTIFICATIONS
|--------------------------------------------------------------------------
*/

$notification_count =
    getCount(
        $conn,
        "SELECT COUNT(*) AS total
         FROM notifications
         WHERE user_id = $user_id
         AND is_read = 0"
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


    <style>

        .dashboard-card {

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;

        }


        .dashboard-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 8px 20px
                rgba(0,0,0,0.12) !important;

        }


        .stat-number {

            font-size: 2rem;

            font-weight: 700;

        }


        .section-title {

            font-weight: 700;

            color: #172033;

        }

    </style>

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

            <?php
            include "../templates/lecturer_sidebar.php";
            ?>

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
                     ACADEMIC OVERVIEW
                ================================================== -->

                <h4 class="section-title mb-3">

                    📊 Academic Overview

                </h4>


                <div class="row g-4">


                    <!-- STUDENTS -->

                    <div class="col-md-4">

                        <a
                            href="students.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        👨‍🎓 Students

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $student_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Registered students

                                    </p>


                                    <small class="text-primary">

                                        View Students →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- SKILLS -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        💡 Student Skills

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $skill_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Skills submitted

                                    </p>


                                    <small class="text-primary">

                                        Review Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- VERIFIED SKILLS -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🏅 Verified Skills

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-success">

                                        <?= $verified_skill_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Lecturer verified

                                    </p>


                                    <small class="text-primary">

                                        View Verified Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- PENDING SKILLS -->

                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⏳ Pending Skills

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-warning">

                                        <?= $pending_skill_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Awaiting verification

                                    </p>


                                    <small class="text-primary">

                                        Verify Skills →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- STUDY GROUPS -->

                    <div class="col-md-4">

                        <a
                            href="study_groups.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        📚 Study Groups

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $group_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Available study groups

                                    </p>


                                    <small class="text-primary">

                                        View Study Groups →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- RESOURCES -->

                    <div class="col-md-4">

                        <a
                            href="resources.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        📁 Resources

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $resource_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Learning resources

                                    </p>


                                    <small class="text-primary">

                                        View Resources →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>



                <!-- =================================================
                     MARKETPLACE
                ================================================== -->

                <hr class="my-5">


                <h4 class="section-title mb-3">

                    🛒 Marketplace Overview

                </h4>


                <div class="row g-4">


                    <!-- ACTIVE SERVICES -->

                    <div class="col-md-4">

                        <a
                            href="../student/marketplace.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🛒 Marketplace Services

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $marketplace_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Active services

                                    </p>


                                    <small class="text-primary">

                                        Browse Marketplace →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- AVAILABLE -->

                    <div class="col-md-4">

                        <a
                            href="../student/marketplace.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🟢 Available Services

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-success">

                                        <?= $available_services_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Currently available

                                    </p>


                                    <small class="text-primary">

                                        View Available Services →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- MY SERVICES -->

                    <div class="col-md-4">

                        <a
                            href="../student/my_services.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        💼 My Services

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $my_services_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Services offered by you

                                    </p>


                                    <small class="text-primary">

                                        Manage My Services →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>



                <!-- =================================================
                     SERVICE REQUESTS
                ================================================== -->

                <hr class="my-5">


                <h4 class="section-title mb-3">

                    📋 Service Request Management

                </h4>


                <div class="row g-4">


                    <!-- TOTAL REQUESTS -->

                    <div class="col-md-4">

                        <a
                            href="../student/service_requests.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        📋 Total Requests

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $total_service_requests; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        All service requests

                                    </p>


                                    <small class="text-primary">

                                        View Requests →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- PENDING -->

                    <div class="col-md-4">

                        <a
                            href="../student/service_requests.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⏳ Pending Requests

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-warning">

                                        <?= $pending_service_requests; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Waiting for action

                                    </p>


                                    <small class="text-primary">

                                        Manage Requests →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- ACCEPTED -->

                    <div class="col-md-4">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body">

                                <h5 class="text-dark">

                                    👍 Accepted

                                </h5>


                                <div
                                    class="stat-number
                                           text-success">

                                    <?= $accepted_service_requests; ?>

                                </div>


                                <p class="text-muted mb-0">

                                    Accepted requests

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- IN PROGRESS -->

                    <div class="col-md-4">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body">

                                <h5 class="text-dark">

                                    🔄 In Progress

                                </h5>


                                <div
                                    class="stat-number
                                           text-primary">

                                    <?= $in_progress_service_requests; ?>

                                </div>


                                <p class="text-muted mb-0">

                                    Services being completed

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- COMPLETED -->

                    <div class="col-md-4">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body">

                                <h5 class="text-dark">

                                    ✅ Completed

                                </h5>


                                <div
                                    class="stat-number
                                           text-success">

                                    <?= $completed_service_requests; ?>

                                </div>


                                <p class="text-muted mb-0">

                                    Completed services

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- CONFIRMED -->

                    <div class="col-md-4">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body">

                                <h5 class="text-dark">

                                    ✔ Confirmed

                                </h5>


                                <div
                                    class="stat-number
                                           text-success">

                                    <?= $confirmed_service_requests; ?>

                                </div>


                                <p class="text-muted mb-0">

                                    Student-confirmed services

                                </p>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     MY SERVICE PERFORMANCE
                ================================================== -->

                <hr class="my-5">


                <h4 class="section-title mb-3">

                    👨‍🏫 My Marketplace Performance

                </h4>


                <div class="row g-4">


                    <!-- REQUESTS RECEIVED -->

                    <div class="col-md-3">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">
                                    📩
                                </div>

                                <div
                                    class="stat-number
                                           text-primary">

                                    <?= $lecturer_requests_count; ?>

                                </div>

                                <p class="text-muted mb-0">

                                    Requests Received

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- PENDING -->

                    <div class="col-md-3">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">
                                    ⏳
                                </div>

                                <div
                                    class="stat-number
                                           text-warning">

                                    <?= $lecturer_pending_requests; ?>

                                </div>

                                <p class="text-muted mb-0">

                                    Pending

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- IN PROGRESS -->

                    <div class="col-md-3">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">
                                    🔄
                                </div>

                                <div
                                    class="stat-number
                                           text-primary">

                                    <?= $lecturer_in_progress; ?>

                                </div>

                                <p class="text-muted mb-0">

                                    In Progress

                                </p>

                            </div>

                        </div>

                    </div>



                    <!-- COMPLETED -->

                    <div class="col-md-3">

                        <div
                            class="card
                                   dashboard-card
                                   shadow-sm
                                   h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">
                                    ✅
                                </div>

                                <div
                                    class="stat-number
                                           text-success">

                                    <?= $lecturer_completed; ?>

                                </div>

                                <p class="text-muted mb-0">

                                    Completed

                                </p>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     COLLABORATIONS
                ================================================== -->

                <hr class="my-5">


                <h4 class="section-title mb-3">

                    🤝 Collaboration Overview

                </h4>


                <div class="row g-4">


                    <div class="col-md-6">

                        <a
                            href="collaboration_requests.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⏳ Pending Collaborations

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-warning">

                                        <?= $pending_collaboration_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Awaiting response

                                    </p>


                                    <small class="text-primary">

                                        View Requests →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <div class="col-md-6">

                        <a
                            href="collaborations.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🤝 Accepted Collaborations

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-success">

                                        <?= $accepted_collaboration_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Active collaborations

                                    </p>


                                    <small class="text-primary">

                                        View Collaborations →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>



                <!-- =================================================
                     REVIEWS & NOTIFICATIONS
                ================================================== -->

                <hr class="my-5">


                <div class="row g-4">


                    <!-- REVIEWS -->

                    <div class="col-md-6">

                        <a
                            href="reviews.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        ⭐ Student Reviews

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-primary">

                                        <?= $review_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Reviews in the system

                                    </p>


                                    <small class="text-primary">

                                        View Reviews →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>



                    <!-- NOTIFICATIONS -->

                    <div class="col-md-6">

                        <a
                            href="notifications.php"
                            class="text-decoration-none">

                            <div
                                class="card
                                       dashboard-card
                                       shadow-sm
                                       h-100">

                                <div class="card-body">

                                    <h5 class="text-dark">

                                        🔔 Notifications

                                    </h5>


                                    <div
                                        class="stat-number
                                               text-danger">

                                        <?= $notification_count; ?>

                                    </div>


                                    <p class="text-muted mb-0">

                                        Unread notifications

                                    </p>


                                    <small class="text-primary">

                                        View Notifications →

                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>



                <!-- =================================================
                     QUICK MANAGEMENT
                ================================================== -->

                <div class="card mt-5 shadow-sm">

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
                                    class="btn
                                           btn-outline-primary
                                           w-100">

                                    👨‍🎓 Manage Students

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="skills.php"
                                    class="btn
                                           btn-outline-success
                                           w-100">

                                    🏅 Verify Skills

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="../student/marketplace.php"
                                    class="btn
                                           btn-outline-primary
                                           w-100">

                                    🛒 Marketplace

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="../student/my_services.php"
                                    class="btn
                                           btn-outline-success
                                           w-100">

                                    💼 My Services

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="../student/service_requests.php"
                                    class="btn
                                           btn-outline-warning
                                           w-100">

                                    📋 Service Requests

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="study_groups.php"
                                    class="btn
                                           btn-outline-primary
                                           w-100">

                                    📚 Study Groups

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="resources.php"
                                    class="btn
                                           btn-outline-primary
                                           w-100">

                                    📁 Learning Resources

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="collaboration_requests.php"
                                    class="btn
                                           btn-outline-warning
                                           w-100">

                                    🤝 Collaboration Requests

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="reviews.php"
                                    class="btn
                                           btn-outline-secondary
                                           w-100">

                                    ⭐ Student Reviews

                                </a>

                            </div>


                            <div class="col-md-4">

                                <a
                                    href="notifications.php"
                                    class="btn
                                           btn-outline-danger
                                           w-100">

                                    🔔 Notifications

                                    <?php if (
                                        $notification_count > 0
                                    ): ?>

                                        <span
                                            class="badge bg-danger">

                                            <?= $notification_count; ?>

                                        </span>

                                    <?php endif; ?>

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
                    and verifying skills, managing marketplace
                    services and service requests, supporting
                    study groups, reviewing learning resources,
                    monitoring collaborations and viewing
                    student reviews.

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