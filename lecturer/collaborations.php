<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'lecturer'
) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Accepted Collaborations
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            collaboration_requests.id,
            collaboration_requests.message,
            collaboration_requests.created_at,

            sender.full_name AS sender_name,
            sender.student_id AS sender_student_id,
            sender.department AS sender_department,
            sender.programme AS sender_programme,

            receiver.full_name AS receiver_name,
            receiver.student_id AS receiver_student_id,
            receiver.department AS receiver_department,
            receiver.programme AS receiver_programme

        FROM collaboration_requests

        INNER JOIN users AS sender
            ON collaboration_requests.sender_id = sender.id

        INNER JOIN users AS receiver
            ON collaboration_requests.receiver_id = receiver.id

        WHERE collaboration_requests.status = 'Accepted'

        ORDER BY collaboration_requests.created_at DESC";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Unable to load collaborations: " .
        mysqli_error($conn)
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Accepted Collaborations | Lecturer | SkillLink UNIMTECH
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container-fluid">

        <span class="navbar-brand">

            🎓 SkillLink UNIMTECH

        </span>

        <span class="text-white">

            👨‍🏫 Lecturer Portal

        </span>

    </div>

</nav>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-3">

            <?php include "../templates/lecturer_sidebar.php"; ?>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9">

            <div class="p-4">

                <h2>

                    🤝 Accepted Collaborations

                </h2>


                <p class="text-muted">

                    View active collaborations between
                    SkillLink UNIMTECH students.

                </p>


                <hr>


                <?php if (
                    mysqli_num_rows($result) > 0
                ): ?>


                    <div class="table-responsive">

                        <table
                            class="table
                                   table-bordered
                                   table-striped
                                   align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Student 1
                                    </th>

                                    <th>
                                        Student 2
                                    </th>

                                    <th>
                                        Collaboration Message
                                    </th>

                                    <th>
                                        Accepted Date
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $collaboration =
                                mysqli_fetch_assoc(
                                    $result
                                )
                            ): ?>


                                <tr>


                                    <!-- STUDENT 1 -->

                                    <td>

                                        <strong>

                                            👤

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'sender_name'
                                                ]
                                            ); ?>

                                        </strong>

                                        <br>

                                        <small
                                            class="text-muted">

                                            ID:

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'sender_student_id'
                                                ]
                                            ); ?>

                                        </small>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'sender_department'
                                                ]
                                            ); ?>

                                        </small>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'sender_programme'
                                                ]
                                            ); ?>

                                        </small>

                                    </td>


                                    <!-- STUDENT 2 -->

                                    <td>

                                        <strong>

                                            👤

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'receiver_name'
                                                ]
                                            ); ?>

                                        </strong>

                                        <br>

                                        <small
                                            class="text-muted">

                                            ID:

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'receiver_student_id'
                                                ]
                                            ); ?>

                                        </small>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'receiver_department'
                                                ]
                                            ); ?>

                                        </small>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $collaboration[
                                                    'receiver_programme'
                                                ]
                                            ); ?>

                                        </small>

                                    </td>


                                    <!-- MESSAGE -->

                                    <td>

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $collaboration[
                                                    'message'
                                                ]
                                            )
                                        ); ?>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <span
                                            class="badge
                                                   bg-success">

                                            ✅ Accepted

                                        </span>

                                        <br><br>

                                        <?= htmlspecialchars(
                                            $collaboration[
                                                'created_at'
                                            ]
                                        ); ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        🤝 No accepted collaborations
                        have been recorded yet.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>