<?php
/**
 * Student - View Activity
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
        SELECT aa.id, aa.activity_id, a.title, a.description, a.activity_type, a.max_marks, a.file_path, aa.status, aa.score
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
    die('Activity not found or access denied.');
}

// Handle quiz attempt redirect
if ($assignment['activity_type'] === ACTIVITY_TYPE_QUIZ) {
    if ($assignment['status'] === STATUS_COMPLETED) {
        // Show results
        header('Location: quiz_result.php?id=' . $assignment_id);
    } else {
        // Redirect to attempt
        header('Location: quiz_attempt.php?id=' . $assignment_id);
    }
    exit();
}

$page_title = htmlspecialchars($assignment['title']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
            
            <div class="card mb-3">
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($assignment['description'] ?? '')); ?></p>
                </div>
            </div>

            <?php if ($assignment['activity_type'] === ACTIVITY_TYPE_PDF && $assignment['file_path']): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">PDF Content</h5>
                    </div>
                    <div class="card-body">
                        <iframe 
                            src="<?php echo SITE_URL . htmlspecialchars($assignment['file_path']); ?>" 
                            style="width:100%; height:600px; border: none;">
                        </iframe>
                        <br><br>
                        <a href="<?php echo SITE_URL . htmlspecialchars($assignment['file_path']); ?>" target="_blank" class="btn btn-primary">
                            Download PDF
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Activity Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Type:</strong> <?php echo ucfirst($assignment['activity_type']); ?>
                        </li>
                        <li class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge bg-<?php 
                                echo $assignment['status'] === STATUS_COMPLETED ? 'success' : 
                                     ($assignment['status'] === STATUS_IN_PROGRESS ? 'warning' : 'secondary');
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $assignment['status'])); ?>
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Maximum Marks:</strong> <?php echo $assignment['max_marks']; ?>
                        </li>
                        <?php if ($assignment['score'] !== null): ?>
                            <li class="mb-2">
                                <strong>Score:</strong> <?php echo $assignment['score']; ?>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <?php if ($assignment['status'] !== STATUS_COMPLETED): ?>
                        <div class="mt-3">
                            <form method="POST" action="">
                                <input type="hidden" name="mark_completed" value="1">
                                <button type="submit" class="btn btn-success w-100">Mark as Completed</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
