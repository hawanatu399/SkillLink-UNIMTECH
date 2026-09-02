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
| Validate Student ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student profile.");
}

$student_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Handle Skill Verification
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();


    if (!isset($_POST['verify_skill_id'])) {
        die("Invalid verification request.");
    }

    $skill_id = (int) $_POST['verify_skill_id'];

    if ($skill_id <= 0) {
        die("Invalid skill ID.");
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm that this skill belongs to this student
    |--------------------------------------------------------------------------
    */

    $check_sql = "SELECT id
                  FROM skills
                  WHERE id = ?
                  AND user_id = ?
                  LIMIT 1";

    $check_stmt = mysqli_prepare(
        $conn,
        $check_sql
    );

    if (!$check_stmt) {
        die(
            "Database error while checking skill: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $check_stmt,
        "ii",
        $skill_id,
        $student_id
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result(
        $check_stmt
    );

    if (mysqli_num_rows($check_result) === 0) {
        die("This skill does not belong to this student.");
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Skill
    |--------------------------------------------------------------------------
    | This uses the same UPDATE approach as the working
    | lecturer/skills.php verification system.
    |--------------------------------------------------------------------------
    */

    $verify_sql = "UPDATE skills
                   SET verified = 1,
                       verified_by = ?,
                       verified_at = CURRENT_TIMESTAMP
                   WHERE id = ?";

    $verify_stmt = mysqli_prepare(
        $conn,
        $verify_sql
    );

    if (!$verify_stmt) {
        die(
            "Database error while preparing verification: "
            . mysqli_error($conn)
        );
    }

    mysqli_stmt_bind_param(
        $verify_stmt,
        "ii",
        $lecturer_id,
        $skill_id
    );

    if (!mysqli_stmt_execute($verify_stmt)) {
        die(
            "Unable to verify skill: "
            . mysqli_stmt_error($verify_stmt)
        );
    }

    recalculate_reputation($conn, $student_id);


    /*
    |--------------------------------------------------------------------------
    | Confirm Update
    |--------------------------------------------------------------------------
    */

    if (mysqli_stmt_affected_rows($verify_stmt) < 1) {
        die(
            "The verification request was received, "
            . "but the skill was not updated."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Return to Student Profile
    |--------------------------------------------------------------------------
    */

    header(
        "Location: view_student.php?id="
        . $student_id
        . "&verified=1"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Get Student Information
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
            bio,
            interests,
            profile_picture,
            reputation_points,
            status

        FROM users

        WHERE id = ?
        AND role = 'student'

        LIMIT 1";

$stmt = mysqli_prepare(
    $conn,
    $sql
);

if (!$stmt) {
    die(
        "Database error: "
        . mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student profile not found.");
}


/*
|--------------------------------------------------------------------------
| Get Student Skills
|--------------------------------------------------------------------------
*/

$skill_sql = "SELECT
                  id,
                  skill_name,
                  skill_level,
                  description,
                  verified,
                  verified_at

              FROM skills

              WHERE user_id = ?

              ORDER BY created_at DESC";

$skill_stmt = mysqli_prepare(
    $conn,
    $skill_sql
);

if (!$skill_stmt) {
    die(
        "Database error while loading skills: "
        . mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $skill_stmt,
    "i",
    $student_id
);

mysqli_stmt_execute($skill_stmt);

$skills_result = mysqli_stmt_get_result(
    $skill_stmt
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Student Profile | Lecturer | SkillLink UNIMTECH
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


        <!-- LECTURER SIDEBAR -->

        <div class="col-md-3">

            <?php include "../templates/lecturer_sidebar.php"; ?>

        </div>


        <!-- MAIN CONTENT -->

        <div class="col-md-9">

            <div class="p-4">


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
                     STUDENT PROFILE
                ================================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-body text-center">


                        <?php if (
                            !empty(
                                $student['profile_picture']
                            )
                            &&
                            $student['profile_picture']
                            !== 'default.png'
                        ): ?>

                            <img
                                src="../uploads/profile/<?= htmlspecialchars(
                                    $student['profile_picture']
                                ); ?>"
                                alt="Student Profile"
                                class="rounded-circle mb-3"
                                width="120"
                                height="120"
                                style="object-fit: cover;">

                        <?php else: ?>

                            <div
                                class="rounded-circle bg-secondary
                                       text-white d-inline-flex
                                       align-items-center justify-content-center
                                       mb-3"
                                style="
                                    width: 120px;
                                    height: 120px;
                                    font-size: 45px;
                                ">

                                👤

                            </div>

                        <?php endif; ?>


                        <h2>

                            👤
                            <?= htmlspecialchars(
                                $student['full_name']
                            ); ?>

                        </h2>


                        <p class="text-muted mb-1">

                            <?= htmlspecialchars(
                                $student['department']
                            ); ?>

                        </p>


                        <?php if (
                            $student['status'] === 'Active'
                        ): ?>

                            <span class="badge bg-success">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">
                                Inactive
                            </span>

                        <?php endif; ?>


                    </div>

                </div>


                <!-- =================================================
                     ACADEMIC INFORMATION
                ================================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-body">

                        <h4>
                            🎓 Academic Information
                        </h4>

                        <hr>


                        <div class="row">


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Student ID:
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $student['student_id']
                                ); ?>

                            </div>


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Email:
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $student['email']
                                ); ?>

                            </div>


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Department:
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $student['department']
                                ); ?>

                            </div>


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Programme:
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $student['programme']
                                ); ?>

                            </div>


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Level:
                                </strong>

                                <br>

                                <?= htmlspecialchars(
                                    $student['level']
                                ); ?>

                            </div>


                            <div class="col-md-6 mb-3">

                                <strong>
                                    Reputation Points:
                                </strong>

                                <br>

                                ⭐
                                <?= (int)
                                    $student['reputation_points']; ?>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ABOUT STUDENT
                ================================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-body">

                        <h4>
                            📝 About Student
                        </h4>

                        <hr>


                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $student['bio']
                                    ?: 'No biography provided.'
                                )
                            ); ?>

                        </p>


                        <h5>
                            🎯 Interests
                        </h5>


                        <p>

                            <?= nl2br(
                                htmlspecialchars(
                                    $student['interests']
                                    ?: 'No interests provided.'
                                )
                            ); ?>

                        </p>

                    </div>

                </div>


                <!-- =================================================
                     STUDENT SKILLS
                ================================================== -->

                <div class="card shadow-sm mb-4">

                    <div class="card-body">

                        <h4>
                            💡 Student Skills
                        </h4>

                        <hr>


                        <?php if (
                            mysqli_num_rows(
                                $skills_result
                            ) > 0
                        ): ?>


                            <div class="table-responsive">

                                <table
                                    class="table table-bordered
                                           table-striped align-middle">

                                    <thead class="table-dark">

                                        <tr>

                                            <th>
                                                Skill
                                            </th>

                                            <th>
                                                Level
                                            </th>

                                            <th>
                                                Description
                                            </th>

                                            <th>
                                                Verification
                                            </th>

                                            <th>
                                                Action
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php while (
                                        $skill =
                                        mysqli_fetch_assoc(
                                            $skills_result
                                        )
                                    ): ?>


                                        <tr>


                                            <!-- SKILL -->

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $skill['skill_name']
                                                    ); ?>

                                                </strong>

                                            </td>


                                            <!-- LEVEL -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $skill['skill_level']
                                                ); ?>

                                            </td>


                                            <!-- DESCRIPTION -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $skill['description']
                                                    ?: 'No description.'
                                                ); ?>

                                            </td>


                                            <!-- VERIFICATION -->

                                            <td>

                                                <?php if (
                                                    (int)
                                                    $skill['verified']
                                                    === 1
                                                ): ?>

                                                    <span
                                                        class="badge bg-success">

                                                        🏅 Lecturer Verified

                                                    </span>


                                                    <?php if (
                                                        !empty(
                                                            $skill['verified_at']
                                                        )
                                                    ): ?>

                                                        <br>

                                                        <small
                                                            class="text-muted">

                                                            Verified:
                                                            <?= htmlspecialchars(
                                                                $skill['verified_at']
                                                            ); ?>

                                                        </small>

                                                    <?php endif; ?>


                                                <?php else: ?>

                                                    <span
                                                        class="badge bg-warning text-dark">

                                                        ⏳ Not Verified

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
                                                        action="view_student.php?id=<?= (int) $student_id; ?>"
                                                    >

                                                        <?= generate_csrf_field(); ?>

                                                        <input
                                                            type="hidden"
                                                            name="verify_skill_id"
                                                            value="<?= (int) $skill['id']; ?>">


                                                        <button
                                                            type="submit"
                                                            class="btn btn-success btn-sm">

                                                            🏅 Verify Skill

                                                        </button>

                                                    </form>

                                                <?php else: ?>

                                                    <span
                                                        class="text-success">

                                                        ✅ Verified

                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <p class="text-muted">

                                This student has not added
                                any skills yet.

                            </p>

                        <?php endif; ?>


                    </div>

                </div>


                <!-- BACK TO STUDENTS -->

                <div class="text-center mb-4">

                    <a
                        href="students.php"
                        class="btn btn-secondary">

                        ← Back to Students

                    </a>

                </div>


            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>