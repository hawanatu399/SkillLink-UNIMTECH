<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    die("Invalid service request.");
}

$request_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Confirmed Service
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            service_requests.id,
            service_requests.requester_id,
            service_requests.provider_id,
            service_requests.status,

            marketplace_services.title,

            users.full_name AS provider_name

        FROM service_requests

        INNER JOIN marketplace_services
            ON service_requests.service_id =
               marketplace_services.id

        INNER JOIN users
            ON service_requests.provider_id =
               users.id

        WHERE service_requests.id = ?

        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $request_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$request = mysqli_fetch_assoc($result);

if (!$request) {
    die("Service request not found.");
}


/*
|--------------------------------------------------------------------------
| Only Requester Can Review
|--------------------------------------------------------------------------
*/

if (
    (int) $request['requester_id'] !== $user_id
) {
    die("You are not authorized to review this service.");
}


/*
|--------------------------------------------------------------------------
| Service Must Be Confirmed
|--------------------------------------------------------------------------
*/

if (
    $request['status'] !== 'Confirmed'
) {
    die(
        "You can only review a confirmed service."
    );
}


/*
|--------------------------------------------------------------------------
| Check Existing Review
|--------------------------------------------------------------------------
*/

$check_sql = "SELECT id
              FROM service_reviews
              WHERE service_request_id = ?
              LIMIT 1";

$check_stmt = mysqli_prepare(
    $conn,
    $check_sql
);

if ($check_stmt) {

    mysqli_stmt_bind_param(
        $check_stmt,
        "i",
        $request_id
    );

    mysqli_stmt_execute(
        $check_stmt
    );

    $check_result =
        mysqli_stmt_get_result(
            $check_stmt
        );

    if (
        mysqli_num_rows(
            $check_result
        ) > 0
    ) {

        die(
            "You have already reviewed this service."
        );
    }
}


include "../templates/header.php";
include "../templates/navbar.php";

?>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <div class="col-md-9 mt-4">

            <div class="card p-4 shadow-sm">

                <h2>
                    ⭐ Review Service
                </h2>

                <p class="text-muted">
                    Share your experience with the service provider.
                </p>

                <hr>

                <div class="alert alert-light border">

                    <h4>
                        <?= htmlspecialchars(
                            $request['title']
                        ); ?>
                    </h4>

                    <p class="mb-0">

                        <strong>
                            Provider:
                        </strong>

                        <?= htmlspecialchars(
                            $request['provider_name']
                        ); ?>

                    </p>

                </div>


                <form
                    action="save_service_review.php"
                    method="POST">

                    <input
                        type="hidden"
                        name="request_id"
                        value="<?= $request_id; ?>">


                    <div class="mb-4">

                        <label class="form-label">

                            <strong>
                                Rating
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
                                ⭐⭐⭐⭐⭐ Excellent
                            </option>

                            <option value="4">
                                ⭐⭐⭐⭐ Very Good
                            </option>

                            <option value="3">
                                ⭐⭐⭐ Average
                            </option>

                            <option value="2">
                                ⭐⭐ Poor
                            </option>

                            <option value="1">
                                ⭐ Very Poor
                            </option>

                        </select>

                    </div>


                    <div class="mb-4">

                        <label class="form-label">

                            <strong>
                                Your Review
                            </strong>

                        </label>

                        <textarea
                            name="comment"
                            class="form-control"
                            rows="6"
                            maxlength="2000"
                            placeholder="Tell other students about your experience..."
                            required></textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-success">

                        ⭐ Submit Review

                    </button>


                    <a
                        href="service_requests.php"
                        class="btn btn-secondary">

                        Cancel

                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include "../templates/footer.php"; ?>