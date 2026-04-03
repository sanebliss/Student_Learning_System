<?php
/**
 * Admin - Delete User
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$user_id = intval($_GET['id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    die('Invalid user ID.');
}

// Get user
$stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    http_response_code(404);
    die('User not found.');
}
$stmt->close();

// Delete user
$delete_stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    header('Location: users_list.php?success=User deleted successfully');
} else {
    header('Location: users_list.php?error=Error deleting user');
}
$delete_stmt->close();
exit();
?>
