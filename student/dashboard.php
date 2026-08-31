<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Logged-In User
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];

$full_name = $_SESSION['full_name'] ?? 'Student';


/*
|--------------------------------------------------------------------------
| Count Student Skills
|--------------------------------------------------------------------------
*/

$skills_count = 0;

$skills_sql = "SELECT COUNT(*) AS total
               FROM skills
               WHERE user_id = ?";

$skills_stmt = mysqli_prepare(
    $conn,
    $skills_sql
);

if ($skills_stmt) {

    mysqli_stmt_bind_param(
        $skills_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($skills_stmt);

    $skills_result =
        mysqli_stmt_get_result(
            $skills_stmt
        );

    $skills_row =
        mysqli_fetch_assoc(
            $skills_result
        );

    $skills_count =
        (int) $skills_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Study Groups
|--------------------------------------------------------------------------
*/

$groups_count = 0;

$groups_sql = "SELECT COUNT(*) AS total
               FROM study_group_members
               WHERE user_id = ?";

$groups_stmt = mysqli_prepare(
    $conn,
    $groups_sql
);

if ($groups_stmt) {

    mysqli_stmt_bind_param(
        $groups_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($groups_stmt);

    $groups_result =
        mysqli_stmt_get_result(
            $groups_stmt
        );

    $groups_row =
        mysqli_fetch_assoc(
            $groups_result
        );

    $groups_count =
        (int) $groups_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Uploaded Resources
|--------------------------------------------------------------------------
*/

$resources_count = 0;

$resources_sql = "SELECT COUNT(*) AS total
                  FROM resources
                  WHERE user_id = ?";

$resources_stmt = mysqli_prepare(
    $conn,
    $resources_sql
);

if ($resources_stmt) {

    mysqli_stmt_bind_param(
        $resources_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($resources_stmt);

    $resources_result =
        mysqli_stmt_get_result(
            $resources_stmt
        );

    $resources_row =
        mysqli_fetch_assoc(
            $resources_result
        );

    $resources_count =
        (int) $resources_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Accepted Collaborations
|--------------------------------------------------------------------------
*/

$collaborations_count = 0;

$collaborations_sql = "SELECT COUNT(*) AS total

                       FROM collaboration_requests

                       WHERE status = 'Accepted'

                       AND (
                           sender_id = ?
                           OR receiver_id = ?
                       )";

$collaborations_stmt =
    mysqli_prepare(
        $conn,
        $collaborations_sql
    );

if ($collaborations_stmt) {

    mysqli_stmt_bind_param(
        $collaborations_stmt,
        "ii",
        $user_id,
        $user_id
    );

    mysqli_stmt_execute(
        $collaborations_stmt
    );

    $collaborations_result =
        mysqli_stmt_get_result(
            $collaborations_stmt
        );

    $collaborations_row =
        mysqli_fetch_assoc(
            $collaborations_result
        );

    $collaborations_count =
        (int) $collaborations_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Unread Notifications
|--------------------------------------------------------------------------
*/

$notifications_count = 0;

$notifications_sql = "SELECT COUNT(*) AS total

                      FROM notifications

                      WHERE user_id = ?

                      AND is_read = 0";

$notifications_stmt =
    mysqli_prepare(
        $conn,
        $notifications_sql
    );

if ($notifications_stmt) {

    mysqli_stmt_bind_param(
        $notifications_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $notifications_stmt
    );

    $notifications_result =
        mysqli_stmt_get_result(
            $notifications_stmt
        );

    $notifications_row =
        mysqli_fetch_assoc(
            $notifications_result
        );

    $notifications_count =
        (int) $notifications_row['total'];
}


/*
|--------------------------------------------------------------------------
| Get Student Rating
|--------------------------------------------------------------------------
*/

$average_rating = 0;

$review_count = 0;

$rating_sql = "SELECT
                   AVG(rating) AS average_rating,
                   COUNT(*) AS review_count

               FROM reviews

               WHERE reviewed_user_id = ?";

$rating_stmt =
    mysqli_prepare(
        $conn,
        $rating_sql
    );

if ($rating_stmt) {

    mysqli_stmt_bind_param(
        $rating_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $rating_stmt
    );

    $rating_result =
        mysqli_stmt_get_result(
            $rating_stmt
        );

    $rating_row =
        mysqli_fetch_assoc(
            $rating_result
        );

    $average_rating =
        $rating_row['average_rating'] !== null
        ? round(
            (float) $rating_row['average_rating'],
            1
        )
        : 0;

    $review_count =
        (int) $rating_row['review_count'];
}


/*
|--------------------------------------------------------------------------
| MARKETPLACE STATISTICS
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Count Active Marketplace Services
|--------------------------------------------------------------------------
*/

$marketplace_count = 0;

$marketplace_sql = "SELECT COUNT(*) AS total

                    FROM marketplace_services

                    WHERE status = 'Active'

                    AND availability = 'Available'";

$marketplace_result =
    mysqli_query(
        $conn,
        $marketplace_sql
    );

if ($marketplace_result) {

    $marketplace_row =
        mysqli_fetch_assoc(
            $marketplace_result
        );

    $marketplace_count =
        (int) $marketplace_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count My Services
|--------------------------------------------------------------------------
*/

$my_services_count = 0;

$my_services_sql = "SELECT COUNT(*) AS total

                    FROM marketplace_services

                    WHERE provider_id = ?

                    AND status = 'Active'";

$my_services_stmt =
    mysqli_prepare(
        $conn,
        $my_services_sql
    );

if ($my_services_stmt) {

    mysqli_stmt_bind_param(
        $my_services_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $my_services_stmt
    );

    $my_services_result =
        mysqli_stmt_get_result(
            $my_services_stmt
        );

    $my_services_row =
        mysqli_fetch_assoc(
            $my_services_result
        );

    $my_services_count =
        (int) $my_services_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Requests Received
|--------------------------------------------------------------------------
*/

$requests_received_count = 0;

$requests_received_sql =
    "SELECT COUNT(*) AS total

     FROM service_requests

     WHERE provider_id = ?";

$requests_received_stmt =
    mysqli_prepare(
        $conn,
        $requests_received_sql
    );

if ($requests_received_stmt) {

    mysqli_stmt_bind_param(
        $requests_received_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $requests_received_stmt
    );

    $requests_received_result =
        mysqli_stmt_get_result(
            $requests_received_stmt
        );

    $requests_received_row =
        mysqli_fetch_assoc(
            $requests_received_result
        );

    $requests_received_count =
        (int) $requests_received_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Requests Sent
|--------------------------------------------------------------------------
*/

$requests_sent_count = 0;

$requests_sent_sql =
    "SELECT COUNT(*) AS total

     FROM service_requests

     WHERE requester_id = ?";

$requests_sent_stmt =
    mysqli_prepare(
        $conn,
        $requests_sent_sql
    );

if ($requests_sent_stmt) {

    mysqli_stmt_bind_param(
        $requests_sent_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $requests_sent_stmt
    );

    $requests_sent_result =
        mysqli_stmt_get_result(
            $requests_sent_stmt
        );

    $requests_sent_row =
        mysqli_fetch_assoc(
            $requests_sent_result
        );

    $requests_sent_count =
        (int) $requests_sent_row['total'];
}


/*
|--------------------------------------------------------------------------
| Count Marketplace Reviews Received
|--------------------------------------------------------------------------
*/

$marketplace_reviews_count = 0;

$marketplace_reviews_sql =
    "SELECT COUNT(*) AS total

     FROM service_reviews

     WHERE provider_id = ?";

$marketplace_reviews_stmt =
    mysqli_prepare(
        $conn,
        $marketplace_reviews_sql
    );

if ($marketplace_reviews_stmt) {

    mysqli_stmt_bind_param(
        $marketplace_reviews_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $marketplace_reviews_stmt
    );

    $marketplace_reviews_result =
        mysqli_stmt_get_result(
            $marketplace_reviews_stmt
        );

    $marketplace_reviews_row =
        mysqli_fetch_assoc(
            $marketplace_reviews_result
        );

    $marketplace_reviews_count =
        (int) $marketplace_reviews_row['total'];
}


/*
|--------------------------------------------------------------------------
| Get Recent Notifications
|--------------------------------------------------------------------------
*/

$recent_notifications_sql = "SELECT
                                 id,
                                 type,
                                 message,
                                 is_read,
                                 created_at

                             FROM notifications

                             WHERE user_id = ?

                             ORDER BY created_at DESC

                             LIMIT 5";

$recent_notifications_stmt =
    mysqli_prepare(
        $conn,
        $recent_notifications_sql
    );

$recent_notifications = [];

if ($recent_notifications_stmt) {

    mysqli_stmt_bind_param(
        $recent_notifications_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $recent_notifications_stmt
    );

    $recent_result =
        mysqli_stmt_get_result(
            $recent_notifications_stmt
        );

    while (
        $notification =
        mysqli_fetch_assoc(
            $recent_result
        )
    ) {

        $recent_notifications[] =
            $notification;
    }
}


/*
|--------------------------------------------------------------------------
| Page Includes
|--------------------------------------------------------------------------
*/

include "../templates/header.php";
include "../templates/navbar.php";

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =====================================================
             MAIN CONTENT
        ====================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card p-4">


                <!-- =================================================
                     WELCOME
                ================================================== -->

                <div class="mb-4">

                    <h2>

                        Welcome,

                        <?= htmlspecialchars(
                            $full_name
                        ); ?>

                        🎉

                    </h2>

                    <p class="text-muted">

                        Welcome to your SkillLink UNIMTECH
                        student dashboard.

                    </p>

                </div>


                <hr>


                <!-- =================================================
                     GENERAL STATISTICS
                ================================================== -->

                <h5 class="mb-3">
                    📊 My Academic & Community Overview
                </h5>

                <div class="row g-4 mt-2">


                    <!-- SKILLS -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="skills.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        💡
                                    </div>

                                    <h3>
                                        <?= $skills_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Skills
                                    </p>

                                    <small class="text-primary">
                                        View Skills →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- COLLABORATIONS -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="collaborations.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        🤝
                                    </div>

                                    <h3>
                                        <?= $collaborations_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Collaborations
                                    </p>

                                    <small class="text-primary">
                                        View Collaborations →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- STUDY GROUPS -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="study_groups.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        📚
                                    </div>

                                    <h3>
                                        <?= $groups_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Groups
                                    </p>

                                    <small class="text-primary">
                                        View Groups →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- RESOURCES -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="resources.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        📁
                                    </div>

                                    <h3>
                                        <?= $resources_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Resources
                                    </p>

                                    <small class="text-primary">
                                        View Resources →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- NOTIFICATIONS -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="notifications.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        🔔
                                    </div>

                                    <h3>
                                        <?= $notifications_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Unread Alerts
                                    </p>

                                    <small class="text-primary">
                                        View Notifications →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- RATING -->

                    <div class="col-md-4 col-lg-2">

                        <a
                            href="view_profile.php?id=<?= $user_id; ?>"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        ⭐
                                    </div>

                                    <h3>

                                        <?php if (
                                            $review_count > 0
                                        ): ?>

                                            <?= number_format(
                                                $average_rating,
                                                1
                                            ); ?>

                                        <?php else: ?>

                                            0

                                        <?php endif; ?>

                                    </h3>

                                    <p class="text-muted mb-0">
                                        Rating
                                    </p>

                                    <small class="text-primary">
                                        View Reviews →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     MARKETPLACE STATISTICS
                ================================================== -->

                <hr class="my-4">

                <h5 class="mb-3">
                    🛒 Marketplace Overview
                </h5>


                <div class="row g-4">


                    <!-- MARKETPLACE -->

                    <div class="col-md-6 col-lg-3">

                        <a
                            href="marketplace.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        🛒
                                    </div>

                                    <h3>
                                        <?= $marketplace_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Available Services
                                    </p>

                                    <small class="text-primary">
                                        Browse Marketplace →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- MY SERVICES -->

                    <div class="col-md-6 col-lg-3">

                        <a
                            href="my_services.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        💼
                                    </div>

                                    <h3>
                                        <?= $my_services_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        My Services
                                    </p>

                                    <small class="text-primary">
                                        Manage Services →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- REQUESTS RECEIVED -->

                    <div class="col-md-6 col-lg-3">

                        <a
                            href="service_requests.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        📩
                                    </div>

                                    <h3>
                                        <?= $requests_received_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Requests Received
                                    </p>

                                    <small class="text-primary">
                                        Manage Requests →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- REQUESTS SENT -->

                    <div class="col-md-6 col-lg-3">

                        <a
                            href="service_requests.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        📤
                                    </div>

                                    <h3>
                                        <?= $requests_sent_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Requests Sent
                                    </p>

                                    <small class="text-primary">
                                        View My Requests →
                                    </small>

                                </div>

                            </div>

                        </a>

                    </div>


                    <!-- REVIEWS RECEIVED -->

                    <div class="col-md-6 col-lg-3">

                        <a
                            href="notifications.php"
                            class="text-decoration-none text-dark">

                            <div class="card text-center shadow-sm h-100">

                                <div class="card-body">

                                    <div class="fs-1">
                                        ⭐
                                    </div>

                                    <h3>
                                        <?= $marketplace_reviews_count; ?>
                                    </h3>

                                    <p class="text-muted mb-0">
                                        Reviews Received
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
                     QUICK ACTIONS
                ================================================== -->

                <hr class="my-4">


                <h4>
                    ⚡ Quick Actions
                </h4>


                <div class="row g-3 mt-2">


                    <div class="col-md-4">

                        <a
                            href="marketplace.php"
                            class="btn btn-outline-success w-100">

                            🛒 Browse Marketplace

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="create_service.php"
                            class="btn btn-outline-success w-100">

                            ➕ Offer a Service

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="my_services.php"
                            class="btn btn-outline-primary w-100">

                            💼 My Services

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="service_requests.php"
                            class="btn btn-outline-primary w-100">

                            📩 Service Requests

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="skills.php"
                            class="btn btn-outline-primary w-100">

                            💡 My Skills

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
                            class="btn btn-outline-success w-100">

                            🤝 Collaboration Requests

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="notifications.php"
                            class="btn btn-outline-warning w-100">

                            🔔 Notifications

                            <?php if (
                                $notifications_count > 0
                            ): ?>

                                <span class="badge bg-danger">

                                    <?= $notifications_count; ?>

                                </span>

                            <?php endif; ?>

                        </a>

                    </div>


                    <div class="col-md-4">

                        <a
                            href="view_profile.php?id=<?= $user_id; ?>"
                            class="btn btn-outline-secondary w-100">

                            👤 My Profile

                        </a>

                    </div>


                </div>


                <!-- =================================================
                     RECENT NOTIFICATIONS
                ================================================== -->

                <hr class="my-4">


                <div
                    class="d-flex
                           justify-content-between
                           align-items-center">

                    <h4 class="mb-0">
                        🔔 Recent Notifications
                    </h4>


                    <a
                        href="notifications.php"
                        class="btn btn-sm btn-outline-primary">

                        View All

                    </a>

                </div>


                <div class="mt-3">


                    <?php if (
                        count(
                            $recent_notifications
                        ) > 0
                    ): ?>


                        <?php foreach (
                            $recent_notifications
                            as $notification
                        ): ?>


                            <div
                                class="alert
                                <?= (int)
                                    $notification['is_read'] === 0
                                    ? 'alert-primary'
                                    : 'alert-light'; ?>">


                                <div
                                    class="d-flex
                                           justify-content-between">


                                    <div>


                                        <?php if (
                                            $notification['type']
                                            === 'collaboration'
                                        ): ?>

                                            🤝

                                        <?php elseif (
                                            $notification['type']
                                            === 'review'
                                        ): ?>

                                            ⭐

                                        <?php elseif (
                                            $notification['type']
                                            === 'skill'
                                        ): ?>

                                            💡

                                        <?php elseif (
                                            $notification['type']
                                            === 'resource'
                                        ): ?>

                                            📁

                                        <?php elseif (
                                            $notification['type']
                                            === 'group'
                                        ): ?>

                                            📚

                                        <?php elseif (
                                            $notification['type']
                                            === 'service_request'
                                        ): ?>

                                            🛒

                                        <?php else: ?>

                                            🔔

                                        <?php endif; ?>


                                        <strong>

                                            <?= htmlspecialchars(
                                                $notification['message']
                                            ); ?>

                                        </strong>


                                    </div>


                                    <?php if (
                                        (int)
                                        $notification['is_read']
                                        === 0
                                    ): ?>

                                        <span
                                            class="badge bg-danger">

                                            New

                                        </span>

                                    <?php endif; ?>


                                </div>


                                <small
                                    class="text-muted">

                                    <?= htmlspecialchars(
                                        $notification['created_at']
                                    ); ?>

                                </small>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div class="alert alert-info">

                            🔔 You currently have no
                            notifications.

                        </div>


                    <?php endif; ?>


                </div>


                <!-- =================================================
                     PROJECT INFORMATION
                ================================================== -->

                <hr class="my-4">


                <div class="alert alert-light border">

                    <h5>
                        🎓 SkillLink UNIMTECH
                    </h5>


                    <p class="mb-0">

                        A web-based student skills exchange and
                        service marketplace designed to help
                        university students discover skills,
                        collaborate, share learning resources,
                        form study groups and build academic
                        connections.

                    </p>

                </div>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>