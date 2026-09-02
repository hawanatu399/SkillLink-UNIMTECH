<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Only POST requests allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: study_groups.php");
    exit();

}

verify_csrf();


$user_id = (int) $_SESSION['user_id'];


$group_name = trim(
    $_POST['group_name'] ?? ''
);

$category = trim(
    $_POST['category'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validate Input
|--------------------------------------------------------------------------
*/

if (
    $group_name === '' ||
    $category === '' ||
    $description === ''
) {

    die(
        "Please complete all study group fields."
    );

}


/*
|--------------------------------------------------------------------------
| Create Study Group
|--------------------------------------------------------------------------
|
| New groups are automatically set to Pending.
| A lecturer must approve the group before it
| becomes available to other students.
|
|--------------------------------------------------------------------------
*/

$status = 'Pending';


$sql = "INSERT INTO study_groups
        (
            creator_id,
            group_name,
            description,
            category,
            status
        )

        VALUES (?, ?, ?, ?, ?)";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to prepare study group: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "issss",
    $user_id,
    $group_name,
    $description,
    $category,
    $status
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Unable to create study group: " .
        mysqli_stmt_error($stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Get Newly Created Group ID
|--------------------------------------------------------------------------
*/

$group_id = mysqli_insert_id($conn);


/*
|--------------------------------------------------------------------------
| Automatically Add Creator as First Member
|--------------------------------------------------------------------------
*/

$member_sql = "INSERT INTO study_group_members
               (
                   group_id,
                   user_id
               )

               VALUES (?, ?)";


$member_stmt = mysqli_prepare(
    $conn,
    $member_sql
);


if (!$member_stmt) {

    die(
        "Group created, but membership could not be prepared: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $member_stmt,
    "ii",
    $group_id,
    $user_id
);


if (!mysqli_stmt_execute($member_stmt)) {

    die(
        "Group created, but membership could not be added: " .
        mysqli_stmt_error($member_stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Return to Study Groups
|--------------------------------------------------------------------------
*/

header(
    "Location: study_groups.php?created=1"
);

exit();

?>