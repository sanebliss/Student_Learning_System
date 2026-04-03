<?php
/**
 * Student - Progress Dashboard
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

// Get all assignments
$stmt = $mysqli->prepare("
    SELECT aa.status, COUNT(*) as count 
    FROM activity_assignments aa
    WHERE aa.student_id = ?
    GROUP BY aa.status
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$status_counts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stats = [
    STATUS_COMPLETED => 0,
    STATUS_IN_PROGRESS => 0,
    STATUS_NOT_STARTED => 0
];

foreach ($status_counts as $count) {
    $stats[$count['status']] = $count['count'];
}

$total = array_sum($stats);
$completion_percent = $total > 0 ? round(($stats[STATUS_COMPLETED] / $total) * 100) : 0;

// Get quiz scores
$quiz_stmt = $mysqli->prepare("
    SELECT aa.id, a.title, aa.score, a.max_marks, aa.completed_at
    FROM activity_assignments aa
    JOIN activities a ON aa.activity_id = a.id
    WHERE aa.student_id = ? AND a.activity_type = ? AND aa.status = ?
    ORDER BY aa.completed_at DESC
");
$activity_type = ACTIVITY_TYPE_QUIZ;
$status = STATUS_COMPLETED;
$quiz_stmt->bind_param("iss", $student_id, $activity_type, $status);
$quiz_stmt->execute();
$quiz_results = $quiz_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$quiz_stmt->close();

// Calculate quiz average
$quiz_avg = 0;
if (count($quiz_results) > 0) {
    $total_score = array_sum(array_map(fn($q) => $q['score'], $quiz_results));
    $total_marks = array_sum(array_map(fn($q) => $q['max_marks'], $quiz_results));
    $quiz_avg = $total_marks > 0 ? round(($total_score / $total_marks) * 100) : 0;
}

$page_title = 'My Progress';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <h2 class="mb-3">My Progress</h2>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Completion</h5>
                    <h2><?php echo $completion_percent; ?>%</h2>
                    <small class="text-muted"><?php echo $stats[STATUS_COMPLETED]; ?>/<?php echo $total; ?> completed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Quiz Average</h5>
                    <h2><?php echo $quiz_avg; ?>%</h2>
                    <small class="text-muted"><?php echo count($quiz_results); ?> quizzes completed</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">In Progress</h5>
                    <h2><?php echo $stats[STATUS_IN_PROGRESS]; ?></h2>
                    <small class="text-muted">Activities</small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Not Started</h5>
                    <h2><?php echo $stats[STATUS_NOT_STARTED]; ?></h2>
                    <small class="text-muted">Activities</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Results -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Quiz Results</h5>
        </div>
        <div class="card-body table-responsive">
            <?php if (count($quiz_results) > 0): ?>
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Quiz Name</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_results as $quiz): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                <td><?php echo $quiz['score']; ?>/<?php echo $quiz['max_marks']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo round(($quiz['score'] / $quiz['max_marks']) * 100); ?>%">
                                            <?php echo round(($quiz['score'] / $quiz['max_marks']) * 100); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($quiz['completed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No quizzes completed yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
