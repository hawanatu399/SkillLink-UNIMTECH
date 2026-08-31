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
    !isset($_GET['service_id']) ||
    !is_numeric($_GET['service_id'])
) {

    die("Invalid service.");

}

$service_id = (int) $_GET['service_id'];


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
            marketplace_services.availability,

            users.full_name,
            users.department,
            users.programme

        FROM marketplace_services

        INNER JOIN users
            ON marketplace_services.provider_id = users.id

        WHERE marketplace_services.id = ?

        AND marketplace_services.status = 'Active'

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Database error: " . mysqli_error($conn));

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $service_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$service = mysqli_fetch_assoc($result);


if (!$service) {

    die("Service not found.");

}


/*
|--------------------------------------------------------------------------
| Prevent Requesting Own Service
|--------------------------------------------------------------------------
*/

if (
    (int) $service['provider_id'] === $user_id
) {

    die("You cannot request your own service.");

}


/*
|--------------------------------------------------------------------------
| Check Availability
|--------------------------------------------------------------------------
*/

if (
    $service['availability'] !== 'Available'
) {

    die("This service is currently unavailable.");

}


/*
|--------------------------------------------------------------------------
| Check Existing Active Request
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id, status

              FROM service_requests

              WHERE service_id = ?

              AND requester_id = ?

              AND status IN (
                  'Pending',
                  'Accepted',
                  'In Progress'
              )

              LIMIT 1";

$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);

if ($check_stmt) {

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $service_id,
        $user_id
    );

    mysqli_stmt_execute(
        $check_stmt
    );

    $check_result =
        mysqli_stmt_get_result(
            $check_stmt
        );

    $existing =
        mysqli_fetch_assoc(
            $check_result
        );

} else {

    $existing = null;

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

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center">

                    <div>

                        <h2>
                            🤝 Request Service
                        </h2>

                        <p class="text-muted mb-0">

                            Send a service request
                            to another student.

                        </p>

                    </div>


                    <a
                        href="view_service.php?id=<?= $service_id; ?>"
                        class="btn btn-outline-primary">

                        ← Back to Service

                    </a>

                </div>

                <hr>


                <!-- SERVICE SUMMARY -->

                <div class="alert alert-light border">

                    <h4>

                        <?= htmlspecialchars(
                            $service['title']
                        ); ?>

                    </h4>

                    <p class="mb-1">

                        <strong>Provider:</strong>

                        <?= htmlspecialchars(
                            $service['full_name']
                        ); ?>

                    </p>

                    <p class="mb-1">

                        <strong>Category:</strong>

                        <?= htmlspecialchars(
                            $service['category']
                        ); ?>

                    </p>

                    <p class="mb-0">

                        <?= nl2br(
                            htmlspecialchars(
                                $service['description']
                            )
                        ); ?>

                    </p>

                </div>


                <?php if ($existing): ?>

                    <div class="alert alert-warning">

                        <h5>
                            ⚠️ Existing Request
                        </h5>

                        <p class="mb-0">

                            You already have an active request
                            for this service.

                            <strong>
                                Status:
                                <?= htmlspecialchars(
                                    $existing['status']
                                ); ?>
                            </strong>

                        </p>

                    </div>

                    <a
                        href="service_requests.php"
                        class="btn btn-primary">

                        📩 View My Service Requests

                    </a>


                <?php else: ?>


                    <!-- REQUEST FORM -->

                    <form
                        action="save_service_request.php"
                        method="POST">

                        <input
                            type="hidden"
                            name="service_id"
                            value="<?= $service_id; ?>">


                        <div class="mb-3">

                            <label
                                class="form-label">

                                What do you need?

                            </label>

                            <textarea
                                name="message"
                                class="form-control"
                                rows="6"
                                maxlength="3000"
                                placeholder="Explain clearly what you need from the service provider..."
                                required></textarea>

                            <div class="form-text">

                                Give the provider enough information
                                to understand your request.

                            </div>

                        </div>


                        <div class="mb-4">

                            <label
                                class="form-label">

                                Preferred Completion Date

                            </label>

                            <input
                                type="date"
                                name="requested_deadline"
                                class="form-control"
                                min="<?= date('Y-m-d'); ?>">

                        </div>


                        <button
                            type="submit"
                            class="btn btn-success">

                            🚀 Send Service Request

                        </button>


                        <a
                            href="view_service.php?id=<?= $service_id; ?>"
                            class="btn btn-secondary">

                            Cancel

                        </a>

                    </form>

                <?php endif; ?>


            </div>

        </div>

    </div>

</div>

<?php include "../templates/footer.php"; ?>