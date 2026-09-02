<?php
require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container-fluid">

<div class="row">

<div class="col-md-3">
<?php include "../templates/sidebar.php"; ?>
</div>

<div class="col-md-9 mt-4">

<div class="card p-4">

<h2>🔍 Find Students by Skill</h2>

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search skill...">

<button class="btn btn-primary">

Search

</button>

</div>     

</form>

<?php

if (isset($_GET['search']) && trim($_GET['search']) !== '') {

    $search = "%" . trim($_GET['search']) . "%";

    $sql = "SELECT 
            users.id,
            users.full_name,
            users.department,
            users.programme,
            users.level,
            skills.skill_name,
            skills.skill_level,
            skills.verified
        FROM users
        INNER JOIN skills 
            ON users.id = skills.user_id
        WHERE skills.skill_name LIKE ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $search);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
?>

    <h4 class="mt-4">Search Results</h4>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

           <thead>
    <tr>
        <th>Name</th>
        <th>Department</th>
        <th>Programme</th>
        <th>Level</th>
        <th>Skill</th>
        <th>Skill Level</th>
        <th>Verification</th>
        <th>Action</th>
    </tr>
</thead>

            <tbody>

            <?php

            if (mysqli_num_rows($result) > 0) {

                while ($row = mysqli_fetch_assoc($result)) {

            ?>

                <tr>

                    <td><?= htmlspecialchars($row['full_name']); ?></td>

                    <td><?= htmlspecialchars($row['department']); ?></td>

                    <td><?= htmlspecialchars($row['programme']); ?></td>

                    <td><?= htmlspecialchars($row['level']); ?></td>

                    <td><?= htmlspecialchars($row['skill_name']); ?></td>
                    <td><?= htmlspecialchars($row['skill_level']); ?></td>
                    <td><?php if ((int)$row['verified'] === 1): ?>
                    <span class="badge bg-success">
                     🏅 Lecturer Verified
                  </span>

    <?php else: ?>

        <span class="badge bg-warning text-dark">
            ⏳ Not Verified
        </span>

    <?php endif; ?>

</td>

<td>

    <a
        href="view_profile.php?id=<?= $row['id']; ?>"
        class="btn btn-success btn-sm">

        View Profile

    </a>

</td>

                </tr>

            <?php

                }

            } else {

            ?>

                <tr>
                    <td colspan="8" class="text-center">
                        No students found with that skill.
                    </td>
                </tr>

            <?php

            }

            ?>

            </tbody>

        </table>

    </div>

<?php

}

?>

<br>  
</div>

</div>

</div>

</div>

<?php include "../templates/footer.php"; ?>