<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Get Logged-In Student
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Collaboration ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['collaboration_id']) ||
    !is_numeric($_GET['collaboration_id'])
) {

    die("Invalid collaboration.");

}


$collaboration_id =
    (int) $_GET['collaboration_id'];


/*
|--------------------------------------------------------------------------
| Get Collaboration
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
            sender_id,
            receiver_id,
            status

        FROM collaboration_requests

        WHERE id = ?

        LIMIT 1";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to load collaboration: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $collaboration_id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$collaboration =
    mysqli_fetch_assoc(
        $result
    );


if (!$collaboration) {

    die("Collaboration not found.");

}


/*
|--------------------------------------------------------------------------
| Check Collaboration Status
|--------------------------------------------------------------------------
*/

if (
    $collaboration['status'] !== 'Accepted'
) {

    die(
        "You can only review an accepted collaboration."
    );

}


/*
|--------------------------------------------------------------------------
| Check User Is Part of Collaboration
|--------------------------------------------------------------------------
*/

$sender_id =
    (int) $collaboration['sender_id'];

$receiver_id =
    (int) $collaboration['receiver_id'];


if (
    $user_id !== $sender_id &&
    $user_id !== $receiver_id
) {

    die(
        "You are not part of this collaboration."
    );

}


/*
|--------------------------------------------------------------------------
| Determine Person Being Reviewed
|--------------------------------------------------------------------------
*/

if ($user_id === $sender_id) {

    $reviewed_user_id =
        $receiver_id;

} else {

    $reviewed_user_id =
        $sender_id;

}


/*
|--------------------------------------------------------------------------
| Get Person Being Reviewed
|--------------------------------------------------------------------------
*/

$user_sql = "SELECT
                 id,
                 full_name,
                 department,
                 programme

             FROM users

             WHERE id = ?

             LIMIT 1";


$user_stmt = mysqli_prepare(
    $conn,
    $user_sql
);


if (!$user_stmt) {

    die(
        "Unable to load student: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $user_stmt,
    "i",
    $reviewed_user_id
);


mysqli_stmt_execute(
    $user_stmt
);


$user_result =
    mysqli_stmt_get_result(
        $user_stmt
    );


$reviewed_user =
    mysqli_fetch_assoc(
        $user_result
    );


if (!$reviewed_user) {

    die("Student not found.");

}


/*
|--------------------------------------------------------------------------
| Check Existing Review
|--------------------------------------------------------------------------
*/

$existing_sql = "SELECT
                     id

                 FROM reviews

                 WHERE reviewer_id = ?
                 AND reviewed_user_id = ?
                 AND collaboration_id = ?

                 LIMIT 1";


$existing_stmt = mysqli_prepare(
    $conn,
    $existing_sql
);


mysqli_stmt_bind_param(
    $existing_stmt,
    "iii",
    $user_id,
    $reviewed_user_id,
    $collaboration_id
);


mysqli_stmt_execute(
    $existing_stmt
);


$existing_result =
    mysqli_stmt_get_result(
        $existing_stmt
    );


if (
    mysqli_num_rows(
        $existing_result
    ) > 0
) {

    die(
        "You have already reviewed this collaboration."
    );

}


/*
|--------------------------------------------------------------------------
| Process Review Submission
|--------------------------------------------------------------------------
*/

$error = "";


if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    $rating =
        isset($_POST['rating'])
        ? (int) $_POST['rating']
        : 0;


    $review =
        trim(
            $_POST['review'] ?? ''
        );


    /*
    |----------------------------------------------------------------------
    | Validate Rating
    |----------------------------------------------------------------------
    */

    if (
        $rating < 1 ||
        $rating > 5
    ) {

        $error =
            "Please select a rating between 1 and 5 stars.";

    }


    /*
    |----------------------------------------------------------------------
    | Validate Review
    |----------------------------------------------------------------------
    */

    elseif (
        $review === ''
    ) {

        $error =
            "Please write a short review.";

    }


    /*
    |----------------------------------------------------------------------
    | Save Review
    |----------------------------------------------------------------------
    */

    else {


        /*
        |------------------------------------------------------------------
        | Start Database Transaction
        |------------------------------------------------------------------
        |
        | This ensures that the review and reputation update
        | are treated as one operation.
        |
        */

        mysqli_begin_transaction(
            $conn
        );


        try {


            /*
            |--------------------------------------------------------------
            | Insert Review
            |--------------------------------------------------------------
            */

            $insert_sql = "INSERT INTO reviews
                           (
                               reviewer_id,
                               reviewed_user_id,
                               collaboration_id,
                               rating,
                               review
                           )

                           VALUES (?, ?, ?, ?, ?)";


            $insert_stmt =
                mysqli_prepare(
                    $conn,
                    $insert_sql
                );


            if (!$insert_stmt) {

                throw new Exception(
                    "Unable to prepare review: " .
                    mysqli_error($conn)
                );

            }


            mysqli_stmt_bind_param(
                $insert_stmt,
                "iiiis",
                $user_id,
                $reviewed_user_id,
                $collaboration_id,
                $rating,
                $review
            );


            if (
                !mysqli_stmt_execute(
                    $insert_stmt
                )
            ) {

                throw new Exception(
                    "Unable to save review: " .
                    mysqli_stmt_error(
                        $insert_stmt
                    )
                );

            }


            /*
            |--------------------------------------------------------------
            | Increase Reputation Points
            |--------------------------------------------------------------
            |
            | 5 stars = +5 points
            | 4 stars = +4 points
            | 3 stars = +3 points
            | 2 stars = +2 points
            | 1 star  = +1 point
            |
            */

            $reputation_sql =
                "UPDATE users

                 SET reputation_points =
                     COALESCE(
                         reputation_points,
                         0
                     ) + ?

                 WHERE id = ?";


            $reputation_stmt =
                mysqli_prepare(
                    $conn,
                    $reputation_sql
                );


            if (!$reputation_stmt) {

                throw new Exception(
                    "Unable to prepare reputation update: " .
                    mysqli_error($conn)
                );

            }


            mysqli_stmt_bind_param(
                $reputation_stmt,
                "ii",
                $rating,
                $reviewed_user_id
            );


            if (
                !mysqli_stmt_execute(
                    $reputation_stmt
                )
            ) {

                throw new Exception(
                    "Unable to update reputation: " .
                    mysqli_stmt_error(
                        $reputation_stmt
                    )
                );

            }


            /*
            |--------------------------------------------------------------
            | Create Notification
            |--------------------------------------------------------------
            */

            $notification_type =
                "review";


            $notification_message =
                "You received a new "
                . $rating
                . "-star rating and review.";


            $notification_sql =
                "INSERT INTO notifications
                 (
                     user_id,
                     type,
                     message,
                     related_id
                 )

                 VALUES (?, ?, ?, ?)";


            $notification_stmt =
                mysqli_prepare(
                    $conn,
                    $notification_sql
                );


            if (!$notification_stmt) {

                throw new Exception(
                    "Unable to prepare notification: " .
                    mysqli_error($conn)
                );

            }


            mysqli_stmt_bind_param(
                $notification_stmt,
                "issi",
                $reviewed_user_id,
                $notification_type,
                $notification_message,
                $collaboration_id
            );


            if (
                !mysqli_stmt_execute(
                    $notification_stmt
                )
            ) {

                throw new Exception(
                    "Unable to create notification: " .
                    mysqli_stmt_error(
                        $notification_stmt
                    )
                );

            }


            /*
            |--------------------------------------------------------------
            | Commit Transaction
            |--------------------------------------------------------------
            */

            mysqli_commit(
                $conn
            );


            /*
            |--------------------------------------------------------------
            | Return to Student Profile
            |--------------------------------------------------------------
            */

            header(
                "Location: view_profile.php?id=" .
                $reviewed_user_id .
                "&reviewed=1"
            );


            exit();


        } catch (
            Exception $e
        ) {


            /*
            |--------------------------------------------------------------
            | Roll Back Changes
            |--------------------------------------------------------------
            */

            mysqli_rollback(
                $conn
            );


            $error =
                $e->getMessage();

        }

    }

}


