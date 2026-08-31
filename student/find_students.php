<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Get Search Filters
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$department = trim($_GET['department'] ?? '');

$programme = trim($_GET['programme'] ?? '');

$level = trim($_GET['level'] ?? '');


/*
|--------------------------------------------------------------------------
| Build Student Search Query
|--------------------------------------------------------------------------
*/

$sql = "SELECT DISTINCT
            users.id,
            users.full_name,
            users.student_id,
            users.department,
            users.programme,
            users.level,
            users.bio,
            users.interests,
            users.profile_picture,
            users.reputation_points

        FROM users

        LEFT JOIN skills
            ON users.id = skills.user_id

        WHERE users.role = 'student'

        AND users.id != ?";


$params = [$user_id = (int) $_SESSION['user_id']];

$types = "i";


/*
|--------------------------------------------------------------------------
| General Search
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= " AND (
                users.full_name LIKE ?
                OR users.student_id LIKE ?
                OR users.department LIKE ?
                OR users.programme LIKE ?
                OR skills.skill_name LIKE ?
              )";

    $search_value =
        "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sssss";

}


/*
|--------------------------------------------------------------------------
| Department Filter
|--------------------------------------------------------------------------
*/

if ($department !== '') {

    $sql .= " AND users.department = ?";

    $params[] = $department;

    $types .= "s";

}


/*
|--------------------------------------------------------------------------
| Programme Filter
|--------------------------------------------------------------------------
*/

if ($programme !== '') {

    $sql .= " AND users.programme = ?";

    $params[] = $programme;

    $types .= "s";

}


/*
|--------------------------------------------------------------------------
| Level Filter
|--------------------------------------------------------------------------
*/

if ($level !== '') {

    $sql .= " AND users.level = ?";

    $params[] = $level;

    $types .= "s";

}


$sql .= " ORDER BY users.full_name ASC";


/*
|--------------------------------------------------------------------------
| Prepare Search
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to search students: " .
        mysqli_error($conn)
    );

}


/*
|--------------------------------------------------------------------------
| Bind Dynamic Parameters
|--------------------------------------------------------------------------
*/

mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);


/*
|--------------------------------------------------------------------------
| Execute Search
|--------------------------------------------------------------------------
*/

mysqli_stmt_execute(
    $stmt
);


$result =
    mysqli_stmt_get_result(
        $stmt
    );


/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = [];

$department_sql = "SELECT DISTINCT department
                   FROM users
                   WHERE role = 'student'
                   AND department IS NOT NULL
                   AND department != ''
                   ORDER BY department ASC";


$department_result =
    mysqli_query(
        $conn,
        $department_sql
    );


if ($department_result) {

    while (
        $row =
        mysqli_fetch_assoc(
            $department_result
        )
    ) {

        $departments[] =
            $row['department'];

    }

}


/*
|--------------------------------------------------------------------------
| Get Programmes
|--------------------------------------------------------------------------
*/

$programmes = [];

$programme_sql = "SELECT DISTINCT programme
                  FROM users
                  WHERE role = 'student'
                  AND programme IS NOT NULL
                  AND programme != ''
                  ORDER BY programme ASC";


$programme_result =
    mysqli_query(
        $conn,
        $programme_sql
    );


if ($programme_result) {

    while (
        $row =
        mysqli_fetch_assoc(
            $programme_result
        )
    ) {

        $programmes[] =
            $row['programme'];

    }

}


/*
|--------------------------------------------------------------------------
| Get Levels
|--------------------------------------------------------------------------
*/

$levels = [];

$level_sql = "SELECT DISTINCT level
              FROM users
              WHERE role = 'student'
              AND level IS NOT NULL
              AND level != ''
              ORDER BY level ASC";


$level_result =
    mysqli_query(
        $conn,
        $level_sql
    );


if ($level_result) {

    while (
        $row =
        mysqli_fetch_assoc(
            $level_result
        )
    ) {

        $levels[] =
            $row['level'];

    }

}


/*
|--------------------------------------------------------------------------
| Page Includes
|--------------------------------------------------------------------------
*/

