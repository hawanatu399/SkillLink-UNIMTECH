<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('admin');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Platform-Wide Stats
|--------------------------------------------------------------------------
*/

$total_students = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'student'")
)['total'];

$total_lecturers = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'lecturer'")
)['total'];

$total_resources = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources")
)['total'];

$pending_resources = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM resources WHERE status = 'Pending'")
)['total'];

$total_groups = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM study_groups")
)['total'];

$pending_groups = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM study_groups WHERE status = 'Pending'")
)['total'];

$total_collaborations = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM collaboration_requests WHERE status = 'Accepted'")
)['total'];


/*
|--------------------------------------------------------------------------
| Recently Registered Users
|--------------------------------------------------------------------------
*/

$recent_users_result = mysqli_query(
    $conn,
    "SELECT id, full_name, email, role, department, created_at
     FROM users
     ORDER BY id DESC
     LIMIT 5"
);

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 p-0">
            <?php include "../templates/admin_sidebar.php"; ?>
        </div>

        <div class="col-md-9 p-4">

            <h2 class="mb-4">Admin Dashboard</h2>

            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Students</h6>
                        <h3><?= (int) $total_students; ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Lecturers</h6>
                        <h3><?= (int) $total_lecturers; ?></h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Resources</h6>
                        <h3><?= (int) $total_resources; ?></h3>
                        <?php if ($pending_resources > 0): ?>
                            <span class="badge bg-warning text-dark"><?= (int) $pending_resources; ?> pending</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Study Groups</h6>
                        <h3><?= (int) $total_groups; ?></h3>
                        <?php if ($pending_groups > 0): ?>
                            <span class="badge bg-warning text-dark"><?= (int) $pending_groups; ?> pending</span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="card p-3 mb-4">
                <h6 class="text-muted mb-1">Active Collaborations</h6>
                <h3><?= (int) $total_collaborations; ?></h3>
            </div>

            <div class="card p-4">

                <h5 class="mb-3">Recently Registered Users</h5>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($recent_users_result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($u['full_name']); ?></td>
                                <td><?= htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($u['role']); ?></span>
                                </td>
                                <td><?= htmlspecialchars($u['department'] ?? '—'); ?></td>
                                <td><?= htmlspecialchars($u['created_at'] ?? '—'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <a href="users.php" class="btn btn-primary btn-sm">View All Users →</a>

            </div>

        </div>

    </div>
</div>

<?php include "../templates/footer.php"; ?>
