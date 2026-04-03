<?php
/**
 * Navigation Bar
 * Displayed on all pages for logged-in users
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
            <?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()) { ?>
                    <?php
                    $current_user = getCurrentUser();
                    if ($current_user) {
                    ?>
                        <!-- Admin Navigation -->
                        <?php if ($current_user['role'] === ROLE_ADMIN) { ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/users_list.php">Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/classes_list.php">Classes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>announcements_list.php">Announcements</a>
                            </li>
                        <?php } ?>

                        <!-- Teacher Navigation -->
                        <?php if ($current_user['role'] === ROLE_TEACHER) { ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>teacher/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>teacher/activities_list.php">Activities</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>teacher/class_progress.php">Progress</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>announcements_list.php">Announcements</a>
                            </li>
                        <?php } ?>

                        <!-- Student Navigation -->
                        <?php if ($current_user['role'] === ROLE_STUDENT) { ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>student/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>student/activities_list.php">Activities</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>student/progress_dashboard.php">Progress</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>announcements_list.php">Announcements</a>
                            </li>
                        <?php } ?>

                        <!-- Parent Navigation -->
                        <?php if ($current_user['role'] === ROLE_PARENT) { ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>parent/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>parent/child_progress.php">Child Progress</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>announcements_list.php">Announcements</a>
                            </li>
                        <?php } ?>

                        <!-- User Profile & Logout (All Roles) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <?php echo htmlspecialchars($current_user['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>profile.php">My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>
