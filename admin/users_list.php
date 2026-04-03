<?php
/**
 * Admin - Users List
 * View all users with pagination and search
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$search = trim($_GET['search'] ?? '');
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * RECORDS_PER_PAGE;

// Build search query
$where = "WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $where .= " AND (full_name LIKE ? OR email LIKE ?)";
    $search_term = "%{$search}%";
    $params = [$search_term, $search_term];
    $types = "ss";
}

// Get total count
$count_stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM users {$where}");
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / RECORDS_PER_PAGE);
$count_stmt->close();

// Get users for current page
$list_stmt = $mysqli->prepare("SELECT id, full_name, email, role, created_at FROM users {$where} ORDER BY created_at DESC LIMIT ?, ?");
$records_per_page = RECORDS_PER_PAGE;
if (!empty($params)) {
    $types .= "ii";
    $params[] = $offset;
    $params[] = $records_per_page;
    $list_stmt->bind_param($types, ...$params);
} else {
    $list_stmt->bind_param("ii", $offset, $records_per_page);
}
$list_stmt->execute();
$users = $list_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$list_stmt->close();

$page_title = 'Users List';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Users Management</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="user_create.php" class="btn btn-primary">Add New User</a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="search" 
                        placeholder="Search by name or email..." 
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-info w-100">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="users_list.php" class="btn btn-secondary w-100 mt-2">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <?php if (count($users) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="user_delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
