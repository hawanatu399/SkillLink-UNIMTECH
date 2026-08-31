<?php

/* ============================================================
   DETECT CURRENT PAGE
============================================================ */

$current_page = basename($_SERVER['PHP_SELF']);


/* ============================================================
   ACTIVE MENU
============================================================ */

function lecturer_active_menu($page, $current_page)
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
        Lecturer Menu
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
                    <?= lecturer_active_menu(
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
             STUDENTS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="students.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
                        'students.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('students.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('students.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                👨‍🎓
                <span class="ms-2">
                    Students
                </span>

            </a>

        </li>


        <!-- =================================================
             STUDENT SKILLS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="skills.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
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
                    Student Skills
                </span>

            </a>

        </li>


        <!-- =================================================
             MARKETPLACE
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="../student/marketplace.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    transition: 0.2s;
                "
                onmouseover="
                    this.style.background='#24324a'
                "
                onmouseout="
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
                href="../student/my_services.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    transition: 0.2s;
                "
                onmouseover="
                    this.style.background='#24324a'
                "
                onmouseout="
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
                href="../student/service_requests.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    transition: 0.2s;
                "
                onmouseover="
                    this.style.background='#24324a'
                "
                onmouseout="
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
             COLLABORATION REQUESTS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="collaboration_requests.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
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
             COLLABORATIONS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="collaborations.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
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
             STUDY GROUPS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="study_groups.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
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
             LEARNING RESOURCES
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="resources.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
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
             STUDENT REVIEWS
        ================================================== -->

        <li class="nav-item mb-1">

            <a
                href="reviews.php"
                class="nav-link text-white rounded px-3 py-2"
                style="
                    <?= lecturer_active_menu(
                        'reviews.php',
                        $current_page
                    ); ?>
                    transition: 0.2s;
                "
                onmouseover="
                    if ('reviews.php' !== '<?= $current_page; ?>')
                    this.style.background='#24324a'
                "
                onmouseout="
                    if ('reviews.php' !== '<?= $current_page; ?>')
                    this.style.background='transparent'
                "
            >

                ⭐
                <span class="ms-2">
                    Student Reviews
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
                    <?= lecturer_active_menu(
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

        Lecturer Portal

    </div>


</div>