<?php

require_once "../config/session.php";
require_once "../includes/auth.php";

require_role('student');
require_once "../config/database.php";

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Validate Group ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    die("Invalid study group.");

}

$group_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Study Group
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            study_groups.id,
            study_groups.group_name,
            study_groups.description,
            study_groups.category,
            study_groups.created_at,
            study_groups.creator_id,
            study_groups.status,

            users.full_name AS creator_name

        FROM study_groups

        INNER JOIN users
            ON study_groups.creator_id = users.id

        WHERE study_groups.id = ?

        LIMIT 1";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Unable to load study group: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $group_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result(
    $stmt
);


$group = mysqli_fetch_assoc(
    $result
);


if (!$group) {

    die(
        "Study group not found."
    );

}


/*
|--------------------------------------------------------------------------
| Check Group Access
|--------------------------------------------------------------------------
|
| Approved groups can be viewed by students.
| The creator can still view their own Pending/Rejected group.
|
|--------------------------------------------------------------------------
*/

$is_creator =
    ((int) $group['creator_id'] === $user_id);


if (
    $group['status'] !== 'Approved' &&
    !$is_creator
) {

    die(
        "This study group is not currently available."
    );

}


/*
|--------------------------------------------------------------------------
| Check Whether Current Student Is Already a Member
|--------------------------------------------------------------------------
*/

$member_check_sql = "SELECT id
                     FROM study_group_members
                     WHERE group_id = ?
                     AND user_id = ?
                     LIMIT 1";


$member_check_stmt = mysqli_prepare(
    $conn,
    $member_check_sql
);


