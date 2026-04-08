<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Method Not Allowed');
}

$email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$honeypot = isset($_POST['website']) ? trim((string) $_POST['website']) : '';

// Honeypot: bots often fill hidden fields. Return OK silently.
if ($honeypot !== '') {
  exit('OK');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(422);
  exit('Please enter a valid email address.');
}

$toAddress = 'subscribers@kachingfxofficial.com';

// Save subscription locally.
$csvPath = __DIR__ . '/subscribers.csv';
$row = [
  date('Y-m-d H:i:s'),
  $email,
  $_SERVER['REMOTE_ADDR'] ?? '',
  $_SERVER['HTTP_USER_AGENT'] ?? ''
];

$handle = fopen($csvPath, 'a');
if ($handle !== false) {
  fputcsv($handle, $row);
  fclose($handle);
}

try {
  $mail = new PHPMailer(true);
  // SMTP configuration (replace placeholders with your real credentials).
  $mail->isSMTP();
  $mail->Host = 'smtp.example.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'your_smtp_username';
  $mail->Password = 'your_smtp_password';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = 587;
  $mail->CharSet = 'UTF-8';
  $mail->setFrom('no-reply@kachingfxofficial.com', 'KachingFxOfficial');
  $mail->addAddress($toAddress);
  $mail->addReplyTo($email);
  $mail->Subject = 'New Newsletter Subscription';
  $mail->Body = "A new newsletter subscription was received.\n\nEmail: {$email}\nTime: {$row[0]}";
  $mail->send();

  exit('OK');
} catch (Exception $e) {
  http_response_code(500);
  exit('Unable to send subscription email right now.');
}
?>
