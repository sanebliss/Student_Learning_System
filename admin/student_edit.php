<?php
/**
 * Admin - Edit Student
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$student_id = intval($_GET['id'] ?? 0);
$student = null;
$error = '';
$success = '';

// Get student
if ($student_id > 0) {
    $stmt = $mysqli->prepare("SELECT id, user_id, class_id, parent_user_id FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$student) {
    http_response_code(404);
    die('Student not found.');
}

// Get classes
$classes_result = $mysqli->query("SELECT id, class_name FROM classes ORDER BY class_name");
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

// Get parents
$parents_result = $mysqli->query("SELECT id, full_name FROM users WHERE role = '" . ROLE_PARENT . "' ORDER BY full_name");
$parents = $parents_result->fetch_all(MYSQLI_ASSOC);

// Get student user info
$user_stmt = $mysqli->prepare("SELECT full_name, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $student['user_id']);
$user_stmt->execute();
$student_user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = intval($_POST['class_id'] ?? 0);
    $parent_user_id = intval($_POST['parent_user_id'] ?? 0);

    if ($class_id <= 0) {
        $error = 'Class is required.';
    } else {
        // Set parent_user_id to NULL if not selected
        $parent_to_update = $parent_user_id > 0 ? $parent_user_id : null;

        // Update student
        $update_stmt = $mysqli->prepare("UPDATE students SET class_id = ?, parent_user_id = ? WHERE id = ?");
        $update_stmt->bind_param("iii", $class_id, $parent_to_update, $student_id);

        if ($update_stmt->execute()) {
            $student['class_id'] = $class_id;
            $student['parent_user_id'] = $parent_to_update;
            $success = 'Student updated successfully!';
        } else {
            $error = 'Error updating student. Please try again.';
        }
        $update_stmt->close();
    }
}

$page_title = 'Edit Student';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Edit Student</h2>

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
                    <div class="mb-3">
                        <label class="form-label"><strong>Student Name:</strong></label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($student_user['full_name']); ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Email:</strong></label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($student_user['email']); ?></p>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-control" id="class_id" name="class_id" required>
                                <option value="">Select a class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $student['class_id'] === $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="parent_user_id" class="form-label">Link Parent User (Optional)</label>
                            <select class="form-control" id="parent_user_id" name="parent_user_id">
                                <option value="">No parent assigned</option>
                                <?php foreach ($parents as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>" <?php echo $student['parent_user_id'] === $parent['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($parent['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Student</button>
                            <a href="students_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