/*
|--------------------------------------------------------------------------
| Page
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

            <div class="card shadow-sm p-4">


                <h2>

                    ⭐ Give Rating & Review

                </h2>


                <p class="text-muted">

                    Share your experience working with
                    this student.

                </p>


                <hr>


                <!-- =================================================
                     STUDENT INFORMATION
                ================================================== -->

                <div class="alert alert-light border">

                    <h5>

                        👤

                        <?= htmlspecialchars(
                            $reviewed_user['full_name']
                        ); ?>

                    </h5>


                    <p class="mb-1">

                        <strong>
                            Department:
                        </strong>

                        <?= htmlspecialchars(
                            $reviewed_user['department']
                        ); ?>

                    </p>


                    <p class="mb-0">

                        <strong>
                            Programme:
                        </strong>

                        <?= htmlspecialchars(
                            $reviewed_user['programme']
                        ); ?>

                    </p>

                </div>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if (
                    $error !== ''
                ): ?>

                    <div class="alert alert-danger">

                        ❌

                        <?= htmlspecialchars(
                            $error
                        ); ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     REVIEW FORM
                ================================================== -->

                <form
                    method="POST"
                    action="">


                    <!-- RATING -->

                    <div class="mb-4">

                        <label
                            class="form-label">

                            <strong>
                                ⭐ Rating
                            </strong>

                        </label>


                        <select
                            name="rating"
                            class="form-select"
                            required>

                            <option value="">
                                Select Rating
                            </option>

                            <option value="5">

                                ⭐⭐⭐⭐⭐
                                Excellent - 5

                            </option>

                            <option value="4">

                                ⭐⭐⭐⭐
                                Very Good - 4

                            </option>

                            <option value="3">

                                ⭐⭐⭐
                                Good - 3

                            </option>

                            <option value="2">

                                ⭐⭐
                                Fair - 2

                            </option>

                            <option value="1">

                                ⭐
                                Poor - 1

                            </option>

                        </select>

                    </div>


                    <!-- REVIEW -->

                    <div class="mb-4">

                        <label
                            class="form-label">

                            <strong>
                                📝 Your Review
                            </strong>

                        </label>


                        <textarea
                            name="review"
                            class="form-control"
                            rows="5"
                            placeholder="Write your experience working with this student..."
                            required></textarea>

                    </div>


                    <!-- BUTTONS -->

                    <button
                        type="submit"
                        class="btn btn-primary">

                        ⭐ Submit Review

                    </button>


                    <a
                        href="view_profile.php?id=<?= $reviewed_user_id; ?>"
                        class="btn btn-secondary">

                        Cancel

                    </a>


                </form>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>