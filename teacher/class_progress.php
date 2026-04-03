<?php
/**
 * Teacher - Class Progress
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$class_id = intval($_GET['class_id'] ?? 0);
$class = null;
$error = '';

// Get class details
if ($class_id > 0) {
    $stmt = $mysqli->prepare("SELECT id, class_name FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$class && $class_id > 0) {
    $error = 'Class not found or access denied.';
}

// If no class selected, show list of classes
if (!$class) {
    $classes_result = $mysqli->query(
        "SELECT id, class_name FROM classes WHERE teacher_id = {$teacher_id} ORDER BY class_name"
    );
    $classes = $classes_result->fetch_all(MYSQLI_ASSOC);

    $page_title = 'Class Progress';
    include '../includes/header.php';
    include '../includes/nav.php';
    ?>
    <div class="container mt-4">
        <h2 class="mb-3">Select Class to View Progress</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (count($classes) > 0): ?>
                    <div class="list-group">
                        <?php foreach ($classes as $c): ?>
                            <a href="?class_id=<?php echo $c['id']; ?>" class="list-group-item list-group-item-action">
                                <?php echo htmlspecialchars($c['class_name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No classes assigned to you yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; 
    exit();
}

// Get students in class with their progress
$students_result = $mysqli->query("
    SELECT s.id, u.full_name, u.email
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.class_id = {$class_id}
    ORDER BY u.full_name
");
$students = $students_result->fetch_all(MYSQLI_ASSOC);

$page_title = 'Class Progress - ' . htmlspecialchars($class['class_name']);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><?php echo htmlspecialchars($class['class_name']); ?> - Progress</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="class_progress.php" class="btn btn-secondary">Change Class</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <?php if (count($students) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Completed</th>
                            <th>In Progress</th>
                            <th>Not Started</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <?php
                            // Get activity counts for this student
                            $counts_result = $mysqli->query("
                                SELECT 
                                    COALESCE(SUM(CASE WHEN status = '" . STATUS_COMPLETED . "' THEN 1 ELSE 0 END), 0) as completed,
                                    COALESCE(SUM(CASE WHEN status = '" . STATUS_IN_PROGRESS . "' THEN 1 ELSE 0 END), 0) as in_progress,
                                    COALESCE(SUM(CASE WHEN status = '" . STATUS_NOT_STARTED . "' THEN 1 ELSE 0 END), 0) as not_started
                                FROM activity_assignments
                                WHERE student_id = {$student['id']}
                            ");
                            $counts = $counts_result->fetch_assoc();
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><span class="badge bg-success"><?php echo $counts['completed']; ?></span></td>
                                <td><span class="badge bg-warning"><?php echo $counts['in_progress']; ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo $counts['not_started']; ?></span></td>
                                <td>
                                    <a href="student_progress.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No students in this class yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
