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
$admin_id = $_SESSION['admin_id'];

// Get current user info
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$current_user = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_username)) {
        $error = 'Username cannot be empty';
    } elseif ($new_username !== $current_user['username']) {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? AND id != ?");
        $stmt->execute([$new_username, $admin_id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username already exists';
        }
    }

    if (empty($error)) {
        if (!empty($current_password)) {
            // Verify current password
            if (!password_verify($current_password, $current_user['password'])) {
                $error = 'Current password is incorrect';
            } elseif (empty($new_password) || empty($confirm_password)) {
                $error = 'Please fill in both new password fields';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            }
        }

        if (empty($error)) {
            try {
                if (!empty($new_password)) {
                    // Update username and password
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_username, password_hash($new_password, PASSWORD_DEFAULT), $admin_id]);
                } else {
                    // Update username only
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
                    $stmt->execute([$new_username, $admin_id]);
                }

                // Log the change
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'user_update', ?)");
                $stmt->execute([$admin_id, "Updated profile settings"]);

                $_SESSION['admin_username'] = $new_username;
                $message = 'Profile updated successfully';

                // Refresh user info
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
                $stmt->execute([$admin_id]);
                $current_user = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Handle subadmin creation (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_subadmin']) && $current_user['user_type'] === 'admin') {
    $subadmin_username = $_POST['subadmin_username'] ?? '';
    $subadmin_password = $_POST['subadmin_password'] ?? '';

    if (empty($subadmin_username) || empty($subadmin_password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $stmt->execute([$subadmin_username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username already exists';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, user_type, password) VALUES (?, 'subadmin', ?)");
                $stmt->execute([$subadmin_username, password_hash($subadmin_password, PASSWORD_DEFAULT)]);

                // Log the action
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'user_update', ?)");
                $stmt->execute([$admin_id, "Created new subadmin user: " . $subadmin_username]);

                $message = 'Subadmin created successfully';
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get list of subadmins if current user is admin
$subadmins = [];
if ($current_user['user_type'] === 'admin') {
    $stmt = $pdo->prepare("SELECT id, username, created_at FROM admin_users WHERE user_type = 'subadmin'");
    $stmt->execute();
    $subadmins = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management - Traders Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                    <?php if ($user['user_type'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logs.php">Logs</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Update Profile</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <small class="text-muted">Required only if changing password</small>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if ($current_user['user_type'] === 'admin'): ?>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4 class="mb-0">Create Subadmin</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="subadmin_username" class="form-label">Subadmin Username</label>
                                    <input type="text" class="form-control" id="subadmin_username" name="subadmin_username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="subadmin_password" class="form-label">Subadmin Password</label>
                                    <input type="password" class="form-control" id="subadmin_password" name="subadmin_password" required>
                                </div>
                                <button type="submit" name="create_subadmin" class="btn btn-success">Create Subadmin</button>
                            </form>

                            <?php if (!empty($subadmins)): ?>
                                <div class="mt-4">
                                    <h5>Existing Subadmins</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Username</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($subadmins as $subadmin): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($subadmin['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($subadmin['created_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>