<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Search and Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$type = trim($_GET['type'] ?? '');

/*
|--------------------------------------------------------------------------
| Build Marketplace Query
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
            marketplace_services.created_at,

            users.full_name,
            users.department,
            users.programme,
            users.level,
            users.profile_picture,
            users.reputation_points,

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

        WHERE marketplace_services.status = 'Active'

        AND marketplace_services.availability = 'Available'

        AND marketplace_services.provider_id != ?";

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$params = [$user_id];
$types = "i";

if ($search !== '') {

    $sql .= "
        AND (
            marketplace_services.title LIKE ?
            OR marketplace_services.category LIKE ?
            OR marketplace_services.description LIKE ?
            OR users.full_name LIKE ?
        )
    ";

    $search_term = "%" . $search . "%";

    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;

    $types .= "ssss";
}

/*
|--------------------------------------------------------------------------
| Category Filter
|--------------------------------------------------------------------------
*/

if ($category !== '') {

    $sql .= "
        AND marketplace_services.category = ?
    ";

    $params[] = $category;
    $types .= "s";
}

/*
|--------------------------------------------------------------------------
| Service Type Filter
|--------------------------------------------------------------------------
*/

if ($type !== '') {

    $sql .= "
        AND marketplace_services.service_type = ?
    ";

    $params[] = $type;
    $types .= "s";
}

/*
|--------------------------------------------------------------------------
| Grouping
|--------------------------------------------------------------------------
*/

$sql .= "
        GROUP BY
            marketplace_services.id,
            marketplace_services.provider_id,
            marketplace_services.title,
            marketplace_services.category,
            marketplace_services.description,
            marketplace_services.price,
            marketplace_services.service_type,
            marketplace_services.availability,
            marketplace_services.created_at,

            users.full_name,
            users.department,
            users.programme,
            users.level,
            users.profile_picture,
            users.reputation_points,

            skills.skill_level,
            skills.verified

        ORDER BY marketplace_services.created_at DESC
";

/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die(
        "Marketplace database error: " .
        mysqli_error($conn)
    );

}

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| Get Categories
|--------------------------------------------------------------------------
*/

$category_sql = "
    SELECT DISTINCT category
    FROM marketplace_services
    WHERE status = 'Active'
    ORDER BY category ASC
";

$category_result =
    mysqli_query(
        $conn,
        $category_sql
    );


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

                    <div>

                        <h2>
                            🛒 SkillLink Marketplace
                        </h2>

                        <p class="text-muted mb-0">

                            Discover student skills and
                            services or offer your own.

                        </p>

                    </div>

                    <div class="mt-2">

                        <a
                            href="create_service.php"
                            class="btn btn-success">

                            ➕ Offer a Service

                        </a>

                        <a
                            href="my_services.php"
                            class="btn btn-outline-primary">

                            💼 My Services

                        </a>

                    </div>

                </div>

                <hr>


                <!-- SEARCH AND FILTERS -->

                <form
                    method="GET"
                    action="marketplace.php"
                    class="row g-3 mb-4">

                    <div class="col-md-5">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search skills or services..."
                            value="<?= htmlspecialchars($search); ?>">

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Category
                        </label>

                        <select
                            name="category"
                            class="form-select">

                            <option value="">
                                All Categories
                            </option>

                            <?php while (
                                $cat =
                                mysqli_fetch_assoc(
                                    $category_result
                                )
                            ): ?>

                                <option
                                    value="<?= htmlspecialchars(
                                        $cat['category']
                                    ); ?>"
                                    <?= $category ===
                                        $cat['category']
                                        ? 'selected'
                                        : ''; ?>>

                                    <?= htmlspecialchars(
                                        $cat['category']
                                    ); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Service Type
                        </label>

                        <select
                            name="type"
                            class="form-select">

                            <option value="">
                                All Types
                            </option>

                            <option
                                value="Skill Exchange"
                                <?= $type ===
                                    'Skill Exchange'
                                    ? 'selected'
                                    : ''; ?>>

                                Skill Exchange

                            </option>

                            <option
                                value="Student Service"
                                <?= $type ===
                                    'Student Service'
                                    ? 'selected'
                                    : ''; ?>>

                                Student Service

                            </option>

                            <option
                                value="Academic Support"
                                <?= $type ===
                                    'Academic Support'
                                    ? 'selected'
                                    : ''; ?>>

                                Academic Support

                            </option>

                            <option
                                value="Project Support"
                                <?= $type ===
                                    'Project Support'
                                    ? 'selected'
                                    : ''; ?>>

                                Project Support

                            </option>

                        </select>

                    </div>


                    <div class="col-md-1 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            🔎

                        </button>

                    </div>

                </form>


                <!-- AVAILABLE SERVICES -->

                <h4 class="mb-3">
                    Available Services
                </h4>


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

                                    <div class="card-body">

                                        <!-- TITLE -->

                                        <div
                                            class="d-flex
                                                   justify-content-between
                                                   align-items-start">

                                            <h5
                                                class="card-title">

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


                                        <!-- PROVIDER -->

                                        <p class="mb-1">

                                            <strong>
                                                Provider:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['full_name']
                                            ); ?>

                                        </p>


                                        <!-- CATEGORY -->

                                        <p class="mb-1">

                                            <strong>
                                                Category:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['category']
                                            ); ?>

                                        </p>


                                        <!-- TYPE -->

                                        <p class="mb-1">

                                            <strong>
                                                Type:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['service_type']
                                            ); ?>

                                        </p>


                                        <!-- PRICE -->

                                        <p class="mb-1">

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


                                        <!-- SKILL LEVEL -->

                                        <p class="mb-1">

                                            <strong>
                                                Skill Level:
                                            </strong>

                                            <?= htmlspecialchars(
                                                $service['skill_level']
                                                ?: 'Not specified'
                                            ); ?>

                                        </p>


                                        <!-- RATING -->

                                        <p class="mb-1">

                                            ⭐

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


                                        <!-- DESCRIPTION -->

                                        <p class="text-muted">

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


                                        <!-- BUTTONS -->

                                        <div class="mt-3">

                                            <a
                                                href="view_service.php?id=<?= (int)
                                                    $service['id']; ?>"
                                                class="btn btn-primary">

                                                👁 View Service

                                            </a>


                                            <a
                                                href="request_service.php?service_id=<?= (int)
                                                    $service['id']; ?>"
                                                class="btn btn-success">

                                                🤝 Request Service

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
                            No services found.
                        </h5>

                        <p class="mb-0">

                            No active student services
                            match your search.

                            Be the first to offer a service.

                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php include "../templates/footer.php"; ?>