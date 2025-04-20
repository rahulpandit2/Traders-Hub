<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

// Get user type
$stmt = $pdo->prepare("SELECT user_type FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $startDate = $_POST['start_date'] ?? '';
    $customFileName = $_POST['file_name'] ?? '';
    
    if (empty($startDate)) {
        $response['message'] = 'Start date is required';
    } elseif (!isset($_FILES['file'])) {
        $response['message'] = 'Please select a file';
    } else {
        $file = $_FILES['file'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedTypes = ['pdf', 'xlsx', 'xls'];
        if (!in_array($fileType, $allowedTypes)) {
            $response['message'] = 'Only PDF and Excel files are allowed';
        } else {
            // Create upload directory if it doesn't exist
            $uploadDir = '../uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $fileName = time() . '_' . ($customFileName ?: $file['name']);
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO files (file_name, original_name, start_date, file_type, file_size, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $fileName,
                        $file['name'],
                        $startDate,
                        $fileType,
                        $file['size'],
                        'uploads/' . $fileName
                    ]);

                    // Log the upload
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'file_upload', ?)");
                    $stmt->execute([$_SESSION['admin_id'], "Uploaded file: " . $fileName]);
                    
                    $response['success'] = true;
                    $response['message'] = 'File uploaded successfully';
                } catch (PDOException $e) {
                    unlink($filePath); // Remove uploaded file if database insert fails
                    $response['message'] = 'Database error: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Error uploading file';
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);