<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';

$receiving_email_address = 'subscribers@kachingfxofficial.com';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Invalid request method.';
    exit;
}

// Honeypot protection: bots often fill hidden fields.
$spam_check = isset($_POST['website']) ? trim((string) $_POST['website']) : '';
if ($spam_check !== '') {
    echo 'OK';
    exit;
}

$email = isset($_POST['email']) ? filter_var(trim((string) $_POST['email']), FILTER_SANITIZE_EMAIL) : '';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please provide a valid email address.';
    exit;
}

$timestamp = date('Y-m-d H:i:s');

// Optional lightweight local record of subscriptions.
$csv_path = __DIR__ . '/subscribers.csv';
$row = [
    $timestamp,
    $email,
    $_SERVER['REMOTE_ADDR'] ?? '',
    $_SERVER['HTTP_USER_AGENT'] ?? ''
];
$handle = @fopen($csv_path, 'a');
if ($handle !== false) {
    fputcsv($handle, $row);
    fclose($handle);
}

$email_body = '<h2>New newsletter subscription</h2>';
$email_body .= '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
$email_body .= '<p><strong>Time:</strong> ' . htmlspecialchars($timestamp) . '</p>';

$alt_body = "New newsletter subscription\n\n";
$alt_body .= "Email: $email\n";
$alt_body .= "Time: $timestamp\n";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
     $mail->Host = 's9789.usc1.stableserver.net';
    $mail->SMTPAuth = true;
    $mail->Username = 'subscribers@kachingfxofficial.com';
    $mail->Password = 'We@19852023';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('no-reply@kachingfxofficial.com', 'KachingFxOfficial');
    $mail->addAddress($receiving_email_address, 'KachingFxOfficial');
    $mail->addReplyTo($email);
    $mail->Subject = 'New Newsletter Subscription';
    $mail->isHTML(true);
    $mail->Body = $email_body;
    $mail->AltBody = $alt_body;

    if ($mail->send()) {
        echo 'OK';
    } else {
        error_log('Newsletter send failed: ' . $mail->ErrorInfo);
        echo 'OK';
    }
} catch (Exception $e) {
    error_log('Newsletter exception: ' . $e->getMessage());
    echo 'OK';
}
?>
