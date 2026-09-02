<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/reputation.php";

/*
|--------------------------------------------------------------------------
| Lecturer Access Only
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'lecturer') {
    header("Location: ../login.php");
    exit();
}

$lecturer_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Handle Skill Verification
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['verify_skill_id'])
) {

    verify_csrf();

    $skill_id = (int) $_POST['verify_skill_id'];

    $verify_sql = "UPDATE skills
                   SET verified = 1,
                       verified_by = ?,
                       verified_at = CURRENT_TIMESTAMP
                   WHERE id = ?";

    $verify_stmt = mysqli_prepare(
        $conn,
        $verify_sql
    );

    mysqli_stmt_bind_param(
        $verify_stmt,
        "ii",
        $lecturer_id,
        $skill_id
    );

    mysqli_stmt_execute($verify_stmt);

    $skill_owner_result = mysqli_query($conn, "SELECT user_id FROM skills WHERE id = " . (int) $skill_id);
    if ($skill_owner_row = mysqli_fetch_assoc($skill_owner_result)) {
        recalculate_reputation($conn, $skill_owner_row['user_id']);
    }

    header("Location: skills.php?verified=1");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get All Student Skills
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            skills.id,
            skills.skill_name,
            skills.skill_level,
            skills.description,
            skills.verified,
            skills.verified_at,

            users.full_name,
            users.student_id,
            users.department,
            users.programme,
            users.level

        FROM skills

        INNER JOIN users
            ON skills.user_id = users.id

        WHERE users.role = 'student'

        ORDER BY skills.created_at DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Student Skills | Lecturer | SkillLink UNIMTECH
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
                    💡 Student Skills
                </h2>


                <p class="text-muted">
                    Review skills submitted by students
                    and verify appropriate skills.
                </p>


                <hr>


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if (
                    isset($_GET['verified']) &&
                    $_GET['verified'] === '1'
                ): ?>

                    <div class="alert alert-success">

                        ✅ Skill successfully verified.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     SKILLS TABLE
                ================================================== -->

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
                                        Student
                                    </th>

                                    <th>
                                        Student ID
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
                                        Skill
                                    </th>

                                    <th>
                                        Skill Level
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
                                $skill =
                                mysqli_fetch_assoc($result)
                            ): ?>


                                <tr>


                                    <!-- STUDENT -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['full_name']
                                        ); ?>

                                    </td>


                                    <!-- STUDENT ID -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['student_id']
                                        ); ?>

                                    </td>


                                    <!-- DEPARTMENT -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['department']
                                        ); ?>

                                    </td>


                                    <!-- PROGRAMME -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['programme']
                                        ); ?>

                                    </td>


                                    <!-- LEVEL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['level']
                                        ); ?>

                                    </td>


                                    <!-- SKILL -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $skill['skill_name']
                                            ); ?>

                                        </strong>


                                        <?php if (
                                            !empty(
                                                $skill['description']
                                            )
                                        ): ?>

                                            <br>

                                            <small
                                                class="text-muted">

                                                <?= htmlspecialchars(
                                                    $skill['description']
                                                ); ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>


                                    <!-- SKILL LEVEL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $skill['skill_level']
                                        ); ?>

                                    </td>


                                    <!-- VERIFICATION STATUS -->

                                    <td>

                                        <?php if (
                                            (int)
                                            $skill['verified']
                                            === 1
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                ✅ Verified

                                            </span>

                                            <br>

                                            <small
                                                class="text-muted">

                                                <?= htmlspecialchars(
                                                    $skill['verified_at']
                                                ); ?>

                                            </small>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-warning text-dark">

                                                ⏳ Pending

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTION -->

                                    <td>


                                        <?php if (
                                            (int)
                                            $skill['verified']
                                            === 0
                                        ): ?>


                                            <form
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to verify this skill?'
                                                    );
                                                ">

<?= generate_csrf_field(); ?>



                                                <input
                                                    type="hidden"
                                                    name="verify_skill_id"
                                                    value="<?= (int)
                                                        $skill['id']; ?>">


                                                <button
                                                    type="submit"
                                                    class="btn btn-success btn-sm">

                                                    ✅ Verify Skill

                                                </button>


                                            </form>


                                        <?php else: ?>


                                            <span
                                                class="text-success">

                                                Verified

                                            </span>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        💡 No student skills have been
                        submitted yet.

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