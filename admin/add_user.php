<?php
require_once '../db_config.php';

$allowGetRequest = false; // Set to false to prevent direct access via GET request

// Redirect if GET requests are not allowed and this is a GET request
if (!$allowGetRequest && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: login.php');
    exit();
}

// Function to add a new admin user
function addAdminUser($username, $password) {
    global $pdo;
    
    try {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare the SQL statement
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        
        // Execute with the username and hashed password
        $stmt->execute([$username, $hashedPassword]);
        
        return true;
    } catch(PDOException $e) {
        // Handle any errors (e.g., duplicate username)
        return false;
    }
}

// Example usage:
// Uncomment and modify these lines to add a new user

// $username = 'admin';
// $password = 'admin123';

// if (addAdminUser($username, $password)) {
//     echo "User added successfully!";
// } else {
//     echo "Error adding user.";
// }