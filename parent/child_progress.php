<?php
/**
 * Parent - Child Progress
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_PARENT);

$parent_user_id = $_SESSION['user_id'];

// Get parent's child
$child_stmt = $mysqli->prepare("
    SELECT s.id, s.user_id, u.full_name, c.class_name
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

// Get all assignments
$stmt = $mysqli->prepare("
    SELECT aa.id, aa.activity_id, a.title, a.activity_type, aa.status, aa.score, a.max_marks, aa.completed_at
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ?
    ORDER BY aa.assigned_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate stats
$total = count($assignments);
$completed = count(array_filter($assignments, fn($a) => $a['status'] === STATUS_COMPLETED));
$completion_percent = $total > 0 ? round(($completed / $total) * 100) : 0;

$page_title = 'Child Progress - ' . htmlspecialchars($child['full_name']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <h2 class="mb-3"><?php echo htmlspecialchars($child['full_name']); ?>'s Progress</h2>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Completion Rate</h6>
                    <h3><?php echo $completion_percent; ?>%</h3>
                    <small class="text-muted"><?php echo $completed; ?>/<?php echo $total; ?> completed</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Class</h6>
                    <p class="card-text"><?php echo htmlspecialchars($child['class_name']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h6>Total Assignments</h6>
                    <h3><?php echo $total; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Overall Progress</h5>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" style="width: <?php echo $completion_percent; ?>%">
                    <?php echo $completion_percent; ?>%
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Details -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Activity Details</h5>
        </div>
        <div class="card-body table-responsive">
            <?php if (count($assignments) > 0): ?>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Activity</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Completed Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $assignment['activity_type'] === ACTIVITY_TYPE_QUIZ ? 'info' : 'secondary'; ?>">
                                        <?php echo ucfirst($assignment['activity_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $assignment['status'] === STATUS_COMPLETED ? 'success' : 
                                             ($assignment['status'] === STATUS_IN_PROGRESS ? 'warning' : 'secondary');
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $assignment['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($assignment['score'] !== null && $assignment['status'] === STATUS_COMPLETED): ?>
                                        <strong><?php echo $assignment['score']; ?>/<?php echo $assignment['max_marks']; ?></strong>
                                        (<?php echo round(($assignment['score'] / $assignment['max_marks']) * 100); ?>%)
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($assignment['completed_at']): ?>
                                        <?php echo date('M d, Y', strtotime($assignment['completed_at'])); ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No assignments assigned yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
