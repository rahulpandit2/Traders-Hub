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

// AJAX endpoint for username availability check
if (isset($_GET['check_username'])) {
    $username = $_GET['check_username'];
    $exclude_id = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;
    
    // Validate username format
    if (!preg_match('/^[a-z0-9._]+$/', $username)) {
        header('Content-Type: application/json');
        echo json_encode(['available' => false, 'invalid_format' => true]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $exclude_id]);
    $count = $stmt->fetchColumn();
    
    header('Content-Type: application/json');
    echo json_encode(['available' => ($count == 0), 'invalid_format' => false]);
    exit;
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_username)) {
        $error = 'Username cannot be empty';
    } elseif (!preg_match('/^[a-z0-9._]+$/', $new_username)) {
        $error = 'Username can only contain lowercase letters, numbers, dots, and underscores';
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
            } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $new_password)) {
                $error = 'Password does not meet complexity requirements';
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

// Handle subadmin status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status']) && $current_user['user_type'] === 'admin') {
    $subadmin_id = $_POST['subadmin_id'] ?? '';

    if (!empty($subadmin_id)) {
        try {
            // Get current status
            $stmt = $pdo->prepare("SELECT status FROM admin_users WHERE id = ? AND user_type = 'subadmin'");
            $stmt->execute([$subadmin_id]);
            $current_status = $stmt->fetchColumn();

            if ($current_status === false) {
                $error = 'Subadmin not found';
            } else {
                // Toggle status
                $new_status = ($current_status === 'active') ? 'disabled' : 'active';

                $stmt = $pdo->prepare("UPDATE admin_users SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $subadmin_id]);

                // Log the action
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'user_update', ?)");
                $stmt->execute([$admin_id, "Changed subadmin status to: " . $new_status]);

                $message = 'Subadmin status updated successfully';

                // Refresh the subadmins list
                $stmt = $pdo->prepare("SELECT id, username, created_at, status FROM admin_users WHERE user_type = 'subadmin'");
                $stmt->execute();
                $subadmins = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle subadmin creation (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_subadmin']) && $current_user['user_type'] === 'admin') {
    $subadmin_username = $_POST['subadmin_username'] ?? '';
    $subadmin_password = $_POST['subadmin_password'] ?? '';

    if (empty($subadmin_username) || empty($subadmin_password)) {
        $error = 'Please fill in all fields';
    } elseif (!preg_match('/^[a-z0-9._]+$/', $subadmin_username)) {
        $error = 'Username can only contain lowercase letters, numbers, dots, and underscores';
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $stmt->execute([$subadmin_username]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username already exists';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,}$/', $subadmin_password)) {
            $error = 'Subadmin password does not meet complexity requirements';
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
    $stmt = $pdo->prepare("SELECT id, username, created_at, status FROM admin_users WHERE user_type = 'subadmin'");
    $stmt->execute();
    $subadmins = $stmt->fetchAll();
}
?>

<?php require_once 'partials/header.php';?>

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
                                <div id="username-feedback" class="form-text"></div>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <small class="text-muted">Required only if changing password</small>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div id="password-requirements" class="mt-2">
                                    <p class="mb-1 small">Password must contain:</p>
                                    <ul class="list-unstyled small ps-3">
                                        <li id="length-check"><span class="text-danger">✖</span> At least 8 characters</li>
                                        <li id="lowercase-check"><span class="text-danger">✖</span> At least 1 lowercase letter</li>
                                        <li id="uppercase-check"><span class="text-danger">✖</span> At least 1 uppercase letter</li>
                                        <li id="number-check"><span class="text-danger">✖</span> At least 1 number</li>
                                        <li id="special-check"><span class="text-danger">✖</span> At least 1 special character</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <div id="password-match" class="mt-1 small"></div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary" id="update-profile-btn">Update Profile</button>
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
                                    <div id="subadmin-username-feedback" class="form-text"></div>
                                    <small class="text-muted">Username can only contain lowercase letters, numbers, dots, and underscores</small>
                                </div>
                                <div class="mb-3">
                                    <label for="subadmin_password" class="form-label">Subadmin Password</label>
                                    <input type="password" class="form-control" id="subadmin_password" name="subadmin_password" required>
                                    <div id="subadmin-password-requirements" class="mt-2">
                                        <p class="mb-1 small">Password must contain:</p>
                                        <ul class="list-unstyled small ps-3">
                                            <li id="sa-length-check"><span class="text-danger">✖</span> At least 8 characters</li>
                                            <li id="sa-lowercase-check"><span class="text-danger">✖</span> At least 1 lowercase letter</li>
                                            <li id="sa-uppercase-check"><span class="text-danger">✖</span> At least 1 uppercase letter</li>
                                            <li id="sa-number-check"><span class="text-danger">✖</span> At least 1 number</li>
                                            <li id="sa-special-check"><span class="text-danger">✖</span> At least 1 special character</li>
                                        </ul>
                                    </div>
                                </div>
                                <button type="submit" name="create_subadmin" class="btn btn-success" id="create-subadmin-btn">Create Subadmin</button>
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
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($subadmins as $subadmin): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($subadmin['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($subadmin['created_at']); ?></td>
                                                        <td>
                                                            <form method="POST" action="" class="d-inline">
                                                                <input type="hidden" name="subadmin_id" value="<?php echo $subadmin['id']; ?>">
                                                                <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $subadmin['status'] === 'active' ? 'btn-danger' : 'btn-success'; ?>">
                                                                    <?php echo $subadmin['status'] === 'active' ? 'Disable' : 'Enable'; ?>
                                                                </button>
                                                            </form>
                                                        </td>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // User profile password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const updateProfileBtn = document.getElementById('update-profile-btn');
        const passwordMatch = document.getElementById('password-match');
        
        // Username validation function
        function validateUsername(username) {
            const regex = /^[a-z0-9._]+$/;
            return regex.test(username);
        }
        
        // Username availability check variables
        const usernameInput = document.getElementById('username');
        const usernameFeedback = document.getElementById('username-feedback');
        const originalUsername = '<?php echo htmlspecialchars($current_user['username']); ?>';
        const adminId = <?php echo $admin_id; ?>;
        
        // Username availability check
        let usernameTimeout;
        usernameInput.addEventListener('input', function() {
            const username = this.value.trim();
            
            // Clear any previous timeout
            clearTimeout(usernameTimeout);
            
            // Don't check if username is empty or unchanged
            if (username === '') {
                usernameFeedback.innerHTML = '';
                return;
            }
            
            if (username === originalUsername) {
                usernameFeedback.innerHTML = '';
                return;
            }
            
            // Validate username format first
            if (!validateUsername(username)) {
                usernameFeedback.innerHTML = '<span class="text-danger">Username can only contain lowercase letters, numbers, dots, and underscores</span>';
                return;
            }
            
            // Show checking message
            usernameFeedback.innerHTML = '<span class="text-muted">Checking availability...</span>';
            
            // Set a timeout to reduce number of requests while typing
            usernameTimeout = setTimeout(function() {
                fetch(`?check_username=${encodeURIComponent(username)}&exclude_id=${adminId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.invalid_format) {
                            usernameFeedback.innerHTML = '<span class="text-danger">Username can only contain lowercase letters, numbers, dots, and underscores</span>';
                        } else if (data.available) {
                            usernameFeedback.innerHTML = '<span class="text-success">Username is available</span>';
                        } else {
                            usernameFeedback.innerHTML = '<span class="text-danger">Username is already taken</span>';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking username:', error);
                        usernameFeedback.innerHTML = '<span class="text-danger">Error checking availability</span>';
                    });
            }, 500);
        });
        
        // Password requirement elements
        const lengthCheck = document.getElementById('length-check');
        const lowercaseCheck = document.getElementById('lowercase-check');
        const uppercaseCheck = document.getElementById('uppercase-check');
        const numberCheck = document.getElementById('number-check');
        const specialCheck = document.getElementById('special-check');
        
        // Hide requirements by default if there's no input
        if (newPasswordInput.value.length === 0) {
            document.getElementById('password-requirements').style.display = 'none';
        }
        
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            
            // Show requirements when user starts typing
            document.getElementById('password-requirements').style.display = 'block';
            
            // Check length
            if(password.length >= 8) {
                lengthCheck.innerHTML = '<span class="text-success">✓</span> At least 8 characters';
            } else {
                lengthCheck.innerHTML = '<span class="text-danger">✖</span> At least 8 characters';
            }
            
            // Check lowercase
            if(/[a-z]/.test(password)) {
                lowercaseCheck.innerHTML = '<span class="text-success">✓</span> At least 1 lowercase letter';
            } else {
                lowercaseCheck.innerHTML = '<span class="text-danger">✖</span> At least 1 lowercase letter';
            }
            
            // Check uppercase
            if(/[A-Z]/.test(password)) {
                uppercaseCheck.innerHTML = '<span class="text-success">✓</span> At least 1 uppercase letter';
            } else {
                uppercaseCheck.innerHTML = '<span class="text-danger">✖</span> At least 1 uppercase letter';
            }
            
            // Check number
            if(/\d/.test(password)) {
                numberCheck.innerHTML = '<span class="text-success">✓</span> At least 1 number';
            } else {
                numberCheck.innerHTML = '<span class="text-danger">✖</span> At least 1 number';
            }
            
            // Check special character
            if(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                specialCheck.innerHTML = '<span class="text-success">✓</span> At least 1 special character';
            } else {
                specialCheck.innerHTML = '<span class="text-danger">✖</span> At least 1 special character';
            }
            
            // Check if passwords match if confirm is not empty
            if(confirmPasswordInput.value) {
                checkPasswordsMatch();
            }
        });
        
        confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
        
        function checkPasswordsMatch() {
            if(newPasswordInput.value === confirmPasswordInput.value) {
                passwordMatch.innerHTML = '<span class="text-success">Passwords match</span>';
            } else {
                passwordMatch.innerHTML = '<span class="text-danger">Passwords do not match</span>';
            }
        }
        
        // Subadmin section validation (if admin user)
        const subadminPasswordInput = document.getElementById('subadmin_password');
        if (subadminPasswordInput) {
            const subadminUsernameInput = document.getElementById('subadmin_username');
            const subadminUsernameFeedback = document.getElementById('subadmin-username-feedback');
            const createSubadminBtn = document.getElementById('create-subadmin-btn');
            
            // Hide requirements by default
            document.getElementById('subadmin-password-requirements').style.display = 'none';
            
            // Subadmin username availability check
            let subadminUsernameTimeout;
            subadminUsernameInput.addEventListener('input', function() {
                const username = this.value.trim();
                
                // Clear any previous timeout
                clearTimeout(subadminUsernameTimeout);
                
                // Don't check if username is empty
                if (username === '') {
                    subadminUsernameFeedback.innerHTML = '';
                    return;
                }
                
                // Validate username format first
                if (!validateUsername(username)) {
                    subadminUsernameFeedback.innerHTML = '<span class="text-danger">Username can only contain lowercase letters, numbers, dots, and underscores</span>';
                    return;
                }
                
                // Show checking message
                subadminUsernameFeedback.innerHTML = '<span class="text-muted">Checking availability...</span>';
                
                // Set a timeout to reduce number of requests while typing
                subadminUsernameTimeout = setTimeout(function() {
                    fetch(`?check_username=${encodeURIComponent(username)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.invalid_format) {
                                subadminUsernameFeedback.innerHTML = '<span class="text-danger">Username can only contain lowercase letters, numbers, dots, and underscores</span>';
                            } else if (data.available) {
                                subadminUsernameFeedback.innerHTML = '<span class="text-success">Username is available</span>';
                            } else {
                                subadminUsernameFeedback.innerHTML = '<span class="text-danger">Username is already taken</span>';
                            }
                        })
                        .catch(error => {
                            console.error('Error checking username:', error);
                            subadminUsernameFeedback.innerHTML = '<span class="text-danger">Error checking availability</span>';
                        });
                }, 500);
            });
            
            subadminPasswordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Show requirements when user starts typing
                document.getElementById('subadmin-password-requirements').style.display = 'block';
                
                // Check length
                if(password.length >= 8) {
                    document.getElementById('sa-length-check').innerHTML = '<span class="text-success">✓</span> At least 8 characters';
                } else {
                    document.getElementById('sa-length-check').innerHTML = '<span class="text-danger">✖</span> At least 8 characters';
                }
                
                // Check lowercase
                if(/[a-z]/.test(password)) {
                    document.getElementById('sa-lowercase-check').innerHTML = '<span class="text-success">✓</span> At least 1 lowercase letter';
                } else {
                    document.getElementById('sa-lowercase-check').innerHTML = '<span class="text-danger">✖</span> At least 1 lowercase letter';
                }
                
                // Check uppercase
                if(/[A-Z]/.test(password)) {
                    document.getElementById('sa-uppercase-check').innerHTML = '<span class="text-success">✓</span> At least 1 uppercase letter';
                } else {
                    document.getElementById('sa-uppercase-check').innerHTML = '<span class="text-danger">✖</span> At least 1 uppercase letter';
                }
                
                // Check number
                if(/\d/.test(password)) {
                    document.getElementById('sa-number-check').innerHTML = '<span class="text-success">✓</span> At least 1 number';
                } else {
                    document.getElementById('sa-number-check').innerHTML = '<span class="text-danger">✖</span> At least 1 number';
                }
                
                // Check special character
                if(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
                    document.getElementById('sa-special-check').innerHTML = '<span class="text-success">✓</span> At least 1 special character';
                } else {
                    document.getElementById('sa-special-check').innerHTML = '<span class="text-danger">✖</span> At least 1 special character';
                }
            });
        }
    });
    </script>
<?php require_once 'partials/footer.php'; ?>