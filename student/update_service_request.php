<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Validate Request
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid service request.");
}

$request_id = (int) $_GET['id'];

$action = $_GET['action'] ?? '';

$allowed_actions = [
    'accept',
    'reject',
    'start'
];

if (!in_array($action, $allowed_actions, true)) {
    die("Invalid action.");
}


/*
|--------------------------------------------------------------------------
| Get Request
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            service_requests.id,
            service_requests.requester_id,
            service_requests.provider_id,
            service_requests.status,
            service_requests.service_id,

            marketplace_services.title

        FROM service_requests

        INNER JOIN marketplace_services
            ON service_requests.service_id =
               marketplace_services.id

        WHERE service_requests.id = ?

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$request = mysqli_fetch_assoc($result);

if (!$request) {
    die("Service request not found.");
}


/*
|--------------------------------------------------------------------------
| Only Provider Can Manage Request
|--------------------------------------------------------------------------
*/

if (
    (int) $request['provider_id'] !== $user_id
) {
    die("You are not authorized to manage this request.");
}


/*
|--------------------------------------------------------------------------
| Determine New Status
|--------------------------------------------------------------------------
*/

$current_status = $request['status'];

$new_status = '';

if ($action === 'accept') {

    if ($current_status !== 'Pending') {
        die("This request can no longer be accepted.");
    }

    $new_status = 'Accepted';

} elseif ($action === 'reject') {

    if ($current_status !== 'Pending') {
        die("This request can no longer be rejected.");
    }

    $new_status = 'Rejected';

} elseif ($action === 'start') {

    if ($current_status !== 'Accepted') {
        die("Only accepted requests can be started.");
    }

    $new_status = 'In Progress';
}


/*
|--------------------------------------------------------------------------
| Update Request
|--------------------------------------------------------------------------
*/

$update_sql = "UPDATE service_requests

               SET status = ?

               WHERE id = ?

               AND provider_id = ?";

$update_stmt = mysqli_prepare(
    $conn,
    $update_sql
);

if (!$update_stmt) {
    die(
        "Unable to update request: "
        . mysqli_error($conn)
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
        "Unable to update request: "
        . mysqli_error($conn)
    );
}


/*
|--------------------------------------------------------------------------
| Notification Message
|--------------------------------------------------------------------------
*/

if ($new_status === 'Accepted') {

    $message =
        "Your service request for '"
        . $request['title']
        . "' has been accepted.";

} elseif ($new_status === 'Rejected') {

    $message =
        "Your service request for '"
        . $request['title']
        . "' has been rejected.";

} else {

    $message =
        "Your service request for '"
        . $request['title']
        . "' has started.";

}


/*
|--------------------------------------------------------------------------
| Notify Requester
|--------------------------------------------------------------------------
*/

$notification_sql =
    "INSERT INTO notifications
    (
        user_id,
        type,
        message,
        related_id
    )

    VALUES (?, ?, ?, ?)";

$notification_stmt =
    mysqli_prepare(
        $conn,
        $notification_sql
    );

if ($notification_stmt) {

    $notification_type =
        "service_request";

    mysqli_stmt_bind_param(
        $notification_stmt,
        "issi",
        $request['requester_id'],
        $notification_type,
        $message,
        $request_id
    );

    mysqli_stmt_execute(
        $notification_stmt
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header(
    "Location: service_requests.php?updated=1"
);

exit();

?>