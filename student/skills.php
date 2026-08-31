<?php

require_once "../config/session.php";
require_once "../includes/auth.php";
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];

include "../templates/header.php";
include "../templates/navbar.php";

?>

<div class="container-fluid">

    <div class="row">

        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <div class="col-md-3">

            <?php include "../templates/sidebar.php"; ?>

        </div>


        <!-- =================================================
             MAIN CONTENT
        ================================================== -->

        <div class="col-md-9 mt-4">

            <div class="card shadow-sm p-4">


                <!-- PAGE TITLE -->

                <h2>

                    💡 My Skills

                </h2>


                <p class="text-muted">

                    Add your skills and showcase your abilities
                    to other students on SkillLink UNIMTECH.

                </p>


                <!-- =================================================
                     SUCCESS MESSAGE
                ================================================== -->

                <?php if (
                    isset($_GET['success']) &&
                    $_GET['success'] == '1'
                ): ?>

                    <div
                        class="alert alert-success alert-dismissible fade show"
                        role="alert">

                        ✅

                        <strong>
                            Skill Added Successfully!
                        </strong>

                        Your skill has been added to your profile
                        and is awaiting lecturer verification.

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                        </button>

                    </div>

                <?php endif; ?>


                <hr>


                <!-- =================================================
                     ADD SKILL
                ================================================== -->

                <h4>

                    ➕ Add a New Skill

                </h4>


                <form
                    action="save_skill.php"
                    method="POST"
                    class="mt-3">


                    <!-- SKILL NAME -->

                    <div class="mb-3">

                        <label
                            class="form-label">

                            <strong>
                                Skill Name
                            </strong>

                        </label>


                        <input
                            type="text"
                            name="skill_name"
                            class="form-control"
                            placeholder="Example: PHP, Python, Networking, Graphic Design"
                            required>

                    </div>


                    <!-- SKILL LEVEL -->

                    <div class="mb-3">

                        <label
                            class="form-label">

                            <strong>
                                Skill Level
                            </strong>

                        </label>


                        <select
                            name="skill_level"
                            class="form-select"
                            required>

                            <option value="">
                                Select Level
                            </option>

                            <option value="Beginner">
                                Beginner
                            </option>

                            <option value="Intermediate">
                                Intermediate
                            </option>

                            <option value="Advanced">
                                Advanced
                            </option>

                        </select>

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="mb-3">

                        <label
                            class="form-label">

                            <strong>
                                Description
                            </strong>

                        </label>


                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Describe your experience or ability in this skill..."></textarea>

                    </div>


                    <!-- SUBMIT -->

                    <button
                        type="submit"
                        class="btn btn-success">

                        ➕ Add Skill

                    </button>


                </form>


                <hr class="my-4">


                <!-- =================================================
                     MY SKILLS
                ================================================== -->

                <h4>

                    💡 My Skills

                </h4>


                <p class="text-muted">

                    Your submitted skills and their lecturer
                    verification status are shown below.

                </p>


                <?php

                /*
                |--------------------------------------------------------------------------
                | Get Student Skills
                |--------------------------------------------------------------------------
                */

                $sql =
                    "SELECT
                        id,
                        skill_name,
                        skill_level,
                        description,
                        verified,
                        created_at

                    FROM skills

                    WHERE user_id = ?

                    ORDER BY created_at DESC";


                $stmt =
                    mysqli_prepare(
                        $conn,
                        $sql
                    );


                mysqli_stmt_bind_param(
                    $stmt,
                    "i",
                    $user_id
                );


                mysqli_stmt_execute(
                    $stmt
                );


                $result =
                    mysqli_stmt_get_result(
                        $stmt
                    );

                ?>


                <?php if (
                    mysqli_num_rows($result) > 0
                ): ?>


                    <div
                        class="table-responsive">


                        <table
                            class="table
                                   table-bordered
                                   table-striped
                                   align-middle">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Skill
                                    </th>

                                    <th>
                                        Level
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th>
                                        Verification
                                    </th>

                                    <th>
                                        Added
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $row =
                                mysqli_fetch_assoc(
                                    $result
                                )
                            ): ?>


                                <tr>


                                    <!-- SKILL -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $row['skill_name']
                                            ); ?>

                                        </strong>

                                    </td>


                                    <!-- LEVEL -->

                                    <td>

                                        <?php

                                        $level =
                                            $row['skill_level'];

                                        if (
                                            $level === 'Beginner'
                                        ):

                                        ?>

                                            <span
                                                class="badge bg-info text-dark">

                                                Beginner

                                            </span>

                                        <?php
                                        elseif (
                                            $level === 'Intermediate'
                                        ):
                                        ?>

                                            <span
                                                class="badge bg-warning text-dark">

                                                Intermediate

                                            </span>

                                        <?php
                                        else:
                                        ?>

                                            <span
                                                class="badge bg-primary">

                                                Advanced

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DESCRIPTION -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $row['description']
                                            )
                                        ): ?>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $row['description']
                                                )
                                            ); ?>

                                        <?php else: ?>

                                            <span
                                                class="text-muted">

                                                No description provided.

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- VERIFICATION -->

                                    <td>

                                        <?php if (
                                            (int)
                                            $row['verified']
                                            === 1
                                        ): ?>

                                            <span
                                                class="badge bg-success">

                                                🏅 Lecturer Verified

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-warning text-dark">

                                                ⏳ Awaiting Verification

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $row['created_at']
                                            )
                                        ): ?>

                                            <?= htmlspecialchars(
                                                $row['created_at']
                                            ); ?>

                                        <?php else: ?>

                                            <span
                                                class="text-muted">

                                                Not available

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>


                    </div>


                <?php else: ?>


                    <div
                        class="alert alert-info">

                        💡 You have not added any skills yet.

                        <br><br>

                        Add your first skill using the form above.

                    </div>


                <?php endif; ?>


                <!-- =================================================
                     VERIFICATION INFORMATION
                ================================================== -->

                <div
                    class="alert alert-info mt-4">

                    <strong>

                        🏅 Skill Verification

                    </strong>

                    <br>

                    Skills you add are initially marked as
                    <strong>Awaiting Verification</strong>.

                    A lecturer can review and verify your skills.
                    Verified skills will display the
                    <strong>Lecturer Verified</strong> badge.

                </div>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>