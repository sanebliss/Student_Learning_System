<?php
/**
 * Student - Attempt Quiz
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_STUDENT);

$user_id = $_SESSION['user_id'];
$assignment_id = intval($_GET['id'] ?? 0);
$assignment = null;
$error = '';

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
        SELECT aa.id, aa.activity_id, a.title, a.max_marks, aa.status
        FROM activity_assignments aa
        JOIN activities a ON aa.activity_id = a.id
        WHERE aa.id = ? AND aa.student_id = ? AND a.activity_type = ?
    ");
    $activity_type = ACTIVITY_TYPE_QUIZ;
    $stmt->bind_param("iis", $assignment_id, $student['id'], $activity_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$assignment) {
    http_response_code(404);
    die('Quiz not found or access denied.');
}

// Get quiz questions
$questions_stmt = $mysqli->prepare("
    SELECT id, question_text, option_a, option_b, option_c, option_d, marks 
    FROM quiz_questions 
    WHERE activity_id = ?
    ORDER BY id
");
$questions_stmt->bind_param("i", $assignment['activity_id']);
$questions_stmt->execute();
$questions = $questions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$questions_stmt->close();

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = [];
    $total_score = 0;
    $question_count = 0;

    foreach ($questions as $question) {
        $selected_option = $_POST['question_' . $question['id']] ?? null;
        $answers[$question['id']] = $selected_option;
        $question_count++;

        if ($selected_option) {
            // Get correct answer
            $correct_stmt = $mysqli->prepare("SELECT correct_option FROM quiz_questions WHERE id = ?");
            $correct_stmt->bind_param("i", $question['id']);
            $correct_stmt->execute();
            $correct_result = $correct_stmt->get_result()->fetch_assoc();
            $correct_stmt->close();

            if ($correct_result['correct_option'] === $selected_option) {
                $total_score += $question['marks'];
            }
        }
    }

    // Create quiz attempt record
    $attempt_stmt = $mysqli->prepare("
        INSERT INTO quiz_attempts (activity_id, student_id, score, total_marks) 
        VALUES (?, ?, ?, ?)
    ");
    $attempt_stmt->bind_param("iiii", $assignment['activity_id'], $student['id'], $total_score, $assignment['max_marks']);
    
    if ($attempt_stmt->execute()) {
        $attempt_id = $attempt_stmt->insert_id;
        $attempt_stmt->close();

        // Store answers
        foreach ($answers as $question_id => $selected_option) {
            if ($selected_option) {
                // Check if answer is correct
                $correct_stmt = $mysqli->prepare("SELECT correct_option FROM quiz_questions WHERE id = ?");
                $correct_stmt->bind_param("i", $question_id);
                $correct_stmt->execute();
                $correct_result = $correct_stmt->get_result()->fetch_assoc();
                $correct_stmt->close();

                $is_correct = $correct_result['correct_option'] === $selected_option ? 1 : 0;

                $answer_stmt = $mysqli->prepare("
                    INSERT INTO student_answers (quiz_attempt_id, question_id, selected_option, is_correct) 
                    VALUES (?, ?, ?, ?)
                ");
                $answer_stmt->bind_param("iisi", $attempt_id, $question_id, $selected_option, $is_correct);
                $answer_stmt->execute();
                $answer_stmt->close();
            }
        }

        // Update assignment status
        $update_stmt = $mysqli->prepare("
            UPDATE activity_assignments 
            SET status = ?, score = ?, completed_at = NOW() 
            WHERE id = ?
        ");
        $status = STATUS_COMPLETED;
        $update_stmt->bind_param("sii", $status, $total_score, $assignment_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Redirect to result
        header('Location: quiz_result.php?id=' . $assignment_id);
        exit();
    } else {
        $error = 'Error submitting quiz. Please try again.';
    }
}

$page_title = 'Attempt Quiz - ' . htmlspecialchars($assignment['title']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <h2 class="mb-3"><?php echo htmlspecialchars($assignment['title']); ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title">
                                Question <?php echo $index + 1; ?> 
                                <span class="badge bg-secondary"><?php echo $question['marks']; ?> marks</span>
                            </h6>
                            <p class="card-text"><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>

                            <div class="options">
                                <div class="form-check mb-2">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="question_<?php echo $question['id']; ?>" 
                                        value="a" 
                                        id="q<?php echo $question['id']; ?>_a"
                                    >
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_a">
                                        A. <?php echo htmlspecialchars($question['option_a']); ?>
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="question_<?php echo $question['id']; ?>" 
                                        value="b" 
                                        id="q<?php echo $question['id']; ?>_b"
                                    >
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_b">
                                        B. <?php echo htmlspecialchars($question['option_b']); ?>
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="question_<?php echo $question['id']; ?>" 
                                        value="c" 
                                        id="q<?php echo $question['id']; ?>_c"
                                    >
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_c">
                                        C. <?php echo htmlspecialchars($question['option_c']); ?>
                                    </label>
                                </div>

                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="question_<?php echo $question['id']; ?>" 
                                        value="d" 
                                        id="q<?php echo $question['id']; ?>_d"
                                    >
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_d">
                                        D. <?php echo htmlspecialchars($question['option_d']); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">Submit Quiz</button>
                    <a href="activities_list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Quiz Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Total Questions:</strong> <?php echo count($questions); ?></li>
                        <li class="mb-2"><strong>Maximum Marks:</strong> <?php echo $assignment['max_marks']; ?></li>
                    </ul>
                    <hr>
                    <p class="text-muted">Answer all questions and click Submit to complete the quiz.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
