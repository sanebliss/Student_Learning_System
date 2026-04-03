<?php
/**
 * Index Page - Redirect to Login or Dashboard
 */

require_once 'config/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    // Redirect to appropriate dashboard based on role
    switch ($_SESSION['role']) {
        case ROLE_ADMIN:
            header('Location: admin/dashboard.php');
            break;
        case ROLE_TEACHER:
            header('Location: teacher/dashboard.php');
            break;
        case ROLE_STUDENT:
            header('Location: student/dashboard.php');
            break;
        case ROLE_PARENT:
            header('Location: parent/dashboard.php');
            break;
        default:
            header('Location: logout.php');
    }
} else {
    header('Location: login.php');
}
exit();
?>
