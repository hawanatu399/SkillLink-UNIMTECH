<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Get Logged-In User
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Request ID and Action
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id']) ||
    !isset($_GET['action'])
) {

    die("Invalid collaboration request.");

}


$request_id = (int) $_GET['id'];

$action = strtolower(
    trim($_GET['action'])
);


/*
|--------------------------------------------------------------------------
| Only Accept or Reject
|--------------------------------------------------------------------------
*/

if (
    $action !== 'accept' &&
    $action !== 'reject'
) {

    die("Invalid collaboration action.");

}


/*
|--------------------------------------------------------------------------
| Convert Action to Database Status
|--------------------------------------------------------------------------
*/

$new_status = (
    $action === 'accept'
)
    ? 'Accepted'
    : 'Rejected';


/*
|--------------------------------------------------------------------------
| Get Request
|--------------------------------------------------------------------------
|
| Only the receiver of the request is allowed to
| accept or reject it.
|
|--------------------------------------------------------------------------
*/

$request_sql = "SELECT
                    id,
                    sender_id,
                    receiver_id,
                    status

                FROM collaboration_requests

                WHERE id = ?

                AND receiver_id = ?

                LIMIT 1";


$request_stmt = mysqli_prepare(
    $conn,
    $request_sql
);


if (!$request_stmt) {

    die(
        "Unable to check collaboration request: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $request_stmt,
    "ii",
    $request_id,
    $user_id
);


mysqli_stmt_execute(
    $request_stmt
);


$request_result =
    mysqli_stmt_get_result(
        $request_stmt
    );


$request =
    mysqli_fetch_assoc(
        $request_result
    );


/*
|--------------------------------------------------------------------------
| Request Not Found
|--------------------------------------------------------------------------
*/

if (!$request) {

    die(
        "Collaboration request not found or you are not authorized to manage it."
    );

}


/*
|--------------------------------------------------------------------------
| Make Sure Request Is Still Pending
|--------------------------------------------------------------------------
*/

if ($request['status'] !== 'Pending') {

    header(
        "Location: collaboration_requests.php?error=already_processed"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Update Collaboration Request
|--------------------------------------------------------------------------
*/

$update_sql = "UPDATE collaboration_requests

               SET status = ?

               WHERE id = ?

               AND receiver_id = ?

               AND status = 'Pending'";


$update_stmt = mysqli_prepare(
    $conn,
    $update_sql
);


if (!$update_stmt) {

    die(
        "Unable to update collaboration request: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $update_stmt,
    "sii",
    $new_status,
    $request_id,
    $user_id
);


if (
    !mysqli_stmt_execute(
        $update_stmt
    )
) {

    die(
        "Unable to update collaboration request: " .
        mysqli_stmt_error($update_stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Make Sure Request Was Updated
|--------------------------------------------------------------------------
*/

if (
    mysqli_stmt_affected_rows(
        $update_stmt
    ) <= 0
) {

    header(
        "Location: collaboration_requests.php?error=already_processed"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Create Notification for Sender
|--------------------------------------------------------------------------
*/

if ($new_status === 'Accepted') {

    $notification_message =
        "Your collaboration request was accepted.";

} else {

    $notification_message =
        "Your collaboration request was rejected.";

}


$notification_type =
    "collaboration";


$notification_sql = "INSERT INTO notifications
                     (
                         user_id,
                         type,
                         message,
                         related_id
                     )

                     VALUES (?, ?, ?, ?)";


$notification_stmt = mysqli_prepare(
    $conn,
    $notification_sql
);


if (!$notification_stmt) {

    die(
        "The collaboration request was updated, " .
        "but the notification could not be prepared: " .
        mysqli_error($conn)
    );

}


$sender_id =
    (int) $request['sender_id'];


mysqli_stmt_bind_param(
    $notification_stmt,
    "issi",
    $sender_id,
    $notification_type,
    $notification_message,
    $request_id
);


if (
    !mysqli_stmt_execute(
        $notification_stmt
    )
) {

    die(
        "The collaboration request was updated, " .
        "but the notification could not be created: " .
        mysqli_stmt_error($notification_stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Return to Collaboration Requests
|--------------------------------------------------------------------------
*/

header(
    "Location: collaboration_requests.php?updated=1&status=" .
    strtolower($new_status)
);

exit();

?>