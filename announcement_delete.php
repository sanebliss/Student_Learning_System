<?php
/**
 * Delete Announcement
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRoles([ROLE_ADMIN, ROLE_TEACHER]);

$user_id = $_SESSION['user_id'];
$announcement_id = intval($_GET['id'] ?? 0);

if ($announcement_id <= 0) {
    http_response_code(400);
    die('Invalid announcement ID.');
}

// Get announcement
$stmt = $mysqli->prepare("SELECT id, posted_by FROM announcements WHERE id = ?");
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    die('Announcement not found.');
}

$announcement = $result->fetch_assoc();
$stmt->close();

// Check permission
if (!isAdmin() && $announcement['posted_by'] != $user_id) {
    http_response_code(403);
    die('Access denied.');
}

// Delete announcement
$delete_stmt = $mysqli->prepare("DELETE FROM announcements WHERE id = ?");
$delete_stmt->bind_param("i", $announcement_id);

if ($delete_stmt->execute()) {
    header('Location: announcements_list.php?success=Announcement deleted successfully');
} else {
    header('Location: announcements_list.php?error=Error deleting announcement');
}
$delete_stmt->close();
exit();
?>
