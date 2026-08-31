<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'lecturer'
) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get All Student Reviews
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            reviews.id,
            reviews.rating,
            reviews.review,
            reviews.created_at,

            reviewer.full_name AS reviewer_name,
            reviewer.student_id AS reviewer_student_id,

            reviewed.full_name AS reviewed_name,
            reviewed.student_id AS reviewed_student_id

        FROM reviews

        INNER JOIN users AS reviewer
            ON reviews.reviewer_id = reviewer.id

        INNER JOIN users AS reviewed
            ON reviews.reviewed_user_id = reviewed.id

        ORDER BY reviews.created_at DESC";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {

    die(
        "Unable to load reviews: " .
        mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| Calculate Summary
|--------------------------------------------------------------------------
*/

$total_reviews = mysqli_num_rows($result);

$average_rating = 0;

$rating_summary_sql = "SELECT
                           AVG(rating) AS average_rating,
                           COUNT(*) AS total

                       FROM reviews";


$rating_summary_result =
    mysqli_query(
        $conn,
        $rating_summary_sql
    );


if ($rating_summary_result) {

    $rating_summary =
        mysqli_fetch_assoc(
            $rating_summary_result
        );

    $average_rating =
        $rating_summary['average_rating'] !== null
        ? round(
            (float) $rating_summary['average_rating'],
            1
        )
        : 0;

}


/*
|--------------------------------------------------------------------------
| Get Rating Distribution
|--------------------------------------------------------------------------
*/

$rating_distribution = [
    5 => 0,
    4 => 0,
    3 => 0,
    2 => 0,
    1 => 0
];


$distribution_sql = "SELECT
                          rating,
                          COUNT(*) AS total

                      FROM reviews

                      GROUP BY rating";


$distribution_result =
    mysqli_query(
        $conn,
        $distribution_sql
    );


if ($distribution_result) {

    while (
        $row =
        mysqli_fetch_assoc(
            $distribution_result
        )
    ) {

        $rating =
            (int) $row['rating'];

        if (
            isset(
                $rating_distribution[$rating]
            )
        ) {

            $rating_distribution[$rating] =
                (int) $row['total'];

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Student Reviews | Lecturer | SkillLink UNIMTECH
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


<!-- =========================================================
     TOP NAVIGATION
========================================================= -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <span class="navbar-brand">

            🎓 SkillLink UNIMTECH

        </span>


        <span class="text-white">

            👨‍🏫 Lecturer Portal

        </span>

    </div>

</nav>


<!-- =========================================================
     PAGE LAYOUT
========================================================= -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <div class="col-md-3">

            <?php include "../templates/lecturer_sidebar.php"; ?>

        </div>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="col-md-9">

            <div class="p-4">


                <!-- PAGE HEADER -->

                <h2>

                    ⭐ Student Reviews

                </h2>


                <p class="text-muted">

                    View ratings and feedback exchanged
                    between students on SkillLink UNIMTECH.

                </p>


                <hr>


                <!-- =================================================
                     SUMMARY CARDS
                ================================================== -->

                <div class="row g-4 mb-4">


                    <!-- TOTAL REVIEWS -->

                    <div class="col-md-6">

                        <div
                            class="card shadow-sm h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">

                                    📝

                                </div>


                                <h2>

                                    <?= $total_reviews; ?>

                                </h2>


                                <p class="text-muted mb-0">

                                    Total Reviews

                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- AVERAGE -->

                    <div class="col-md-6">

                        <div
                            class="card shadow-sm h-100">

                            <div class="card-body text-center">

                                <div class="fs-1">

                                    ⭐

                                </div>


                                <h2>

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

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     RATING DISTRIBUTION
                ================================================== -->

                <div
                    class="card shadow-sm mb-4">

                    <div class="card-body">

                        <h4>

                            📊 Rating Distribution

                        </h4>


                        <hr>


                        <?php foreach (
                            [5, 4, 3, 2, 1]
                            as $rating
                        ): ?>


                            <div class="row align-items-center mb-2">


                                <div class="col-md-2">

                                    <strong>

                                        <?= $rating; ?> ⭐

                                    </strong>

                                </div>


                                <div class="col-md-8">

                                    <?php

                                    $percentage =
                                        $total_reviews > 0
                                        ? (
                                            $rating_distribution[
                                                $rating
                                            ] /
                                            $total_reviews
                                        ) * 100
                                        : 0;

                                    ?>


                                    <div
                                        class="progress"
                                        style="height:20px;">

                                        <div
                                            class="progress-bar"
                                            role="progressbar"
                                            style="width: <?= $percentage; ?>%;">

                                            <?= $rating_distribution[
                                                $rating
                                            ]; ?>

                                        </div>

                                    </div>

                                </div>


                                <div class="col-md-2">

                                    <?= $rating_distribution[
                                        $rating
                                    ]; ?>

                                    review<?= $rating_distribution[
                                        $rating
                                    ] != 1 ? 's' : ''; ?>

                                </div>


                            </div>


                        <?php endforeach; ?>


                    </div>

                </div>


                <!-- =================================================
                     REVIEWS TABLE
                ================================================== -->

                <div
                    class="card shadow-sm">

                    <div class="card-body">


                        <h4>

                            📝 All Student Reviews

                        </h4>


                        <hr>


                        <?php if (
                            $total_reviews > 0
                        ): ?>


                            <div
                                class="table-responsive">


                                <table
                                    class="table
                                           table-bordered
                                           table-striped
                                           align-middle">


                                    <thead
                                        class="table-dark">

                                        <tr>

                                            <th>
                                                Reviewer
                                            </th>

                                            <th>
                                                Reviewed Student
                                            </th>

                                            <th>
                                                Rating
                                            </th>

                                            <th>
                                                Review
                                            </th>

                                            <th>
                                                Date
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php while (
                                        $review =
                                        mysqli_fetch_assoc(
                                            $result
                                        )
                                    ): ?>


                                        <tr>


                                            <!-- REVIEWER -->

                                            <td>

                                                <strong>

                                                    👤

                                                    <?= htmlspecialchars(
                                                        $review[
                                                            'reviewer_name'
                                                        ]
                                                    ); ?>

                                                </strong>


                                                <br>


                                                <small
                                                    class="text-muted">

                                                    ID:

                                                    <?= htmlspecialchars(
                                                        $review[
                                                            'reviewer_student_id'
                                                        ]
                                                    ); ?>

                                                </small>

                                            </td>


                                            <!-- REVIEWED STUDENT -->

                                            <td>

                                                <strong>

                                                    👤

                                                    <?= htmlspecialchars(
                                                        $review[
                                                            'reviewed_name'
                                                        ]
                                                    ); ?>

                                                </strong>


                                                <br>


                                                <small
                                                    class="text-muted">

                                                    ID:

                                                    <?= htmlspecialchars(
                                                        $review[
                                                            'reviewed_student_id'
                                                        ]
                                                    ); ?>

                                                </small>

                                            </td>


                                            <!-- RATING -->

                                            <td>

                                                <?php

                                                $rating =
                                                    (int)
                                                    $review[
                                                        'rating'
                                                    ];

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


                                                <br>


                                                <strong>

                                                    <?= $rating; ?>/5

                                                </strong>

                                            </td>


                                            <!-- REVIEW -->

                                            <td>

                                                <?= nl2br(
                                                    htmlspecialchars(
                                                        $review[
                                                            'review'
                                                        ]
                                                    )
                                                ); ?>

                                            </td>


                                            <!-- DATE -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $review[
                                                        'created_at'
                                                    ]
                                                ); ?>

                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div
                                class="alert alert-info">

                                ⭐ No student reviews have
                                been submitted yet.

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>