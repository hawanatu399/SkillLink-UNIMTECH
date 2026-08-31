<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Make Sure Request Is POST
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header("Location: profile.php");

    exit();

}


$user_id =
    (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Submitted Information
|--------------------------------------------------------------------------
*/

$full_name =
    trim(
        $_POST['full_name'] ?? ''
    );

$phone =
    trim(
        $_POST['phone'] ?? ''
    );

$gender =
    trim(
        $_POST['gender'] ?? ''
    );

$bio =
    trim(
        $_POST['bio'] ?? ''
    );

$interests =
    trim(
        $_POST['interests'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| Validate Full Name
|--------------------------------------------------------------------------
*/

if (
    $full_name === ''
) {

    die(
        "Full name cannot be empty."
    );

}


/*
|--------------------------------------------------------------------------
| Validate Gender
|--------------------------------------------------------------------------
*/

$allowed_genders = [
    '',
    'Male',
    'Female',
    'Other'
];


if (
    !in_array(
        $gender,
        $allowed_genders,
        true
    )
) {

    die(
        "Invalid gender selected."
    );

}


/*
|--------------------------------------------------------------------------
| Get Existing Profile Picture
|--------------------------------------------------------------------------
*/

$current_picture = '';


$picture_sql =
    "SELECT profile_picture

     FROM users

     WHERE id = ?

     LIMIT 1";


$picture_stmt =
    mysqli_prepare(
        $conn,
        $picture_sql
    );


mysqli_stmt_bind_param(
    $picture_stmt,
    "i",
    $user_id
);


mysqli_stmt_execute(
    $picture_stmt
);


$picture_result =
    mysqli_stmt_get_result(
        $picture_stmt
    );


$current_user =
    mysqli_fetch_assoc(
        $picture_result
    );


if ($current_user) {

    $current_picture =
        $current_user[
            'profile_picture'
        ];

}


$new_picture =
    $current_picture;


/*
|--------------------------------------------------------------------------
| Profile Picture Upload
|--------------------------------------------------------------------------
*/

if (
    isset(
        $_FILES['profile_picture']
    ) &&
    $_FILES['profile_picture']['error']
    !== UPLOAD_ERR_NO_FILE
) {


    if (
        $_FILES['profile_picture']['error']
        !== UPLOAD_ERR_OK
    ) {

        die(
            "There was an error uploading the profile picture."
        );

    }


    $file =
        $_FILES['profile_picture'];


    /*
    |----------------------------------------------------------------------
    | Maximum Size = 2MB
    |----------------------------------------------------------------------
    */

    $max_size =
        2 * 1024 * 1024;


    if (
        $file['size'] >
        $max_size
    ) {

        die(
            "Profile picture is too large. " .
            "Maximum size is 2MB."
        );

    }


    /*
    |----------------------------------------------------------------------
    | Allowed Extensions
    |----------------------------------------------------------------------
    */

    $allowed_extensions = [

        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp'

    ];


    $original_name =
        $file['name'];


    $extension =
        strtolower(
            pathinfo(
                $original_name,
                PATHINFO_EXTENSION
            )
        );


    if (
        !in_array(
            $extension,
            $allowed_extensions,
            true
        )
    ) {

        die(
            "Invalid image type. " .
            "Allowed: JPG, JPEG, PNG, GIF, WEBP."
        );

    }


    /*
    |----------------------------------------------------------------------
    | Verify Image
    |----------------------------------------------------------------------
    */

    $image_info =
        getimagesize(
            $file['tmp_name']
        );


    if (
        $image_info === false
    ) {

        die(
            "The uploaded file is not a valid image."
        );

    }


    /*
    |----------------------------------------------------------------------
    | Create Directory
    |----------------------------------------------------------------------
    */

    $upload_directory =
        "../uploads/profile_pictures/";


    if (
        !is_dir(
            $upload_directory
        )
    ) {

        if (
            !mkdir(
                $upload_directory,
                0755,
                true
            )
        ) {

            die(
                "Unable to create profile picture directory."
            );

        }

    }


    /*
    |----------------------------------------------------------------------
    | Generate Secure Filename
    |----------------------------------------------------------------------
    */

    $new_filename =
        bin2hex(
            random_bytes(16)
        )
        . "."
        . $extension;


    $destination =
        $upload_directory
        . $new_filename;


    /*
    |----------------------------------------------------------------------
    | Move Uploaded File
    |----------------------------------------------------------------------
    */

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $destination
        )
    ) {

        die(
            "Unable to save the profile picture."
        );

    }


    $new_picture =
        "uploads/profile_pictures/"
        . $new_filename;


    /*
    |----------------------------------------------------------------------
    | Delete Old Picture
    |----------------------------------------------------------------------
    */

    if (
        !empty(
            $current_picture
        )
    ) {

        $old_picture =
            "../"
            . $current_picture;


        if (
            file_exists(
                $old_picture
            )
        ) {

            unlink(
                $old_picture
            );

        }

    }

}


/*
|--------------------------------------------------------------------------
| Update User Profile
|--------------------------------------------------------------------------
*/

$sql =
    "UPDATE users

     SET
         full_name = ?,
         phone = ?,
         gender = ?,
         bio = ?,
         interests = ?,
         profile_picture = ?

     WHERE id = ?";


$stmt =
    mysqli_prepare(
        $conn,
        $sql
    );


if (!$stmt) {

    die(
        "Unable to prepare profile update: "
        . mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ssssssi",
    $full_name,
    $phone,
    $gender,
    $bio,
    $interests,
    $new_picture,
    $user_id
);


/*
|--------------------------------------------------------------------------
| Execute Update
|--------------------------------------------------------------------------
*/

if (
    mysqli_stmt_execute(
        $stmt
    )
) {


    /*
    |----------------------------------------------------------------------
    | Update Session
    |----------------------------------------------------------------------
    */

    $_SESSION['full_name'] =
        $full_name;


    /*
    |----------------------------------------------------------------------
    | Return to Profile
    |----------------------------------------------------------------------
    */

    header(
        "Location: profile.php?updated=1"
    );

    exit();


} else {


    /*
    |----------------------------------------------------------------------
    | Remove New Image If Database Update Failed
    |----------------------------------------------------------------------
    */

    if (
        isset(
            $destination
        ) &&
        file_exists(
            $destination
        )
    ) {

        unlink(
            $destination
        );

    }


    die(
        "Unable to update profile: "
        . mysqli_error($conn)
    );

}

?>