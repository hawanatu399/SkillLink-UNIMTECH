<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Handle New Listing Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_listing'])) {

    verify_csrf();

    $listing_type = $_POST['listing_type'] ?? '';
    $skill_name = trim($_POST['skill_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $availability = trim($_POST['availability'] ?? '');

    if (!in_array($listing_type, ['Offering', 'Seeking'], true) || $skill_name === '' || $description === '') {
        die("Please fill in all required fields. <a href='marketplace.php'>Go back</a>");
    }

    $sql = "INSERT INTO marketplace_listings (user_id, listing_type, skill_name, description, availability)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $listing_type, $skill_name, $description, $availability);
    mysqli_stmt_execute($stmt);

    header("Location: marketplace.php?posted=1");
    exit();
}


/*
|--------------------------------------------------------------------------
| Handle Closing Own Listing
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_listing'])) {

    verify_csrf();

    $listing_id = (int) ($_POST['listing_id'] ?? 0);

    $sql = "UPDATE marketplace_listings SET status = 'Closed' WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $listing_id, $user_id);
    mysqli_stmt_execute($stmt);

    header("Location: marketplace.php?closed=1");
    exit();
}


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$type_filter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = ["ml.status = 'Open'", "ml.user_id != ?"];
$params = [$user_id];
$param_types = "i";

if (in_array($type_filter, ['Offering', 'Seeking'], true)) {
    $where[] = "ml.listing_type = ?";
    $params[] = $type_filter;
    $param_types .= "s";
}

if ($search !== '') {
    $where[] = "ml.skill_name LIKE ?";
    $params[] = "%$search%";
    $param_types .= "s";
}

$where_sql = implode(" AND ", $where);

$sql = "SELECT ml.id, ml.listing_type, ml.skill_name, ml.description, ml.availability, ml.created_at,
               u.id AS owner_id, u.full_name, u.department, u.reputation_points
        FROM marketplace_listings ml
        JOIN users u ON ml.user_id = u.id
        WHERE $where_sql
        ORDER BY ml.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $param_types, ...$params);
mysqli_stmt_execute($stmt);
$listings_result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| My Own Listings
|--------------------------------------------------------------------------
*/

$my_listings_result = mysqli_query(
    $conn,
    "SELECT id, listing_type, skill_name, description, status, created_at
     FROM marketplace_listings
     WHERE user_id = $user_id
     ORDER BY created_at DESC"
);

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3">
            <?php include "../templates/sidebar.php"; ?>
        </div>

        <div class="col-md-9 mt-4">

            <div class="card p-4 mb-4">

                <h2>🛒 Skill Marketplace</h2>
                <p class="text-muted">
                    Post what you can teach or what you're looking to learn —
                    a place to actively advertise, not just wait to be matched.
                </p>

                <?php if (isset($_GET['posted'])): ?>
                    <div class="alert alert-success">Your listing has been posted.</div>
                <?php endif; ?>
                <?php if (isset($_GET['closed'])): ?>
                    <div class="alert alert-success">Listing closed.</div>
                <?php endif; ?>

                <hr>

                <!-- =================================================
                     POST A NEW LISTING
                ================================================== -->

                <h5>Post a Listing</h5>

                <form method="POST" action="marketplace.php" class="row g-2 mb-2">

                    <?= generate_csrf_field(); ?>

                    <div class="col-md-3">
                        <select name="listing_type" class="form-select" required>
                            <option value="">I am...</option>
                            <option value="Offering">Offering to teach</option>
                            <option value="Seeking">Seeking to learn</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="skill_name" class="form-control" placeholder="Skill (e.g. Python)" required>
                    </div>

                    <div class="col-md-4">
                        <input type="text" name="description" class="form-control" placeholder="Short description" required>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" name="create_listing" value="1" class="btn btn-primary w-100">Post</button>
                    </div>

                    <div class="col-md-6">
                        <input type="text" name="availability" class="form-control" placeholder="Availability (optional, e.g. weekday evenings)">
                    </div>

                </form>

            </div>

            <!-- =================================================
                 MY OWN LISTINGS
            ================================================== -->

            <div class="card p-4 mb-4">
                <h5>My Listings</h5>
                <table class="table table-sm">
                    <thead>
                        <tr><th>Type</th><th>Skill</th><th>Status</th><th>Posted</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($my_listings_result) === 0): ?>
                            <tr><td colspan="5" class="text-muted text-center">You haven't posted anything yet.</td></tr>
                        <?php endif; ?>
                        <?php while ($mine = mysqli_fetch_assoc($my_listings_result)): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= $mine['listing_type'] === 'Offering' ? 'success' : 'info'; ?>">
                                        <?= htmlspecialchars($mine['listing_type']); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($mine['skill_name']); ?></td>
                                <td>
                                    <?php if ($mine['status'] === 'Open'): ?>
                                        <span class="badge bg-primary">Open</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($mine['created_at']); ?></td>
                                <td>
                                    <?php if ($mine['status'] === 'Open'): ?>
                                        <form method="POST" action="marketplace.php" class="d-inline">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="listing_id" value="<?= (int) $mine['id']; ?>">
                                            <button type="submit" name="close_listing" value="1" class="btn btn-sm btn-outline-secondary">Close</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- =================================================
                 BROWSE OTHER LISTINGS
            ================================================== -->

            <div class="card p-4">

                <h5>Browse Listings</h5>

                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by skill"
                               value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="Offering" <?= $type_filter === 'Offering' ? 'selected' : ''; ?>>Offering to teach</option>
                            <option value="Seeking" <?= $type_filter === 'Seeking' ? 'selected' : ''; ?>>Seeking to learn</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                    </div>
                </form>

                <div class="row g-3">

                    <?php if (mysqli_num_rows($listings_result) === 0): ?>
                        <p class="text-muted text-center">No listings match your filters right now.</p>
                    <?php endif; ?>

                    <?php while ($listing = mysqli_fetch_assoc($listings_result)): ?>
                        <div class="col-md-6">
                            <div class="card p-3 h-100">

                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="badge bg-<?= $listing['listing_type'] === 'Offering' ? 'success' : 'info'; ?>">
                                        <?= $listing['listing_type'] === 'Offering' ? 'Offering to teach' : 'Seeking to learn'; ?>
                                    </span>
                                    <span class="badge bg-light text-dark">Rep: <?= (int) $listing['reputation_points']; ?></span>
                                </div>

                                <h6 class="mt-2"><?= htmlspecialchars($listing['skill_name']); ?></h6>
                                <p class="small mb-1"><?= htmlspecialchars($listing['description']); ?></p>

                                <?php if (!empty($listing['availability'])): ?>
                                    <p class="small text-muted mb-1">🕒 <?= htmlspecialchars($listing['availability']); ?></p>
                                <?php endif; ?>

                                <p class="small text-muted">
                                    Posted by <?= htmlspecialchars($listing['full_name']); ?>
                                    (<?= htmlspecialchars($listing['department'] ?? '—'); ?>)
                                </p>

                                <form method="POST" action="request_collaboration.php" class="mt-auto">
                                    <?= generate_csrf_field(); ?>
                                    <input type="hidden" name="student_id" value="<?= (int) $listing['owner_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        🤝 Contact About This
                                    </button>
                                </form>

                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>

            </div>

        </div>

    </div>
</div>

<?php include "../templates/footer.php"; ?>
