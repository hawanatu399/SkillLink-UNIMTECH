<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

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

            <div class="card p-4">

                <h2>🤝 Collaboration Requests</h2>

                <p class="text-muted">
                    View and manage requests from other students.
                </p>

                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Get collaboration requests received by the logged-in student
                |--------------------------------------------------------------------------
                */

                $sql = "SELECT
                            collaboration_requests.id,
                            collaboration_requests.message,
                            collaboration_requests.status,
                            collaboration_requests.created_at,

                            users.id AS sender_id,
                            users.full_name,
                            users.department,
                            users.programme,
                            users.level

                        FROM collaboration_requests

                        INNER JOIN users
                            ON collaboration_requests.sender_id = users.id

                        WHERE collaboration_requests.receiver_id = ?

                        ORDER BY collaboration_requests.created_at DESC";


                $stmt = mysqli_prepare($conn, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $user_id
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                ?>


                <?php if (mysqli_num_rows($result) > 0): ?>


                    <?php while ($request = mysqli_fetch_assoc($result)): ?>


                        <div class="card mb-3 shadow-sm">

                            <div class="card-body">

                                <h5 class="card-title">

                                    👤
                                    <?= htmlspecialchars($request['full_name']); ?>

                                </h5>


                                <p class="mb-1">

                                    <strong>Department:</strong>

                                    <?= htmlspecialchars(
                                        $request['department']
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>Programme:</strong>

                                    <?= htmlspecialchars(
                                        $request['programme']
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>Level:</strong>

                                    <?= htmlspecialchars(
                                        $request['level']
                                    ); ?>

                                </p>


                                <p class="mt-3">

                                    <strong>Message:</strong><br>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $request['message']
                                        )
                                    ); ?>

                                </p>


                                <p>

                                    <strong>Status:</strong>

                                    <?php if ($request['status'] === 'Pending'): ?>

                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>

                                    <?php elseif ($request['status'] === 'Accepted'): ?>

                                        <span class="badge bg-success">
                                            Accepted
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Rejected
                                        </span>

                                    <?php endif; ?>

                                </p>


                                <p class="text-muted">

                                    <small>

                                        Sent on:
                                        <?= htmlspecialchars(
                                            $request['created_at']
                                        ); ?>

                                    </small>

                                </p>


                                <?php if ($request['status'] === 'Pending'): ?>

                                    <div class="mt-3">

                                        <form method="POST" action="update_collaboration.php" class="d-inline">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $request['id']; ?>">
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-success">
                                                ✅ Accept
                                            </button>
                                        </form>

                                        <form method="POST" action="update_collaboration.php" class="d-inline">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $request['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-danger">
                                                ❌ Reject
                                            </button>
                                        </form>

                                    </div>

                                <?php endif; ?>


                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="alert alert-info">

                        You currently have no collaboration requests.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>