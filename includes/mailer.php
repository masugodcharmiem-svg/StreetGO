<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// =========================================================
//  FIX: MANUALLY LOAD PHPMAILER (Since you don't use Composer)
// =========================================================

// Check if files exist to prevent "No such file" error
if (!file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    die("Error: PHPMailer files not found. Please check Step 1 instructions.");
}

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// =========================================================

function sendVerificationEmail($userEmail, $verificationLink) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        
        // ---------------------------------------------------
        // ENTER YOUR GMAIL DETAILS HERE
        // ---------------------------------------------------
        $mail->Username   = 'your_real_email@gmail.com';     // <--- REPLACE WITH YOUR GMAIL
        $mail->Password   = 'xxxx xxxx xxxx xxxx';           // <--- REPLACE WITH 16-DIGIT APP PASSWORD
        // ---------------------------------------------------

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            
        $mail->Port       = 587;                                    

        // Recipients
        $mail->setFrom($mail->Username, 'StreetGo Admin');
        $mail->addAddress($userEmail);     

        // Content
        $mail->isHTML(true);                                  
        $mail->Subject = 'Verify Your StreetGo Account';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #e55a28;'>Welcome to StreetGo!</h2>
                <p>Thank you for registering. To start ordering delicious street food, please verify your email address.</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationLink}' style='background-color: #e55a28; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify Email Account</a>
                </p>
                <p>Or copy this link:<br><small>{$verificationLink}</small></p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the exact error to a file so you can debug it
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>