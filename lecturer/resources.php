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
| Handle Resource Moderation
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Validate Resource ID
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['resource_id']) ||
        !is_numeric($_POST['resource_id'])
    ) {
        die("Invalid resource.");
    }

    $resource_id = (int) $_POST['resource_id'];


    /*
    |--------------------------------------------------------------------------
    | Determine Action
    |--------------------------------------------------------------------------
    */

    if (isset($_POST['approve_resource'])) {

        $new_status = 'Approved';

    } elseif (isset($_POST['reject_resource'])) {

        $new_status = 'Rejected';

    } else {

        die("Invalid moderation action.");
    }


    /*
    |--------------------------------------------------------------------------
    | Update Resource Status
    |--------------------------------------------------------------------------
    */

    $update_sql = "UPDATE resources
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
        $resource_id
    );


    if (!mysqli_stmt_execute($update_stmt)) {

        die(
            "Unable to update resource: " .
            mysqli_stmt_error($update_stmt)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Return to Resources Page
    |--------------------------------------------------------------------------
    */

    header(
        "Location: resources.php?updated=1"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| Get All Learning Resources
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            resources.id,
            resources.title,
            resources.description,
            resources.category,
            resources.file_name,
            resources.file_path,
            resources.uploaded_at,
            resources.status,
            resources.approved_by,
            resources.approved_at,

            users.full_name AS uploader_name

        FROM resources

        INNER JOIN users
            ON resources.user_id = users.id

        ORDER BY resources.uploaded_at DESC";


$result = mysqli_query(
    $conn,
    $sql
);


if (!$result) {

    die(
        "Unable to load learning resources: " .
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
        Learning Resources | Lecturer | SkillLink UNIMTECH
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
                    📁 Learning Resources
                </h2>


                <p class="text-muted">
                    Review and manage learning resources
                    uploaded by members of SkillLink UNIMTECH.
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

                        ✅ Resource status updated successfully.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     RESOURCE TABLE
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
                                        Title
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Uploaded By
                                    </th>

                                    <th>
                                        Date
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
                                $resource =
                                mysqli_fetch_assoc($result)
                            ): ?>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | Existing records without status
                                |--------------------------------------------------------------------------
                                */

                                $status =
                                    !empty($resource['status'])
                                    ? $resource['status']
                                    : 'Pending';

                                ?>


                                <tr>


                                    <!-- TITLE -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $resource['title']
                                            ); ?>

                                        </strong>

                                    </td>


                                    <!-- CATEGORY -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $resource['category']
                                            )
                                        ): ?>

                                            <span
                                                class="badge bg-primary">

                                                <?= htmlspecialchars(
                                                    $resource['category']
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
                                            $resource['description']
                                            ?: 'No description provided.'
                                        ); ?>

                                    </td>


                                    <!-- UPLOADED BY -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $resource['uploader_name']
                                        ); ?>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $resource['uploaded_at']
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


                                        <!-- OPEN / DOWNLOAD -->

                                        <?php if (
                                            !empty(
                                                $resource['file_path']
                                            )
                                        ): ?>

                                            <a
                                                href="../<?= htmlspecialchars(
                                                    $resource['file_path']
                                                ); ?>"
                                                target="_blank"
                                                class="btn btn-primary btn-sm mb-1">

                                                👁 Open File

                                            </a>

                                            <br>

                                        <?php endif; ?>


                                        <!-- =================================================
                                             PENDING ACTIONS
                                        ================================================== -->

                                        <?php if (
                                            $status === 'Pending'
                                        ): ?>


                                            <!-- APPROVE -->

                                            <form
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to approve this resource?'
                                                    );
                                                ">


                                                <input
                                                    type="hidden"
                                                    name="resource_id"
                                                    value="<?= (int) $resource['id']; ?>">


                                                <button
                                                    type="submit"
                                                    name="approve_resource"
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
                                                        'Are you sure you want to reject this resource?'
                                                    );
                                                ">


                                                <input
                                                    type="hidden"
                                                    name="resource_id"
                                                    value="<?= (int) $resource['id']; ?>">


                                                <button
                                                    type="submit"
                                                    name="reject_resource"
                                                    class="btn btn-danger btn-sm mb-1">

                                                    ❌ Reject

                                                </button>

                                            </form>


                                        <?php elseif (
                                            $status === 'Approved'
                                        ): ?>


                                            <span
                                                class="text-success">

                                                ✅ Resource Approved

                                            </span>


                                        <?php elseif (
                                            $status === 'Rejected'
                                        ): ?>


                                            <span
                                                class="text-danger">

                                                ❌ Resource Rejected

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

                        📁 No learning resources have
                        been uploaded yet.

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