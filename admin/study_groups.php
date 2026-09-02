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

    if (!isset($_POST['group_id']) || !is_numeric($_POST['group_id'])) {
        die("Invalid study group.");
    }

    $group_id = (int) $_POST['group_id'];

    if (isset($_POST['delete_group'])) {

        // Remove members first to respect the foreign key relationship.
        $del_members = mysqli_prepare($conn, "DELETE FROM study_group_members WHERE group_id = ?");
        mysqli_stmt_bind_param($del_members, "i", $group_id);
        mysqli_stmt_execute($del_members);

        $del_group = mysqli_prepare($conn, "DELETE FROM study_groups WHERE id = ?");
        mysqli_stmt_bind_param($del_group, "i", $group_id);
        mysqli_stmt_execute($del_group);

    }

    header("Location: study_groups.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| List All Study Groups Platform-Wide
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            study_groups.id,
            study_groups.group_name,
            study_groups.category,
            study_groups.status,
            study_groups.created_at,
            users.full_name AS creator_name,
            (SELECT COUNT(*) FROM study_group_members WHERE group_id = study_groups.id) AS member_count
        FROM study_groups
        JOIN users ON study_groups.creator_id = users.id
        ORDER BY study_groups.created_at DESC";

$result = mysqli_query($conn, $sql);

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 p-0">
            <?php include "../templates/admin_sidebar.php"; ?>
        </div>

        <div class="col-md-9 p-4">

            <h2 class="mb-4">All Study Groups</h2>

            <div class="card p-4">

                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Category</th>
                            <th>Created By</th>
                            <th>Members</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr><td colspan="6" class="text-center text-muted">No study groups found.</td></tr>
                        <?php endif; ?>

                        <?php while ($g = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($g['group_name']); ?></td>
                                <td><?= htmlspecialchars($g['category']); ?></td>
                                <td><?= htmlspecialchars($g['creator_name']); ?></td>
                                <td><?= (int) $g['member_count']; ?></td>
                                <td>
                                    <?php
                                        $badge = match ($g['status']) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    ?>
                                    <span class="badge bg-<?= $badge; ?>"><?= htmlspecialchars($g['status']); ?></span>
                                </td>
                                <td>
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Delete this study group permanently?');">
                                        <?= generate_csrf_field(); ?>
                                        <input type="hidden" name="group_id" value="<?= (int) $g['id']; ?>">
                                        <button type="submit" name="delete_group" value="1" class="btn btn-sm btn-outline-danger">
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
