<?php
class PromotionMailer extends Mailer {
    public function send_promotion($user) {
		$this->deliver(t('emails.promotion.subject'), $user->email, render_mailer('mailers/promotion', 'template', [
			'user'	=> $user
		]));
    }
}
