<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

include "../templates/header.php";
include "../templates/navbar.php";

?>

<div class="container-fluid">

    <div class="row">

        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =====================================================
             MAIN CONTENT
        ====================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card p-4">


                <h2>
                    📚 Study Groups
                </h2>


                <p class="text-muted">
                    Create and join academic study groups.
                </p>


                <hr>


                <!-- =================================================
                     GROUP CREATED MESSAGE
                ================================================== -->

                <?php if (isset($_GET['created'])): ?>

                    <div class="alert alert-success">

                        ✅ Study group created successfully.

                        <br>

                        <small>
                            Your study group is now
                            <strong>pending lecturer approval</strong>.
                        </small>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     CREATE STUDY GROUP
                ================================================== -->

                <h4>
                    Create a Study Group
                </h4>


                <form
                    action="create_group.php"
                    method="POST">


                    <div class="mb-3">

                        <label class="form-label">
                            Group Name
                        </label>


                        <input
                            type="text"
                            name="group_name"
                            class="form-control"
                            placeholder="Example: Database Systems Study Group"
                            required>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Category
                        </label>


                        <select
                            name="category"
                            class="form-control"
                            required>


                            <option value="">
                                Select Category
                            </option>


                            <option value="Programming">
                                Programming
                            </option>


                            <option value="Networking">
                                Networking
                            </option>


                            <option value="Cybersecurity">
                                Cybersecurity
                            </option>


                            <option value="Database">
                                Database
                            </option>


                            <option value="Web Development">
                                Web Development
                            </option>


                            <option value="Final Year Project">
                                Final Year Project
                            </option>


                            <option value="Other">
                                Other
                            </option>


                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>


                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Describe the purpose of this study group..."
                            required></textarea>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        ➕ Create Study Group

                    </button>


                </form>


                <hr class="my-4">


                <!-- =================================================
                     AVAILABLE STUDY GROUPS
                ================================================== -->

                <h4>
                    📚 Available Study Groups
                </h4>


                <p class="text-muted">
                    Browse approved study groups and find
                    other students with similar academic interests.
                </p>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Get Study Groups
                |--------------------------------------------------------------------------
                |
                | Students can see:
                |
                | 1. All APPROVED groups
                | 2. Their own PENDING groups
                | 3. Their own REJECTED groups
                |
                */

                $groups_sql = "SELECT
                                    study_groups.id,
                                    study_groups.creator_id,
                                    study_groups.group_name,
                                    study_groups.description,
                                    study_groups.category,
                                    study_groups.created_at,
                                    study_groups.status,

                                    users.full_name AS creator_name,

                                    (
                                        SELECT COUNT(*)
                                        FROM study_group_members
                                        WHERE study_group_members.group_id =
                                              study_groups.id
                                    ) AS member_count

                                FROM study_groups

                                INNER JOIN users
                                    ON study_groups.creator_id = users.id

                                WHERE
                                    study_groups.status = 'Approved'
                                    OR
                                    study_groups.creator_id = ?

                                ORDER BY
                                    study_groups.created_at DESC";


                $groups_stmt = mysqli_prepare(
                    $conn,
                    $groups_sql
                );


                if (!$groups_stmt) {

                    die(
                        "Unable to load study groups: " .
                        mysqli_error($conn)
                    );

                }


                mysqli_stmt_bind_param(
                    $groups_stmt,
                    "i",
                    $user_id
                );


                mysqli_stmt_execute(
                    $groups_stmt
                );


                $groups_result =
                    mysqli_stmt_get_result(
                        $groups_stmt
                    );

                ?>


                <?php if (
                    $groups_result &&
                    mysqli_num_rows(
                        $groups_result
                    ) > 0
                ): ?>


                    <div class="row">


                        <?php while (
                            $group =
                            mysqli_fetch_assoc(
                                $groups_result
                            )
                        ): ?>


                            <div class="col-md-6 mb-4">


                                <div
                                    class="card h-100 shadow-sm">


                                    <div class="card-body">


                                        <!-- =================================================
                                             GROUP NAME
                                        ================================================== -->

                                        <h5 class="card-title">

                                            📚

                                            <?= htmlspecialchars(
                                                $group['group_name']
                                            ); ?>

                                        </h5>


                                        <!-- =================================================
                                             CATEGORY
                                        ================================================== -->

                                        <span
                                            class="badge bg-primary">

                                            <?= htmlspecialchars(
                                                $group['category']
                                            ); ?>

                                        </span>


                                        <!-- =================================================
                                             STATUS
                                        ================================================== -->

                                        <div class="mt-2">


                                            <?php if (
                                                $group['status']
                                                === 'Approved'
                                            ): ?>

                                                <span
                                                    class="badge bg-success">

                                                    ✅ Approved

                                                </span>


                                            <?php elseif (
                                                $group['status']
                                                === 'Rejected'
                                            ): ?>

                                                <span
                                                    class="badge bg-danger">

                                                    ❌ Rejected

                                                </span>


                                            <?php else: ?>

                                                <span
                                                    class="badge bg-warning text-dark">

                                                    ⏳ Pending Approval

                                                </span>

                                            <?php endif; ?>


                                        </div>


                                        <!-- =================================================
                                             DESCRIPTION
                                        ================================================== -->

                                        <p class="mt-3">

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $group['description']
                                                )
                                            ); ?>

                                        </p>


                                        <!-- =================================================
                                             CREATOR
                                        ================================================== -->

                                        <p
                                            class="text-muted mb-1">

                                            👤 Created by:

                                            <?= htmlspecialchars(
                                                $group['creator_name']
                                            ); ?>

                                        </p>


                                        <!-- =================================================
                                             MEMBERS
                                        ================================================== -->

                                        <p class="text-muted">

                                            👥 Members:

                                            <?= (int)
                                                $group['member_count']; ?>

                                        </p>


                                        <!-- =================================================
                                             GROUP ACTION
                                        ================================================== -->

                                        <?php if (
                                            $group['status']
                                            === 'Approved'
                                        ): ?>


                                            <a
                                                href="view_group.php?id=<?= (int) $group['id']; ?>"
                                                class="btn btn-outline-primary">

                                                👁 View Group

                                            </a>


                                        <?php elseif (
                                            (int)
                                            $group['creator_id']
                                            === $user_id
                                        ): ?>


                                            <?php if (
                                                $group['status']
                                                === 'Pending'
                                            ): ?>

                                                <div
                                                    class="alert alert-warning mt-3 mb-0">

                                                    ⏳ Your study group is
                                                    waiting for lecturer approval.

                                                </div>


                                            <?php elseif (
                                                $group['status']
                                                === 'Rejected'
                                            ): ?>

                                                <div
                                                    class="alert alert-danger mt-3 mb-0">

                                                    ❌ Your study group was
                                                    rejected by a lecturer.

                                                </div>

                                            <?php endif; ?>


                                        <?php endif; ?>


                                    </div>

                                </div>

                            </div>


                        <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        📚 No approved study groups are
                        currently available.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>