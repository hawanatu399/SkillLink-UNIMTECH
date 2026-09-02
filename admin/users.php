<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('admin');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Handle Role Change / Delete Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $target_id = (int) ($_POST['user_id'] ?? 0);

    if ($target_id === (int) $_SESSION['user_id']) {
        die("You cannot modify your own admin account from this panel. <a href='users.php'>Go back</a>");
    }

    if (isset($_POST['change_role'])) {

        $new_role = $_POST['new_role'] ?? '';

        if (!in_array($new_role, ['student', 'lecturer', 'admin'], true)) {
            die("Invalid role.");
        }

        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_role, $target_id);
        mysqli_stmt_execute($stmt);

    } elseif (isset($_POST['toggle_status'])) {

        $new_status = $_POST['new_status'] ?? '';

        if (!in_array($new_status, ['Active', 'Suspended'], true)) {
            die("Invalid status.");
        }

        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $new_status, $target_id);
        mysqli_stmt_execute($stmt);

    } elseif (isset($_POST['delete_user'])) {

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $target_id);
        mysqli_stmt_execute($stmt);

    }

    header("Location: users.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Search / Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$role_filter = trim($_GET['role'] ?? '');

$where_clauses = [];
$params = [];
$param_types = "";

if ($search !== '') {
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $param_types .= "ss";
}

if (in_array($role_filter, ['student', 'lecturer', 'admin'], true)) {
    $where_clauses[] = "role = ?";
    $params[] = $role_filter;
    $param_types .= "s";
}

$where_sql = count($where_clauses) > 0
    ? "WHERE " . implode(" AND ", $where_clauses)
    : "";

$sql = "SELECT id, full_name, email, role, department, programme, level, status
        FROM users
        $where_sql
        ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 p-0">
            <?php include "../templates/admin_sidebar.php"; ?>
        </div>

        <div class="col-md-9 p-4">

            <h2 class="mb-4">Manage Users</h2>

            <form method="GET" class="row g-2 mb-4">

                <div class="col-md-6">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by name or email"
                        value="<?= htmlspecialchars($search); ?>">
                </div>

                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="student" <?= $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="lecturer" <?= $role_filter === 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>

            </form>

            <div class="card p-4">

                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No users found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php while ($u = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']); ?></td>
                                <td><?= htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($u['role']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($u['department'] ?? '—'); ?></td>
                                <td>
                                    <?php if (($u['status'] ?? 'Active') === 'Suspended'): ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>

                                    <div class="d-flex gap-2 flex-wrap">

                                        <form method="POST" class="d-flex gap-1">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id']; ?>">
                                            <select name="new_role" class="form-select form-select-sm">
                                                <option value="student" <?= $u['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                                <option value="lecturer" <?= $u['role'] === 'lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <button type="submit" name="change_role" value="1" class="btn btn-sm btn-outline-primary">
                                                Set
                                            </button>
                                        </form>

                                        <form method="POST">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id']; ?>">
                                            <?php if (($u['status'] ?? 'Active') === 'Suspended'): ?>
                                                <input type="hidden" name="new_status" value="Active">
                                                <button type="submit" name="toggle_status" value="1" class="btn btn-sm btn-outline-success">
                                                    Reactivate
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="new_status" value="Suspended">
                                                <button type="submit" name="toggle_status" value="1" class="btn btn-sm btn-outline-warning">
                                                    Suspend
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Permanently delete this user? This cannot be undone.');">
                                            <?= generate_csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id']; ?>">
                                            <button type="submit" name="delete_user" value="1" class="btn btn-sm btn-outline-danger">
                                                Delete
                                            </button>
                                        </form>

                                    </div>

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
