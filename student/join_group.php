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
    header("Location: study_groups.php");
    exit();
}

verify_csrf();


/*
|--------------------------------------------------------------------------
| Get Current User
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Group ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_POST['id']) ||
    !is_numeric($_POST['id'])
) {

    die("Invalid study group.");

}

$group_id = (int) $_POST['id'];


/*
|--------------------------------------------------------------------------
| Get Study Group
|--------------------------------------------------------------------------
*/

$group_sql = "SELECT
                  id,
                  group_name,
                  creator_id,
                  status

              FROM study_groups

              WHERE id = ?

              LIMIT 1";


$group_stmt = mysqli_prepare(
    $conn,
    $group_sql
);


if (!$group_stmt) {

    die(
        "Unable to check study group: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $group_stmt,
    "i",
    $group_id
);


mysqli_stmt_execute(
    $group_stmt
);


$group_result =
    mysqli_stmt_get_result(
        $group_stmt
    );


$group =
    mysqli_fetch_assoc(
        $group_result
    );


/*
|--------------------------------------------------------------------------
| Check Group Exists
|--------------------------------------------------------------------------
*/

if (!$group) {

    die("Study group not found.");

}


/*
|--------------------------------------------------------------------------
| Only Approved Groups Can Be Joined
|--------------------------------------------------------------------------
*/

if ($group['status'] !== 'Approved') {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&error=not_approved"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Check Existing Membership
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id
              FROM study_group_members
              WHERE group_id = ?
              AND user_id = ?
              LIMIT 1";


$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);


if (!$check_stmt) {

    die(
        "Unable to check membership: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $check_stmt,
    "ii",
    $group_id,
    $user_id
);


mysqli_stmt_execute(
    $check_stmt
);


$check_result =
    mysqli_stmt_get_result(
        $check_stmt
    );


/*
|--------------------------------------------------------------------------
| Already a Member
|--------------------------------------------------------------------------
*/

if (
    mysqli_num_rows(
        $check_result
    ) > 0
) {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&already_member=1"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Add Student to Study Group
|--------------------------------------------------------------------------
*/

$insert_sql = "INSERT INTO study_group_members
               (group_id, user_id)
               VALUES (?, ?)";


$insert_stmt = mysqli_prepare(
    $conn,
    $insert_sql
);


if (!$insert_stmt) {

    die(
        "Unable to join study group: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $insert_stmt,
    "ii",
    $group_id,
    $user_id
);


/*
|--------------------------------------------------------------------------
| Execute Membership Insert
|--------------------------------------------------------------------------
*/

if (
    mysqli_stmt_execute(
        $insert_stmt
    )
) {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&joined=1"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Handle Database Error
|--------------------------------------------------------------------------
*/

die(
    "Unable to join study group: " .
    mysqli_stmt_error($insert_stmt)
);

?>