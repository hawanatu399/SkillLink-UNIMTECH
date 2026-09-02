<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Only POST requests allowed (state-changing action)
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: find_students.php");
    exit();
}

verify_csrf();


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$sender_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Receiver ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST['student_id']) ||
    !is_numeric($_POST['student_id'])
) {

    die("Invalid student selected.");

}

$receiver_id = (int) $_POST['student_id'];


/*
|--------------------------------------------------------------------------
| Prevent Self Collaboration Request
|--------------------------------------------------------------------------
*/

if ($sender_id === $receiver_id) {

    die(
        "You cannot send a collaboration request to yourself."
    );

}


/*
|--------------------------------------------------------------------------
| Check Receiver
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            full_name,
            role

        FROM users

        WHERE id = ?

        LIMIT 1";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to check student: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $receiver_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result(
    $stmt
);


$receiver = mysqli_fetch_assoc(
    $result
);


if (!$receiver) {

    die("Student not found.");

}


/*
|--------------------------------------------------------------------------
| Make Sure Receiver Is a Student
|--------------------------------------------------------------------------
*/

if ($receiver['role'] !== 'student') {

    die(
        "Collaboration requests can only be sent to students."
    );

}


/*
|--------------------------------------------------------------------------
| Check Existing Pending Request
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT
                  id

              FROM collaboration_requests

              WHERE sender_id = ?
              AND receiver_id = ?
              AND status = 'Pending'

              LIMIT 1";


$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);


if (!$check_stmt) {

    die(
        "Unable to check existing request: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $sender_id,
    $receiver_id
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

    header(
        "Location: view_profile.php?id=" .
        $receiver_id .
        "&request=already_pending"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Collaboration Message
|--------------------------------------------------------------------------
*/

$message =
    "I would like to collaborate with you on SkillLink UNIMTECH.";


/*
|--------------------------------------------------------------------------
| Insert Collaboration Request
|--------------------------------------------------------------------------
*/

$insert_sql = "INSERT INTO collaboration_requests
               (
                   sender_id,
                   receiver_id,
                   message,
                   status
               )

               VALUES (?, ?, ?, 'Pending')";


$insert_stmt = mysqli_prepare(
    $conn,
    $insert_sql
);


if (!$insert_stmt) {

    die(
        "Unable to create collaboration request: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $insert_stmt,
    "iis",
    $sender_id,
    $receiver_id,
    $message
);


if (
    !mysqli_stmt_execute(
        $insert_stmt
    )
) {

    die(
        "Unable to send collaboration request: " .
        mysqli_stmt_error($insert_stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Get Request ID
|--------------------------------------------------------------------------
*/

$request_id =
    mysqli_insert_id($conn);


/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

$notification_message =
    "You received a new collaboration request from a student.";


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
        "Collaboration request was created, " .
        "but the notification could not be prepared: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $notification_stmt,
    "issi",
    $receiver_id,
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
        "Collaboration request was created, " .
        "but the notification could not be created: " .
        mysqli_stmt_error($notification_stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Return to Student Profile
|--------------------------------------------------------------------------
*/

header(
    "Location: view_profile.php?id=" .
    $receiver_id .
    "&request=sent"
);

exit();

?>