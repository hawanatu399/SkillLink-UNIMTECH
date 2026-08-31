<?php

/* ============================================================
   DETECT CURRENT PAGE
============================================================ */

$current_page = basename($_SERVER['PHP_SELF']);


/* ============================================================
   ACTIVE MENU FUNCTION
============================================================ */

function active_menu($page, $current_page)
{
    if ($page === $current_page) {

        return "
            background: #0d6efd;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(13,110,253,0.25);
        ";

    }

    return "";
}


/* ============================================================
   HOVER SCRIPT
============================================================ */

?>

<div
    class="p-3"
    style="
        min-height: 100vh;
        background: #172033;
        color: white;
    "
>


    <!-- =====================================================
         BRAND
    ====================================================== -->

    <div class="text-center mb-4">

        <div
            class="rounded-circle mx-auto mb-2"
            style="
                width: 55px;
                height: 55px;
                background: #0d6efd;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 26px;
            "
        >
            🎓
        </div>


        <h5 class="mb-0">
            SkillLink
        </h5>


        <small class="text-secondary">
            UNIMTECH
        </small>

    </div>


    <hr
        style="
            border-color: rgba(255,255,255,0.15);
        "
    >


    <!-- =====================================================
         MENU TITLE
    ====================================================== -->

    <div
        class="text-uppercase small mb-2"
        style="
            color: #9ca3af;
            letter-spacing: 1px;
        "
    >
        Student Menu
    </div>


    <ul class="nav flex-column">


        <!-- =================================================
             DASHBOARD
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="dashboard.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'dashboard.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('dashboard.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('dashboard.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                🏠
                <span class="ms-2">
                    Dashboard
                </span>

            </a>

        </li>


        <!-- =================================================
             PROFILE
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="profile.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'profile.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('profile.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('profile.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                👤
                <span class="ms-2">
                    My Profile
                </span>

            </a>

        </li>


        <!-- =================================================
             EDIT PROFILE
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="edit_profile.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'edit_profile.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('edit_profile.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('edit_profile.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                ✏️
                <span class="ms-2">
                    Edit Profile
                </span>

            </a>

        </li>


        <!-- =================================================
             SKILLS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="skills.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'skills.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('skills.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('skills.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                💡
                <span class="ms-2">
                    My Skills
                </span>

            </a>

        </li>


        <!-- =================================================
             FIND STUDENTS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="find_students.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'find_students.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('find_students.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('find_students.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                🔎
                <span class="ms-2">
                    Find Students
                </span>

            </a>

        </li>


        <!-- =================================================
             MARKETPLACE
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="marketplace.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'marketplace.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('marketplace.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('marketplace.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                🛒
                <span class="ms-2">
                    Marketplace
                </span>

            </a>

        </li>


        <!-- =================================================
             MY SERVICES
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="my_services.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'my_services.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('my_services.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('my_services.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                💼
                <span class="ms-2">
                    My Services
                </span>

            </a>

        </li>


        <!-- =================================================
             SERVICE REQUESTS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="service_requests.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'service_requests.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('service_requests.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('service_requests.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                📋
                <span class="ms-2">
                    Service Requests
                </span>

            </a>

        </li>


        <!-- =================================================
             COLLABORATIONS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="collaborations.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'collaborations.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('collaborations.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('collaborations.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                🤝
                <span class="ms-2">
                    Collaborations
                </span>

            </a>

        </li>


        <!-- =================================================
             COLLABORATION REQUESTS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="collaboration_requests.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'collaboration_requests.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('collaboration_requests.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('collaboration_requests.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                📩
                <span class="ms-2">
                    Collaboration Requests
                </span>

            </a>

        </li>


        <!-- =================================================
             STUDY GROUPS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="study_groups.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'study_groups.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('study_groups.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('study_groups.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                📚
                <span class="ms-2">
                    Study Groups
                </span>

            </a>

        </li>


        <!-- =================================================
             RESOURCES
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="resources.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'resources.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('resources.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('resources.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                📁
                <span class="ms-2">
                    Learning Resources
                </span>

            </a>

        </li>


        <!-- =================================================
             NOTIFICATIONS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="notifications.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= active_menu(
                        'notifications.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('notifications.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('notifications.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                🔔
                <span class="ms-2">
                    Notifications
                </span>

            </a>

        </li>


    </ul>


    <!-- =====================================================
         DIVIDER
    ====================================================== -->

    <hr
        style="
            border-color: rgba(255,255,255,0.15);
        "
    >


    <!-- =====================================================
         LOGOUT
    ====================================================== -->

    <a
        href="../logout.php"
        class="nav-link text-danger rounded px-3 py-2"
        onmouseover="
            this.style.background='#3a2630'
        "
        onmouseout="
            this.style.background='transparent'
        "
    >

        🚪
        <span class="ms-2">
            Logout
        </span>

    </a>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <div
        class="text-center mt-4"
        style="
            color: #6b7280;
            font-size: 12px;
        "
    >

        SkillLink UNIMTECH<br>

        Student Portal

    </div>


</div>