<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');

require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| CREATE NEW MARKETPLACE SERVICE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_service'])) {

    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $service_type = trim($_POST['service_type'] ?? '');
    $availability = trim($_POST['availability'] ?? 'Available');

    $allowed_types = [
        'Skill Exchange',
        'Student Service',
        'Academic Support',
        'Project Support'
    ];

    $allowed_availability = [
        'Available',
        'Unavailable'
    ];

    if (
        $title === '' ||
        $category === '' ||
        $description === '' ||
        !in_array($service_type, $allowed_types, true) ||
        !in_array($availability, $allowed_availability, true)
    ) {
        die("
            <div style='font-family:Arial;padding:40px;text-align:center;'>
                <h3 style='color:red;'>Please complete all required fields.</h3>
                <a href='marketplace.php'>Go Back</a>
            </div>
        ");
    }

    if ($price === '') {
        $price_value = null;
    } else {
        if (!is_numeric($price) || $price < 0) {
            die("
                <div style='font-family:Arial;padding:40px;text-align:center;'>
                    <h3 style='color:red;'>Please enter a valid price.</h3>
                    <a href='marketplace.php'>Go Back</a>
                </div>
            ");
        }

        $price_value = (float) $price;
    }

    /*
    | Find matching skill if one exists.
    | skill_id is allowed to be NULL.
    */

    $skill_id = null;

    $skill_stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM skills
         WHERE skill_name = ?
         LIMIT 1"
    );

    if ($skill_stmt) {

        mysqli_stmt_bind_param(
            $skill_stmt,
            "s",
            $category
        );

        mysqli_stmt_execute($skill_stmt);

        $skill_result = mysqli_stmt_get_result($skill_stmt);

        if ($skill = mysqli_fetch_assoc($skill_result)) {
            $skill_id = (int) $skill['id'];
        }

        mysqli_stmt_close($skill_stmt);
    }


    /*
    | Insert marketplace service
    */

    $sql = "
        INSERT INTO marketplace_services
        (
            provider_id,
            skill_id,
            title,
            category,
            description,
            price,
            service_type,
            availability,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Unable to prepare marketplace service.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iisssdss",
        $user_id,
        $skill_id,
        $title,
        $category,
        $description,
        $price_value,
        $service_type,
        $availability
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: marketplace.php?posted=1");

    exit();
}


/*
|--------------------------------------------------------------------------
| CLOSE / DEACTIVATE OWN SERVICE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_service'])) {

    verify_csrf();

    $service_id = (int) ($_POST['service_id'] ?? 0);

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE marketplace_services
         SET status = 'Inactive'
         WHERE id = ?
         AND provider_id = ?"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $service_id,
            $user_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }

    header("Location: marketplace.php?closed=1");

    exit();
}


/*
|--------------------------------------------------------------------------
| SEARCH / FILTER
|--------------------------------------------------------------------------
*/

$category_filter = trim($_GET['category'] ?? '');
$search = trim($_GET['search'] ?? '');
$service_type_filter = trim($_GET['service_type'] ?? '');


$where = [
    "ms.status = 'Active'",
    "ms.availability = 'Available'",
    "ms.provider_id != ?"
];

$params = [$user_id];
$param_types = "i";


if ($category_filter !== '') {

    $where[] = "ms.category = ?";

    $params[] = $category_filter;

    $param_types .= "s";
}


if ($service_type_filter !== '') {

    $allowed_types = [
        'Skill Exchange',
        'Student Service',
        'Academic Support',
        'Project Support'
    ];

    if (in_array($service_type_filter, $allowed_types, true)) {

        $where[] = "ms.service_type = ?";

        $params[] = $service_type_filter;

        $param_types .= "s";
    }
}


if ($search !== '') {

    $where[] = "
        (
            ms.title LIKE ?
            OR ms.category LIKE ?
            OR ms.description LIKE ?
        )
    ";

    $search_value = "%{$search}%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $param_types .= "sss";
}


$where_sql = implode(" AND ", $where);


/*
|--------------------------------------------------------------------------
| GET MARKETPLACE SERVICES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        ms.id,
        ms.provider_id,
        ms.skill_id,
        ms.title,
        ms.category,
        ms.description,
        ms.price,
        ms.service_type,
        ms.availability,
        ms.status,
        ms.created_at,

        u.id AS owner_id,
        u.full_name,
        u.email,
        u.department,
        u.programme,
        u.reputation_points

    FROM marketplace_services ms

    INNER JOIN users u
        ON ms.provider_id = u.id

    WHERE {$where_sql}

    ORDER BY ms.created_at DESC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Unable to load marketplace services.");
}

mysqli_stmt_bind_param(
    $stmt,
    $param_types,
    ...$params
);

mysqli_stmt_execute($stmt);

$listings_result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| MY SERVICES
|--------------------------------------------------------------------------
*/

$my_services_result = mysqli_query(
    $conn,
    "
    SELECT
        id,
        title,
        category,
        description,
        price,
        service_type,
        availability,
        status,
        created_at

    FROM marketplace_services

    WHERE provider_id = {$user_id}

    ORDER BY created_at DESC
    "
);


/*
|--------------------------------------------------------------------------
| CATEGORY LIST
|--------------------------------------------------------------------------
*/

$categories_result = mysqli_query(
    $conn,
    "
    SELECT DISTINCT category
    FROM marketplace_services
    WHERE status = 'Active'
    ORDER BY category ASC
    "
);


include "../templates/header.php";

?>

<div class="container-fluid">

    <div class="row">

        <!-- STUDENT SIDEBAR -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9 mt-4">


            <!-- HEADER -->

            <div class="card p-4 mb-4 shadow-sm">

                <h2 class="fw-bold">

                    🛒 Skill Marketplace

                </h2>

                <p class="text-muted">

                    Discover student services, academic support,
                    project assistance and skills available within
                    the UNIMTECH community.

                </p>


                <?php if (isset($_GET['posted'])): ?>

                    <div class="alert alert-success">

                        ✅ Your service has been successfully published.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET['closed'])): ?>

                    <div class="alert alert-success">

                        ✅ Your service has been deactivated.

                    </div>

                <?php endif; ?>

            </div>


            <!-- CREATE SERVICE -->

            <div class="card p-4 mb-4 shadow-sm">

                <h5 class="fw-bold mb-3">

                    ➕ Publish a Service

                </h5>


                <form
                    method="POST"
                    action="marketplace.php"
                >

                    <?= generate_csrf_field(); ?>


                    <div class="row g-3">


                        <!-- TITLE -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Service Title

                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="e.g. Website Design and Development"
                                required
                            >

                        </div>


                        <!-- CATEGORY -->

                        <div class="col-md-6">

                            <label class="form-label">

                                Category

                            </label>

                            <input
                                type="text"
                                name="category"
                                class="form-control"
                                placeholder="e.g. Programming"
                                required
                            >

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="col-md-12">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                                placeholder="Describe the service you provide..."
                                required
                            ></textarea>

                        </div>


                        <!-- PRICE -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Price

                            </label>

                            <input
                                type="number"
                                name="price"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="Optional"
                            >

                        </div>


                        <!-- SERVICE TYPE -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Service Type

                            </label>

                            <select
                                name="service_type"
                                class="form-select"
                                required
                            >

                                <option value="">

                                    Select Type

                                </option>

                                <option value="Skill Exchange">

                                    Skill Exchange

                                </option>

                                <option value="Student Service">

                                    Student Service

                                </option>

                                <option value="Academic Support">

                                    Academic Support

                                </option>

                                <option value="Project Support">

                                    Project Support

                                </option>

                            </select>

                        </div>


                        <!-- AVAILABILITY -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Availability

                            </label>

                            <select
                                name="availability"
                                class="form-select"
                            >

                                <option value="Available">

                                    Available

                                </option>

                                <option value="Unavailable">

                                    Unavailable

                                </option>

                            </select>

                        </div>


                        <!-- BUTTON -->

                        <div class="col-md-12">

                            <button
                                type="submit"
                                name="create_service"
                                value="1"
                                class="btn btn-primary"
                            >

                                🚀 Publish Service

                            </button>

                        </div>

                    </div>

                </form>

            </div>


            <!-- MY SERVICES -->

            <div class="card p-4 mb-4 shadow-sm">

                <h5 class="fw-bold mb-3">

                    📋 My Services

                </h5>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>

                            <tr>

                                <th>Service</th>

                                <th>Category</th>

                                <th>Type</th>

                                <th>Price</th>

                                <th>Status</th>

                                <th></th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (
                                !$my_services_result ||
                                mysqli_num_rows($my_services_result) === 0
                            ): ?>

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center text-muted"
                                    >

                                        You have not published any services yet.

                                    </td>

                                </tr>

                            <?php else: ?>


                                <?php while (
                                    $mine = mysqli_fetch_assoc(
                                        $my_services_result
                                    )
                                ): ?>

                                    <tr>

                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $mine['title']
                                                ); ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $mine['category']
                                            ); ?>

                                        </td>


                                        <td>

                                            <span class="badge bg-info text-dark">

                                                <?= htmlspecialchars(
                                                    $mine['service_type']
                                                ); ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?php if (
                                                $mine['price'] !== null
                                            ): ?>

                                                <?= htmlspecialchars(
                                                    number_format(
                                                        (float) $mine['price'],
                                                        2
                                                    )
                                                ); ?>

                                            <?php else: ?>

                                                <span class="text-muted">

                                                    Free / Not specified

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $mine['status'] === 'Active'
                                            ): ?>

                                                <span class="badge bg-success">

                                                    Active

                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">

                                                    Inactive

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <td>

                                            <?php if (
                                                $mine['status'] === 'Active'
                                            ): ?>

                                                <form
                                                    method="POST"
                                                    action="marketplace.php"
                                                >

                                                    <?= generate_csrf_field(); ?>

                                                    <input
                                                        type="hidden"
                                                        name="service_id"
                                                        value="<?= (int) $mine['id']; ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        name="close_service"
                                                        value="1"
                                                        class="btn btn-sm btn-outline-danger"
                                                    >

                                                        Deactivate

                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>


                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- BROWSE MARKETPLACE -->

            <div class="card p-4 shadow-sm">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5 class="fw-bold mb-1">

                            🔎 Browse Services

                        </h5>

                        <p class="text-muted mb-0">

                            Find services offered by other students.

                        </p>

                    </div>

                </div>


                <!-- FILTER -->

                <form
                    method="GET"
                    class="row g-2 mb-4"
                >


                    <div class="col-md-4">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search services..."
                            value="<?= htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-3">

                        <select
                            name="category"
                            class="form-select"
                        >

                            <option value="">

                                All Categories

                            </option>


                            <?php if ($categories_result): ?>

                                <?php while (
                                    $category_row =
                                        mysqli_fetch_assoc(
                                            $categories_result
                                        )
                                ): ?>

                                    <option
                                        value="<?= htmlspecialchars(
                                            $category_row['category']
                                        ); ?>"
                                        <?= $category_filter ===
                                            $category_row['category']
                                            ? 'selected'
                                            : ''; ?>
                                    >

                                        <?= htmlspecialchars(
                                            $category_row['category']
                                        ); ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <select
                            name="service_type"
                            class="form-select"
                        >

                            <option value="">

                                All Service Types

                            </option>

                            <option
                                value="Skill Exchange"
                                <?= $service_type_filter ===
                                    'Skill Exchange'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Skill Exchange

                            </option>

                            <option
                                value="Student Service"
                                <?= $service_type_filter ===
                                    'Student Service'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Student Service

                            </option>

                            <option
                                value="Academic Support"
                                <?= $service_type_filter ===
                                    'Academic Support'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Academic Support

                            </option>

                            <option
                                value="Project Support"
                                <?= $service_type_filter ===
                                    'Project Support'
                                    ? 'selected'
                                    : ''; ?>
                            >

                                Project Support

                            </option>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-outline-primary w-100"
                        >

                            Search

                        </button>

                    </div>

                </form>


                <!-- SERVICE CARDS -->

                <div class="row g-3">

                    <?php if (
                        !$listings_result ||
                        mysqli_num_rows($listings_result) === 0
                    ): ?>

                        <div class="col-12">

                            <div class="alert alert-light text-center">

                                No active services match your search.

                            </div>

                        </div>

                    <?php else: ?>


                        <?php while (
                            $listing = mysqli_fetch_assoc(
                                $listings_result
                            )
                        ): ?>

                            <div class="col-md-6">

                                <div class="card h-100 border shadow-sm">

                                    <div class="card-body">


                                        <div class="d-flex justify-content-between align-items-start mb-2">

                                            <span class="badge bg-primary">

                                                <?= htmlspecialchars(
                                                    $listing['service_type']
                                                ); ?>

                                            </span>


                                            <span class="badge bg-light text-dark">

                                                ⭐
                                                <?= (int) $listing[
                                                    'reputation_points'
                                                ]; ?>

                                            </span>

                                        </div>


                                        <h5 class="fw-bold">

                                            <?= htmlspecialchars(
                                                $listing['title']
                                            ); ?>

                                        </h5>


                                        <p class="text-muted small">

                                            <?= htmlspecialchars(
                                                $listing['category']
                                            ); ?>

                                        </p>


                                        <p>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $listing['description']
                                                )
                                            ); ?>

                                        </p>


                                        <?php if (
                                            $listing['price'] !== null
                                        ): ?>

                                            <p class="fw-bold">

                                                Price:

                                                <?= htmlspecialchars(
                                                    number_format(
                                                        (float) $listing['price'],
                                                        2
                                                    )
                                                ); ?>

                                            </p>

                                        <?php else: ?>

                                            <p class="text-success fw-bold">

                                                Free / Price negotiable

                                            </p>

                                        <?php endif; ?>


                                        <p class="small text-muted mb-1">

                                            👤

                                            <?= htmlspecialchars(
                                                $listing['full_name']
                                            ); ?>

                                        </p>


                                        <?php if (
                                            !empty(
                                                $listing['department']
                                            )
                                        ): ?>

                                            <p class="small text-muted">

                                                🏫

                                                <?= htmlspecialchars(
                                                    $listing['department']
                                                ); ?>

                                            </p>

                                        <?php endif; ?>


                                        <form
                                            method="POST"
                                            action="request_collaboration.php"
                                            class="mt-3"
                                        >

                                            <?= generate_csrf_field(); ?>

                                            <input
                                                type="hidden"
                                                name="student_id"
                                                value="<?= (int) $listing['owner_id']; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-outline-primary btn-sm"
                                            >

                                                🤝 Contact Provider

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        <?php endwhile; ?>


                    <?php endif; ?>

                </div>

            </div>


        </div>

    </div>

</div>


<?php

include "../templates/footer.php";

?>