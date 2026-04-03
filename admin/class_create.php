<?php
/**
 * Admin - Create Class
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$error = '';
$success = '';

// Get teachers
$teachers_result = $mysqli->query("SELECT id, full_name FROM users WHERE role = '" . ROLE_TEACHER . "' ORDER BY full_name");
$teachers = $teachers_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    if (empty($class_name) || empty($grade) || $teacher_id <= 0) {
        $error = 'Class name, grade, and teacher are required.';
    } else {
        // Insert class
        $insert_stmt = $mysqli->prepare("INSERT INTO classes (class_name, grade, section, teacher_id) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("sssi", $class_name, $grade, $section, $teacher_id);

        if ($insert_stmt->execute()) {
            $success = 'Class created successfully!';
            $_POST = [];
        } else {
            $error = 'Error creating class. Please try again.';
        }
        $insert_stmt->close();
    }
}

$page_title = 'Create Class';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Create New Class</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="classes_list.php" class="alert-link">View all classes</a>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="class_name" 
                                name="class_name" 
                                value="<?php echo htmlspecialchars($_POST['class_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="grade" 
                                name="grade" 
                                value="<?php echo htmlspecialchars($_POST['grade'] ?? ''); ?>"
                                placeholder="e.g., 1st, 2nd, 3rd"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="section" class="form-label">Section</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="section" 
                                name="section" 
                                value="<?php echo htmlspecialchars($_POST['section'] ?? ''); ?>"
                                placeholder="e.g., A, B, C"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="teacher_id" class="form-label">Assign Teacher</label>
                            <select class="form-control" id="teacher_id" name="teacher_id" required>
                                <option value="">Select a teacher</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>" <?php echo (intval($_POST['teacher_id'] ?? 0) === $teacher['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teacher['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Class</button>
                            <a href="classes_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
