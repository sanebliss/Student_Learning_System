<?php
/**
 * Teacher - Edit Activity
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$activity_id = intval($_GET['id'] ?? 0);
$activity = null;
$error = '';
$success = '';

// Get activity
if ($activity_id > 0) {
    $stmt = $mysqli->prepare("
        SELECT id, title, description, activity_type, class_id, due_date, max_marks, file_path 
        FROM activities 
        WHERE id = ? AND created_by = ?
    ");
    $stmt->bind_param("ii", $activity_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $activity = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$activity) {
    http_response_code(404);
    die('Activity not found or access denied.');
}

// Get classes for this teacher
$classes_result = $mysqli->query(
    "SELECT id, class_name FROM classes WHERE teacher_id = {$teacher_id} ORDER BY class_name"
);
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? null;
    $max_marks = intval($_POST['max_marks'] ?? 100);

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        // Update activity
        $class_id_update = $class_id > 0 ? $class_id : null;
        $due_date_update = !empty($due_date) ? $due_date : null;

        $stmt = $mysqli->prepare("
            UPDATE activities 
            SET title = ?, description = ?, class_id = ?, due_date = ?, max_marks = ? 
            WHERE id = ? AND created_by = ?
        ");
        $stmt->bind_param(
            "ssiiiii",
            $title,
            $description,
            $class_id_update,
            $due_date_update,
            $max_marks,
            $activity_id,
            $teacher_id
        );

        if ($stmt->execute()) {
            $activity['title'] = $title;
            $activity['description'] = $description;
            $activity['class_id'] = $class_id_update;
            $activity['due_date'] = $due_date_update;
            $activity['max_marks'] = $max_marks;
            $success = 'Activity updated successfully!';
        } else {
            $error = 'Error updating activity. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Edit Activity';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-3">Edit Activity</h2>

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
                        <label class="form-label"><strong>Activity Type:</strong></label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-<?php echo $activity['activity_type'] === ACTIVITY_TYPE_QUIZ ? 'info' : 'secondary'; ?>">
                                <?php echo ucfirst($activity['activity_type']); ?>
                            </span>
                        </p>
                    </div>

                    <?php if ($activity['activity_type'] === ACTIVITY_TYPE_PDF && $activity['file_path']): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Current PDF:</strong></label>
                            <p class="form-control-plaintext">
                                <a href="<?php echo SITE_URL . htmlspecialchars($activity['file_path']); ?>" target="_blank" class="btn btn-sm btn-info">
                                    View PDF
                                </a>
                            </p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Activity Title</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                value="<?php echo htmlspecialchars($activity['title']); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea 
                                class="form-control" 
                                id="description" 
                                name="description" 
                                rows="4"
                            ><?php echo htmlspecialchars($activity['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Assign to Class (Optional)</label>
                            <select class="form-control" id="class_id" name="class_id">
                                <option value="">No specific class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo $activity['class_id'] === $class['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="due_date" class="form-label">Due Date (Optional)</label>
                            <input 
                                type="datetime-local" 
                                class="form-control" 
                                id="due_date" 
                                name="due_date" 
                                value="<?php echo !empty($activity['due_date']) ? date('Y-m-d\TH:i', strtotime($activity['due_date'])) : ''; ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="max_marks" class="form-label">Maximum Marks</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="max_marks" 
                                name="max_marks" 
                                value="<?php echo htmlspecialchars($activity['max_marks']); ?>"
                                min="1"
                            >
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Activity</button>
                            <a href="activities_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
