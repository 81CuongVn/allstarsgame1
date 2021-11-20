<?php
class BetaMailer extends Mailer {
    public function send_beta($user) {
		$this->deliver(t('emails.beta.subject'), $user->email, render_mailer('mailers/beta', 'launched', [
			'user'	=> $user
		]));
    }
}
