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

$lecturer_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Handle Study Group Moderation
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();


    /*
    |--------------------------------------------------------------------------
    | Validate Group ID
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['group_id']) ||
        !is_numeric($_POST['group_id'])
    ) {
        die("Invalid study group.");
    }

    $group_id = (int) $_POST['group_id'];


    /*
    |--------------------------------------------------------------------------
    | Determine Moderation Action
    |--------------------------------------------------------------------------
    */

    if (isset($_POST['approve_group'])) {

        $new_status = 'Approved';

    } elseif (isset($_POST['reject_group'])) {

        $new_status = 'Rejected';

    } else {

        die("Invalid moderation action.");
    }


    /*
    |--------------------------------------------------------------------------
    | Update Study Group
    |--------------------------------------------------------------------------
    */

    $update_sql = "UPDATE study_groups
                   SET status = ?,
                       approved_by = ?,
                       approved_at = CURRENT_TIMESTAMP
                   WHERE id = ?";

    $update_stmt = mysqli_prepare(
        $conn,
        $update_sql
    );


    if (!$update_stmt) {

        die(
            "Database error: " .
            mysqli_error($conn)
        );

    }


    mysqli_stmt_bind_param(
        $update_stmt,
        "sii",
        $new_status,
        $lecturer_id,
        $group_id
    );


    if (!mysqli_stmt_execute($update_stmt)) {

        die(
            "Unable to update study group: " .
            mysqli_stmt_error($update_stmt)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Redirect Back to Study Groups
    |--------------------------------------------------------------------------
    */

    header(
        "Location: study_groups.php?updated=1"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Get All Study Groups
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            study_groups.id,
            study_groups.group_name,
            study_groups.description,
            study_groups.category,
            study_groups.created_at,
            study_groups.status,
            study_groups.approved_by,
            study_groups.approved_at,

            users.full_name AS creator_name

        FROM study_groups

        INNER JOIN users
            ON study_groups.creator_id = users.id

        ORDER BY study_groups.created_at DESC";

$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Unable to load study groups: " .
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
        Study Groups | Lecturer | SkillLink UNIMTECH
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
                    📚 Study Groups
                </h2>


                <p class="text-muted">
                    View and manage study groups created
                    by students on SkillLink UNIMTECH.
                </p>


                <hr>


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if (
                    isset($_GET['updated']) &&
                    $_GET['updated'] === '1'
                ): ?>

                    <div class="alert alert-success">

                        ✅ Study group status updated successfully.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     STUDY GROUP TABLE
                ================================================== -->

                <?php if (
                    mysqli_num_rows($result) > 0
                ): ?>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered
                                   table-striped align-middle">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Group Name
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Created By
                                    </th>

                                    <th>
                                        Date Created
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
                                $group =
                                mysqli_fetch_assoc($result)
                            ): ?>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Default Existing Groups to Pending
                                |--------------------------------------------------------------------------
                                */

                                $status =
                                    !empty($group['status'])
                                    ? $group['status']
                                    : 'Pending';

                                ?>


                                <tr>


                                    <!-- GROUP NAME -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $group['group_name']
                                            ); ?>

                                        </strong>

                                    </td>


                                    <!-- CATEGORY -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $group['category']
                                            )
                                        ): ?>

                                            <span
                                                class="badge bg-primary">

                                                <?= htmlspecialchars(
                                                    $group['category']
                                                ); ?>

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="text-muted">

                                                Not specified

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DESCRIPTION -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $group['description']
                                            ?: 'No description provided.'
                                        ); ?>

                                    </td>


                                    <!-- CREATOR -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $group['creator_name']
                                        ); ?>

                                    </td>


                                    <!-- DATE CREATED -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $group['created_at']
                                        ); ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>


                                        <?php if (
                                            $status === 'Approved'
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                ✅ Approved

                                            </span>


                                        <?php elseif (
                                            $status === 'Rejected'
                                        ): ?>

                                            <span
                                                class="badge bg-danger">

                                                ❌ Rejected

                                            </span>


                                        <?php else: ?>

                                            <span
                                                class="badge bg-warning text-dark">

                                                ⏳ Pending

                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- ACTION -->

                                    <td>


                                        <!-- VIEW GROUP -->

                                        <a
                                            href="view_group.php?id=<?= (int) $group['id']; ?>"
                                            class="btn btn-primary btn-sm mb-1">

                                            👁 View Group

                                        </a>


                                        <br>


                                        <?php if (
                                            $status === 'Pending'
                                        ): ?>


                                            <!-- APPROVE -->

                                            <form
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to approve this study group?'
                                                    );
                                                ">

<?= generate_csrf_field(); ?>



                                                <input
                                                    type="hidden"
                                                    name="group_id"
                                                    value="<?= (int) $group['id']; ?>">


                                                <button
                                                    type="submit"
                                                    name="approve_group"
                                                    class="btn btn-success btn-sm mb-1">

                                                    ✅ Approve

                                                </button>

                                            </form>


                                            <!-- REJECT -->

                                            <form
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to reject this study group?'
                                                    );
                                                ">

<?= generate_csrf_field(); ?>



                                                <input
                                                    type="hidden"
                                                    name="group_id"
                                                    value="<?= (int) $group['id']; ?>">


                                                <button
                                                    type="submit"
                                                    name="reject_group"
                                                    class="btn btn-danger btn-sm mb-1">

                                                    ❌ Reject

                                                </button>

                                            </form>


                                        <?php elseif (
                                            $status === 'Approved'
                                        ): ?>


                                            <span
                                                class="text-success">

                                                ✅ Group Approved

                                            </span>


                                        <?php elseif (
                                            $status === 'Rejected'
                                        ): ?>


                                            <span
                                                class="text-danger">

                                                ❌ Group Rejected

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

                        📚 No study groups have been
                        created yet.

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