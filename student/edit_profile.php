<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id=?";

$stmt = mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param($stmt,"i",$user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

include "../templates/header.php";

?>

<div class="container mt-5">

<div class="card p-4">

<h2>Edit My Profile</h2>

<hr>

<form action="update_profile.php" method="POST" enctype="multipart/form-data">

<?= generate_csrf_field(); ?>

<div class="mb-3">

<label>Full Name</label>

<input
type="text"
class="form-control"
name="full_name"
value="<?= htmlspecialchars($user['full_name']); ?>">

</div>

<div class="mb-3">

<label>Phone</label>

<input
type="text"
class="form-control"
name="phone"
value="<?= htmlspecialchars($user['phone']); ?>">

</div>

<div class="mb-3">

<label>Biography</label>

<textarea
class="form-control"
rows="5"
name="bio"><?= htmlspecialchars($user['bio']); ?></textarea>

</div>

<div class="mb-3">

<label>Interests</label>

<textarea
class="form-control"
rows="4"
name="interests"><?= htmlspecialchars($user['interests']); ?></textarea>

</div>

<div class="mb-3">

<label>Profile Picture</label>

<input
type="file"
class="form-control"
name="profile_picture">

</div>

<button class="btn btn-success">

Save Changes

</button>

</form>

</div>

</div>

<?php include "../templates/footer.php"; ?>