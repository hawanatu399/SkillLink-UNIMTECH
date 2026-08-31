<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
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
        Registered Students | Lecturer | SkillLink UNIMTECH
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>


<body>


<!-- =========================================================
     TOP NAVIGATION
========================================================= -->

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


<!-- =========================================================
     PAGE LAYOUT
========================================================= -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             REUSABLE LECTURER SIDEBAR
        ================================================== -->

        <div class="col-md-3">

            <?php include "../templates/lecturer_sidebar.php"; ?>

        </div>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="col-md-9">

            <div class="p-4">


                <h2>
                    👨‍🎓 Registered Students
                </h2>


                <p class="text-muted">
                    View students registered on
                    SkillLink UNIMTECH.
                </p>


                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Get Registered Students
                |--------------------------------------------------------------------------
                */

                $sql = "SELECT
                            id,
                            full_name,
                            student_id,
                            email,
                            department,
                            programme,
                            level,
                            status

                        FROM users

                        WHERE role = 'student'

                        ORDER BY full_name ASC";

                $result = mysqli_query($conn, $sql);

                ?>


                <?php if (
                    $result &&
                    mysqli_num_rows($result) > 0
                ): ?>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered
                                   table-striped align-middle">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Name
                                    </th>

                                    <th>
                                        Student ID
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Department
                                    </th>

                                    <th>
                                        Programme
                                    </th>

                                    <th>
                                        Level
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $student =
                                mysqli_fetch_assoc($result)
                            ): ?>


                                <tr>


                                    <!-- STUDENT NAME -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['full_name']
                                        ); ?>

                                    </td>


                                    <!-- STUDENT ID -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['student_id']
                                        ); ?>

                                    </td>


                                    <!-- EMAIL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['email']
                                        ); ?>

                                    </td>


                                    <!-- DEPARTMENT -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['department']
                                        ); ?>

                                    </td>


                                    <!-- PROGRAMME -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['programme']
                                        ); ?>

                                    </td>


                                    <!-- LEVEL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $student['level']
                                        ); ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php if (
                                            $student['status']
                                            === 'Active'
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                Active

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-danger">

                                                Inactive

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- VIEW PROFILE -->

                                    <td>

                                        <a
                                            href="view_student.php?id=<?= (int) $student['id']; ?>"
                                            class="btn btn-primary btn-sm">

                                            👁 View Profile

                                        </a>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        No students have been
                        registered yet.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     BOOTSTRAP JAVASCRIPT
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>