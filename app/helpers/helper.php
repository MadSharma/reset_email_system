<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendEmail')) {
    function sendEmail($mailConfig)
    {
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->SMTPDebug = 2; // Enable debugging
            $mail->isSMTP();
            $mail->Host = env('EMAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('EMAIL_USERNAME');
            $mail->Password = env('EMAIL_PASSWORD');
            $mail->SMTPSecure = env('EMAIL_ENCRYPTION');
            $mail->Port = env('EMAIL_PORT');

            // Sender information
            $mail->setFrom($mailConfig['mail_form_email'], $mailConfig['mail_form_name']);

            // Recipient information
            $mail->addAddress($mailConfig['mail_recipient_email'], $mailConfig['mail_recipient_name']);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $mailConfig['mail_subject'];
            $mail->Body = $mailConfig['mail_body'];

            // Send the email
            if ($mail->send()) {
                return true;
            } else {
                error_log('PHPMailer Error: ' . $mail->ErrorInfo); // Log the error message
                return false;
            }
        } catch (Exception $e) {
            error_log('PHPMailer Exception: ' . $e->getMessage()); // Log the exception message
            return false;
        }
    }
}

// Other helper functions can go here
