<?php
/**
 * Student Dashboard
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_STUDENT);

$user_id = $_SESSION['user_id'];

// Get student details
$student_stmt = $mysqli->prepare("SELECT id, class_id FROM students WHERE user_id = ?");
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();
$student_stmt->close();

if (!$student) {
    die('Student record not found.');
}

$student_id = $student['id'];
$class_id = $student['class_id'];

// Get class details
$class_stmt = $mysqli->prepare("SELECT class_name, grade FROM classes WHERE id = ?");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class = $class_stmt->get_result()->fetch_assoc();
$class_stmt->close();

// Get total assignments
$total_stmt = $mysqli->query("SELECT COUNT(*) as count FROM activity_assignments WHERE student_id = {$student_id}");
$total_count = $total_stmt->fetch_assoc()['count'];

// Get completed assignments
$completed_stmt = $mysqli->query("
    SELECT COUNT(*) as count FROM activity_assignments 
    WHERE student_id = {$student_id} AND status = '" . STATUS_COMPLETED . "'
");
$completed_count = $completed_stmt->fetch_assoc()['count'];

$completion_percent = $total_count > 0 ? round(($completed_count / $total_count) * 100) : 0;

// Get recent assignments
$recent_stmt = $mysqli->prepare("
    SELECT aa.id, aa.activity_id, a.title, a.activity_type, aa.status 
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ?
    ORDER BY aa.assigned_at DESC
    LIMIT 5
");
$recent_stmt->bind_param("i", $student_id);
$recent_stmt->execute();
$recent_assignments = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_stmt->close();

$page_title = 'Student Dashboard';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Student Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Completion Rate</h5>
                    <h2 class="card-text"><?php echo $completion_percent; ?>%</h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Completed</h5>
                    <h2 class="card-text"><?php echo $completed_count; ?>/<?php echo $total_count; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Class</h5>
                    <p class="card-text"><?php echo htmlspecialchars($class['class_name'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Grade</h5>
                    <p class="card-text"><?php echo htmlspecialchars($class['grade'] ?? 'N/A'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Assignments</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_assignments) > 0): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($recent_assignments as $assignment): ?>
                                <li class="mb-2">
                                    <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo ucfirst($assignment['activity_type']); ?> • 
                                        <span class="badge bg-<?php 
                                            echo $assignment['status'] === STATUS_COMPLETED ? 'success' : 
                                                 ($assignment['status'] === STATUS_IN_PROGRESS ? 'warning' : 'secondary');
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $assignment['status'])); ?>
                                        </span>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No assignments yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="activities_list.php" class="btn btn-primary me-2 mb-2">View Activities</a>
                    <a href="progress_dashboard.php" class="btn btn-success me-2 mb-2">View Progress</a>
                    <a href="../announcements_list.php" class="btn btn-info mb-2">View Announcements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
