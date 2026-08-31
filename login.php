<?php include 'templates/header.php'; ?>

<div class="container">

<div class="row justify-content-center mt-5">

<div class="col-md-5">

<div class="card p-4">

<div class="text-center">

<h2 class="mb-3">
🎓 SkillLink UNIMTECH
</h2>

<p class="text-muted">
Student Skills Sharing Platform
</p>

</div>

<form action="/SkillLink-UNIMTECH/login_process.php" method="POST">

<div class="mb-3">

<label class="form-label">

Email Address

</label>

<input
type="email"
class="form-control"
name="email"
required>

</div>

<div class="mb-3">

<label class="form-label">

Password

</label>

<input
type="password"
class="form-control"
name="password"
required>

</div>

<button
type="submit"
class="btn btn-primary w-100">

Login

</button>

</form>

<hr>

<div class="text-center">

Don't have an account?

<br><br>

<a
href="register.php"
class="btn btn-outline-primary">

Create Account

</a>

</div>

</div>

</div>

</div>

</div>

<?php include 'templates/footer.php'; ?>