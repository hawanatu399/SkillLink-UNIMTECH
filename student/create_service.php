<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get student's skills
|--------------------------------------------------------------------------
*/

$sql = "SELECT id, skill_name, skill_level
        FROM skills
        WHERE user_id = ?
        ORDER BY skill_name ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

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

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h2>➕ Offer a Service</h2>

                        <p class="text-muted mb-0">
                            Create a service listing that other
                            UNIMTECH students can discover and request.
                        </p>

                    </div>

                    <a
                        href="marketplace.php"
                        class="btn btn-outline-primary">

                        🛒 Marketplace

                    </a>

                </div>

                <hr>

                <?php if (isset($_GET['error'])): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET['error']); ?>
                    </div>

                <?php endif; ?>


                <?php if (mysqli_num_rows($result) === 0): ?>

                    <div class="alert alert-warning">

                        <h5>⚠️ Add a skill first</h5>

                        <p>
                            You need at least one skill before
                            creating a marketplace service.
                        </p>

                        <a
                            href="skills.php"
                            class="btn btn-primary">

                            💡 Add My Skill

                        </a>

                    </div>

                <?php else: ?>


                    <form
                        action="save_service.php"
                        method="POST">

                        <!-- SERVICE TITLE -->
                        <div class="mb-3">

                            <label class="form-label">
                                Service Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                maxlength="200"
                                placeholder="Example: Website Design"
                                required>

                        </div>


                        <!-- RELATED SKILL -->
                        <div class="mb-3">

                            <label class="form-label">
                                Related Skill
                            </label>

                            <select
                                name="skill_id"
                                class="form-select"
                                required>

                                <option value="">
                                    Select your skill
                                </option>

                                <?php while (
                                    $skill =
                                    mysqli_fetch_assoc($result)
                                ): ?>

                                    <option
                                        value="<?= (int) $skill['id']; ?>">

                                        <?= htmlspecialchars(
                                            $skill['skill_name']
                                        ); ?>

                                        -
                                        <?= htmlspecialchars(
                                            $skill['skill_level']
                                        ); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- CATEGORY -->
                        <div class="mb-3">

                            <label class="form-label">
                                Category
                            </label>

                            <select
                                name="category"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Category
                                </option>

                                <option value="Web Development">
                                    Web Development
                                </option>

                                <option value="Graphic Design">
                                    Graphic Design
                                </option>

                                <option value="Programming">
                                    Programming
                                </option>

                                <option value="Networking">
                                    Networking
                                </option>

                                <option value="Database">
                                    Database
                                </option>

                                <option value="Cybersecurity">
                                    Cybersecurity
                                </option>

                                <option value="Academic Support">
                                    Academic Support
                                </option>

                                <option value="Writing">
                                    Writing
                                </option>

                                <option value="Photography">
                                    Photography
                                </option>

                                <option value="Video Editing">
                                    Video Editing
                                </option>

                                <option value="Other">
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- SERVICE TYPE -->
                        <div class="mb-3">

                            <label class="form-label">
                                Service Type
                            </label>

                            <select
                                name="service_type"
                                class="form-select"
                                required>

                                <option value="Student Service">
                                    Student Service
                                </option>

                                <option value="Skill Exchange">
                                    Skill Exchange
                                </option>

                                <option value="Academic Support">
                                    Academic Support
                                </option>

                                <option value="Project Support">
                                    Project Support
                                </option>

                            </select>

                        </div>


                        <!-- DESCRIPTION -->
                        <div class="mb-3">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="6"
                                maxlength="3000"
                                placeholder="Explain clearly what you offer, what students can request, and what they can expect..."
                                required></textarea>

                        </div>


                        <!-- PRICE -->
                        <div class="mb-3">

                            <label class="form-label">
                                💰 Service Price
                            </label>

                            <div class="input-group">

                                <span class="input-group-text">
                                    Le
                                </span>

                                <input
                                    type="number"
                                    name="price"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    placeholder="Example: 150.00"
                                    required>

                            </div>

                            <small class="text-muted">
                                Enter the price for the service in Sierra Leonean Leones.
                            </small>

                        </div>


                        <!-- AVAILABILITY -->
                        <div class="mb-4">

                            <label class="form-label">
                                Availability
                            </label>

                            <select
                                name="availability"
                                class="form-select">

                                <option value="Available">
                                    Available
                                </option>

                                <option value="Unavailable">
                                    Unavailable
                                </option>

                            </select>

                        </div>


                        <!-- SUBMIT -->
                        <button
                            type="submit"
                            class="btn btn-success">

                            🚀 Publish Service

                        </button>


                        <a
                            href="marketplace.php"
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