<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';      // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'graciajopson@gmail.com';   // Your Gmail address
        $mail->Password   = 'orpd usko ocbt dniy';      // Gmail App Password (NOT your normal password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($_POST['email'], $_POST['name']); // From contact form
        $mail->addAddress('receiver@example.com', 'Website Admin'); // Where the email goes

        // Content
        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body    = nl2br("Name: {$_POST['name']}<br>Email: {$_POST['email']}<br><br>Message:<br>{$_POST['message']}");
        $mail->AltBody = "Name: {$_POST['name']}\nEmail: {$_POST['email']}\n\nMessage:\n{$_POST['message']}";

        $mail->send();
        echo '<p style="color:green;">✅ Message sent successfully!</p>';
    } catch (Exception $e) {
        echo '<p style="color:red;">❌ Message could not be sent. Reason: ' . htmlspecialchars($mail->ErrorInfo) . '</p>';

    }
}
?>
