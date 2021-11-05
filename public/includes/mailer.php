<?php
use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
	function deliver($subject, $to, $message) {
		$mailConfig	= MAIL_CONFIG;
		if (!$mailConfig['active']) {
			return true;
		}

		$mail = new PHPMailer(true);

		// Server settings
		$mail->Host			= $mailConfig['host'];							// Set the SMTP server to send through
		$mail->Port			= $mailConfig['port'];							// TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
		$mail->Username		= $mailConfig['username'];						// SMTP username
		$mail->Password		= $mailConfig['password'];						// SMTP password

		$mail->isSMTP();													// Send using SMTP
		$mail->SMTPAuth		= true;											// Enable SMTP authentication
		$mail->SMTPSecure	= 'ssl';										// Enable SSL encryption; `PHPMailer::ENCRYPTION_STARTTLS` encouraged
		$mail->SMTPOptions	= [
			'ssl' => [
				'verify_peer'		=> false,
				'verify_peer_name'	=> false,
				'allow_self_signed'	=> true
			]
		];

		$mail->setFrom($mailConfig['from'], $mailConfig['from_name']);
		$mail->addReplyTo($mailConfig['from'], $mailConfig['from_name']);
		$mail->addAddress($to);												// Add a recipient

		// Content
		$mail->isHTML(true);												// Set email format to HTML
		$mail->Subject	= $subject;
		$mail->Body		= $message;
		$mail->AltBody	= 'This is a plain-text message body';

		return $mail->send();
	}

	static function dispatch($method, $params) {
		$class		= get_called_class();
		$callable	= new ReflectionMethod($class, $method);

		$callable->invokeArgs(new $class(), $params);
	}
}
