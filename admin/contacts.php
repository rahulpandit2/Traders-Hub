<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get user type
$stmt = $pdo->prepare("SELECT user_type FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

$message = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $contact_id = $_POST['contact_id'];
    $new_status = $_POST['status'] === 'pending' ? 'replied' : 'pending';
    
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $contact_id]);
        
        // Log the status change
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'contact_status', ?)");
        $stmt->execute([$_SESSION['admin_id'], "Updated contact status to: " . $new_status . " for contact ID: " . $contact_id]);
        
        $message = 'Status updated successfully';
    } catch (PDOException $e) {
        $error = 'Error updating status: ' . $e->getMessage();
    }
}

// Pagination settings
// Get settings from cookies or defaults
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (isset($_COOKIE['contact_per_page']) ? (int)$_COOKIE['contact_per_page'] : 10);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Filtering and sorting parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : (isset($_COOKIE['contact_status']) ? $_COOKIE['contact_status'] : '');
$date_filter = isset($_GET['date']) ? $_GET['date'] : (isset($_COOKIE['contact_date']) ? $_COOKIE['contact_date'] : '');
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

// Get total count with filters
$count_query = "SELECT COUNT(*) FROM contacts WHERE 1=1";
$count_params = [];

// Add filters to count query
if ($status_filter !== '') {
    $count_query .= " AND status = ?";
    $count_params[] = $status_filter;
}
if ($date_filter !== '') {
    $count_query .= " AND DATE(created_at) = ?";
    $count_params[] = $date_filter;
}

// Execute count query
$stmt = $pdo->prepare($count_query);
$stmt->execute($count_params);
$total_contacts = $stmt->fetchColumn();
$total_pages = ceil($total_contacts / $per_page);

// Build the main query
$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];
$paramIndex = 1;

// Add filters
if ($status_filter !== '') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}
if ($date_filter !== '') {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date_filter;
}

// Add sorting
$valid_sort_columns = ['created_at', 'subject', 'email', 'name', 'status', 'id'];
$sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';
$query .= " ORDER BY $sort_by $sort_order LIMIT ? OFFSET ?";

// Execute query
$stmt = $pdo->prepare($query);

// Bind all parameters
$paramIndex = 1;
foreach ($params as $value) {
    $stmt->bindValue($paramIndex++, $value);
}
$stmt->bindValue($paramIndex++, (int)$per_page, PDO::PARAM_INT);
$stmt->bindValue($paramIndex, (int)$offset, PDO::PARAM_INT);

// Execute with all parameters
$stmt->execute();
$contacts = $stmt->fetchAll();
?>

<?php require_once 'partials/header.php';?>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Filter Contacts</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date Received</option>
                            <option value="subject" <?php echo $sort_by === 'subject' ? 'selected' : ''; ?>>Subject</option>
                            <option value="email" <?php echo $sort_by === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                            <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Order</label>
                        <select name="order" class="form-select">
                            <option value="desc" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                            <option value="asc" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Show per page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $per_page === 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $per_page === 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary" onclick="saveContactSettings()">Apply</button>
                    <script>
                        function saveContactSettings() {
                            const status = document.querySelector('select[name="status"]').value;
                            const date = document.querySelector('input[name="date"]').value;
                            const perPage = document.querySelector('select[name="per_page"]').value;
                            
                            CookieSettings.setCookie('contact_status', status);
                            CookieSettings.setCookie('contact_date', date);
                            CookieSettings.setCookie('contact_per_page', perPage);
                        }

                        // Set initial values from cookies
                        window.onload = function() {
                            const status = CookieSettings.getCookie('contact_status');
                            const date = CookieSettings.getCookie('contact_date');
                            const perPage = CookieSettings.getCookie('contact_per_page');

                            if (status) document.querySelector('select[name="status"]').value = status;
                            if (date) document.querySelector('input[name="date"]').value = date;
                            if (perPage) document.querySelector('select[name="per_page"]').value = perPage;
                        };
                    </script>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Contact Requests</h5>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['id']); ?></td>
                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                        <td>
                            <span class="message-preview" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#messageModal" data-message="<?php echo htmlspecialchars($contact['message']); ?>">
                                <?php echo strlen($contact['message']) > 50 ? htmlspecialchars(substr($contact['message'], 0, 50)) . ' ...See more' : htmlspecialchars($contact['message']); ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($contact['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $contact['status'] === 'replied' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($contact['status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $contact['status'] ?? 'pending'; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $contact['status'] === 'replied' ? 'warning' : 'success'; ?>">
                                    Mark as <?php echo $contact['status'] === 'replied' ? 'Pending' : 'Replied'; ?>
                                </button>
                            </form>
                        </td>
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
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">&laquo;</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">&raquo;</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Full Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageModal = document.getElementById('messageModal');
            messageModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const message = button.getAttribute('data-message');
                document.getElementById('modalMessage').textContent = message;
            });
        });
    </script>
</body>
</html>