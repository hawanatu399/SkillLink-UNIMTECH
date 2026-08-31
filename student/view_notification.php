<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Notification ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid notification.");
}

$notification_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Notification
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

        WHERE id = ?

        AND user_id = ?

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die(
        "Unable to load notification: " .
        mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $notification_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$notification = mysqli_fetch_assoc($result);

if (!$notification) {
    die("Notification not found.");
}


/*
|--------------------------------------------------------------------------
| Mark Notification as Read
|--------------------------------------------------------------------------
*/

$read_sql = "UPDATE notifications

             SET is_read = 1

             WHERE id = ?

             AND user_id = ?";

$read_stmt = mysqli_prepare(
    $conn,
    $read_sql
);

if ($read_stmt) {

    mysqli_stmt_bind_param(
        $read_stmt,
        "ii",
        $notification_id,
        $user_id
    );

    mysqli_stmt_execute($read_stmt);
}


/*
|--------------------------------------------------------------------------
| Get Notification Type
|--------------------------------------------------------------------------
*/

$type = strtolower(
    trim(
        $notification['type']
    )
);

$related_id = (int) (
    $notification['related_id'] ?? 0
);


/*
|--------------------------------------------------------------------------
| Collaboration Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'collaboration'
) {

    header(
        "Location: collaboration_requests.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Marketplace Service Request Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'service_request'
) {

    header(
        "Location: service_requests.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Review Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'review'
) {

    /*
    |--------------------------------------------------------------------------
    | Find Review
    |--------------------------------------------------------------------------
    */

    if ($related_id > 0) {

        $review_sql =
            "SELECT
                 reviewed_user_id

             FROM reviews

             WHERE collaboration_id = ?

             ORDER BY created_at DESC

             LIMIT 1";

        $review_stmt = mysqli_prepare(
            $conn,
            $review_sql
        );

        if ($review_stmt) {

            mysqli_stmt_bind_param(
                $review_stmt,
                "i",
                $related_id
            );

            mysqli_stmt_execute(
                $review_stmt
            );

            $review_result =
                mysqli_stmt_get_result(
                    $review_stmt
                );

            $review =
                mysqli_fetch_assoc(
                    $review_result
                );

            if (
                $review &&
                !empty(
                    $review['reviewed_user_id']
                )
            ) {

                header(
                    "Location: view_profile.php?id="
                    . (int)
                    $review['reviewed_user_id']
                );

                exit();

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Review Not Found
    |--------------------------------------------------------------------------
    */

    header(
        "Location: notifications.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Skill Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'skill'
) {

    header(
        "Location: skills.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Resource Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'resource'
) {

    header(
        "Location: resources.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Study Group Notification
|--------------------------------------------------------------------------
*/

if (
    $type === 'group'
) {

    header(
        "Location: study_groups.php"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Default Notification
|--------------------------------------------------------------------------
*/

header(
    "Location: notifications.php"
);

exit();

?>