<?php
/**
 * Admin - Delete Student
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$student_id = intval($_GET['id'] ?? 0);

if ($student_id <= 0) {
    http_response_code(400);
    die('Invalid student ID.');
}

// Check if student exists
$stmt = $mysqli->prepare("SELECT id FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    http_response_code(404);
    die('Student not found.');
}
$stmt->close();

// Delete student
$delete_stmt = $mysqli->prepare("DELETE FROM students WHERE id = ?");
$delete_stmt->bind_param("i", $student_id);

if ($delete_stmt->execute()) {
    header('Location: students_list.php?success=Student removed successfully');
} else {
    header('Location: students_list.php?error=Error removing student');
}
$delete_stmt->close();
exit();
?>
