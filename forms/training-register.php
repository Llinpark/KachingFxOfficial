<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';

$receiving_email_address = 'info@kachingfxofficial.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid request method.';
    exit;
}

$spam_check = isset($_POST['website']) ? trim($_POST['website']) : '';
if (!empty($spam_check)) {
    echo 'OK';
    exit;
}

$form_type = isset($_POST['form_type']) ? trim($_POST['form_type']) : '';
if ($form_type !== 'training_registration') {
    echo 'OK';
    exit;
}

$name = isset($_POST['name']) ? strip_tags(trim($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? strip_tags(trim($_POST['phone'])) : '';
$course = isset($_POST['course']) ? strip_tags(trim($_POST['course'])) : '';
$payment_method = isset($_POST['payment_method']) ? strip_tags(trim($_POST['payment_method'])) : '';
$message = isset($_POST['message']) ? strip_tags(trim($_POST['message'])) : '';

if (empty($name) || empty($email) || empty($phone) || empty($course) || empty($payment_method)) {
    echo 'Please complete all required fields.';
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please provide a valid email address.';
    exit;
}

$subject = 'Training Registration Request: ' . $name;
$email_body = "<h2>New training registration request</h2>";
$email_body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
$email_body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
$email_body .= "<p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>";
$email_body .= "<p><strong>Selected Course:</strong> " . htmlspecialchars($course) . "</p>";
$email_body .= "<p><strong>Payment Method:</strong> " . htmlspecialchars($payment_method) . "</p>";
$email_body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";

$alt_body = "New training registration request:\n\n";
$alt_body .= "Name: $name\n";
$alt_body .= "Email: $email\n";
$alt_body .= "Phone: $phone\n";
$alt_body .= "Selected Course: $course\n";
$alt_body .= "Payment Method: $payment_method\n";
$alt_body .= "Message:\n$message\n";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.example.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-smtp-user@example.com';
    $mail->Password = 'your-smtp-password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('no-reply@kachingfxofficial.com', 'KachingFxOfficial');
    $mail->addAddress($receiving_email_address, 'KachingFxOfficial');
    $mail->addReplyTo($email, $name);
    $mail->Subject = $subject;
    $mail->isHTML(true);
    $mail->Body = $email_body;
    $mail->AltBody = $alt_body;

    if ($mail->send()) {
        echo 'OK';
    } else {
        echo 'Unable to send registration request at this time. Please try again later.';
    }
} catch (Exception $e) {
    echo 'Unable to send registration request at this time. Please try again later.';
}
?>