include "../templates/header.php";
include "../templates/navbar.php";

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =====================================================
             MAIN CONTENT
        ====================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card p-4">


                <!-- =================================================
                     PAGE HEADER
                ================================================== -->

                <h2>

                    🔎 Find Students

                </h2>


                <p class="text-muted">

                    Discover students by name, skills,
                    department, programme or level.

                </p>


                <hr>


                <!-- =================================================
                     SEARCH FORM
                ================================================== -->

                <form
                    method="GET"
                    action="find_students.php">


                    <div class="row g-3">


                        <!-- SEARCH -->

                        <div class="col-md-12">

                            <label class="form-label">

                                Search

                            </label>


                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="<?= htmlspecialchars($search); ?>"
                                placeholder="Search name, student ID, department, programme or skill...">

                        </div>


                        <!-- DEPARTMENT -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Department

                            </label>


                            <select
                                name="department"
                                class="form-select">


                                <option value="">

                                    All Departments

                                </option>


                                <?php foreach (
                                    $departments
                                    as $dept
                                ): ?>


                                    <option
                                        value="<?= htmlspecialchars($dept); ?>"
                                        <?= $department === $dept
                                            ? 'selected'
                                            : ''; ?>>

                                        <?= htmlspecialchars($dept); ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>

                        </div>


                        <!-- PROGRAMME -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Programme

                            </label>


                            <select
                                name="programme"
                                class="form-select">


                                <option value="">

                                    All Programmes

                                </option>


                                <?php foreach (
                                    $programmes
                                    as $prog
                                ): ?>


                                    <option
                                        value="<?= htmlspecialchars($prog); ?>"
                                        <?= $programme === $prog
                                            ? 'selected'
                                            : ''; ?>>

                                        <?= htmlspecialchars($prog); ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>

                        </div>


                        <!-- LEVEL -->

                        <div class="col-md-4">

                            <label class="form-label">

                                Level

                            </label>


                            <select
                                name="level"
                                class="form-select">


                                <option value="">

                                    All Levels

                                </option>


                                <?php foreach (
                                    $levels
                                    as $student_level
                                ): ?>


                                    <option
                                        value="<?= htmlspecialchars($student_level); ?>"
                                        <?= $level === $student_level
                                            ? 'selected'
                                            : ''; ?>>

                                        <?= htmlspecialchars(
                                            $student_level
                                        ); ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>

                        </div>


                        <!-- BUTTONS -->

                        <div class="col-md-12">

                            <button
                                type="submit"
                                class="btn btn-primary">

                                🔎 Search Students

                            </button>


                            <a
                                href="find_students.php"
                                class="btn btn-secondary">

                                Reset

                            </a>

                        </div>


                    </div>


                </form>


                <hr class="my-4">


                <!-- =================================================
                     RESULTS
                ================================================== -->

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           mb-3">

                    <h4 class="mb-0">

                        👥 Student Results

                    </h4>


                    <span
                        class="badge bg-primary">

                        <?= mysqli_num_rows(
                            $result
                        ); ?>

                        Student<?= mysqli_num_rows(
                            $result
                        ) != 1 ? 's' : ''; ?>

                    </span>

                </div>


                <?php if (
                    mysqli_num_rows(
                        $result
                    ) > 0
                ): ?>


                    <div class="row">


                    <?php while (
                        $student =
                        mysqli_fetch_assoc(
                            $result
                        )
                    ): ?>


                        <div
                            class="col-md-6 col-lg-4 mb-4">


                            <div
                                class="card h-100
                                       shadow-sm">


                                <div
                                    class="card-body">


                                    <!-- PROFILE -->

                                    <div
                                        class="text-center mb-3">


                                        <?php if (
                                            !empty(
                                                $student[
                                                    'profile_picture'
                                                ]
                                            )
                                        ): ?>


                                            <img
                                                src="../<?= htmlspecialchars(
                                                    $student[
                                                        'profile_picture'
                                                    ]
                                                ); ?>"
                                                alt="Profile"
                                                class="rounded-circle"
                                                style="
                                                    width:80px;
                                                    height:80px;
                                                    object-fit:cover;
                                                ">


                                        <?php else: ?>


                                            <div
                                                class="rounded-circle
                                                       bg-secondary
                                                       text-white
                                                       d-flex
                                                       align-items-center
                                                       justify-content-center
                                                       mx-auto"
                                                style="
                                                    width:80px;
                                                    height:80px;
                                                    font-size:35px;
                                                ">

                                                👤

                                            </div>


                                        <?php endif; ?>


                                    </div>


                                    <!-- NAME -->

                                    <h5
                                        class="text-center">


                                        <?= htmlspecialchars(
                                            $student[
                                                'full_name'
                                            ]
                                        ); ?>


                                    </h5>


                                    <!-- STUDENT ID -->

                                    <p
                                        class="text-center
                                               text-muted mb-2">


                                        <?= htmlspecialchars(
                                            $student[
                                                'student_id'
                                            ]
                                        ); ?>


                                    </p>


                                    <!-- INFORMATION -->

                                    <p class="mb-1">

                                        <strong>
                                            🏫 Department:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $student[
                                                'department'
                                            ]
                                        ); ?>

                                    </p>


                                    <p class="mb-1">

                                        <strong>
                                            🎓 Programme:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $student[
                                                'programme'
                                            ]
                                        ); ?>

                                    </p>


                                    <p class="mb-3">

                                        <strong>
                                            📚 Level:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $student[
                                                'level'
                                            ]
                                        ); ?>

                                    </p>


                                    <!-- REPUTATION -->

                                    <p class="text-center">

                                        ⭐ Reputation:

                                        <strong>

                                            <?= (int)
                                                $student[
                                                    'reputation_points'
                                                ]; ?>

                                        </strong>

                                    </p>


                                    <!-- ACTIONS -->

                                    <div
                                        class="d-grid
                                               gap-2">


                                        <a
                                            href="view_profile.php?id=<?= (int) $student['id']; ?>"
                                            class="btn btn-primary">

                                            👁 View Profile

                                        </a>


                                        <a
                                            href="request_collaboration.php?student_id=<?= (int) $student['id']; ?>"
                                            class="btn btn-outline-success">

                                            🤝 Request Collaboration

                                        </a>


                                    </div>


                                </div>

                            </div>

                        </div>


                    <?php endwhile; ?>


                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        🔎 No students were found matching
                        your search criteria.

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>