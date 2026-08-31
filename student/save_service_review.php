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

    header("Location: service_requests.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Get Input
|--------------------------------------------------------------------------
*/

$request_id = (int) ($_POST['request_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if ($request_id <= 0) {

    die("Invalid service request.");

}

if ($rating < 1 || $rating > 5) {

    die("Rating must be between 1 and 5.");

}

if ($comment === '') {

    die("Please write a review.");

}


/*
|--------------------------------------------------------------------------
| Get Confirmed Service
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            service_requests.id,
            service_requests.requester_id,
            service_requests.provider_id,
            service_requests.status,
            marketplace_services.title,
            users.full_name AS provider_name

        FROM service_requests

        INNER JOIN marketplace_services
            ON service_requests.service_id =
               marketplace_services.id

        INNER JOIN users
            ON service_requests.provider_id =
               users.id

        WHERE service_requests.id = ?

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
| Only Requester Can Review
|--------------------------------------------------------------------------
*/

if (
    (int) $request['requester_id'] !== $user_id
) {

    die(
        "You are not authorized to review this service."
    );

}


/*
|--------------------------------------------------------------------------
| Service Must Be Confirmed
|--------------------------------------------------------------------------
*/

if (
    $request['status'] !== 'Confirmed'
) {

    die(
        "Only confirmed services can be reviewed."
    );

}


/*
|--------------------------------------------------------------------------
| Prevent Duplicate Review
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id

              FROM service_reviews

              WHERE service_request_id = ?

              LIMIT 1";

$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);

if (!$check_stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $check_stmt,
    "i",
    $request_id
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
        "You have already reviewed this service."
    );

}


/*
|--------------------------------------------------------------------------
| Provider
|--------------------------------------------------------------------------
*/

$provider_id =
    (int) $request['provider_id'];


/*
|--------------------------------------------------------------------------
| Save Review
|--------------------------------------------------------------------------
*/

$insert_sql = "INSERT INTO service_reviews

               (
                   service_request_id,
                   provider_id,
                   reviewer_id,
                   rating,
                   comment
               )

               VALUES (?, ?, ?, ?, ?)";

$insert_stmt =
    mysqli_prepare(
        $conn,
        $insert_sql
    );

if (!$insert_stmt) {

    die(
        "Unable to prepare review: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $insert_stmt,
    "iiiis",
    $request_id,
    $provider_id,
    $user_id,
    $rating,
    $comment
);


if (
    !mysqli_stmt_execute(
        $insert_stmt
    )
) {

    die(
        "Unable to save review: " .
        mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| Update Provider Reputation
|--------------------------------------------------------------------------
|
| Every rating contributes its rating value
| to the provider's reputation points.
|
*/

$reputation_sql = "UPDATE users

                   SET reputation_points =
                       COALESCE(reputation_points, 0)
                       + ?

                   WHERE id = ?";

$reputation_stmt =
    mysqli_prepare(
        $conn,
        $reputation_sql
    );

if ($reputation_stmt) {

    mysqli_stmt_bind_param(
        $reputation_stmt,
        "ii",
        $rating,
        $provider_id
    );

    mysqli_stmt_execute(
        $reputation_stmt
    );

}


/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

$notification_type =
    "review";

$notification_message =
    "You received a "
    . $rating
    . "-star review for '"
    . $request['title']
    . "' from a student.";


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

    /*
    | related_id stores the service request ID.
    */

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
    "Location: service_requests.php?reviewed=1"
);

exit();

?>