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
                    📁 Learning Resources
                </h2>


                <p class="text-muted">
                    Share useful academic materials with other
                    UNIMTECH students.
                </p>


                <hr>


                <!-- =================================================
                     UPLOAD SUCCESS MESSAGE
                ================================================== -->

                <?php if (isset($_GET['uploaded'])): ?>

                    <div class="alert alert-success">

                        ✅ Resource uploaded successfully!

                        <br>

                        <small>
                            Your resource is now
                            <strong>pending lecturer approval</strong>.
                        </small>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     UPLOAD FORM
                ================================================== -->

                <h4>
                    Upload a Learning Resource
                </h4>


                <form
                    action="upload_resource.php"
                    method="POST"
                    enctype="multipart/form-data">


                    <!-- TITLE -->

                    <div class="mb-3">

                        <label class="form-label">
                            Resource Title
                        </label>


                        <input
                            type="text"
                            name="title"
                            class="form-control"
                            placeholder="Example: Database Systems Notes"
                            required>

                    </div>


                    <!-- CATEGORY -->

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


                            <option value="Academic">
                                Academic
                            </option>


                            <option value="Other">
                                Other
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
                            rows="4"
                            placeholder="Describe the learning resource..."
                            required></textarea>

                    </div>


                    <!-- FILE -->

                    <div class="mb-3">

                        <label class="form-label">
                            Select File
                        </label>


                        <input
                            type="file"
                            name="resource_file"
                            class="form-control"
                            required>


                        <small class="text-muted">

                            Allowed:
                            PDF, DOC, DOCX, PPT, PPTX, ZIP

                            <br>

                            Maximum size: 5MB

                        </small>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="btn btn-primary">

                        📤 Upload Resource

                    </button>


                </form>


                <hr class="my-4">


                <!-- =================================================
                     SHARED RESOURCES
                ================================================== -->

                <h4>
                    📚 Shared Learning Resources
                </h4>


                <p class="text-muted">
                    Browse approved academic resources shared
                    by UNIMTECH students.
                </p>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Get Resources
                |--------------------------------------------------------------------------
                |
                | Students can see:
                |
                | 1. All approved resources
                | 2. Their own pending resources
                | 3. Their own rejected resources
                |
                */

                $resources_sql = "SELECT
                                    resources.id,
                                    resources.user_id,
                                    resources.title,
                                    resources.description,
                                    resources.category,
                                    resources.file_name,
                                    resources.file_path,
                                    resources.uploaded_at,
                                    resources.status,

                                    users.full_name AS uploader_name

                                  FROM resources

                                  INNER JOIN users
                                    ON resources.user_id = users.id

                                  WHERE
                                    resources.status = 'Approved'
                                    OR
                                    resources.user_id = ?

                                  ORDER BY
                                    resources.uploaded_at DESC";


                $resources_stmt = mysqli_prepare(
                    $conn,
                    $resources_sql
                );


                if (!$resources_stmt) {

                    die(
                        "Unable to load resources: " .
                        mysqli_error($conn)
                    );

                }


                mysqli_stmt_bind_param(
                    $resources_stmt,
                    "i",
                    $user_id
                );


                mysqli_stmt_execute(
                    $resources_stmt
                );


                $resources_result =
                    mysqli_stmt_get_result(
                        $resources_stmt
                    );

                ?>


                <?php if (
                    $resources_result &&
                    mysqli_num_rows(
                        $resources_result
                    ) > 0
                ): ?>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered
                                   table-striped align-middle">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Resource
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Uploaded By
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $resource =
                                mysqli_fetch_assoc(
                                    $resources_result
                                )
                            ): ?>


                                <tr>


                                    <!-- =================================================
                                         RESOURCE
                                    ================================================== -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $resource['title']
                                            ); ?>

                                        </strong>


                                        <br>


                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $resource['description']
                                            ); ?>

                                        </small>

                                    </td>


                                    <!-- =================================================
                                         CATEGORY
                                    ================================================== -->

                                    <td>

                                        <span
                                            class="badge bg-primary">

                                            <?= htmlspecialchars(
                                                $resource['category']
                                            ); ?>

                                        </span>

                                    </td>


                                    <!-- =================================================
                                         UPLOADER
                                    ================================================== -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $resource['uploader_name']
                                        ); ?>

                                    </td>


                                    <!-- =================================================
                                         STATUS
                                    ================================================== -->

                                    <td>


                                        <?php if (
                                            $resource['status']
                                            === 'Approved'
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                ✅ Approved

                                            </span>


                                        <?php elseif (
                                            $resource['status']
                                            === 'Rejected'
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


                                    <!-- =================================================
                                         DATE
                                    ================================================== -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $resource['uploaded_at']
                                        ); ?>

                                    </td>


                                    <!-- =================================================
                                         ACTION
                                    ================================================== -->

                                    <td>


                                        <?php if (
                                            $resource['status']
                                            === 'Approved'
                                        ): ?>


                                            <a
                                                href="../<?= htmlspecialchars(
                                                    $resource['file_path']
                                                ); ?>"
                                                class="btn btn-success btn-sm"
                                                target="_blank">

                                                📥 Download

                                            </a>


                                        <?php elseif (
                                            (int)
                                            $resource['user_id']
                                            === $user_id
                                        ): ?>


                                            <?php if (
                                                $resource['status']
                                                === 'Pending'
                                            ): ?>

                                                <span
                                                    class="text-warning">

                                                    ⏳ Waiting for approval

                                                </span>


                                            <?php elseif (
                                                $resource['status']
                                                === 'Rejected'
                                            ): ?>

                                                <span
                                                    class="text-danger">

                                                    ❌ Not approved

                                                </span>

                                            <?php endif; ?>


                                        <?php else: ?>


                                            <span
                                                class="text-muted">

                                                Not available

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

                        📚 No approved learning resources
                        are currently available.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>