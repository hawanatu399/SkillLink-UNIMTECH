<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Mark All Notifications as Read
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['mark_all']) &&
    $_GET['mark_all'] === '1'
) {

    $mark_all_sql = "UPDATE notifications
                     SET is_read = 1
                     WHERE user_id = ?";

    $mark_all_stmt = mysqli_prepare(
        $conn,
        $mark_all_sql
    );

    if ($mark_all_stmt) {

        mysqli_stmt_bind_param(
            $mark_all_stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute(
            $mark_all_stmt
        );

    }

    header(
        "Location: notifications.php"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Get Notifications
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            type,
            message,
            related_id,
            is_read,
            created_at

        FROM notifications

        WHERE user_id = ?

        ORDER BY created_at DESC";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to load notifications: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute(
    $stmt
);


$result = mysqli_stmt_get_result(
    $stmt
);


/*
|--------------------------------------------------------------------------
| Count Unread Notifications
|--------------------------------------------------------------------------
*/

$unread_sql = "SELECT COUNT(*) AS unread_count
               FROM notifications
               WHERE user_id = ?
               AND is_read = 0";


$unread_stmt = mysqli_prepare(
    $conn,
    $unread_sql
);


$unread_count = 0;


if ($unread_stmt) {

    mysqli_stmt_bind_param(
        $unread_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute(
        $unread_stmt
    );

    $unread_result =
        mysqli_stmt_get_result(
            $unread_stmt
        );

    $unread_row =
        mysqli_fetch_assoc(
            $unread_result
        );

    $unread_count =
        (int) $unread_row['unread_count'];

}


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
                     HEADER
                ================================================== -->

                <div class="d-flex justify-content-between
                            align-items-center flex-wrap">

                    <div>

                        <h2>
                            🔔 Notifications
                        </h2>

                        <p class="text-muted mb-0">

                            View your latest SkillLink UNIMTECH
                            notifications.

                        </p>

                    </div>


                    <?php if ($unread_count > 0): ?>

                        <div class="mt-2">

                            <span
                                class="badge bg-danger fs-6">

                                <?= $unread_count; ?>
                                Unread

                            </span>

                        </div>

                    <?php endif; ?>

                </div>


                <hr>


                <!-- =================================================
                     MARK ALL AS READ
                ================================================== -->

                <?php if ($unread_count > 0): ?>

                    <div class="mb-4">

                        <a
                            href="notifications.php?mark_all=1"
                            class="btn btn-outline-primary btn-sm">

                            ✓ Mark All as Read

                        </a>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     NOTIFICATIONS
                ================================================== -->

                <?php if (
                    $result &&
                    mysqli_num_rows($result) > 0
                ): ?>


                    <?php while (
                        $notification =
                        mysqli_fetch_assoc($result)
                    ): ?>


                        <?php

                        $is_unread =
                            ((int) $notification['is_read'] === 0);

                        ?>


                        <div
                            class="card mb-3
                            <?= $is_unread
                                ? 'border-primary'
                                : 'border-light'; ?>">


                            <div class="card-body">


                                <div class="d-flex
                                            justify-content-between
                                            align-items-start">


                                    <div class="flex-grow-1">


                                        <!-- TYPE -->

                                        <?php if (
                                            $notification['type']
                                            === 'collaboration'
                                        ): ?>

                                            <span
                                                class="badge bg-primary mb-2">

                                                🤝 Collaboration

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-secondary mb-2">

                                                🔔 Notification

                                            </span>

                                        <?php endif; ?>


                                        <!-- MESSAGE -->

                                        <p class="mb-2">

                                            <?= htmlspecialchars(
                                                $notification['message']
                                            ); ?>

                                        </p>


                                        <!-- DATE -->

                                        <small class="text-muted">

                                            📅

                                            <?= htmlspecialchars(
                                                $notification['created_at']
                                            ); ?>

                                        </small>


                                    </div>


                                    <!-- UNREAD INDICATOR -->

                                    <?php if ($is_unread): ?>

                                        <span
                                            class="badge bg-danger">

                                            New

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge bg-secondary">

                                            Read

                                        </span>

                                    <?php endif; ?>


                                </div>


                                <!-- =================================================
                                     ACTION
                                ================================================== -->

                                <?php if (
                                    $notification['type']
                                    === 'collaboration' &&
                                    !empty(
                                        $notification['related_id']
                                    )
                                ): ?>


                                    <div class="mt-3">


                                        <a
                                            href="view_notification.php?id=<?= (int) $notification['id']; ?>"
                                            class="btn btn-outline-primary btn-sm">

                                            👁 View

                                        </a>


                                    </div>


                                <?php endif; ?>


                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="alert alert-info">

                        🔔 You currently have no notifications.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>