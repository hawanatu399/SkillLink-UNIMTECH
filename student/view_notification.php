<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
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

$notification_id =
    (int) $_GET['id'];


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


$stmt = mysqli_prepare(
    $conn,
    $sql
);


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


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$notification =
    mysqli_fetch_assoc(
        $result
    );


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

    mysqli_stmt_execute(
        $read_stmt
    );

}


/*
|--------------------------------------------------------------------------
| Collaboration Notification
|--------------------------------------------------------------------------
*/

if (
    $notification['type'] === 'collaboration' &&
    !empty($notification['related_id'])
) {

    header(
        "Location: collaboration_requests.php"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Default Return
|--------------------------------------------------------------------------
*/

header(
    "Location: notifications.php"
);

exit();

?>