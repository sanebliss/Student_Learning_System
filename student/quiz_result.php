<?php
/**
 * Student - Quiz Result
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_STUDENT);

$user_id = $_SESSION['user_id'];
$assignment_id = intval($_GET['id'] ?? 0);
$assignment = null;

// Get student
$student_stmt = $mysqli->prepare("SELECT id FROM students WHERE user_id = ?");
$student_stmt->bind_param("i", $user_id);
$student_stmt->execute();
$student = $student_stmt->get_result()->fetch_assoc();
$student_stmt->close();

if (!$student) {
    die('Student record not found.');
}

// Get assignment
if ($assignment_id > 0) {
    $stmt = $mysqli->prepare("
        SELECT aa.id, aa.activity_id, a.title, a.max_marks, aa.score
        FROM activity_assignments aa
        JOIN activities a ON aa.activity_id = a.id
        WHERE aa.id = ? AND aa.student_id = ?
    ");
    $stmt->bind_param("ii", $assignment_id, $student['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$assignment) {
    http_response_code(404);
    die('Assignment not found.');
}

// Get quiz attempt with answers
$attempt_stmt = $mysqli->prepare("
    SELECT id FROM quiz_attempts 
    WHERE activity_id = ? AND student_id = ?
    ORDER BY attempted_at DESC
    LIMIT 1
");
$attempt_stmt->bind_param("ii", $assignment['activity_id'], $student['id']);
$attempt_stmt->execute();
$attempt = $attempt_stmt->get_result()->fetch_assoc();
$attempt_stmt->close();

if (!$attempt) {
    die('Attempt not found.');
}

// Get questions with answers
$questions_stmt = $mysqli->prepare("
    SELECT qq.id, qq.question_text, qq.option_a, qq.option_b, qq.option_c, qq.option_d, 
           qq.correct_option, qq.marks, sa.selected_option, sa.is_correct
    FROM quiz_questions qq
    LEFT JOIN student_answers sa ON qq.id = sa.question_id AND sa.quiz_attempt_id = ?
    WHERE qq.activity_id = ?
    ORDER BY qq.id
");
$questions_stmt->bind_param("ii", $attempt['id'], $assignment['activity_id']);
$questions_stmt->execute();
$questions = $questions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$questions_stmt->close();

$percentage = round(($assignment['score'] / $assignment['max_marks']) * 100);

$page_title = 'Quiz Result - ' . htmlspecialchars($assignment['title']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-3 text-center">
                <div class="card-body">
                    <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
                    <h1 class="display-4 mb-2" style="color: <?php echo $percentage >= 50 ? '#28a745' : '#dc3545'; ?>">
                        <?php echo $percentage; ?>%
                    </h1>
                    <h3 class="card-title">
                        <?php echo $assignment['score']; ?>/<?php echo $assignment['max_marks']; ?> Points
                    </h3>
                    <p class="card-text">
                        <?php 
                        if ($percentage >= 80) {
                            echo 'Excellent! Great job!';
                        } elseif ($percentage >= 60) {
                            echo 'Good work! You did well.';
                        } elseif ($percentage >= 40) {
                            echo 'Fair attempt. You may need more practice.';
                        } else {
                            echo 'Please try again and improve your score.';
                        }
                        ?>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Detailed Results</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="mb-3 p-3 border rounded" style="background-color: <?php echo ($question['is_correct'] === 1 || ($question['is_correct'] === null && $question['selected_option'] === null)) ? '#f0f8f0' : '#f8f0f0'; ?>">
                            <h6 class="mb-2">
                                Question <?php echo $index + 1; ?>
                                <?php if ($question['selected_option']): ?>
                                    <span class="badge bg-<?php echo $question['is_correct'] ? 'success' : 'danger'; ?>">
                                        <?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Attempted</span>
                                <?php endif; ?>
                            </h6>

                            <p class="mb-2"><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>

                            <ul class="list-unstyled ms-3 mb-2">
                                <li class="mb-1">
                                    <span class="<?php echo ($question['correct_option'] === 'a') ? 'fw-bold text-success' : ''; ?>">
                                        A. <?php echo htmlspecialchars($question['option_a']); ?>
                                        <?php if ($question['correct_option'] === 'a'): ?> ✓<?php endif; ?>
                                    </span>
                                    <?php if ($question['selected_option'] === 'a' && !$question['is_correct']): ?>
                                        <span class="text-danger"> (Your answer)</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-1">
                                    <span class="<?php echo ($question['correct_option'] === 'b') ? 'fw-bold text-success' : ''; ?>">
                                        B. <?php echo htmlspecialchars($question['option_b']); ?>
                                        <?php if ($question['correct_option'] === 'b'): ?> ✓<?php endif; ?>
                                    </span>
                                    <?php if ($question['selected_option'] === 'b' && !$question['is_correct']): ?>
                                        <span class="text-danger"> (Your answer)</span>
                                    <?php endif; ?>
                                </li>
                                <li class="mb-1">
                                    <span class="<?php echo ($question['correct_option'] === 'c') ? 'fw-bold text-success' : ''; ?>">
                                        C. <?php echo htmlspecialchars($question['option_c']); ?>
                                        <?php if ($question['correct_option'] === 'c'): ?> ✓<?php endif; ?>
                                    </span>
                                    <?php if ($question['selected_option'] === 'c' && !$question['is_correct']): ?>
                                        <span class="text-danger"> (Your answer)</span>
                                    <?php endif; ?>
                                </li>
                                <li>
                                    <span class="<?php echo ($question['correct_option'] === 'd') ? 'fw-bold text-success' : ''; ?>">
                                        D. <?php echo htmlspecialchars($question['option_d']); ?>
                                        <?php if ($question['correct_option'] === 'd'): ?> ✓<?php endif; ?>
                                    </span>
                                    <?php if ($question['selected_option'] === 'd' && !$question['is_correct']): ?>
                                        <span class="text-danger"> (Your answer)</span>
                                    <?php endif; ?>
                                </li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-3">
                <a href="activities_list.php" class="btn btn-primary">Back to Activities</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
