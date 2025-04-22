<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user info
$stmt = $pdo->prepare("SELECT user_type FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

// Only admin can view logs
if ($user['user_type'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (isset($_COOKIE['logs_per_page']) ? (int)$_COOKIE['logs_per_page'] : 20);
$offset = ($page - 1) * $per_page;

// Filter parameters
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$username = isset($_GET['username']) ? $_GET['username'] : '';
$action_type = isset($_GET['action_type']) ? $_GET['action_type'] : '';

// Sorting parameters
$valid_columns = ['created_at', 'username', 'action_type'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $valid_columns) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

// Build WHERE clause
// Build WHERE clause
$where_conditions = [];
$params = [];
$paramIndex = 1;

if ($date_from) {
    $where_conditions[] = "l.created_at >= :param" . $paramIndex;
    $params[':param' . $paramIndex++] = $date_from . ' 00:00:00';
}
if ($date_to) {
    $where_conditions[] = "l.created_at <= :param" . $paramIndex;
    $params[':param' . $paramIndex++] = $date_to . ' 23:59:59';
}
if ($username) {
    $where_conditions[] = "u.username LIKE :param" . $paramIndex;
    $params[':param' . $paramIndex++] = "%$username%";
}
if ($action_type) {
    $where_conditions[] = "l.action_type = :param" . $paramIndex;
    $params[':param' . $paramIndex++] = $action_type;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(*) FROM activity_logs l JOIN admin_users u ON l.user_id = u.id $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Get unique action types for filter dropdown
$action_types_stmt = $pdo->query("SELECT DISTINCT action_type FROM activity_logs ORDER BY action_type");
$action_types = $action_types_stmt->fetchAll(PDO::FETCH_COLUMN);

// Get logs with user info
$sql = "SELECT l.*, u.username FROM activity_logs l JOIN admin_users u ON l.user_id = u.id $where_clause ORDER BY $sort_column $sort_order LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$paramIndex = 1;
foreach ($params as $param) {
    $stmt->bindValue(':param' . $paramIndex, $param);
    $paramIndex++;
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<?php require_once 'partials/header.php';?>

    <div class="container py-5">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Activity Logs</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" placeholder="Search username">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Action Type</label>
                        <select name="action_type" class="form-select">
                            <option value="">All</option>
                            <?php foreach ($action_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $action_type === $type ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(str_replace('_', ' ', $type)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Show per page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $per_page === 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $per_page === 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2" onclick="saveLogSettings()">Apply</button>
                        <a href="logs.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
                <script>
                    function saveLogSettings() {
                        const perPage = document.querySelector('select[name="per_page"]').value;
                        CookieSettings.setCookie('logs_per_page', perPage);
                    }

                    // Set initial values from cookies
                    window.onload = function() {
                        const perPage = CookieSettings.getCookie('logs_per_page');
                        if (perPage) document.querySelector('select[name="per_page"]').value = perPage;
                    };
                </script>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?page=<?php echo $page; ?>&per_page=<?php echo $per_page; ?>&sort=created_at&order=<?php echo $sort_column === 'created_at' && $sort_order === 'DESC' ? 'asc' : 'desc'; ?><?php echo $date_from ? '&date_from='.$date_from : ''; ?><?php echo $date_to ? '&date_to='.$date_to : ''; ?><?php echo $username ? '&username='.$username : ''; ?><?php echo $action_type ? '&action_type='.$action_type : ''; ?>" class="text-dark text-decoration-none">
                                        Date/Time
                                        <?php if ($sort_column === 'created_at'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'DESC' ? 'down' : 'up'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?page=<?php echo $page; ?>&per_page=<?php echo $per_page; ?>&sort=username&order=<?php echo $sort_column === 'username' && $sort_order === 'DESC' ? 'asc' : 'desc'; ?><?php echo $date_from ? '&date_from='.$date_from : ''; ?><?php echo $date_to ? '&date_to='.$date_to : ''; ?><?php echo $username ? '&username='.$username : ''; ?><?php echo $action_type ? '&action_type='.$action_type : ''; ?>" class="text-dark text-decoration-none">
                                        User
                                        <?php if ($sort_column === 'username'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'DESC' ? 'down' : 'up'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?page=<?php echo $page; ?>&per_page=<?php echo $per_page; ?>&sort=action_type&order=<?php echo $sort_column === 'action_type' && $sort_order === 'DESC' ? 'asc' : 'desc'; ?><?php echo $date_from ? '&date_from='.$date_from : ''; ?><?php echo $date_to ? '&date_to='.$date_to : ''; ?><?php echo $username ? '&username='.$username : ''; ?><?php echo $action_type ? '&action_type='.$action_type : ''; ?>" class="text-dark text-decoration-none">
                                        Action
                                        <?php if ($sort_column === 'action_type'): ?>
                                            <i class="bi bi-arrow-<?php echo $sort_order === 'DESC' ? 'down' : 'up'; ?>"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($log['action_type']) {
                                            'file_upload' => 'success',
                                            'file_delete' => 'danger',
                                            'contact_status' => 'info',
                                            'user_update' => 'warning',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $log['action_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&per_page=<?php echo $per_page; ?>">&laquo;</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&per_page=<?php echo $per_page; ?>">&raquo;</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php require_once 'partials/footer.php'; ?>