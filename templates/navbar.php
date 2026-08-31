<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">

<div class="container-fluid">

<a class="navbar-brand fw-bold" href="../student/dashboard.php">

🎓 SkillLink UNIMTECH

</a>

<div class="ms-auto">

<span class="text-white me-3">

Welcome,

<?= htmlspecialchars($_SESSION['full_name']); ?>

</span>

<a href="../logout.php" class="btn btn-light btn-sm">

Logout

</a>

</div>

</div>

</nav>