<?php
/**
 * Teacher Dashboard
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];

// Get assigned classes
$classes_result = $mysqli->query(
    "SELECT id, class_name, grade FROM classes WHERE teacher_id = {$teacher_id}"
);
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

// Get total activities created
$activities_result = $mysqli->query(
    "SELECT COUNT(*) as count FROM activities WHERE created_by = {$teacher_id}"
);
$total_activities = $activities_result->fetch_assoc()['count'];

// Get total students in classes
$students_result = $mysqli->query(
    "SELECT COUNT(*) as count FROM students WHERE class_id IN 
    (SELECT id FROM classes WHERE teacher_id = {$teacher_id})"
);
$total_students = $students_result->fetch_assoc()['count'];

// Get recent activities
$recent_stmt = $mysqli->prepare("
    SELECT id, title, activity_type, created_at 
    FROM activities 
    WHERE created_by = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$recent_stmt->bind_param("i", $teacher_id);
$recent_stmt->execute();
$recent_activities = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_stmt->close();

$page_title = 'Teacher Dashboard';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Teacher Dashboard</h2>
            <p class="text-muted">Manage your classes, activities, and track student progress</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">My Classes</h5>
                    <h2 class="card-text"><?php echo count($classes); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="card-text"><?php echo $total_students; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Activities Created</h5>
                    <h2 class="card-text"><?php echo $total_activities; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Classes -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Classes</h5>
                </div>
                <div class="card-body">
                    <?php if (count($classes) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($classes as $class): ?>
                                <a href="class_progress.php?class_id=<?php echo $class['id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($class['class_name']); ?></h6>
                                        <small><?php echo htmlspecialchars($class['grade']); ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No classes assigned yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (count($recent_activities) > 0): ?>
                        <ul class="list-unstyled">
                            <?php foreach ($recent_activities as $activity): ?>
                                <li class="mb-2">
                                    <strong><?php echo htmlspecialchars($activity['title']); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo ucfirst($activity['activity_type']); ?> • 
                                        <?php echo date('M d, Y', strtotime($activity['created_at'])); ?>
                                    </small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No activities created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="activities_list.php" class="btn btn-primary me-2">View All Activities</a>
                    <a href="activity_create.php" class="btn btn-success me-2">Create Activity</a>
                    <a href="class_progress.php" class="btn btn-info me-2">View Class Progress</a>
                    <a href="../announcements_list.php" class="btn btn-warning">View Announcements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
