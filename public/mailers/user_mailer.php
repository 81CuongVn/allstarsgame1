<?php
class UserMailer extends Mailer {
    public function send_join($user) {
        $this->deliver(t('emails.join.subject'), $user->email, render_mailer('mailers/user', 'send_join', [ 'user' => $user ]));
    }

	public function send_join_fb($user) {
        $this->deliver(t('emails.join.subject'), $user->email, render_mailer('mailers/user', 'send_join_fb', [ 'user' => $user ]));
    }

    public function password_change($user) {
        $this->deliver(t('emails.password_change.subject'), $user->email, render_mailer('mailers/user', 'password_change', [ 'user' => $user ]));
    }

    public function password_changed($user) {
        $this->deliver(t('emails.password_changed.subject'), $user->email, render_mailer('mailers/user', 'password_changed', [ 'user' => $user ]));
    }

    public function ip_lock($user) {
        $this->deliver(t('emails.ip_lock.subject'), $user->email, render_mailer('mailers/user', 'ip_lock', [ 'user' => $user ]));
    }
}
