<?php
/**
 * Teacher - Delete Activity
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$activity_id = intval($_GET['id'] ?? 0);

if ($activity_id <= 0) {
    http_response_code(400);
    die('Invalid activity ID.');
}

// Check if activity exists and belongs to teacher
$stmt = $mysqli->prepare("SELECT id, file_path FROM activities WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $activity_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('Activity not found or access denied.');
}

$activity = $result->fetch_assoc();
$stmt->close();

// Delete associated files if PDF
if (!empty($activity['file_path']) && file_exists(UPLOADS_DIR . basename($activity['file_path']))) {
    unlink(UPLOADS_DIR . basename($activity['file_path']));
}

// Delete activity
$delete_stmt = $mysqli->prepare("DELETE FROM activities WHERE id = ? AND created_by = ?");
$delete_stmt->bind_param("ii", $activity_id, $teacher_id);

if ($delete_stmt->execute()) {
    header('Location: activities_list.php?success=Activity deleted successfully');
} else {
    header('Location: activities_list.php?error=Error deleting activity');
}
$delete_stmt->close();
exit();
?>
