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
    header("Location: edit_profile.php");
    exit();
}

verify_csrf();


$user_id = (int) $_SESSION['user_id'];

$full_name  = trim($_POST['full_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$bio        = trim($_POST['bio'] ?? '');
$interests  = trim($_POST['interests'] ?? '');

if ($full_name === '') {
    die("Full name cannot be empty. <a href='edit_profile.php'>Go back</a>");
}


/*
|--------------------------------------------------------------------------
| Handle Optional Profile Picture Upload
|--------------------------------------------------------------------------
|
| Mirrors the validation approach used in upload_resource.php: random
| filename, extension whitelist, size cap, and a content sanity check.
|
*/

$new_picture_filename = null;

if (
    isset($_FILES['profile_picture']) &&
    $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK
) {

    $file = $_FILES['profile_picture'];

    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file['size'] > $max_size) {
        die("Profile picture is too large. Maximum size is 2MB. <a href='edit_profile.php'>Go back</a>");
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowed_extensions, true)) {
        die("Invalid image type. Allowed: JPG, PNG, GIF, WEBP. <a href='edit_profile.php'>Go back</a>");
    }

    // Basic content check: confirm it's actually an image, not just a
    // renamed file with an image extension.
    if (@getimagesize($file['tmp_name']) === false) {
        die("The uploaded file is not a valid image. <a href='edit_profile.php'>Go back</a>");
    }

    $new_picture_filename = bin2hex(random_bytes(16)) . "." . $extension;

    $upload_directory = "../uploads/profile_pictures/";

    if (!is_dir($upload_directory)) {
        mkdir($upload_directory, 0755, true);
    }

    $destination = $upload_directory . $new_picture_filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Unable to upload profile picture. <a href='edit_profile.php'>Go back</a>");
    }
}


/*
|--------------------------------------------------------------------------
| Update Database
|--------------------------------------------------------------------------
*/

if ($new_picture_filename !== null) {

    $sql = "UPDATE users
            SET full_name = ?,
                phone = ?,
                bio = ?,
                interests = ?,
                profile_picture = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $full_name,
        $phone,
        $bio,
        $interests,
        $new_picture_filename,
        $user_id
    );

} else {

    $sql = "UPDATE users
            SET full_name = ?,
                phone = ?,
                bio = ?,
                interests = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt,
        "ssssi",
        $full_name,
        $phone,
        $bio,
        $interests,
        $user_id
    );

}

if (!mysqli_stmt_execute($stmt)) {
    error_log("Profile update failed: " . mysqli_stmt_error($stmt));
    die("Something went wrong updating your profile. Please try again.");
}

// Keep session display name in sync
$_SESSION['full_name'] = $full_name;

header("Location: profile.php?updated=1");
exit();

?>
