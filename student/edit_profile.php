<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Get Logged-in User
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Current User Information
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            full_name,
            phone,
            gender,
            bio,
            interests,
            profile_picture

        FROM users

        WHERE id = ?

        LIMIT 1";


$stmt = mysqli_prepare(
    $conn,
    $sql
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $stmt
);

$result = mysqli_stmt_get_result(
    $stmt
);

$user = mysqli_fetch_assoc(
    $result
);


if (!$user) {

    die("User profile not found.");

}


include "../templates/header.php";
include "../templates/navbar.php";

?>


<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card shadow-sm p-4">


                <h2>

                    ✏️ Edit My Profile

                </h2>


                <p class="text-muted">

                    Update your personal information and
                    profile details.

                </p>


                <hr>


                <!-- =================================================
                     EDIT PROFILE FORM
                ================================================== -->

                <form
                    action="update_profile.php"
                    method="POST"
                    enctype="multipart/form-data">


                    <!-- FULL NAME -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Full Name
                            </strong>

                        </label>


                        <input
                            type="text"
                            class="form-control"
                            name="full_name"
                            value="<?= htmlspecialchars(
                                $user['full_name']
                            ); ?>"
                            required>

                    </div>


                    <!-- PHONE -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Phone
                            </strong>

                        </label>


                        <input
                            type="text"
                            class="form-control"
                            name="phone"
                            value="<?= htmlspecialchars(
                                $user['phone'] ?? ''
                            ); ?>"
                            placeholder="Enter your phone number">

                    </div>


                    <!-- GENDER -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Gender
                            </strong>

                        </label>


                        <select
                            name="gender"
                            class="form-select">

                            <option value="">
                                Select Gender
                            </option>


                            <option
                                value="Male"
                                <?= (
                                    ($user['gender'] ?? '')
                                    === 'Male'
                                )
                                ? 'selected'
                                : ''
                                ?>>

                                Male

                            </option>


                            <option
                                value="Female"
                                <?= (
                                    ($user['gender'] ?? '')
                                    === 'Female'
                                )
                                ? 'selected'
                                : ''
                                ?>>

                                Female

                            </option>


                            <option
                                value="Other"
                                <?= (
                                    ($user['gender'] ?? '')
                                    === 'Other'
                                )
                                ? 'selected'
                                : ''
                                ?>>

                                Other

                            </option>

                        </select>

                    </div>


                    <!-- BIOGRAPHY -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Biography
                            </strong>

                        </label>


                        <textarea
                            class="form-control"
                            rows="5"
                            name="bio"
                            placeholder="Tell other students about yourself..."><?= htmlspecialchars(
                                $user['bio'] ?? ''
                            ); ?></textarea>

                    </div>


                    <!-- INTERESTS -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Interests
                            </strong>

                        </label>


                        <textarea
                            class="form-control"
                            rows="4"
                            name="interests"
                            placeholder="Enter your academic or professional interests..."><?= htmlspecialchars(
                                $user['interests'] ?? ''
                            ); ?></textarea>

                    </div>


                    <!-- CURRENT PROFILE PICTURE -->

                    <?php if (
                        !empty(
                            $user['profile_picture']
                        )
                    ): ?>

                        <div class="mb-3">

                            <label class="form-label">

                                <strong>
                                    Current Profile Picture
                                </strong>

                            </label>


                            <br>


                            <img
                                src="../<?= htmlspecialchars(
                                    $user['profile_picture']
                                ); ?>"
                                alt="Profile Picture"
                                class="img-thumbnail"
                                style="
                                    width:150px;
                                    height:150px;
                                    object-fit:cover;
                                ">

                        </div>

                    <?php endif; ?>


                    <!-- NEW PROFILE PICTURE -->

                    <div class="mb-3">

                        <label class="form-label">

                            <strong>
                                Change Profile Picture
                            </strong>

                        </label>


                        <input
                            type="file"
                            class="form-control"
                            name="profile_picture"
                            accept=".jpg,.jpeg,.png,.gif,.webp">


                        <small class="text-muted">

                            Allowed:
                            JPG, JPEG, PNG, GIF and WEBP.

                            <br>

                            Maximum size: 2MB.

                        </small>

                    </div>


                    <!-- BUTTONS -->

                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-success">

                            💾 Save Changes

                        </button>


                        <a
                            href="profile.php"
                            class="btn btn-secondary">

                            ↩ Cancel

                        </a>

                    </div>


                </form>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>