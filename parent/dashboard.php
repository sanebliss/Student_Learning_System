<?php
/**
 * Parent Dashboard
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_PARENT);

$parent_user_id = $_SESSION['user_id'];

// Get parent's child
$child_stmt = $mysqli->prepare("
    SELECT s.id, s.user_id, u.full_name, c.class_name, c.grade
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN classes c ON s.class_id = c.id
    WHERE s.parent_user_id = ?
");
$child_stmt->bind_param("i", $parent_user_id);
$child_stmt->execute();
$child = $child_stmt->get_result()->fetch_assoc();
$child_stmt->close();

if (!$child) {
    die('No child assigned to your account.');
}

$student_id = $child['id'];

// Get completion stats
$stats_result = $mysqli->query("
    SELECT 
        COUNT(*) as total,
        COALESCE(SUM(CASE WHEN status = '" . STATUS_COMPLETED . "' THEN 1 ELSE 0 END), 0) as completed
    FROM activity_assignments
    WHERE student_id = {$student_id}
");
$stats = $stats_result->fetch_assoc();

// Get recent activities
$recent_stmt = $mysqli->prepare("
    SELECT aa.id, a.title, aa.status, aa.score, a.max_marks, aa.completed_at
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ?
    ORDER BY aa.assigned_at DESC
    LIMIT 5
");
$recent_stmt->bind_param("i", $student_id);
$recent_stmt->execute();
$recent_activities = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_stmt->close();

$page_title = 'Parent Dashboard';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Parent Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>
        </div>
    </div>

    <!-- Child Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Child Information</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Name:</strong> <?php echo htmlspecialchars($child['full_name']); ?></li>
                        <li class="mb-2"><strong>Class:</strong> <?php echo htmlspecialchars($child['class_name']); ?></li>
                        <li><strong>Grade:</strong> <?php echo htmlspecialchars($child['grade']); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="card-title">Completion Rate</h6>
                    <h2><?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%</h2>
                    <small class="text-muted"><?php echo $stats['completed']; ?>/<?php echo $stats['total']; ?> completed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Activity</h5>
        </div>
        <div class="card-body">
            <?php if (count($recent_activities) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Activity</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $activity['status'] === STATUS_COMPLETED ? 'success' : 
                                                 ($activity['status'] === STATUS_IN_PROGRESS ? 'warning' : 'secondary');
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($activity['score'] !== null): ?>
                                            <?php echo $activity['score']; ?>/<?php echo $activity['max_marks']; ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($activity['completed_at']): ?>
                                            <?php echo date('M d, Y', strtotime($activity['completed_at'])); ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No activities yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="child_progress.php" class="btn btn-primary me-2">View Detailed Progress</a>
                    <a href="../announcements_list.php" class="btn btn-info">View Announcements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
