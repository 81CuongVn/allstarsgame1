<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
	function deliver($subject, $to, $message) {
		global $mailConfig;

		$mail = new PHPMailer(true);
		try {
			// Server settings
			$mail->SMTPDebug	= SMTP::DEBUG_SERVER;						// Enable verbose debug output
			$mail->isSMTP();												// Send using SMTP
//			$mail->Host			= $mailConfig['host'];						// Set the SMTP server to send through
			$mail->Host			= gethostbyname($mailConfig['host']);
			$mail->SMTPAuth		= true;										// Enable SMTP authentication
			$mail->Username		= $mailConfig['username'];					// SMTP username
			$mail->Password		= $mailConfig['password'];					// SMTP password
			$mail->SMTPSecure	= PHPMailer::ENCRYPTION_STARTTLS;			// Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			$mail->Port			= $mailConfig['port'];						// TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

			$mail->setFrom($mailConfig['from'], $mailConfig['from_name']);
			$mail->addReplyTo($mailConfig['from'], $mailConfig['from_name']);
			$mail->addAddress($to);											// Add a recipient

			// Content
			$mail->isHTML(true);											// Set email format to HTML
			$mail->Subject	= $subject;
			$mail->Body		= $message;
			$mail->AltBody	= 'This is a plain-text message body';

			return $mail->send();
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			return FALSE;
		}
	}

	static function dispatch($method, $params) {
		$class		= get_called_class();
		$callable	= new ReflectionMethod($class, $method);

		$callable->invokeArgs(new $class(), $params);
	}
}