if (!$member_check_stmt) {

    die(
        "Unable to check membership: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $member_check_stmt,
    "ii",
    $group_id,
    $user_id
);


mysqli_stmt_execute(
    $member_check_stmt
);


$member_check_result =
    mysqli_stmt_get_result(
        $member_check_stmt
    );


$is_member =
    mysqli_num_rows(
        $member_check_result
    ) > 0;


/*
|--------------------------------------------------------------------------
| Get Group Members
|--------------------------------------------------------------------------
*/

$members_sql = "SELECT
                    users.id,
                    users.full_name,
                    users.department,
                    users.programme,
                    study_group_members.joined_at

                FROM study_group_members

                INNER JOIN users
                    ON study_group_members.user_id =
                       users.id

                WHERE study_group_members.group_id = ?

                ORDER BY study_group_members.joined_at ASC";


$members_stmt = mysqli_prepare(
    $conn,
    $members_sql
);


if (!$members_stmt) {

    die(
        "Unable to load group members: " .
        mysqli_error($conn)
    );

}


mysqli_stmt_bind_param(
    $members_stmt,
    "i",
    $group_id
);


mysqli_stmt_execute(
    $members_stmt
);


$members_result =
    mysqli_stmt_get_result(
        $members_stmt
    );


/*
|--------------------------------------------------------------------------
| Count Members
|--------------------------------------------------------------------------
*/

$member_count =
    mysqli_num_rows(
        $members_result
    );


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
                     SUCCESS / INFORMATION MESSAGES
                ================================================== -->

                <?php if (isset($_GET['joined'])): ?>

                    <div class="alert alert-success">

                        ✅ <strong>Success!</strong>

                        You have successfully joined this
                        study group.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET['left'])): ?>

                    <div class="alert alert-success">

                        🚪 <strong>Success!</strong>

                        You have successfully left this
                        study group.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET['already_member'])): ?>

                    <div class="alert alert-info">

                        ℹ️ You are already a member of this
                        study group.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['error']) &&
                    $_GET['error'] === 'not_approved'
                ): ?>

                    <div class="alert alert-warning">

                        ⏳ This study group has not yet been
                        approved by a lecturer.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['error']) &&
                    $_GET['error'] === 'creator_cannot_leave'
                ): ?>

                    <div class="alert alert-warning">

                        👑 The group creator cannot leave
                        their own study group.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET['error']) &&
                    $_GET['error'] === 'not_member'
                ): ?>

                    <div class="alert alert-info">

                        ℹ️ You are not currently a member
                        of this study group.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     GROUP HEADER
                ================================================== -->

                <div class="d-flex justify-content-between
                            align-items-start flex-wrap">

                    <div>

                        <h2>

                            📚

                            <?= htmlspecialchars(
                                $group['group_name']
                            ); ?>

                        </h2>


                        <span class="badge bg-primary">

                            <?= htmlspecialchars(
                                $group['category']
                            ); ?>

                        </span>

                    </div>


                    <!-- =================================================
                         STATUS
                    ================================================== -->

                    <div class="mt-2">


                        <?php if (
                            $group['status']
                            === 'Approved'
                        ): ?>

                            <span
                                class="badge bg-success fs-6">

                                ✅ Approved

                            </span>


                        <?php elseif (
                            $group['status']
                            === 'Rejected'
                        ): ?>

                            <span
                                class="badge bg-danger fs-6">

                                ❌ Rejected

                            </span>


                        <?php else: ?>

                            <span
                                class="badge bg-warning text-dark fs-6">

                                ⏳ Pending Approval

                            </span>

                        <?php endif; ?>


                    </div>

                </div>


                <hr>


                <!-- =================================================
                     DESCRIPTION
                ================================================== -->

                <h5>
                    About This Group
                </h5>


                <p>

                    <?= nl2br(
                        htmlspecialchars(
                            $group['description']
                        )
                    ); ?>

                </p>


                <!-- =================================================
                     GROUP INFORMATION
                ================================================== -->

                <div class="row mb-3">


                    <div class="col-md-6">

                        <p class="text-muted mb-1">

                            👤 Created by

                        </p>

                        <strong>

                            <?= htmlspecialchars(
                                $group['creator_name']
                            ); ?>

                        </strong>

                    </div>


                    <div class="col-md-6">

                        <p class="text-muted mb-1">

                            👥 Members

                        </p>

                        <strong>

                            <?= $member_count; ?>

                        </strong>

                    </div>


                </div>


                <p class="text-muted">

                    📅 Created:

                    <?= htmlspecialchars(
                        $group['created_at']
                    ); ?>

                </p>


                <hr>


                <!-- =================================================
                     GROUP STATUS / MEMBERSHIP
                ================================================== -->

                <?php if (
                    $group['status']
                    === 'Pending'
                ): ?>


                    <div class="alert alert-warning">

                        <strong>

                            ⏳ Waiting for Lecturer Approval

                        </strong>

                        <br>

                        This study group has not yet been
                        approved by a lecturer.

                    </div>


                <?php elseif (
                    $group['status']
                    === 'Rejected'
                ): ?>


                    <div class="alert alert-danger">

                        <strong>

                            ❌ Study Group Rejected

                        </strong>

                        <br>

                        This study group was not approved
                        by a lecturer.

                    </div>


                <?php elseif (
                    $group['status']
                    === 'Approved'
                ): ?>


                    <?php if ($is_member): ?>


                        <!-- =============================================
                             MEMBER CONFIRMATION
                        ============================================== -->

                        <div class="alert alert-success">

                            ✅

                            <strong>

                                You are a member of this
                                study group.

                            </strong>

                        </div>


                        <!-- =============================================
                             LEAVE GROUP
                        ============================================== -->

                        <?php if (!$is_creator): ?>


                            <form
                                method="POST"
                                action="leave_group.php"
                                class="d-inline"
                                onsubmit="
                                    return confirm(
                                        'Are you sure you want to leave this study group?'
                                    );
                                ">

                                <?= generate_csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int) $group_id; ?>">

                                <button type="submit" class="btn btn-outline-danger mb-4">
                                    🚪 Leave Study Group
                                </button>

                            </form>


                        <?php else: ?>


                            <div class="alert alert-info">

                                👑

                                <strong>
                                    You are the creator of this
                                    study group.
                                </strong>

                                <br>

                                The group creator cannot leave
                                the group.

                            </div>


                        <?php endif; ?>


                    <?php else: ?>


                        <!-- =============================================
                             JOIN GROUP
                        ============================================== -->

                        <form method="POST" action="join_group.php" class="d-inline">

                            <?= generate_csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int) $group_id; ?>">

                            <button type="submit" class="btn btn-primary mb-4">
                                ➕ Join Study Group
                            </button>

                        </form>


                    <?php endif; ?>


                <?php endif; ?>


                <!-- =================================================
                     GROUP MEMBERS
                ================================================== -->

                <h4>
                    👥 Group Members
                </h4>


                <?php if ($member_count > 0): ?>


                    <div class="table-responsive">

                        <table
                            class="table table-bordered
                                   table-striped
                                   align-middle">


                            <thead class="table-dark">

                                <tr>

                                    <th>
                                        Name
                                    </th>

                                    <th>
                                        Department
                                    </th>

                                    <th>
                                        Programme
                                    </th>

                                    <th>
                                        Joined
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $member =
                                mysqli_fetch_assoc(
                                    $members_result
                                )
                            ): ?>


                                <tr>


                                    <td>

                                        <?php if (
                                            (int)
                                            $member['id']
                                            ===
                                            (int)
                                            $group['creator_id']
                                        ): ?>

                                            👑

                                        <?php endif; ?>


                                        <?= htmlspecialchars(
                                            $member['full_name']
                                        ); ?>


                                        <?php if (
                                            (int)
                                            $member['id']
                                            ===
                                            (int)
                                            $group['creator_id']
                                        ): ?>

                                            <span
                                                class="badge bg-secondary">

                                                Group Creator

                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $member['department']
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $member['programme']
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $member['joined_at']
                                        ); ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                            </tbody>

                        </table>

                    </div>


                <?php else: ?>


                    <div class="alert alert-info">

                        👥 No members have joined this
                        study group yet.

                    </div>


                <?php endif; ?>


                <hr>


                <!-- =================================================
                     BACK BUTTON
                ================================================== -->

                <a
                    href="study_groups.php"
                    class="btn btn-secondary">

                    ← Back to Study Groups

                </a>


            </div>

        </div>

    </div>

</div>


<?php include "../templates/footer.php"; ?>