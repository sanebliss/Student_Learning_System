<?php
/**
 * Logout Page
 */

require_once 'config/config.php';

// Destroy session and redirect
session_destroy();
header('Location: login.php');
exit();
?>
