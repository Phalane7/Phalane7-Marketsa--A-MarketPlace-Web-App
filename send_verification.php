<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendVerificationEmail($email, $name, $token) {
    $verifyLink = 'http://localhost/MarketSA/verify_email.php?token=' . $token;

    $mail = new PHPMailer(true);
    

    try {
      
        $mail->isSMTP();
$mail->Host       = '';
$mail->SMTPAuth   = true;
$mail->Username   = '';
$mail->Password   = '';
$mail->SMTPSecure = 'tls';
$mail->Port       = ;


        $mail->setFrom('no-reply@marketsa.co.za', 'MarketSA');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Verify your MarketSA account';
        $mail->Body    = '
        <div style="font-family:Poppins,Arial,sans-serif;max-width:520px;margin:40px auto;
                    background:#fff;border-radius:16px;overflow:hidden;
                    box-shadow:0 4px 24px rgba(0,0,0,.08);">
            <div style="background:#1a7a4a;padding:32px;text-align:center;">
                <h1 style="color:#fff;margin:0;font-size:26px;">MarketSA</h1>
            </div>
            <div style="padding:32px;">
                <p style="color:#555;font-size:15px;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
                <p style="color:#555;font-size:15px;">Welcome to MarketSA 🇿🇦 Click below to verify your email:</p>
                <div style="text-align:center;margin:28px 0;">
                    <a href="' . $verifyLink . '"
                       style="background:#1a7a4a;color:#fff;text-decoration:none;
                              padding:13px 32px;border-radius:50px;font-size:15px;font-weight:700;">
                        Verify My Email
                    </a>
                </div>
                <p style="color:#aaa;font-size:13px;">
                    This link expires in 24 hours. If you did not register, ignore this email.
                </p>
            </div>
            <div style="text-align:center;padding:20px;color:#aaa;font-size:12px;
                        border-top:1px solid #f0f0f0;">
                © MarketSA. Made in South Africa 🇿🇦
            </div>
        </div>';

        $mail->send();
        return true;
   } catch (Exception $e) {
    error_log('Mailer error: ' . $mail->ErrorInfo);
    return false;
}
}