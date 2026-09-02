<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('admin');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Handle Delete
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    if (!isset($_POST['resource_id']) || !is_numeric($_POST['resource_id'])) {
        die("Invalid resource.");
    }

    $resource_id = (int) $_POST['resource_id'];

    if (isset($_POST['delete_resource'])) {

        // Remove the underlying file first, then the database record.
        $file_sql = "SELECT file_path FROM resources WHERE id = ?";
        $file_stmt = mysqli_prepare($conn, $file_sql);
        mysqli_stmt_bind_param($file_stmt, "i", $resource_id);
        mysqli_stmt_execute($file_stmt);
        $file_result = mysqli_stmt_get_result($file_stmt);

        if ($row = mysqli_fetch_assoc($file_result)) {
            $full_path = "../" . $row['file_path'];
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }

        $delete_sql = "DELETE FROM resources WHERE id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_sql);
        mysqli_stmt_bind_param($delete_stmt, "i", $resource_id);
        mysqli_stmt_execute($delete_stmt);

    }

    header("Location: resources.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| List All Resources Platform-Wide
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            resources.id,
            resources.title,
            resources.category,
            resources.file_name,
            resources.status,
            resources.uploaded_at,
            users.full_name AS uploader_name
        FROM resources
        JOIN users ON resources.user_id = users.id
        ORDER BY resources.uploaded_at DESC";

$result = mysqli_query($conn, $sql);

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 p-0">
            <?php include "../templates/admin_sidebar.php"; ?>
        </div>

        <div class="col-md-9 p-4">

            <h2 class="mb-4">All Resources</h2>

            <div class="card p-4">

                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Uploaded By</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">No resources found.</td></tr>
                        <?php endif; ?>

                        <?php while ($r = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['title']); ?></td>
                                <td><?= htmlspecialchars($r['category']); ?></td>
                                <td><?= htmlspecialchars($r['uploader_name']); ?></td>
                                <td>
                                    <?php
                                        $badge = match ($r['status']) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($r['status']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($r['uploaded_at']); ?></td>
                                <td>
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Delete this resource permanently?');">
                                        <?= generate_csrf_field(); ?>
                                        <input type="hidden" name="resource_id" value="<?= (int) $r['id']; ?>">
                                        <button type="submit" name="delete_resource" value="1" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>

        </div>

    </div>
</div>

<?php include "../templates/footer.php"; ?>
