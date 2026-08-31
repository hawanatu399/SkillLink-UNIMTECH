<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid service request.");
}

$request_id = (int) $_GET['id'];


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

mysqli_stmt_bind_param($stmt, "i", $request_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$request = mysqli_fetch_assoc($result);

if (!$request) {
    die("Service request not found.");
}


/*
|--------------------------------------------------------------------------
| Only Provider Can Complete
|--------------------------------------------------------------------------
*/

if ((int) $request['provider_id'] !== $user_id) {
    die("You are not authorized to complete this service.");
}


/*
|--------------------------------------------------------------------------
| Must Be In Progress
|--------------------------------------------------------------------------
*/

if ($request['status'] !== 'In Progress') {
    die("Only services currently in progress can be marked completed.");
}


/*
|--------------------------------------------------------------------------
| Mark Completed
|--------------------------------------------------------------------------
*/

$update_sql = "UPDATE service_requests
               SET status = 'Completed'
               WHERE id = ?
               AND provider_id = ?
               AND status = 'In Progress'";

$update_stmt = mysqli_prepare($conn, $update_sql);

if (!$update_stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $update_stmt,
    "ii",
    $request_id,
    $user_id
);

if (!mysqli_stmt_execute($update_stmt)) {
    die("Unable to complete service.");
}


/*
|--------------------------------------------------------------------------
| Notify Student
|--------------------------------------------------------------------------
*/

$type = "service_request";

$message =
    "The service provider has marked '"
    . $request['title']
    . "' as completed. Please confirm completion.";

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
    mysqli_prepare($conn, $notification_sql);

if ($notification_stmt) {

    mysqli_stmt_bind_param(
        $notification_stmt,
        "issi",
        $request['requester_id'],
        $type,
        $message,
        $request_id
    );

    mysqli_stmt_execute($notification_stmt);
}


header(
    "Location: service_requests.php?completed=1"
);

exit();

?>