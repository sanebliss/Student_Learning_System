<?php
/**
 * Teacher - Manage Quiz Questions
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$activity_id = intval($_GET['activity_id'] ?? 0);
$activity = null;
$error = '';
$success = '';

// Get activity
if ($activity_id > 0) {
    $stmt = $mysqli->prepare("
        SELECT id, title, activity_type 
        FROM activities 
        WHERE id = ? AND created_by = ? AND activity_type = ?
    ");
    $activity_type = ACTIVITY_TYPE_QUIZ;
    $stmt->bind_param("iis", $activity_id, $teacher_id, $activity_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $activity = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$activity) {
    http_response_code(404);
    die('Quiz activity not found or access denied.');
}

// Get existing questions
$questions_stmt = $mysqli->prepare("
    SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, marks 
    FROM quiz_questions 
    WHERE activity_id = ? 
    ORDER BY id
");
$questions_stmt->bind_param("i", $activity_id);
$questions_stmt->execute();
$questions = $questions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$questions_stmt->close();

// Handle question add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $question_text = trim($_POST['question_text'] ?? '');
        $option_a = trim($_POST['option_a'] ?? '');
        $option_b = trim($_POST['option_b'] ?? '');
        $option_c = trim($_POST['option_c'] ?? '');
        $option_d = trim($_POST['option_d'] ?? '');
        $correct_option = $_POST['correct_option'] ?? '';
        $marks = intval($_POST['marks'] ?? 1);

        if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d) || empty($correct_option)) {
            $error = 'All fields are required.';
        } elseif (!in_array($correct_option, ['a', 'b', 'c', 'd'])) {
            $error = 'Invalid correct option.';
        } elseif ($marks < 1) {
            $error = 'Marks must be at least 1.';
        } else {
            $insert_stmt = $mysqli->prepare("
                INSERT INTO quiz_questions (activity_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insert_stmt->bind_param(
                "issssssi",
                $activity_id,
                $question_text,
                $option_a,
                $option_b,
                $option_c,
                $option_d,
                $correct_option,
                $marks
            );

            if ($insert_stmt->execute()) {
                $success = 'Question added successfully!';
                // Refresh questions
                $questions_stmt = $mysqli->prepare("
                    SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, marks 
                    FROM quiz_questions 
                    WHERE activity_id = ? 
                    ORDER BY id
                ");
                $questions_stmt->bind_param("i", $activity_id);
                $questions_stmt->execute();
                $questions = $questions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $questions_stmt->close();
                $_POST = [];
            } else {
                $error = 'Error adding question. Please try again.';
            }
            $insert_stmt->close();
        }
    }
}

// Handle question delete
if (isset($_GET['delete_question_id'])) {
    $question_id = intval($_GET['delete_question_id']);
    
    // Verify question belongs to this activity and teacher
    $check_stmt = $mysqli->prepare("
        SELECT qq.id FROM quiz_questions qq
        JOIN activities a ON qq.activity_id = a.id
        WHERE qq.id = ? AND a.id = ? AND a.created_by = ?
    ");
    $check_stmt->bind_param("iii", $question_id, $activity_id, $teacher_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $delete_stmt = $mysqli->prepare("DELETE FROM quiz_questions WHERE id = ?");
        $delete_stmt->bind_param("i", $question_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Refresh questions
        $questions_stmt = $mysqli->prepare("
            SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, marks 
            FROM quiz_questions 
            WHERE activity_id = ? 
            ORDER BY id
        ");
        $questions_stmt->bind_param("i", $activity_id);
        $questions_stmt->execute();
        $questions = $questions_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $questions_stmt->close();
        
        $success = 'Question deleted successfully!';
    }
    $check_stmt->close();
}

$page_title = 'Manage Quiz Questions';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Manage Quiz: <?php echo htmlspecialchars($activity['title']); ?></h2>
            <p class="text-muted">Total Questions: <?php echo count($questions); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="activities_list.php" class="btn btn-secondary">Back to Activities</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Add Question Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add New Question</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">

                <div class="mb-3">
                    <label for="question_text" class="form-label">Question Text</label>
                    <textarea 
                        class="form-control" 
                        id="question_text" 
                        name="question_text" 
                        rows="3"
                        required
                    ><?php echo htmlspecialchars($_POST['question_text'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="option_a" class="form-label">Option A</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="option_a" 
                                name="option_a" 
                                value="<?php echo htmlspecialchars($_POST['option_a'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="option_b" class="form-label">Option B</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="option_b" 
                                name="option_b" 
                                value="<?php echo htmlspecialchars($_POST['option_b'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="option_c" class="form-label">Option C</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="option_c" 
                                name="option_c" 
                                value="<?php echo htmlspecialchars($_POST['option_c'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="option_d" class="form-label">Option D</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="option_d" 
                                name="option_d" 
                                value="<?php echo htmlspecialchars($_POST['option_d'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="correct_option" class="form-label">Correct Option</label>
                            <select class="form-control" id="correct_option" name="correct_option" required>
                                <option value="">Select correct option</option>
                                <option value="a" <?php echo ($_POST['correct_option'] ?? '') === 'a' ? 'selected' : ''; ?>>Option A</option>
                                <option value="b" <?php echo ($_POST['correct_option'] ?? '') === 'b' ? 'selected' : ''; ?>>Option B</option>
                                <option value="c" <?php echo ($_POST['correct_option'] ?? '') === 'c' ? 'selected' : ''; ?>>Option C</option>
                                <option value="d" <?php echo ($_POST['correct_option'] ?? '') === 'd' ? 'selected' : ''; ?>>Option D</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="marks" 
                                name="marks" 
                                value="<?php echo htmlspecialchars($_POST['marks'] ?? '1'); ?>"
                                min="1"
                                required
                            >
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Add Question</button>
            </form>
        </div>
    </div>

    <!-- Questions List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Questions (<?php echo count($questions); ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (count($questions) > 0): ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-3 border-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title">Question <?php echo $index + 1; ?> <span class="badge bg-secondary"><?php echo $question['marks']; ?> marks</span></h6>
                                <a href="?activity_id=<?php echo $activity_id; ?>&delete_question_id=<?php echo $question['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this question?');">Delete</a>
                            </div>
                            <p class="card-text"><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                            <ul class="list-unstyled ms-3">
                                <li class="mb-1">
                                    <span class="<?php echo $question['correct_option'] === 'a' ? 'bg-success text-white px-2 rounded' : ''; ?>">
                                        A. <?php echo htmlspecialchars($question['option_a']); ?>
                                        <?php if ($question['correct_option'] === 'a'): ?> <i class="bi bi-check-circle"></i> ✓<?php endif; ?>
                                    </span>
                                </li>
                                <li class="mb-1">
                                    <span class="<?php echo $question['correct_option'] === 'b' ? 'bg-success text-white px-2 rounded' : ''; ?>">
                                        B. <?php echo htmlspecialchars($question['option_b']); ?>
                                        <?php if ($question['correct_option'] === 'b'): ?> ✓<?php endif; ?>
                                    </span>
                                </li>
                                <li class="mb-1">
                                    <span class="<?php echo $question['correct_option'] === 'c' ? 'bg-success text-white px-2 rounded' : ''; ?>">
                                        C. <?php echo htmlspecialchars($question['option_c']); ?>
                                        <?php if ($question['correct_option'] === 'c'): ?> ✓<?php endif; ?>
                                    </span>
                                </li>
                                <li>
                                    <span class="<?php echo $question['correct_option'] === 'd' ? 'bg-success text-white px-2 rounded' : ''; ?>">
                                        D. <?php echo htmlspecialchars($question['option_d']); ?>
                                        <?php if ($question['correct_option'] === 'd'): ?> ✓<?php endif; ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No questions added yet. Add questions using the form above.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
