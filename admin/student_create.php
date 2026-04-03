<?php
/**
 * Admin - Create Student
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$error = '';
$success = '';

// Get classes
$classes_result = $mysqli->query("SELECT id, class_name FROM classes ORDER BY class_name");
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

// Get parents
$parents_result = $mysqli->query("SELECT id, full_name FROM users WHERE role = '" . ROLE_PARENT . "' ORDER BY full_name");
$parents = $parents_result->fetch_all(MYSQLI_ASSOC);

// Get available student users (users with role='student' that are not yet in students table)
$students_sql = "
    SELECT u.id, u.full_name 
    FROM users u 
    WHERE u.role = ? 
    AND u.id NOT IN (SELECT user_id FROM students)
    ORDER BY u.full_name
";
$stmt = $mysqli->prepare($students_sql);
$role = ROLE_STUDENT;
$stmt->bind_param("s", $role);
$stmt->execute();
$available_students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $parent_user_id = intval($_POST['parent_user_id'] ?? 0);

    if ($user_id <= 0 || $class_id <= 0) {
        $error = 'Student and class are required.';
    } else {
        // Check if student already exists
        $check_stmt = $mysqli->prepare("SELECT id FROM students WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'This student is already enrolled.';
        } else {
            // Set parent_user_id to NULL if not selected
            $parent_to_insert = $parent_user_id > 0 ? $parent_user_id : null;

            // Insert student
            $insert_stmt = $mysqli->prepare("INSERT INTO students (user_id, class_id, parent_user_id) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iii", $user_id, $class_id, $parent_to_insert);

            if ($insert_stmt->execute()) {
                $success = 'Student enrolled successfully!';
                $_POST = [];
            } else {
                $error = 'Error enrolling student. Please try again.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

$page_title = 'Enroll Student';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Enroll New Student</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="students_list.php" class="alert-link">View all students</a>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select Student User</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                <option value="">Choose a student...</option>
                                <?php foreach ($available_students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo (intval($_POST['user_id'] ?? 0) === $student['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($student['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($available_students)): ?>
                                <small class="text-danger">No available student users. Please create student users first.</small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Assign to Class</label>
                            <select class="form-control" id="class_id" name="class_id" required>
                                <option value="">Select a class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo (intval($_POST['class_id'] ?? 0) === $class['id']) ? 'selected' : ''; ?>>
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
                                    <option value="<?php echo $parent['id']; ?>" <?php echo (intval($_POST['parent_user_id'] ?? 0) === $parent['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($parent['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Enroll Student</button>
                            <a href="students_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
