<?php
require_once "config/session.php";
include 'templates/header.php';
?>

<div class="container">

<div class="row justify-content-center mt-5 mb-5">

<div class="col-md-6">

<div class="card p-4">

<h3 class="mb-4 text-center">Student Registration</h3>

<form action="register_process.php" method="POST">

    <?= generate_csrf_field(); ?>

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" class="form-control" name="full_name" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Student ID</label>
        <input type="text" class="form-control" name="student_id" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required minlength="6">
    </div>

    <div class="mb-3">
        <label class="form-label">Department</label>
        <input type="text" class="form-control" name="department">
    </div>

    <div class="mb-3">
        <label class="form-label">Programme</label>
        <input type="text" class="form-control" name="programme">
    </div>

    <div class="mb-3">
        <label class="form-label">Level</label>
        <select name="level" class="form-select">
            <option value="">Select Level</option>
            <option>100</option>
            <option>200</option>
            <option>300</option>
            <option>400</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary w-100">Register</button>

    <p class="text-center mt-3">
        Already have an account? <a href="login.php">Login</a>
    </p>

</form>

</div>

</div>

</div>

</div>

<?php include 'templates/footer.php'; ?>
