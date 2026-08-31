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
| Get User Profile
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            full_name,
            student_id,
            email,
            department,
            programme,
            level,
            phone,
            gender,
            bio,
            interests,
            profile_picture,
            reputation_points

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


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$user =
    mysqli_fetch_assoc(
        $result
    );


if (!$user) {

    die("Profile not found.");

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


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if (
                    isset($_GET['updated']) &&
                    $_GET['updated'] == '1'
                ): ?>

                    <div
                        class="alert alert-success alert-dismissible fade show"
                        role="alert">

                        ✅

                        <strong>
                            Profile Updated!
                        </strong>

                        Your profile has been updated successfully.

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                        </button>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     PROFILE HEADER
                ================================================== -->

                <div class="text-center mb-4">


                    <?php if (
                        !empty(
                            $user['profile_picture']
                        )
                    ): ?>

                        <img
                            src="../<?= htmlspecialchars(
                                $user['profile_picture']
                            ); ?>"
                            alt="Profile Picture"
                            class="rounded-circle img-thumbnail mb-3"
                            style="
                                width:150px;
                                height:150px;
                                object-fit:cover;
                            ">

                    <?php else: ?>

                        <div
                            class="rounded-circle
                                   bg-secondary
                                   text-white
                                   d-flex
                                   align-items-center
                                   justify-content-center
                                   mx-auto
                                   mb-3"
                            style="
                                width:150px;
                                height:150px;
                                font-size:60px;
                            ">

                            👤

                        </div>

                    <?php endif; ?>


                    <h2>

                        👤

                        <?= htmlspecialchars(
                            $user['full_name']
                        ); ?>

                    </h2>


                    <p class="text-muted mb-0">

                        <?= htmlspecialchars(
                            $user['department']
                        ); ?>

                    </p>


                    <p class="text-muted">

                        <?= htmlspecialchars(
                            $user['programme']
                        ); ?>

                    </p>


                </div>


                <hr>


                <!-- =================================================
                     ACADEMIC INFORMATION
                ================================================== -->

                <h4>

                    🎓 Academic Information

                </h4>


                <table
                    class="table table-bordered mt-3">


                    <tr>

                        <th
                            style="width:30%;">

                            Student ID

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['student_id']
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Email

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['email']
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Department

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['department']
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Programme

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['programme']
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Level

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['level']
                            ); ?>

                        </td>

                    </tr>


                </table>


                <hr>


                <!-- =================================================
                     PERSONAL INFORMATION
                ================================================== -->

                <h4>

                    📱 Personal Information

                </h4>


                <table
                    class="table table-bordered mt-3">


                    <tr>

                        <th
                            style="width:30%;">

                            Phone

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['phone']
                                ?: 'Not provided'
                            ); ?>

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Gender

                        </th>

                        <td>

                            <?= htmlspecialchars(
                                $user['gender']
                                ?: 'Not provided'
                            ); ?>

                        </td>

                    </tr>


                </table>


                <hr>


                <!-- =================================================
                     BIOGRAPHY
                ================================================== -->

                <h4>

                    📝 Biography

                </h4>


                <div
                    class="border rounded p-3 mt-3 bg-light">

                    <?php if (
                        !empty(
                            $user['bio']
                        )
                    ): ?>

                        <?= nl2br(
                            htmlspecialchars(
                                $user['bio']
                            )
                        ); ?>

                    <?php else: ?>

                        <span
                            class="text-muted">

                            No biography added yet.

                        </span>

                    <?php endif; ?>

                </div>


                <hr>


                <!-- =================================================
                     INTERESTS
                ================================================== -->

                <h4>

                    🎯 Interests

                </h4>


                <div
                    class="border rounded p-3 mt-3 bg-light">

                    <?php if (
                        !empty(
                            $user['interests']
                        )
                    ): ?>

                        <?= nl2br(
                            htmlspecialchars(
                                $user['interests']
                            )
                        ); ?>

                    <?php else: ?>

                        <span
                            class="text-muted">

                            No interests added yet.

                        </span>

                    <?php endif; ?>

                </div>


                <hr>


                <!-- =================================================
                     REPUTATION
                ================================================== -->

                <div class="text-center">

                    <h5>

                        ⭐ Reputation Points

                    </h5>


                    <h2 class="text-primary">

                        <?= (int) (
                            $user['reputation_points']
                            ?? 0
                        ); ?>

                    </h2>


                </div>


                <hr>


                <!-- =================================================
                     ACTION BUTTONS
                ================================================== -->

                <div class="text-center">


                    <a
                        href="edit_profile.php"
                        class="btn btn-primary">

                        ✏️ Edit Profile

                    </a>


                    <a
                        href="dashboard.php"
                        class="btn btn-secondary">

                        🏠 Dashboard

                    </a>


                </div>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>