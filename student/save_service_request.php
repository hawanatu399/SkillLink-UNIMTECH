<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| POST Only
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: marketplace.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Input
|--------------------------------------------------------------------------
*/

$service_id =
    (int) ($_POST['service_id'] ?? 0);

$message =
    trim($_POST['message'] ?? '');

$requested_deadline =
    trim(
        $_POST['requested_deadline'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| Validate
|--------------------------------------------------------------------------
*/

if (
    $service_id <= 0 ||
    $message === ''
) {

    die(
        "Please provide all required information."
    );

}


/*
|--------------------------------------------------------------------------
| Validate Deadline
|--------------------------------------------------------------------------
*/

if (
    $requested_deadline !== '' &&
    !preg_match(
        '/^\d{4}-\d{2}-\d{2}$/',
        $requested_deadline
    )
) {

    die("Invalid deadline.");

}


if (
    $requested_deadline !== '' &&
    $requested_deadline < date('Y-m-d')
) {

    die(
        "The requested deadline cannot be in the past."
    );

}


/*
|--------------------------------------------------------------------------
| Get Service Provider
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            provider_id,
            title,
            availability,
            status

        FROM marketplace_services

        WHERE id = ?

        LIMIT 1";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $service_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result(
        $stmt
    );

$service =
    mysqli_fetch_assoc(
        $result
    );


if (!$service) {

    die("Service not found.");

}


/*
|--------------------------------------------------------------------------
| Prevent Requesting Own Service
|--------------------------------------------------------------------------
*/

$provider_id =
    (int) $service['provider_id'];


if ($provider_id === $user_id) {

    die(
        "You cannot request your own service."
    );

}


/*
|--------------------------------------------------------------------------
| Check Service Availability
|--------------------------------------------------------------------------
*/

if (
    $service['status'] !== 'Active' ||
    $service['availability'] !== 'Available'
) {

    die(
        "This service is currently unavailable."
    );

}


/*
|--------------------------------------------------------------------------
| Prevent Duplicate Active Requests
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id

              FROM service_requests

              WHERE service_id = ?

              AND requester_id = ?

              AND status IN (
                  'Pending',
                  'Accepted',
                  'In Progress'
              )

              LIMIT 1";

$check_stmt =
    mysqli_prepare(
        $conn,
        $check_sql
    );

mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $service_id,
    $user_id
);

mysqli_stmt_execute(
    $check_stmt
);

$check_result =
    mysqli_stmt_get_result(
        $check_stmt
    );


if (
    mysqli_num_rows(
        $check_result
    ) > 0
) {

    die(
        "You already have an active request for this service."
    );

}


/*
|--------------------------------------------------------------------------
| Insert Service Request
|--------------------------------------------------------------------------
*/

$insert_sql = "INSERT INTO service_requests

               (
                   service_id,
                   requester_id,
                   provider_id,
                   message,
                   requested_deadline,
                   status
               )

               VALUES (?, ?, ?, ?, NULLIF(?, ''), 'Pending')";


$insert_stmt =
    mysqli_prepare(
        $conn,
        $insert_sql
    );


if (!$insert_stmt) {

    die(
        "Unable to prepare request: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $insert_stmt,
    "iiiss",
    $service_id,
    $user_id,
    $provider_id,
    $message,
    $requested_deadline
);


if (
    !mysqli_stmt_execute(
        $insert_stmt
    )
) {

    die(
        "Unable to send service request: " .
        mysqli_error($conn)
    );

}


$request_id =
    mysqli_insert_id($conn);


/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

$notification_type =
    "service_request";


$notification_message =
    "You received a new service request for: "
    . $service['title'];


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

    mysqli_stmt_bind_param(
        $notification_stmt,
        "issi",
        $provider_id,
        $notification_type,
        $notification_message,
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
    "Location: service_requests.php?sent=1"
);

exit();

?>