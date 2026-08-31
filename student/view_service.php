<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Service ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid service.");

}

$service_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Service
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            marketplace_services.id,
            marketplace_services.provider_id,
            marketplace_services.title,
            marketplace_services.category,
            marketplace_services.description,
            marketplace_services.price,
            marketplace_services.service_type,
            marketplace_services.availability,
            marketplace_services.status,
            marketplace_services.created_at,

            users.full_name,
            users.student_id,
            users.department,
            users.programme,
            users.level,
            users.profile_picture,
            users.reputation_points,

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

        INNER JOIN users
            ON marketplace_services.provider_id = users.id

        LEFT JOIN skills
            ON marketplace_services.skill_id = skills.id

        LEFT JOIN reviews
            ON reviews.reviewed_user_id =
               marketplace_services.provider_id

        WHERE marketplace_services.id = ?

        AND marketplace_services.status = 'Active'

        GROUP BY
            marketplace_services.id,
            marketplace_services.provider_id,
            marketplace_services.title,
            marketplace_services.category,
            marketplace_services.description,
            marketplace_services.price,
            marketplace_services.service_type,
            marketplace_services.availability,
            marketplace_services.status,
            marketplace_services.created_at,

            users.full_name,
            users.student_id,
            users.department,
            users.programme,
            users.level,
            users.profile_picture,
            users.reputation_points,

            skills.skill_name,
            skills.skill_level,
            skills.verified

        LIMIT 1";


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
    $service_id
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result($stmt);

$service =
    mysqli_fetch_assoc($result);


if (!$service) {

    die("Service not found.");

}


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

                <!-- HEADER -->

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           flex-wrap">

                    <h2>

                        🛒
                        <?= htmlspecialchars(
                            $service['title']
                        ); ?>

                    </h2>

                    <a
                        href="marketplace.php"
                        class="btn btn-outline-primary">

                        ← Marketplace

                    </a>

                </div>

                <hr>


                <div class="row">


                    <!-- SERVICE INFORMATION -->

                    <div class="col-md-8">

                        <h4>
                            Service Description
                        </h4>

                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $service['description']
                                )
                            ); ?>

                        </p>

                        <hr>


                        <h4>
                            Service Information
                        </h4>


                        <!-- CATEGORY -->

                        <p>

                            <strong>
                                Category:
                            </strong>

                            <?= htmlspecialchars(
                                $service['category']
                            ); ?>

                        </p>


                        <!-- PRICE -->

                        <p>

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
                                    class="badge bg-success fs-6">

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


                        <!-- SERVICE TYPE -->

                        <p>

                            <strong>
                                Service Type:
                            </strong>

                            <?= htmlspecialchars(
                                $service['service_type']
                            ); ?>

                        </p>


                        <!-- SKILL -->

                        <p>

                            <strong>
                                Skill:
                            </strong>

                            <?= htmlspecialchars(
                                $service['skill_name']
                                ?: 'Not specified'
                            ); ?>

                        </p>


                        <!-- SKILL LEVEL -->

                        <p>

                            <strong>
                                Skill Level:
                            </strong>

                            <?= htmlspecialchars(
                                $service['skill_level']
                                ?: 'Not specified'
                            ); ?>

                        </p>


                        <!-- AVAILABILITY -->

                        <p>

                            <strong>
                                Availability:
                            </strong>


                            <?php if (
                                $service['availability']
                                === 'Available'
                            ): ?>

                                <span
                                    class="badge bg-success">

                                    Available

                                </span>

                            <?php else: ?>

                                <span
                                    class="badge bg-secondary">

                                    Currently Unavailable

                                </span>

                            <?php endif; ?>

                        </p>

                    </div>


                    <!-- PROVIDER -->

                    <div class="col-md-4">

                        <div
                            class="card bg-light">

                            <div
                                class="card-body">


                                <h5>
                                    👤 Service Provider
                                </h5>

                                <hr>


                                <h5>

                                    <?= htmlspecialchars(
                                        $service['full_name']
                                    ); ?>

                                </h5>


                                <p class="mb-1">

                                    <strong>
                                        Student ID:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $service['student_id']
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>
                                        Department:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $service['department']
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>
                                        Programme:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $service['programme']
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>
                                        Level:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $service['level']
                                    ); ?>

                                </p>


                                <hr>


                                <!-- RATING -->

                                <p>

                                    ⭐
                                    <strong>
                                        Rating:
                                    </strong>


                                    <?php if (
                                        $service['review_count']
                                        > 0
                                    ): ?>

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

                                            No reviews yet

                                        </span>

                                    <?php endif; ?>

                                </p>


                                <!-- REPUTATION -->

                                <p>

                                    🏆
                                    <strong>
                                        Reputation:
                                    </strong>

                                    <?= (int)
                                        $service[
                                            'reputation_points'
                                        ]; ?>

                                </p>


                                <!-- VERIFIED -->

                                <?php if (
                                    (int)
                                    $service['verified']
                                    === 1
                                ): ?>

                                    <span
                                        class="badge bg-success">

                                        🏅 Lecturer Verified

                                    </span>

                                <?php endif; ?>


                                <!-- ACTION -->

                                <div
                                    class="mt-4">


                                    <?php if (
                                        (int)
                                        $service['provider_id']
                                        !== $user_id
                                    ): ?>


                                        <?php if (
                                            $service[
                                                'availability'
                                            ]
                                            === 'Available'
                                        ): ?>

                                            <a
                                                href="request_service.php?service_id=<?= (int)
                                                    $service['id']; ?>"
                                                class="btn btn-success w-100">

                                                🤝 Request This Service

                                            </a>

                                        <?php else: ?>

                                            <button
                                                class="btn btn-secondary w-100"
                                                disabled>

                                                Currently Unavailable

                                            </button>

                                        <?php endif; ?>


                                    <?php else: ?>


                                        <a
                                            href="my_services.php"
                                            class="btn btn-primary w-100">

                                            ⚙ Manage My Services

                                        </a>


                                    <?php endif; ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>