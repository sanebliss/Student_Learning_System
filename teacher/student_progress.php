<?php
/**
 * Teacher - Student Progress Details
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$student_id = intval($_GET['student_id'] ?? 0);
$student = null;

// Get student and verify teacher access
if ($student_id > 0) {
    $stmt = $mysqli->prepare("
        SELECT s.id, s.user_id, u.full_name, u.email, c.id as class_id, c.class_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN classes c ON s.class_id = c.id
        WHERE s.id = ? AND c.teacher_id = ?
    ");
    $stmt->bind_param("ii", $student_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$student) {
    http_response_code(404);
    die('Student not found or access denied.');
}

// Get student's activity assignments
$activities_stmt = $mysqli->prepare("
    SELECT aa.id, aa.activity_id, a.title, a.activity_type, aa.status, aa.score, aa.assigned_at, aa.completed_at, a.max_marks
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ?
    ORDER BY aa.assigned_at DESC
");
$activities_stmt->bind_param("i", $student_id);
$activities_stmt->execute();
$assignments = $activities_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$activities_stmt->close();

// Calculate statistics
$total = count($assignments);
$completed = count(array_filter($assignments, fn($a) => $a['status'] === STATUS_COMPLETED));
$completion_percent = $total > 0 ? round(($completed / $total) * 100) : 0;

// Calculate average score
$quiz_scores = array_filter($assignments, fn($a) => $a['activity_type'] === ACTIVITY_TYPE_QUIZ && $a['score'] !== null);
$avg_score = 0;
if (count($quiz_scores) > 0) {
    $total_score = array_sum(array_map(fn($a) => $a['score'], $quiz_scores));
    $total_marks = array_sum(array_map(fn($a) => $a['max_marks'], $quiz_scores));
    $avg_score = $total_marks > 0 ? round(($total_score / $total_marks) * 100) : 0;
}

$page_title = 'Student Progress - ' . htmlspecialchars($student['full_name']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><?php echo htmlspecialchars($student['full_name']); ?> - Progress</h2>
            <p class="text-muted"><?php echo htmlspecialchars($student['class_name']); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="class_progress.php?class_id=<?php echo $student['class_id']; ?>" class="btn btn-secondary">Back to Class</a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Completion</h6>
                    <h3><?php echo $completion_percent; ?>%</h3>
                    <small class="text-muted"><?php echo $completed; ?>/<?php echo $total; ?> completed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Average Quiz Score</h6>
                    <h3><?php echo $avg_score; ?>%</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Total Assignments</h6>
                    <h3><?php echo $total; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Email</h6>
                    <p class="card-text" style="font-size: 0.9em;"><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Activity Assignments</h5>
        </div>
        <div class="card-body table-responsive">
            <?php if (count($assignments) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Activity</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Completed</th>
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
                                    <?php if ($assignment['score'] !== null): ?>
                                        <strong><?php echo $assignment['score']; ?>/<?php echo $assignment['max_marks']; ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($assignment['completed_at']): ?>
                                        <?php echo date('M d, Y', strtotime($assignment['completed_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No assignments for this student yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
