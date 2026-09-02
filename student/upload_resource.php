<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Make sure request is POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: resources.php");

    exit();

}

verify_csrf();


$user_id = (int) $_SESSION['user_id'];

$title = trim(
    $_POST['title'] ?? ''
);

$category = trim(
    $_POST['category'] ?? ''
);

$description = trim(
    $_POST['description'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validate Text Fields
|--------------------------------------------------------------------------
*/

if (
    $title === '' ||
    $category === '' ||
    $description === ''
) {

    die(
        "Please complete all resource fields."
    );

}


/*
|--------------------------------------------------------------------------
| Check File Upload
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES['resource_file']) ||
    $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK
) {

    die(
        "Please select a valid file."
    );

}


$file = $_FILES['resource_file'];


/*
|--------------------------------------------------------------------------
| Maximum File Size = 5MB
|--------------------------------------------------------------------------
*/

$max_size = 5 * 1024 * 1024;


if ($file['size'] > $max_size) {

    die(
        "File is too large. Maximum size is 5MB."
    );

}


/*
|--------------------------------------------------------------------------
| Allowed File Extensions
|--------------------------------------------------------------------------
*/

$allowed_extensions = [
    'pdf',
    'doc',
    'docx',
    'ppt',
    'pptx',
    'zip'
];


$original_name = $file['name'];


$file_extension = strtolower(
    pathinfo(
        $original_name,
        PATHINFO_EXTENSION
    )
);


if (!in_array(
    $file_extension,
    $allowed_extensions,
    true
)) {

    die(
        "Invalid file type. " .
        "Allowed files: PDF, DOC, DOCX, PPT, PPTX, ZIP."
    );

}


/*
|--------------------------------------------------------------------------
| Create Secure Random Filename
|--------------------------------------------------------------------------
*/

$new_filename =
    bin2hex(
        random_bytes(16)
    )
    . "."
    . $file_extension;


/*
|--------------------------------------------------------------------------
| Upload Directory
|--------------------------------------------------------------------------
*/

$upload_directory =
    "../uploads/resources/";


$destination =
    $upload_directory
    . $new_filename;


/*
|--------------------------------------------------------------------------
| Make Sure Upload Directory Exists
|--------------------------------------------------------------------------
*/

if (!is_dir($upload_directory)) {

    if (!mkdir(
        $upload_directory,
        0755,
        true
    )) {

        die(
            "Unable to create upload directory."
        );

    }

}


/*
|--------------------------------------------------------------------------
| Move Uploaded File
|--------------------------------------------------------------------------
*/

if (!move_uploaded_file(
    $file['tmp_name'],
    $destination
)) {

    die(
        "Unable to upload the file."
    );

}


/*
|--------------------------------------------------------------------------
| Resource Status
|--------------------------------------------------------------------------
|
| Every new resource must be reviewed by a lecturer.
|
|--------------------------------------------------------------------------
*/

$status = 'Pending';


/*
|--------------------------------------------------------------------------
| File Path Saved in Database
|--------------------------------------------------------------------------
*/

$file_path =
    "uploads/resources/"
    . $new_filename;


/*
|--------------------------------------------------------------------------
| Save Resource Information in Database
|--------------------------------------------------------------------------
*/

$sql = "INSERT INTO resources
        (
            user_id,
            title,
            description,
            category,
            file_name,
            file_path,
            status
        )

        VALUES (?, ?, ?, ?, ?, ?, ?)";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    /*
    |--------------------------------------------------------------------------
    | Delete Uploaded File if Statement Preparation Fails
    |--------------------------------------------------------------------------
    */

    if (file_exists($destination)) {

        unlink($destination);

    }


    die(
        "Database error: "
        . mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| Bind Database Parameters
|--------------------------------------------------------------------------
*/

mysqli_stmt_bind_param(
    $stmt,
    "issssss",
    $user_id,
    $title,
    $description,
    $category,
    $original_name,
    $file_path,
    $status
);


/*
|--------------------------------------------------------------------------
| Execute Database Insert
|--------------------------------------------------------------------------
*/

if (!mysqli_stmt_execute($stmt)) {

    /*
    |--------------------------------------------------------------------------
    | Delete Uploaded File if Database Insertion Fails
    |--------------------------------------------------------------------------
    */

    if (file_exists($destination)) {

        unlink($destination);

    }


    die(
        "Database error: "
        . mysqli_stmt_error($stmt)
    );

}


/*
|--------------------------------------------------------------------------
| Return to Resources Page
|--------------------------------------------------------------------------
*/

header(
    "Location: resources.php?uploaded=1"
);

exit();

?>