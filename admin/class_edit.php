<?php
/**
 * Admin - Edit Class
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$class_id = intval($_GET['id'] ?? 0);
$class = null;
$error = '';
$success = '';

// Get class
if ($class_id > 0) {
    $stmt = $mysqli->prepare("SELECT id, class_name, grade, section, teacher_id FROM classes WHERE id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $class = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$class) {
    http_response_code(404);
    die('Class not found.');
}

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
        // Update class
        $update_stmt = $mysqli->prepare("UPDATE classes SET class_name = ?, grade = ?, section = ?, teacher_id = ? WHERE id = ?");
        $update_stmt->bind_param("sssii", $class_name, $grade, $section, $teacher_id, $class_id);

        if ($update_stmt->execute()) {
            $class['class_name'] = $class_name;
            $class['grade'] = $grade;
            $class['section'] = $section;
            $class['teacher_id'] = $teacher_id;
            $success = 'Class updated successfully!';
        } else {
            $error = 'Error updating class. Please try again.';
        }
        $update_stmt->close();
    }
}

$page_title = 'Edit Class';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Edit Class</h2>

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
                                value="<?php echo htmlspecialchars($class['class_name']); ?>"
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
                                value="<?php echo htmlspecialchars($class['grade']); ?>"
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
                                value="<?php echo htmlspecialchars($class['section'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="teacher_id" class="form-label">Assign Teacher</label>
                            <select class="form-control" id="teacher_id" name="teacher_id" required>
                                <option value="">Select a teacher</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?php echo $teacher['id']; ?>" <?php echo $class['teacher_id'] === $teacher['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teacher['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Class</button>
                            <a href="classes_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
