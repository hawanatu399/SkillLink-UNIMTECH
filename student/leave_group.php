<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Get Current Student
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Group ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid study group.");

}

$group_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Check That Group Exists
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


if (!$group) {

    die("Study group not found.");

}


/*
|--------------------------------------------------------------------------
| Only Approved Groups
|--------------------------------------------------------------------------
*/

if ($group['status'] !== 'Approved') {

    header(
        "Location: view_group.php?id=" .
        $group_id
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Prevent Group Creator From Leaving
|--------------------------------------------------------------------------
|
| The creator owns the study group and should remain
| as a member.
|
|--------------------------------------------------------------------------
*/

if (
    (int) $group['creator_id'] ===
    $user_id
) {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&error=creator_cannot_leave"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Check Membership
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


$membership =
    mysqli_fetch_assoc(
        $check_result
    );


/*
|--------------------------------------------------------------------------
| Student Is Not a Member
|--------------------------------------------------------------------------
*/

if (!$membership) {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&error=not_member"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Remove Membership
|--------------------------------------------------------------------------
*/

$delete_sql = "DELETE FROM study_group_members
               WHERE group_id = ?
               AND user_id = ?
               LIMIT 1";


$delete_stmt = mysqli_prepare(
    $conn,
    $delete_sql
);


if (!$delete_stmt) {

    die(
        "Unable to leave study group: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $delete_stmt,
    "ii",
    $group_id,
    $user_id
);


if (
    mysqli_stmt_execute(
        $delete_stmt
    )
) {

    header(
        "Location: view_group.php?id=" .
        $group_id .
        "&left=1"
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Database Error
|--------------------------------------------------------------------------
*/

die(
    "Unable to leave study group: " .
    mysqli_stmt_error($delete_stmt)
);

?>