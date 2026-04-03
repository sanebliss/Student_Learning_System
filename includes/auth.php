<?php
/**
 * Authentication and Authorization Helper Functions
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

/**
 * Require specific role
 */
function requireRole($required_role) {
    requireLogin();
    
    if ($_SESSION['role'] !== $required_role) {
        http_response_code(403);
        die('Access Denied: You do not have permission to access this page.');
    }
}

/**
 * Require one of multiple roles
 */
function requireRoles($required_roles) {
    requireLogin();
    
    if (!in_array($_SESSION['role'], $required_roles)) {
        http_response_code(403);
        die('Access Denied: You do not have permission to access this page.');
    }
}

/**
 * Get current logged-in user
 */
function getCurrentUser() {
    global $mysqli;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $mysqli->prepare("SELECT id, email, full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get student details by user_id
 */
function getStudentByUserId($user_id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT id, user_id, class_id, parent_user_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get parent's child (student)
 */
function getParentChild($parent_user_id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT id, user_id, class_id FROM students WHERE parent_user_id = ?");
    $stmt->bind_param("i", $parent_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Check if teacher teaches a specific class
 */
function teachesClass($teacher_id, $class_id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Check if student is in a specific class
 */
function studentInClass($student_id, $class_id) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT id FROM students WHERE id = ? AND class_id = ?");
    $stmt->bind_param("ii", $student_id, $class_id);
    $stmt->execute();
    
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_ADMIN;
}

/**
 * Check if user is teacher
 */
function isTeacher() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_TEACHER;
}

/**
 * Check if user is student
 */
function isStudent() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_STUDENT;
}

/**
 * Check if user is parent
 */
function isParent() {
    return isLoggedIn() && $_SESSION['role'] === ROLE_PARENT;
}

/**
 * Logout user
 */
function logout() {
    session_destroy();
    header('Location: ' . SITE_URL . 'login.php');
    exit();
}

?>
