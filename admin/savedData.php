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

// Handle AJAX request for server list
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_servers' && isset($_GET['terminal_type'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT server FROM saved_data WHERE terminal_type = ? AND user_id = ?");
    $stmt->execute([$_GET['terminal_type'], $_SESSION['admin_id']]);
    $servers = $stmt->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: application/json');
    echo json_encode($servers);
    exit;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'edit') {
                // Validate required fields
                if (
                    empty($_POST['id']) || empty($_POST['title']) ||
                    empty($_POST['terminal_type']) || empty($_POST['server']) ||
                    empty($_POST['account_number'])
                ) {
                    throw new Exception("Required fields cannot be empty");
                }

                // First verify the record belongs to the user
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_data WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['id'], $_SESSION['admin_id']]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception("Record not found or access denied");
                }

                // In the edit action handling
                $sql = "UPDATE saved_data SET 
                    title = ?, 
                    terminal_type = ?, 
                    server = ?, 
                    account_number = ?, 
                    short_note = ?";
                $params = [
                    $_POST['title'],
                    $_POST['terminal_type'],
                    $_POST['server'],
                    $_POST['account_number'],
                    $_POST['short_note'] ?? null
                ];

                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $sql .= ", updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?";
                $params[] = $_POST['id'];
                $params[] = $_SESSION['admin_id'];

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                if ($stmt->rowCount() > 0) {
                    $message = "Data updated successfully!";
                } else {
                    throw new Exception("No changes were made");
                }
            } elseif ($_POST['action'] === 'delete') {
                if (empty($_POST['id'])) {
                    throw new Exception("Invalid delete request");
                }

                // First verify the record belongs to the user
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_data WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['id'], $_SESSION['admin_id']]);
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception("Record not found or access denied");
                }
                
                // Remove the incorrect query that was causing the error
                // Delete the record
                $stmt = $pdo->prepare("DELETE FROM saved_data WHERE id = ? AND user_id = ?");
                $stmt->execute([$_POST['id'], $_SESSION['admin_id']]);

                if ($stmt->rowCount() > 0) {
                    $message = "Data deleted successfully!";
                } else {
                    throw new Exception("Failed to delete record");
                }
            }
        } else {
            // Adding new data
            if (
                empty($_POST['title']) || empty($_POST['terminal_type']) ||
                empty($_POST['server']) || empty($_POST['account_number']) ||
                empty($_POST['password'])
            ) {
                throw new Exception("All fields except short note are required");
            }

            $stmt = $pdo->prepare("INSERT INTO saved_data (user_id, title, terminal_type, server, 
                                  account_number, password, short_note, created_at, updated_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");

            $stmt->execute([
                $_SESSION['admin_id'],
                $_POST['title'],
                $_POST['terminal_type'],
                $_POST['server'],
                $_POST['account_number'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['short_note'] ?? null
            ]);

            if ($stmt->rowCount() > 0) {
                $message = "Data saved successfully!";
            } else {
                throw new Exception("Failed to save data");
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get list of servers based on terminal type
$servers = [];
if (isset($_GET['terminal_type'])) {
    $stmt = $pdo->prepare("SELECT DISTINCT server FROM saved_data WHERE terminal_type = ? AND user_id = ?");
    $stmt->execute([$_GET['terminal_type'], $_SESSION['admin_id']]);
    $servers = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get saved data with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where_conditions = ["user_id = ?"];
$params = [$_SESSION['admin_id']];

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $where_conditions[] = "(title LIKE ? OR account_number LIKE ?)";
    $params[] = $search;
    $params[] = $search;
}

if (!empty($_GET['terminal_type'])) {
    $where_conditions[] = "terminal_type = ?";
    $params[] = $_GET['terminal_type'];
}

if (!empty($_GET['server'])) {
    $where_conditions[] = "server = ?";
    $params[] = $_GET['server'];
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// First get total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_data $where_clause");
$stmt->execute($params);
$total_records = $stmt->fetchColumn();

$total_pages = ceil($total_records / $per_page);

// Fix: Use the LIMIT and OFFSET directly in the query string
$stmt = $pdo->prepare("SELECT * FROM saved_data $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$saved_data = $stmt->fetchAll();

$page_title = "Saved Data";
require_once 'partials/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or account number" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <select name="terminal_type" id="terminal_type" class="form-control">
                        <option value="">All Terminal Types</option>
                        <option value="MT4" <?php echo isset($_GET['terminal_type']) && $_GET['terminal_type'] === 'MT4' ? 'selected' : ''; ?>>MT4</option>
                        <option value="MT5" <?php echo isset($_GET['terminal_type']) && $_GET['terminal_type'] === 'MT5' ? 'selected' : ''; ?>>MT5</option>
                        <option value="webterminal" <?php echo isset($_GET['terminal_type']) && $_GET['terminal_type'] === 'webterminal' ? 'selected' : ''; ?>>Web Terminal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="server" class="form-control">
                        <option value="">All Servers</option>
                        <?php foreach ($servers as $server): ?>
                            <option value="<?php echo htmlspecialchars($server); ?>"
                                <?php echo isset($_GET['server']) && $_GET['server'] === $server ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($server); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="savedData.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#addNewDataForm" aria-expanded="true" aria-controls="addNewDataForm">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Add New Data</h4>(Collapse/Expand)
            </div>
        </div>
        <div class="collapse show" id="addNewDataForm">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="terminal_type" class="form-label">Terminal Type</label>
                            <select class="form-select" id="terminal_type" name="terminal_type" required>
                                <option value="">Select Terminal Type</option>
                                <option value="MT4">MT4</option>
                                <option value="MT5">MT5</option>
                                <option value="webterminal">Web Terminal</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="server" class="form-label">Server</label>
                            <input type="text" class="form-control" id="server" name="server" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="account_number" class="form-label">Account Number</label>
                            <input type="text" class="form-control" id="account_number" name="account_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="short_note" class="form-label">Short Note</label>
                            <textarea class="form-control" id="short_note" name="short_note" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Data</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Saved Data</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Terminal Type</th>
                            <th>Server</th>
                            <th>Account Number</th>
                            <th>Short Note</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($saved_data as $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($data['title']); ?></td>
                                <td><?php echo htmlspecialchars($data['terminal_type']); ?></td>
                                <td><?php echo htmlspecialchars($data['server']); ?></td>
                                <td><?php echo htmlspecialchars($data['account_number']); ?></td>
                                <td><?php echo htmlspecialchars($data['short_note'] ?? ''); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($data['created_at'])); ?></td>
                                <td>
                                    <!-- In the table rows, ensure data attributes are correctly named -->
                                    <button class="btn btn-sm btn-primary edit-btn"
                                        data-id="<?php echo $data['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($data['title']); ?>"
                                        data-terminal-type="<?php echo htmlspecialchars($data['terminal_type']); ?>"
                                        data-server="<?php echo htmlspecialchars($data['server']); ?>"
                                        data-account-number="<?php echo htmlspecialchars($data['account_number']); ?>"
                                        data-short-note="<?php echo htmlspecialchars($data['short_note'] ?? ''); ?>">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn"
                                        data-id="<?php echo $data['id']; ?>"
                                        data-title="<?php echo htmlspecialchars($data['title']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Saved Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" method="POST" action="">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" id="edit_id">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" id="edit_title" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Terminal Type</label>
                                    <select class="form-select" name="terminal_type" id="edit_terminal_type" required>
                                        <option value="MT4">MT4</option>
                                        <option value="MT5">MT5</option>
                                        <option value="webterminal">Web Terminal</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Server</label>
                                    <input type="text" class="form-control" name="server" id="edit_server" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Account Number</label>
                                    <input type="text" class="form-control" name="account_number" id="edit_account_number" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control" name="password" id="edit_password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Short Note</label>
                                    <textarea class="form-control" name="short_note" id="edit_short_note" rows="3"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" form="editForm" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete "<span id="delete_title"></span>"?
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" id="delete_id">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&terminal_type=<?php echo urlencode($_GET['terminal_type'] ?? ''); ?>&server=<?php echo urlencode($_GET['server'] ?? ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // terminal_type change event listener
    document.getElementById('terminal_type').addEventListener('change', function() {
        const terminalType = this.value;
        const serverSelect = document.querySelector('select[name="server"]');
        serverSelect.innerHTML = '<option value="">All Servers</option>'; // Reset options

        if (terminalType) {
            fetch(`savedData.php?ajax=get_servers&terminal_type=${terminalType}`, {
                    credentials: 'include' // Include session cookies
                })
                .then(response => response.json())
                .then(servers => {
                    servers.forEach(server => {
                        const option = document.createElement('option');
                        option.value = server;
                        option.textContent = server;
                        serverSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching servers:', error));
        }
    });

    // Edit button handler
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_terminal_type').value = data.terminalType;
            document.getElementById('edit_server').value = data.server;
            document.getElementById('edit_account_number').value = data.accountNumber;
            document.getElementById('edit_short_note').value = data.shortNote;
            document.getElementById('edit_password').value = ''; // Clear password field
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    // Delete button handler
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('delete_id').value = data.id;
            document.getElementById('delete_title').textContent = data.title;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
</script>

<?php require_once 'partials/footer.php'; ?>