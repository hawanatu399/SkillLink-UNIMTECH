<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('admin');
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Most Common Skills Platform-Wide
|--------------------------------------------------------------------------
|
| This is the core of the "skill gap" view: which skills are well
| represented among students, and what proportion of each has actually
| been lecturer-verified. A department can use this to see, at a glance,
| where self-reported claims are common but verification is lagging.
|
*/

$top_skills_result = mysqli_query(
    $conn,
    "SELECT
        skill_name,
        COUNT(*) AS total_students,
        SUM(CASE WHEN verified = 1 THEN 1 ELSE 0 END) AS verified_count,
        SUM(CASE WHEN skill_level = 'Advanced' THEN 1 ELSE 0 END) AS advanced_count
     FROM skills
     GROUP BY skill_name
     ORDER BY total_students DESC
     LIMIT 10"
);

$top_skills = [];
while ($row = mysqli_fetch_assoc($top_skills_result)) {
    $top_skills[] = $row;
}


/*
|--------------------------------------------------------------------------
| Department Activity Breakdown
|--------------------------------------------------------------------------
*/

$department_result = mysqli_query(
    $conn,
    "SELECT
        u.department,
        COUNT(DISTINCT u.id) AS student_count,
        COUNT(DISTINCT s.id) AS skill_count,
        COUNT(DISTINCT r.id) AS resource_count
     FROM users u
     LEFT JOIN skills s ON s.user_id = u.id
     LEFT JOIN resources r ON r.user_id = u.id
     WHERE u.role = 'student' AND u.department IS NOT NULL AND u.department != ''
     GROUP BY u.department
     ORDER BY student_count DESC"
);

$departments = [];
while ($row = mysqli_fetch_assoc($department_result)) {
    $departments[] = $row;
}


/*
|--------------------------------------------------------------------------
| Resource Category Breakdown
|--------------------------------------------------------------------------
*/

$category_result = mysqli_query(
    $conn,
    "SELECT category, COUNT(*) AS total
     FROM resources
     WHERE status = 'Approved'
     GROUP BY category
     ORDER BY total DESC"
);

$categories = [];
$category_labels = [];
$category_counts = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row;
    $category_labels[] = $row['category'];
    $category_counts[] = (int) $row['total'];
}


/*
|--------------------------------------------------------------------------
| Collaboration Success Rate
|--------------------------------------------------------------------------
*/

$collab_stats = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) AS accepted,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending
     FROM collaboration_requests"
));

$total_requests = (int) $collab_stats['total'];
$acceptance_rate = $total_requests > 0
    ? round(((int) $collab_stats['accepted'] / $total_requests) * 100, 1)
    : 0;


/*
|--------------------------------------------------------------------------
| Skill Labels for Chart
|--------------------------------------------------------------------------
*/

$skill_labels = array_column($top_skills, 'skill_name');
$skill_counts = array_map('intval', array_column($top_skills, 'total_students'));

include "../templates/header.php";
?>

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 p-0">
            <?php include "../templates/admin_sidebar.php"; ?>
        </div>

        <div class="col-md-9 p-4">

            <h2 class="mb-2">Skill Gap & Activity Analytics</h2>
            <p class="text-muted mb-4">
                Platform-wide visibility no group chat or generic directory gives a department:
                what skills the student body actually has, how much of that is lecturer-verified,
                and where collaboration is or isn't happening.
            </p>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Collaboration Requests</h6>
                        <h3><?= $total_requests; ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Acceptance Rate</h6>
                        <h3><?= $acceptance_rate; ?>%</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 text-center">
                        <h6 class="text-muted">Skills Tracked</h6>
                        <h3><?= count($top_skills); ?>+</h3>
                    </div>
                </div>
            </div>

            <div class="card p-4 mb-4">
                <h5 class="mb-3">Top 10 Skills Platform-Wide</h5>

                <canvas id="skillsChart" height="90"></canvas>

                <table class="table table-sm table-hover mt-4">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Students</th>
                            <th>Verified</th>
                            <th>Verification Rate</th>
                            <th>Advanced-Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_skills as $skill):
                            $rate = $skill['total_students'] > 0
                                ? round(($skill['verified_count'] / $skill['total_students']) * 100)
                                : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($skill['skill_name']); ?></td>
                                <td><?= (int) $skill['total_students']; ?></td>
                                <td><?= (int) $skill['verified_count']; ?></td>
                                <td>
                                    <div class="progress" style="height: 16px;">
                                        <div class="progress-bar <?= $rate < 30 ? 'bg-warning' : 'bg-success'; ?>"
                                             style="width: <?= $rate; ?>%">
                                            <?= $rate; ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?= (int) $skill['advanced_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card p-4 mb-4">
                <h5 class="mb-3">Activity by Department</h5>
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Students</th>
                            <th>Skills Listed</th>
                            <th>Resources Shared</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?= htmlspecialchars($dept['department']); ?></td>
                                <td><?= (int) $dept['student_count']; ?></td>
                                <td><?= (int) $dept['skill_count']; ?></td>
                                <td><?= (int) $dept['resource_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($departments) === 0): ?>
                            <tr><td colspan="4" class="text-center text-muted">No department data yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card p-4">
                <h5 class="mb-3">Approved Resources by Category</h5>
                <table class="table table-sm table-hover">
                    <thead>
                        <tr><th>Category</th><th>Approved Resources</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= htmlspecialchars($cat['category']); ?></td>
                                <td><?= (int) $cat['total']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($categories) === 0): ?>
                            <tr><td colspan="2" class="text-center text-muted">No approved resources yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('skillsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($skill_labels); ?>,
        datasets: [{
            label: 'Students with this skill',
            data: <?= json_encode($skill_counts); ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

<?php include "../templates/footer.php"; ?>
