<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Validate Student ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid student profile.");

}


$student_id = (int) $_GET['id'];

$current_user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Student Profile
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


if (!$stmt) {

    die(
        "Unable to load student profile: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$student =
    mysqli_fetch_assoc(
        $result
    );


if (!$student) {

    die("Student profile not found.");

}


/*
|--------------------------------------------------------------------------
| Get Student Skills
|--------------------------------------------------------------------------
*/

$skill_sql = "SELECT
                  skill_name,
                  skill_level,
                  description,
                  verified

              FROM skills

              WHERE user_id = ?

              ORDER BY created_at DESC";


$skill_stmt = mysqli_prepare(
    $conn,
    $skill_sql
);


mysqli_stmt_bind_param(
    $skill_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $skill_stmt
);


$skills_result =
    mysqli_stmt_get_result(
        $skill_stmt
    );


/*
|--------------------------------------------------------------------------
| Get Average Rating and Review Count
|--------------------------------------------------------------------------
*/

$rating_sql = "SELECT
                   AVG(rating) AS average_rating,
                   COUNT(*) AS review_count

               FROM reviews

               WHERE reviewed_user_id = ?";


$rating_stmt = mysqli_prepare(
    $conn,
    $rating_sql
);


mysqli_stmt_bind_param(
    $rating_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $rating_stmt
);


$rating_result =
    mysqli_stmt_get_result(
        $rating_stmt
    );


$rating_data =
    mysqli_fetch_assoc(
        $rating_result
    );


$average_rating =
    $rating_data['average_rating'] !== null
    ? round(
        (float) $rating_data['average_rating'],
        1
    )
    : 0;


$review_count =
    (int) $rating_data['review_count'];


/*
|--------------------------------------------------------------------------
| Get Reviews
|--------------------------------------------------------------------------
*/

$reviews_sql = "SELECT
                    reviews.id,
                    reviews.rating,
                    reviews.review,
                    reviews.created_at,

                    users.full_name AS reviewer_name,
                    users.department AS reviewer_department

                FROM reviews

                INNER JOIN users
                    ON reviews.reviewer_id = users.id

                WHERE reviews.reviewed_user_id = ?

                ORDER BY reviews.created_at DESC";


$reviews_stmt = mysqli_prepare(
    $conn,
    $reviews_sql
);


mysqli_stmt_bind_param(
    $reviews_stmt,
    "i",
    $student_id
);


mysqli_stmt_execute(
    $reviews_stmt
);


$reviews_result =
    mysqli_stmt_get_result(
        $reviews_stmt
    );


/*
|--------------------------------------------------------------------------
| Find Accepted Collaboration
|--------------------------------------------------------------------------
|
| This is used to determine whether the logged-in student
| can review the profile owner.
|
|--------------------------------------------------------------------------
*/

$review_collaboration_id = null;


if ($current_user_id !== $student_id) {

    $collaboration_sql = "SELECT
                              id

                          FROM collaboration_requests

                          WHERE status = 'Accepted'

                          AND (
                              (
                                  sender_id = ?
                                  AND receiver_id = ?
                              )

                              OR

                              (
                                  sender_id = ?
                                  AND receiver_id = ?
                              )
                          )

                          ORDER BY created_at DESC

                          LIMIT 1";


    $collaboration_stmt =
        mysqli_prepare(
            $conn,
            $collaboration_sql
        );


    mysqli_stmt_bind_param(
        $collaboration_stmt,
        "iiii",
        $current_user_id,
        $student_id,
        $student_id,
        $current_user_id
    );


    mysqli_stmt_execute(
        $collaboration_stmt
    );


    $collaboration_result =
        mysqli_stmt_get_result(
            $collaboration_stmt
        );


    $collaboration =
        mysqli_fetch_assoc(
            $collaboration_result
        );


    if ($collaboration) {

        $review_collaboration_id =
            (int) $collaboration['id'];

    }

}


/*
|--------------------------------------------------------------------------
| Check Whether Current User Already Reviewed
|--------------------------------------------------------------------------
*/

$already_reviewed = false;


if (
    $review_collaboration_id !== null &&
    $current_user_id !== $student_id
) {

    $review_check_sql = "SELECT
                             id

                         FROM reviews

                         WHERE reviewer_id = ?

                         AND reviewed_user_id = ?

                         AND collaboration_id = ?

                         LIMIT 1";


    $review_check_stmt =
        mysqli_prepare(
            $conn,
            $review_check_sql
        );


    mysqli_stmt_bind_param(
        $review_check_stmt,
        "iii",
        $current_user_id,
        $student_id,
        $review_collaboration_id
    );


    mysqli_stmt_execute(
        $review_check_stmt
    );


    $review_check_result =
        mysqli_stmt_get_result(
            $review_check_stmt
        );


    if (
        mysqli_num_rows(
            $review_check_result
        ) > 0
    ) {

        $already_reviewed = true;

    }

}


/*
|--------------------------------------------------------------------------
| Page Includes
|--------------------------------------------------------------------------
*/

include "../templates/header.php";
include "../templates/navbar.php";

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =====================================================
             MAIN CONTENT
        ====================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card p-4">


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if (isset($_GET['reviewed'])): ?>

                    <div class="alert alert-success">

                        ✅ Your rating and review were submitted
                        successfully!

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET['request'])): ?>

                    <?php if ($_GET['request'] === 'sent'): ?>

                        <div class="alert alert-success">

                            🤝 Collaboration request sent
                            successfully!

                        </div>

                    <?php elseif (
                        $_GET['request'] === 'already_pending'
                    ): ?>

                        <div class="alert alert-warning">

                            ⚠️ You already have a pending
                            collaboration request with this student.

                        </div>

                    <?php endif; ?>

                <?php endif; ?>


                <!-- =================================================
                     PROFILE HEADER
                ================================================== -->

                <div class="text-center">


                    <?php if (
                        !empty(
                            $student['profile_picture']
                        )
                    ): ?>

                        <img
                            src="../<?= htmlspecialchars(
                                $student['profile_picture']
                            ); ?>"
                            alt="Profile Picture"
                            class="rounded-circle mb-3"
                            style="
                                width:120px;
                                height:120px;
                                object-fit:cover;
                            ">

                    <?php else: ?>

                        <div
                            class="rounded-circle bg-secondary
                                   text-white d-flex
                                   align-items-center
                                   justify-content-center
                                   mx-auto mb-3"
                            style="
                                width:120px;
                                height:120px;
                                font-size:50px;
                            ">

                            👤

                        </div>

                    <?php endif; ?>


                    <h2>

                        👤

                        <?= htmlspecialchars(
                            $student['full_name']
                        ); ?>

                    </h2>


                    <p class="text-muted">

                        <?= htmlspecialchars(
                            $student['department']
                        ); ?>

                    </p>


                    <!-- RATING SUMMARY -->

                    <div class="mb-3">

                        <?php if ($review_count > 0): ?>

                            <span class="fs-5">

                                ⭐

                                <strong>
                                    <?= number_format(
                                        $average_rating,
                                        1
                                    ); ?>
                                </strong>

                                / 5

                            </span>

                            <span class="text-muted">

                                (
                                <?= $review_count; ?>

                                review<?= $review_count != 1
                                    ? 's'
                                    : ''; ?>

                                )

                            </span>

                        <?php else: ?>

                            <span class="text-muted">

                                ⭐ No reviews yet

                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <hr>


                <!-- =================================================
                     ACADEMIC INFORMATION
                ================================================== -->

                <h4>
                    🎓 Academic Information
                </h4>


                <p>

                    <strong>
                        Student ID:
                    </strong>

                    <?= htmlspecialchars(
                        $student['student_id']
                    ); ?>

                </p>


                <p>

                    <strong>
                        Programme:
                    </strong>

                    <?= htmlspecialchars(
                        $student['programme']
                    ); ?>

                </p>


                <p>

                    <strong>
                        Level:
                    </strong>

                    <?= htmlspecialchars(
                        $student['level']
                    ); ?>

                </p>


                <hr>


                <!-- =================================================
                     ABOUT ME
                ================================================== -->

                <h4>
                    📝 About Me
                </h4>


                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $student['bio']
                            ?: 'No biography provided.'
                        )
                    ); ?>

                </p>


                <!-- =================================================
                     INTERESTS
                ================================================== -->

                <h4>
                    🎯 Interests
                </h4>


                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $student['interests']
                            ?: 'No interests provided.'
                        )
                    ); ?>

                </p>


                <hr>


                <!-- =================================================
                     SKILLS
                ================================================== -->

                <h4>
                    💡 Skills
                </h4>


                <?php if (
                    mysqli_num_rows(
                        $skills_result
                    ) > 0
                ): ?>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered
                                   table-striped">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Skill
                                    </th>

                                    <th>
                                        Level
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Verification
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $skill =
                                mysqli_fetch_assoc(
                                    $skills_result
                                )
                            ): ?>


                                <tr>


                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['skill_name']
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['skill_level']
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['description']
                                            ?: 'No description.'
                                        ); ?>

                                    </td>


                                    <td>

                                        <?php if (
                                            (int)
                                            $skill['verified']
                                            === 1
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                🏅 Lecturer Verified

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge
                                                       bg-warning
                                                       text-dark">

                                                ⏳ Not Verified

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <p class="text-muted">

                        This student has not added any skills yet.

                    </p>


                <?php endif; ?>


                <hr>


                <!-- =================================================
                     RATINGS AND REVIEWS
                ================================================== -->

                <h4>

                    ⭐ Ratings & Reviews

                </h4>


                <?php if ($review_count > 0): ?>

                    <div class="alert alert-light border">

                        <div class="row text-center">


                            <div class="col-md-6">

                                <h2>

                                    ⭐
                                    <?= number_format(
                                        $average_rating,
                                        1
                                    ); ?>

                                    / 5

                                </h2>

                                <p class="text-muted mb-0">

                                    Average Rating

                                </p>

                            </div>


                            <div class="col-md-6">

                                <h2>

                                    <?= $review_count; ?>

                                </h2>

                                <p class="text-muted mb-0">

                                    Total Review<?= $review_count != 1
                                        ? 's'
                                        : ''; ?>

                                </p>

                            </div>


                        </div>

                    </div>


                    <!-- REVIEWS -->

                    <?php while (
                        $review =
                        mysqli_fetch_assoc(
                            $reviews_result
                        )
                    ): ?>


                        <div
                            class="card mb-3 shadow-sm">


                            <div class="card-body">


                                <div
                                    class="d-flex
                                           justify-content-between
                                           flex-wrap">


                                    <div>

                                        <strong>

                                            👤

                                            <?= htmlspecialchars(
                                                $review['reviewer_name']
                                            ); ?>

                                        </strong>


                                        <?php if (
                                            !empty(
                                                $review[
                                                    'reviewer_department'
                                                ]
                                            )
                                        ): ?>

                                            <small
                                                class="text-muted">

                                                —
                                                <?= htmlspecialchars(
                                                    $review[
                                                        'reviewer_department'
                                                    ]
                                                ); ?>

                                            </small>

                                        <?php endif; ?>

                                    </div>


                                    <small
                                        class="text-muted">

                                        <?= htmlspecialchars(
                                            $review['created_at']
                                        ); ?>

                                    </small>

                                </div>


                                <div class="mt-2">

                                    <?php

                                    $rating =
                                        (int)
                                        $review['rating'];

                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ):

                                    ?>

                                        <?php if (
                                            $i <= $rating
                                        ): ?>

                                            ⭐

                                        <?php else: ?>

                                            ☆

                                        <?php endif; ?>

                                    <?php endfor; ?>


                                    <strong>

                                        <?= $rating; ?>/5

                                    </strong>

                                </div>


                                <p class="mt-2 mb-0">

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $review['review']
                                        )
                                    ); ?>

                                </p>


                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="alert alert-info">

                        ⭐ This student has not received
                        any ratings or reviews yet.

                    </div>


                <?php endif; ?>


                <hr>


                <!-- =================================================
                     REPUTATION + ACTIONS
                ================================================== -->

                <div class="text-center">


                    <p>

                        ⭐ Reputation Points:

                        <strong>

                            <?= (int)
                                $student[
                                    'reputation_points'
                                ]; ?>

                        </strong>

                    </p>


                    <?php if (
                        $student_id != $current_user_id
                    ): ?>


                        <!-- COLLABORATION -->

                        <a
                            href="request_collaboration.php?student_id=<?= $student_id; ?>"
                            class="btn btn-primary">

                            🤝 Request Collaboration

                        </a>


                        <!-- GIVE REVIEW -->

                        <?php if (
                            $review_collaboration_id
                            !== null &&
                            !$already_reviewed
                        ): ?>


                            <a
                                href="give_review.php?collaboration_id=<?= $review_collaboration_id; ?>"
                                class="btn btn-warning">

                                ⭐ Give Rating & Review

                            </a>


                        <?php elseif (
                            $review_collaboration_id
                            !== null &&
                            $already_reviewed
                        ): ?>


                            <span
                                class="btn btn-outline-success">

                                ✅ Already Reviewed

                            </span>


                        <?php endif; ?>


                    <?php endif; ?>


                </div>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>