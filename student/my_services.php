<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get My Marketplace Services
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            marketplace_services.id,
            marketplace_services.title,
            marketplace_services.category,
            marketplace_services.description,
            marketplace_services.price,
            marketplace_services.service_type,
            marketplace_services.availability,
            marketplace_services.status,
            marketplace_services.created_at,

            skills.skill_name,
            skills.skill_level,
            skills.verified,

            COALESCE(
                AVG(reviews.rating),
                0
            ) AS average_rating,

            COUNT(
                DISTINCT reviews.id
            ) AS review_count

        FROM marketplace_services

        LEFT JOIN skills
            ON marketplace_services.skill_id =
               skills.id

        LEFT JOIN reviews
            ON reviews.reviewed_user_id =
               marketplace_services.provider_id

        WHERE marketplace_services.provider_id = ?

        GROUP BY
            marketplace_services.id,
            marketplace_services.title,
            marketplace_services.category,
            marketplace_services.description,
            marketplace_services.price,
            marketplace_services.service_type,
            marketplace_services.availability,
            marketplace_services.status,
            marketplace_services.created_at,

            skills.skill_name,
            skills.skill_level,
            skills.verified

        ORDER BY marketplace_services.created_at DESC";


$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {

    die(
        "Database error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);


include "../templates/header.php";
include "../templates/navbar.php";

?>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9 mt-4">

            <div class="card p-4 shadow-sm">


                <!-- PAGE HEADER -->

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           flex-wrap">

                    <div>

                        <h2>
                            ⚙️ My Marketplace Services
                        </h2>

                        <p class="text-muted mb-0">

                            Manage the services you offer
                            to other students.

                        </p>

                    </div>


                    <div class="mt-2">

                        <a
                            href="create_service.php"
                            class="btn btn-success">

                            ➕ Offer Service

                        </a>


                        <a
                            href="service_requests.php"
                            class="btn btn-outline-primary">

                            📩 Service Requests

                        </a>

                    </div>

                </div>


                <hr>


                <!-- SUCCESS MESSAGE -->

                <?php if (
                    isset($_GET['success'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ✅ Service published successfully.

                    </div>

                <?php endif; ?>


                <!-- SERVICES -->

                <?php if (
                    mysqli_num_rows($result) > 0
                ): ?>

                    <div class="row g-4">


                        <?php while (
                            $service =
                            mysqli_fetch_assoc($result)
                        ): ?>


                            <div class="col-md-6">


                                <div
                                    class="card h-100 shadow-sm">


                                    <div
                                        class="card-body">


                                        <!-- TITLE -->

                                        <div
                                            class="d-flex
                                                   justify-content-between
                                                   align-items-start">

                                            <h5>

                                                <?= htmlspecialchars(
                                                    $service['title']
                                                ); ?>

                                            </h5>


                                            <?php if (
                                                (int)
                                                $service['verified']
                                                === 1
                                            ): ?>

                                                <span
                                                    class="badge bg-success">

                                                    🏅 Verified

                                                </span>

                                            <?php endif; ?>

                                        </div>


                                        <!-- CATEGORY -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                Category:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['category']
                                            ); ?>

                                        </p>


                                        <!-- SKILL -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                Skill:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['skill_name']
                                                ?: 'Not specified'
                                            ); ?>

                                        </p>


                                        <!-- SKILL LEVEL -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                Skill Level:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['skill_level']
                                                ?: 'Not specified'
                                            ); ?>

                                        </p>


                                        <!-- SERVICE TYPE -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                Type:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['service_type']
                                            ); ?>

                                        </p>


                                        <!-- PRICE -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                💰 Price:
                                            </strong>


                                            <?php if (
                                                $service['price']
                                                !== null &&
                                                $service['price']
                                                !== ''
                                            ): ?>

                                                <span
                                                    class="fw-bold text-success">

                                                    Le
                                                    <?= number_format(
                                                        (float)
                                                        $service['price'],
                                                        2
                                                    ); ?>

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="text-muted">

                                                    Price not specified

                                                </span>

                                            <?php endif; ?>

                                        </p>


                                        <!-- RATING -->

                                        <p
                                            class="mb-1">

                                            <strong>
                                                Rating:
                                            </strong>


                                            <?php if (
                                                $service[
                                                    'review_count'
                                                ] > 0
                                            ): ?>

                                                ⭐

                                                <?= number_format(
                                                    (float)
                                                    $service[
                                                        'average_rating'
                                                    ],
                                                    1
                                                ); ?>

                                                / 5

                                                <small
                                                    class="text-muted">

                                                    (
                                                    <?= (int)
                                                        $service[
                                                            'review_count'
                                                        ]; ?>
                                                    reviews)

                                                </small>

                                            <?php else: ?>

                                                <span
                                                    class="text-muted">

                                                    No reviews

                                                </span>

                                            <?php endif; ?>

                                        </p>


                                        <!-- DESCRIPTION -->

                                        <p
                                            class="text-muted">

                                            <?= htmlspecialchars(
                                                mb_strimwidth(
                                                    $service[
                                                        'description'
                                                    ],
                                                    0,
                                                    160,
                                                    '...'
                                                )
                                            ); ?>

                                        </p>


                                        <!-- STATUS -->

                                        <div
                                            class="mb-3">

                                            <strong>
                                                Status:
                                            </strong>


                                            <?php if (
                                                $service['status']
                                                === 'Active'
                                            ): ?>

                                                <span
                                                    class="badge bg-success">

                                                    Active

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge bg-secondary">

                                                    Inactive

                                                </span>

                                            <?php endif; ?>


                                            <?php if (
                                                $service[
                                                    'availability'
                                                ]
                                                === 'Available'
                                            ): ?>

                                                <span
                                                    class="badge bg-primary">

                                                    Available

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge bg-warning text-dark">

                                                    Unavailable

                                                </span>

                                            <?php endif; ?>

                                        </div>


                                        <!-- ACTION -->

                                        <div
                                            class="mt-3">

                                            <a
                                                href="view_service.php?id=<?= (int)
                                                    $service['id']; ?>"
                                                class="btn btn-primary">

                                                👁 View Service

                                            </a>


                                            <a
                                                href="service_requests.php"
                                                class="btn btn-outline-success">

                                                📩 Requests

                                            </a>

                                        </div>


                                    </div>

                                </div>

                            </div>


                        <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div
                        class="alert alert-info">

                        <h5>
                            📭 No Services Yet
                        </h5>

                        <p class="mb-3">

                            You have not published any
                            marketplace services yet.

                        </p>


                        <a
                            href="create_service.php"
                            class="btn btn-success">

                            ➕ Create Your First Service

                        </a>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>