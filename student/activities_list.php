<?php
/**
 * Student - Activities List
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_STUDENT);

$user_id = $_SESSION['user_id'];

// Get student details
$student_stmt = $mysqli->prepare("SELECT id FROM students WHERE user_id = ?");
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();
$student_stmt->close();

if (!$student) {
    die('Student record not found.');
}

$student_id = $student['id'];

// Get assigned activities
$activities_stmt = $mysqli->prepare("
    SELECT aa.id as assignment_id, aa.activity_id, a.title, a.activity_type, a.description, aa.status, aa.score, a.max_marks, aa.assigned_at
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ?
    ORDER BY aa.assigned_at DESC
");
$activities_stmt->bind_param("i", $student_id);
$activities_stmt->execute();
$activities = $activities_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$activities_stmt->close();

$page_title = 'My Activities';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <h2 class="mb-3">My Activities</h2>

    <div class="card">
        <div class="card-body">
            <?php if (count($activities) > 0): ?>
                <div class="row">
                    <?php foreach ($activities as $activity): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($activity['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars(substr($activity['description'] ?? '', 0, 100)); ?>...</p>
                                    
                                    <div class="mb-2">
                                        <span class="badge bg-<?php echo $activity['activity_type'] === ACTIVITY_TYPE_QUIZ ? 'info' : 'secondary'; ?>">
                                            <?php echo ucfirst($activity['activity_type']); ?>
                                        </span>
                                        <span class="badge bg-<?php 
                                            echo $activity['status'] === STATUS_COMPLETED ? 'success' : 
                                                 ($activity['status'] === STATUS_IN_PROGRESS ? 'warning' : 'secondary');
                                        ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $activity['status'])); ?>
                                        </span>
                                    </div>

                                    <?php if ($activity['score'] !== null && $activity['status'] === STATUS_COMPLETED): ?>
                                        <p class="mb-2"><strong>Score: <?php echo $activity['score']; ?>/<?php echo $activity['max_marks']; ?></strong></p>
                                    <?php endif; ?>

                                    <a href="activity_view.php?id=<?php echo $activity['assignment_id']; ?>" class="btn btn-primary btn-sm">
                                        <?php echo $activity['status'] === STATUS_COMPLETED ? 'View' : 'Open'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">No activities assigned to you yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
