<?php
/**
 * Admin - Students List
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$class_id = intval($_GET['class_id'] ?? 0);
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * RECORDS_PER_PAGE;

// Get classes for filter
$classes_result = $mysqli->query("SELECT id, class_name FROM classes ORDER BY class_name");
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);

// Build query
$where = "WHERE 1=1";
$count_params = [];
$list_params = [];

if ($class_id > 0) {
    $where .= " AND s.class_id = ?";
    $count_params[] = $class_id;
    $list_params[] = $class_id;
}

// Count query
$count_sql = "SELECT COUNT(*) as total FROM students s {$where}";
$count_stmt = $mysqli->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param(str_repeat('i', count($count_params)), ...$count_params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / RECORDS_PER_PAGE);
$count_stmt->close();

// List query
$list_sql = "
    SELECT s.id, s.user_id, u.full_name, u.email, c.class_name, p.full_name as parent_name
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN classes c ON s.class_id = c.id
    LEFT JOIN users p ON s.parent_user_id = p.id
    {$where}
    ORDER BY u.full_name
    LIMIT ?, ?
";

$list_stmt = $mysqli->prepare($list_sql);
$records_per_page = RECORDS_PER_PAGE;
if (!empty($list_params)) {
    $list_params[] = $offset;
    $list_params[] = $records_per_page;
    $list_stmt->bind_param(str_repeat('i', count($list_params)), ...$list_params);
} else {
    $list_stmt->bind_param("ii", $offset, $records_per_page);
}
$list_stmt->execute();
$students = $list_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$list_stmt->close();

$page_title = 'Students Management';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Students Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="student_create.php" class="btn btn-primary">Add New Student</a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <select class="form-control" name="class_id">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $class_id === $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-info w-100">Filter</button>
                    <?php if ($class_id > 0): ?>
                        <a href="students_list.php" class="btn btn-secondary w-100 mt-2">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <?php if (count($students) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['parent_name'] ?? 'Not Assigned'); ?></td>
                                <td>
                                    <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="student_delete.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No students found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo $class_id > 0 ? '&class_id=' . $class_id : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $class_id > 0 ? '&class_id=' . $class_id : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $class_id > 0 ? '&class_id=' . $class_id : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $class_id > 0 ? '&class_id=' . $class_id : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $class_id > 0 ? '&class_id=' . $class_id : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
