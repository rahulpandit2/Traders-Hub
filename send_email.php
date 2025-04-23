<?php
// Prevent direct access via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Access Denied']);
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$sender_name = 'Traders Hub';
$sender_email = 'test@gmail.com';
$sender_password = 'test';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Enable debugging
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->Debugoutput = function($str, $level) {
        error_log("PHPMailer debug: $str");
    };

    //Server settings
    $mail->isSMTP();
    $mail->SMTPAuth   = true;
    $mail->Host       = 'smtp.gmail.com';
    $mail->Username   = $sender_email;
    $mail->Password   = $sender_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Additional debug settings
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    //Recipients
    $mail->setFrom($sender_email, $sender_name);
    $mail->addAddress('rp1618938+tradershubauto@gmail.com', 'Rahul Pandit');     // Add first recipient
    $mail->addAddress('rahulpandit131415+tradershubauto@gmail.com', 'Rahul Pandit');  // Add second recipient

    //Content
    $mail->isHTML(true);                                        //Set email format to HTML
    
    // Get the form data (assuming these are passed via POST)
    $name = $_POST['name'] ?? 'Not provided';
    $email = $_POST['email'] ?? 'Not provided';
    $message = $_POST['message'] ?? 'Not provided';
    
    $mail->Subject = 'New Contact Form Submission - Traders Hub';
    
    // Create HTML body with proper formatting
    $mail->Body = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Message:</strong></p>
        <p>{$message}</p>
        <hr>
        <p><small>This email was sent from Traders Hub contact form.</small></p>
    ";

    // Plain text version for non-HTML mail clients
    $mail->AltBody = "
        New Contact Form Submission
        
        Name: {$name}
        Email: {$email}
        Message: {$message}
        
        This email was sent from Traders Hub contact form.
    ";

    $mail->send();
    error_log("Email sent successfully");
    echo json_encode(['success' => true, 'message' => 'Message has been sent successfully']);
} catch (Exception $e) {
    error_log("Email sending failed: " . $mail->ErrorInfo);
    echo json_encode([
        'success' => false, 
        'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}",
        'debug_info' => $mail->ErrorInfo
    ]);
}