<?php
/**
 * Teacher - Create Activity
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get classes for this teacher
$classes_result = $mysqli->query(
    "SELECT id, class_name FROM classes WHERE teacher_id = {$teacher_id} ORDER BY class_name"
);
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $activity_type = $_POST['activity_type'] ?? '';
    $class_id = intval($_POST['class_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? null;
    $max_marks = intval($_POST['max_marks'] ?? 100);
    $file_path = null;

    // Validation
    if (empty($title) || empty($activity_type)) {
        $error = 'Title and type are required.';
    } elseif (!in_array($activity_type, [ACTIVITY_TYPE_PDF, ACTIVITY_TYPE_QUIZ])) {
        $error = 'Invalid activity type.';
    } elseif ($activity_type === ACTIVITY_TYPE_PDF && empty($_FILES['pdf_file']['name'])) {
        $error = 'PDF file is required for PDF activities.';
    } else {
        // Handle file upload for PDF
        if ($activity_type === ACTIVITY_TYPE_PDF && !empty($_FILES['pdf_file']['name'])) {
            $file = $_FILES['pdf_file'];
            
            // Validate file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'File upload error.';
            } elseif ($file['size'] > MAX_FILE_SIZE) {
                $error = 'File size exceeds maximum allowed size.';
            } elseif ($file['type'] !== 'application/pdf') {
                $error = 'Only PDF files are allowed.';
            } else {
                // Create uploads directory if it doesn't exist
                if (!is_dir(UPLOADS_DIR)) {
                    mkdir(UPLOADS_DIR, 0755, true);
                }

                // Generate unique filename
                $filename = 'activity_' . time() . '_' . uniqid() . '.pdf';
                $filepath = UPLOADS_DIR . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $file_path = 'uploads/' . $filename;
                } else {
                    $error = 'Failed to upload file.';
                }
            }
        }

        // If no error so far, insert activity
        if (empty($error)) {
            $class_id_insert = $class_id > 0 ? $class_id : null;
            $due_date_insert = !empty($due_date) ? $due_date : null;

            $stmt = $mysqli->prepare("
                INSERT INTO activities (title, description, activity_type, class_id, created_by, due_date, max_marks, file_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sssiisii",
                $title,
                $description,
                $activity_type,
                $class_id_insert,
                $teacher_id,
                $due_date_insert,
                $max_marks,
                $file_path
            );

            if ($stmt->execute()) {
                $success = 'Activity created successfully!';
                $_POST = [];
            } else {
                $error = 'Error creating activity. Please try again.';
            }
            $stmt->close();
        }
    }
}

$page_title = 'Create Activity';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-3">Create New Activity</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="activities_list.php" class="alert-link">View all activities</a>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Activity Title</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
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
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="activity_type" class="form-label">Activity Type</label>
                            <select class="form-control" id="activity_type" name="activity_type" required onchange="updateFormFields()">
                                <option value="">Select activity type</option>
                                <option value="<?php echo ACTIVITY_TYPE_PDF; ?>" <?php echo ($_POST['activity_type'] ?? '') === ACTIVITY_TYPE_PDF ? 'selected' : ''; ?>>PDF Content</option>
                                <option value="<?php echo ACTIVITY_TYPE_QUIZ; ?>" <?php echo ($_POST['activity_type'] ?? '') === ACTIVITY_TYPE_QUIZ ? 'selected' : ''; ?>>MCQ Quiz</option>
                            </select>
                        </div>

                        <div class="mb-3" id="pdf_upload_div" style="display: none;">
                            <label for="pdf_file" class="form-label">Upload PDF File</label>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="pdf_file" 
                                name="pdf_file" 
                                accept="application/pdf"
                            >
                            <small class="text-muted">Maximum file size: 5 MB</small>
                        </div>

                        <div class="mb-3">
                            <label for="class_id" class="form-label">Assign to Class (Optional)</label>
                            <select class="form-control" id="class_id" name="class_id">
                                <option value="">No specific class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php echo (intval($_POST['class_id'] ?? 0) === $class['id']) ? 'selected' : ''; ?>>
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
                                value="<?php echo htmlspecialchars($_POST['due_date'] ?? ''); ?>"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="max_marks" class="form-label">Maximum Marks</label>
                            <input 
                                type="number" 
                                class="form-control" 
                                id="max_marks" 
                                name="max_marks" 
                                value="<?php echo htmlspecialchars($_POST['max_marks'] ?? '100'); ?>"
                                min="1"
                            >
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create Activity</button>
                            <a href="activities_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateFormFields() {
    const activityType = document.getElementById('activity_type').value;
    const pdfUploadDiv = document.getElementById('pdf_upload_div');
    const pdfFileInput = document.getElementById('pdf_file');
    
    if (activityType === '<?php echo ACTIVITY_TYPE_PDF; ?>') {
        pdfUploadDiv.style.display = 'block';
        pdfFileInput.required = true;
    } else {
        pdfUploadDiv.style.display = 'none';
        pdfFileInput.required = false;
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', updateFormFields);
</script>

<?php include '../includes/footer.php'; ?>
