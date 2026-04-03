<?php
/**
 * Admin - Delete Class
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$class_id = intval($_GET['id'] ?? 0);

if ($class_id <= 0) {
    http_response_code(400);
    die('Invalid class ID.');
}

// Check if class exists
$stmt = $mysqli->prepare("SELECT id FROM classes WHERE id = ?");
$stmt->bind_param("i", $class_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    http_response_code(404);
    die('Class not found.');
}
$stmt->close();

// Delete class
$delete_stmt = $mysqli->prepare("DELETE FROM classes WHERE id = ?");
$delete_stmt->bind_param("i", $class_id);

if ($delete_stmt->execute()) {
    header('Location: classes_list.php?success=Class deleted successfully');
} else {
    header('Location: classes_list.php?error=Error deleting class');
}
$delete_stmt->close();
exit();
?>
