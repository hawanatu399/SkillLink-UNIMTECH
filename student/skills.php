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

<h2>💡 My Skills</h2>

<?php
if(isset($_GET['success'])){
?>
<div class="alert alert-success">

Skill added successfully!

</div>

<?php
}
?>

<hr>

<form action="save_skill.php" method="POST">

<?= generate_csrf_field(); ?>

<div class="mb-3">

<label>Skill Name</label>

<input
type="text"
name="skill_name"
class="form-control"
placeholder="Example: PHP"
required>

</div>

<div class="mb-3">

<label>Skill Level</label>

<select
name="skill_level"
class="form-control"
required>

<option value="">Select Level</option>

<option>Beginner</option>

<option>Intermediate</option>

<option>Advanced</option>

</select>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"
rows="4"></textarea>

</div>

<button class="btn btn-success">

Add Skill

</button>

</form>

<hr>

<h3>My Skills</h3>

<table class="table table-bordered table-striped">

<tr>

<th>Skill</th>

<th>Level</th>

<th>Description</th>

<th>Verification</th>

</tr>

<?php

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM skills WHERE user_id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td>
    <?= htmlspecialchars($row['skill_name']) ?>
</td>

<td>
    <?= htmlspecialchars($row['skill_level']) ?>
</td>

<td>
    <?= htmlspecialchars($row['description']) ?>
</td>

<td>

    <?php if ((int)$row['verified'] === 1): ?>

        <span class="badge bg-success">
            🏅 Lecturer Verified
        </span>

    <?php else: ?>

        <span class="badge bg-warning text-dark">
            ⏳ Not Verified
        </span>

    <?php endif; ?>

</td>

</tr>

<?php
}
?>

</table>

</div>

</div>

</div>

</div>

<?php include "../templates/footer.php"; ?>