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

                <h2>🤝 My Collaborations</h2>

                <p class="text-muted">
                    Students you are currently collaborating with.
                </p>

                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Find accepted collaborations
                |--------------------------------------------------------------------------
                */

                $sql = "SELECT
                            collaboration_requests.id,
                            collaboration_requests.sender_id,
                            collaboration_requests.receiver_id,
                            collaboration_requests.created_at,

                            sender.full_name AS sender_name,
                            sender.department AS sender_department,
                            sender.programme AS sender_programme,

                            receiver.full_name AS receiver_name,
                            receiver.department AS receiver_department,
                            receiver.programme AS receiver_programme

                        FROM collaboration_requests

                        INNER JOIN users AS sender
                            ON collaboration_requests.sender_id = sender.id

                        INNER JOIN users AS receiver
                            ON collaboration_requests.receiver_id = receiver.id

                        WHERE
                            (
                                collaboration_requests.sender_id = ?
                                OR
                                collaboration_requests.receiver_id = ?
                            )

                        AND collaboration_requests.status = 'Accepted'

                        ORDER BY collaboration_requests.created_at DESC";


                $stmt = mysqli_prepare($conn, $sql);

                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $user_id,
                    $user_id
                );

                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                ?>


                <?php if (mysqli_num_rows($result) > 0): ?>


                    <?php while ($collaboration = mysqli_fetch_assoc($result)): ?>


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | Determine the other student
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $collaboration['sender_id']
                            == $user_id
                        ) {

                            $other_name =
                                $collaboration['receiver_name'];

                            $other_department =
                                $collaboration['receiver_department'];

                            $other_programme =
                                $collaboration['receiver_programme'];

                            $other_id =
                                $collaboration['receiver_id'];

                        } else {

                            $other_name =
                                $collaboration['sender_name'];

                            $other_department =
                                $collaboration['sender_department'];

                            $other_programme =
                                $collaboration['sender_programme'];

                            $other_id =
                                $collaboration['sender_id'];

                        }

                        ?>


                        <div class="card mb-3 shadow-sm">

                            <div class="card-body">

                                <h5 class="card-title">

                                    👤
                                    <?= htmlspecialchars(
                                        $other_name
                                    ); ?>

                                </h5>


                                <p class="mb-1">

                                    <strong>
                                        Department:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $other_department
                                    ); ?>

                                </p>


                                <p class="mb-1">

                                    <strong>
                                        Programme:
                                    </strong>

                                    <?= htmlspecialchars(
                                        $other_programme
                                    ); ?>

                                </p>


                                <p class="mt-3">

                                    <span class="badge bg-success">

                                        ✅ Active Collaboration

                                    </span>

                                </p>


                                <a
                                    href="view_profile.php?id=<?= $other_id; ?>"
                                    class="btn btn-primary btn-sm">

                                    View Profile

                                </a>

                            </div>

                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="alert alert-info">

                        You do not have any accepted collaborations yet.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>