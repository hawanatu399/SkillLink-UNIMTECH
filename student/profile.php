<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM users WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

include "../templates/header.php";

?>

<div class="container mt-5">

<div class="card p-4">

<h2>👤 My Profile</h2>

<hr>

<table class="table">

<tr>
<th>Full Name</th>
<td><?= htmlspecialchars($user['full_name']); ?></td>
</tr>

<tr>
<th>Student ID</th>
<td><?= htmlspecialchars($user['student_id']); ?></td>
</tr>

<tr>
<th>Email</th>
<td><?= htmlspecialchars($user['email']); ?></td>
</tr>

<tr>
<th>Department</th>
<td><?= htmlspecialchars($user['department']); ?></td>
</tr>

<tr>
<th>Programme</th>
<td><?= htmlspecialchars($user['programme']); ?></td>
</tr>

<tr>
<th>Level</th>
<td><?= htmlspecialchars($user['level']); ?></td>
</tr>

<tr>
<th>Phone</th>
<td><?= htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
</tr>

<tr>
<th>Gender</th>
<td><?= htmlspecialchars($user['gender'] ?? 'Not provided'); ?></td>
</tr>

<tr>
<th>Biography</th>
<td><?= nl2br(htmlspecialchars($user['bio'] ?? 'No biography yet.')); ?></td>
</tr>

<tr>
<th>Interests</th>
<td><?= nl2br(htmlspecialchars($user['interests'] ?? 'No interests added yet.')); ?></td>
</tr>

</table>

<a href="edit_profile.php" class="btn btn-primary">

Edit Profile

</a>

</div>

</div>

<?php include "../templates/footer.php"; ?>