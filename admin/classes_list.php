<?php
/**
 * Admin - Classes List
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * RECORDS_PER_PAGE;

// Get total count
$count_result = $mysqli->query("SELECT COUNT(*) as total FROM classes");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / RECORDS_PER_PAGE);

// Get classes with teacher names
$stmt = $mysqli->prepare("
    SELECT c.id, c.class_name, c.grade, c.section, c.teacher_id, u.full_name as teacher_name 
    FROM classes c 
    JOIN users u ON c.teacher_id = u.id 
    ORDER BY c.class_name 
    LIMIT ?, ?
");
$records_per_page = RECORDS_PER_PAGE;
$stmt->bind_param("ii", $offset, $records_per_page);
$stmt->execute();
$classes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Classes Management';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Classes Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="class_create.php" class="btn btn-primary">Add New Class</a>
        </div>
    </div>

    <!-- Classes Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <?php if (count($classes) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Class Name</th>
                            <th>Grade</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td><?php echo htmlspecialchars($class['grade']); ?></td>
                                <td><?php echo htmlspecialchars($class['section'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($class['teacher_name']); ?></td>
                                <td>
                                    <a href="class_edit.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="class_delete.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No classes found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
