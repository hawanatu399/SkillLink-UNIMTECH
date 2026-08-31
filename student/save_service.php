<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Only POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: create_service.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Collect Input
|--------------------------------------------------------------------------
*/

$title =
    trim($_POST['title'] ?? '');

$skill_id =
    (int) ($_POST['skill_id'] ?? 0);

$category =
    trim($_POST['category'] ?? '');

$service_type =
    trim($_POST['service_type'] ?? '');

$description =
    trim($_POST['description'] ?? '');

$price =
    (float) ($_POST['price'] ?? 0);

$availability =
    trim($_POST['availability'] ?? 'Available');


/*
|--------------------------------------------------------------------------
| Validate
|--------------------------------------------------------------------------
*/

$allowed_types = [
    'Skill Exchange',
    'Student Service',
    'Academic Support',
    'Project Support'
];

$allowed_availability = [
    'Available',
    'Unavailable'
];


if (
    $title === '' ||
    $skill_id <= 0 ||
    $category === '' ||
    $description === ''
) {

    header(
        "Location: create_service.php?error=" .
        urlencode(
            "Please complete all required fields."
        )
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Validate Price
|--------------------------------------------------------------------------
*/

if ($price < 0) {

    header(
        "Location: create_service.php?error=" .
        urlencode(
            "Invalid service price."
        )
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Validate Service Type
|--------------------------------------------------------------------------
*/

if (!in_array(
    $service_type,
    $allowed_types,
    true
)) {

    header(
        "Location: create_service.php?error=" .
        urlencode(
            "Invalid service type."
        )
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Validate Availability
|--------------------------------------------------------------------------
*/

if (!in_array(
    $availability,
    $allowed_availability,
    true
)) {

    header(
        "Location: create_service.php?error=" .
        urlencode(
            "Invalid availability option."
        )
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Verify Skill Belongs to Current User
|--------------------------------------------------------------------------
*/

$skill_sql = "SELECT id
              FROM skills
              WHERE id = ?
              AND user_id = ?
              LIMIT 1";

$skill_stmt =
    mysqli_prepare(
        $conn,
        $skill_sql
    );

if (!$skill_stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $skill_stmt,
    "ii",
    $skill_id,
    $user_id
);

mysqli_stmt_execute(
    $skill_stmt
);

$skill_result =
    mysqli_stmt_get_result(
        $skill_stmt
    );

if (
    mysqli_num_rows(
        $skill_result
    ) === 0
) {

    die(
        "Invalid skill selected."
    );

}


/*
|--------------------------------------------------------------------------
| Insert Service
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO marketplace_services
        (
            provider_id,
            skill_id,
            title,
            category,
            description,
            price,
            service_type,
            availability,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );

if (!$stmt) {

    die(
        "Unable to create service: " .
        mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| Bind Parameters
|--------------------------------------------------------------------------
*/

mysqli_stmt_bind_param(
    $stmt,
    "iisssdss",
    $user_id,
    $skill_id,
    $title,
    $category,
    $description,
    $price,
    $service_type,
    $availability
);


/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

if (
    mysqli_stmt_execute($stmt)
) {

    header(
        "Location: marketplace.php?success=1"
    );

    exit();

}


die(
    "Unable to publish service: " .
    mysqli_error($conn)
);

?>