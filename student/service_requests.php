<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Requests I Sent
|--------------------------------------------------------------------------
*/

$sent_sql = "SELECT
                 service_requests.id,
                 service_requests.message,
                 service_requests.requested_deadline,
                 service_requests.status,
                 service_requests.created_at,

                 marketplace_services.title,
                 marketplace_services.category,

                 users.full_name AS provider_name,

                 (
                     SELECT COUNT(*)
                     FROM service_reviews sr
                     WHERE sr.service_request_id =
                           service_requests.id
                 ) AS review_exists

             FROM service_requests

             INNER JOIN marketplace_services
                 ON service_requests.service_id =
                    marketplace_services.id

             INNER JOIN users
                 ON service_requests.provider_id =
                    users.id

             WHERE service_requests.requester_id = ?

             ORDER BY service_requests.created_at DESC";


$sent_stmt = mysqli_prepare(
    $conn,
    $sent_sql
);

if (!$sent_stmt) {
    die(
        "Unable to load requests: " .
        mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $sent_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $sent_stmt
);

$sent_result = mysqli_stmt_get_result(
    $sent_stmt
);


/*
|--------------------------------------------------------------------------
| Requests I Received
|--------------------------------------------------------------------------
*/

$received_sql = "SELECT
                     service_requests.id,
                     service_requests.message,
                     service_requests.requested_deadline,
                     service_requests.status,
                     service_requests.created_at,

                     marketplace_services.title,
                     marketplace_services.category,

                     users.full_name AS requester_name

                 FROM service_requests

                 INNER JOIN marketplace_services
                     ON service_requests.service_id =
                        marketplace_services.id

                 INNER JOIN users
                     ON service_requests.requester_id =
                        users.id

                 WHERE service_requests.provider_id = ?

                 ORDER BY service_requests.created_at DESC";


$received_stmt = mysqli_prepare(
    $conn,
    $received_sql
);

if (!$received_stmt) {
    die(
        "Unable to load received requests: " .
        mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $received_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute(
    $received_stmt
);

$received_result = mysqli_stmt_get_result(
    $received_stmt
);


include "../templates/header.php";
include "../templates/navbar.php";

?>

<div class="container-fluid">

    <div class="row">

        <!-- =========================================================
             SIDEBAR
        ========================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =========================================================
             MAIN CONTENT
        ========================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card p-4 shadow-sm">


                <!-- =================================================
                     HEADER
                ================================================== -->

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           flex-wrap">

                    <div>

                        <h2>
                            📩 Service Requests
                        </h2>

                        <p class="text-muted mb-0">

                            Manage marketplace service requests.

                        </p>

                    </div>


                    <div class="mt-2">

                        <a
                            href="marketplace.php"
                            class="btn btn-primary">

                            🛒 Marketplace

                        </a>

                    </div>

                </div>


                <hr>


                <!-- =================================================
                     SUCCESS MESSAGES
                ================================================== -->

                <?php if (
                    isset($_GET['sent'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ✅ Service request sent successfully.

                        The service provider has been notified.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['updated'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ✅ Service request status updated successfully.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['completed'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ✅ Service marked as completed.

                        The student has been notified.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['confirmed'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ✅ Service completion confirmed successfully.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['reviewed'])
                ): ?>

                    <div
                        class="alert alert-success">

                        ⭐ Your review was submitted successfully.

                        Thank you for helping other students.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     REQUESTS RECEIVED
                ================================================== -->

                <h4>

                    📥 Requests Received

                </h4>

                <hr>


                <?php if (
                    mysqli_num_rows(
                        $received_result
                    ) > 0
                ): ?>


                    <?php while (
                        $request =
                        mysqli_fetch_assoc(
                            $received_result
                        )
                    ): ?>


                        <div
                            class="card mb-3
                                   border-primary">

                            <div class="card-body">


                                <h5>

                                    <?= htmlspecialchars(
                                        $request['title']
                                    ); ?>

                                </h5>


                                <p>

                                    <strong>
                                        From:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $request['requester_name']
                                    ); ?>

                                </p>


                                <p>

                                    <strong>
                                        Request:
                                    </strong>

                                    <br>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $request['message']
                                        )
                                    ); ?>

                                </p>


                                <?php if (
                                    !empty(
                                        $request[
                                            'requested_deadline'
                                        ]
                                    )
                                ): ?>

                                    <p>

                                        <strong>
                                            Preferred Deadline:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $request[
                                                'requested_deadline'
                                            ]
                                        ); ?>

                                    </p>

                                <?php endif; ?>


                                <p>

                                    <strong>
                                        Status:
                                    </strong>


                                    <?php if (
                                        $request['status']
                                        === 'Pending'
                                    ): ?>

                                        <span
                                            class="badge bg-warning text-dark">

                                            ⏳ Pending

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Accepted'
                                    ): ?>

                                        <span
                                            class="badge bg-success">

                                            ✅ Accepted

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'In Progress'
                                    ): ?>

                                        <span
                                            class="badge bg-primary">

                                            ▶ In Progress

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Completed'
                                    ): ?>

                                        <span
                                            class="badge bg-info text-dark">

                                            ✅ Completed

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Confirmed'
                                    ): ?>

                                        <span
                                            class="badge bg-success">

                                            ✅ Confirmed

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Rejected'
                                    ): ?>

                                        <span
                                            class="badge bg-danger">

                                            ❌ Rejected

                                        </span>


                                    <?php else: ?>

                                        <span
                                            class="badge bg-secondary">

                                            <?= htmlspecialchars(
                                                $request['status']
                                            ); ?>

                                        </span>

                                    <?php endif; ?>

                                </p>


                                <!-- =================================================
                                     PROVIDER ACTIONS
                                ================================================== -->

                                <?php if (
                                    $request['status']
                                    === 'Pending'
                                ): ?>

                                    <a
                                        href="update_service_request.php?id=<?= (int)
                                            $request['id']; ?>&action=accept"
                                        class="btn btn-success">

                                        ✅ Accept

                                    </a>


                                    <a
                                        href="update_service_request.php?id=<?= (int)
                                            $request['id']; ?>&action=reject"
                                        class="btn btn-danger">

                                        ❌ Reject

                                    </a>


                                <?php elseif (
                                    $request['status']
                                    === 'Accepted'
                                ): ?>

                                    <a
                                        href="update_service_request.php?id=<?= (int)
                                            $request['id']; ?>&action=start"
                                        class="btn btn-primary">

                                        ▶ Start Service

                                    </a>


                                <?php elseif (
                                    $request['status']
                                    === 'In Progress'
                                ): ?>

                                    <a
                                        href="complete_service.php?id=<?= (int)
                                            $request['id']; ?>"
                                        class="btn btn-success">

                                        ✅ Mark Completed

                                    </a>


                                <?php elseif (
                                    $request['status']
                                    === 'Confirmed'
                                ): ?>

                                    <span
                                        class="badge bg-success">

                                        🎉 Transaction Completed

                                    </span>

                                <?php endif; ?>


                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div
                        class="alert alert-info">

                        You have not received any service requests.

                    </div>


                <?php endif; ?>


                <!-- =================================================
                     REQUESTS SENT
                ================================================== -->

                <hr class="my-4">


                <h4>

                    📤 My Requests

                </h4>


                <hr>


                <?php if (
                    mysqli_num_rows(
                        $sent_result
                    ) > 0
                ): ?>


                    <?php while (
                        $request =
                        mysqli_fetch_assoc(
                            $sent_result
                        )
                    ): ?>


                        <div
                            class="card mb-3
                                   border-secondary">

                            <div class="card-body">


                                <h5>

                                    <?= htmlspecialchars(
                                        $request['title']
                                    ); ?>

                                </h5>


                                <p>

                                    <strong>
                                        Provider:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $request['provider_name']
                                    ); ?>

                                </p>


                                <p>

                                    <strong>
                                        My Request:
                                    </strong>

                                    <br>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $request['message']
                                        )
                                    ); ?>

                                </p>


                                <!-- STATUS -->

                                <p>

                                    <strong>
                                        Status:
                                    </strong>


                                    <?php if (
                                        $request['status']
                                        === 'Pending'
                                    ): ?>

                                        <span
                                            class="badge bg-warning text-dark">

                                            ⏳ Pending

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Accepted'
                                    ): ?>

                                        <span
                                            class="badge bg-success">

                                            ✅ Accepted

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'In Progress'
                                    ): ?>

                                        <span
                                            class="badge bg-primary">

                                            ▶ In Progress

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Completed'
                                    ): ?>

                                        <span
                                            class="badge bg-info text-dark">

                                            ⏳ Awaiting Confirmation

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Confirmed'
                                    ): ?>

                                        <span
                                            class="badge bg-success">

                                            ✅ Completed

                                        </span>


                                    <?php elseif (
                                        $request['status']
                                        === 'Rejected'
                                    ): ?>

                                        <span
                                            class="badge bg-danger">

                                            ❌ Rejected

                                        </span>


                                    <?php else: ?>

                                        <span
                                            class="badge bg-secondary">

                                            <?= htmlspecialchars(
                                                $request['status']
                                            ); ?>

                                        </span>

                                    <?php endif; ?>

                                </p>


                                <!-- =================================================
                                     REQUESTER ACTIONS
                                ================================================== -->


                                <?php if (
                                    $request['status']
                                    === 'Completed'
                                ): ?>

                                    <a
                                        href="confirm_service.php?id=<?= (int)
                                            $request['id']; ?>"
                                        class="btn btn-success">

                                        ✅ Confirm Completion

                                    </a>


                                <?php elseif (
                                    $request['status']
                                    === 'Confirmed'
                                ): ?>


                                    <?php if (
                                        (int)
                                        $request['review_exists']
                                        === 0
                                    ): ?>

                                        <div
                                            class="alert alert-success mt-3">

                                            <strong>
                                                🎉 Service Completed!
                                            </strong>

                                            <br>

                                            Your service has been
                                            successfully completed.

                                            <br><br>

                                            Please share your experience
                                            with other students.

                                        </div>


                                        <a
                                            href="service_review.php?id=<?= (int)
                                                $request['id']; ?>"
                                            class="btn btn-warning">

                                            ⭐ Leave Review

                                        </a>


                                    <?php else: ?>

                                        <div
                                            class="alert alert-light border mt-3">

                                            ⭐

                                            <strong>
                                                Review Submitted
                                            </strong>

                                            <br>

                                            Thank you for reviewing
                                            this service.

                                        </div>

                                    <?php endif; ?>


                                <?php endif; ?>


                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div
                        class="alert alert-info">

                        You have not requested any services yet.

                        <br><br>

                        <a
                            href="marketplace.php"
                            class="btn btn-primary">

                            🔎 Browse Marketplace

                        </a>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>