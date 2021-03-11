<?php
class UserMailer extends Mailer {
    public function send_join_fb($user) {
        $this->deliver(t('emails.join.subject'), $user->email, render_mailer('user_mailer', 'send_join_fb', array('user' => $user)));
    }

    public function send_join($user) {
        $this->deliver(t('emails.join.subject'), $user->email, render_mailer('user_mailer', 'send_join', array('user' => $user)));
    }

    public function send_join_beta($user) {
        $this->deliver(t('emails.join.subject_beta'), $user->email, render_mailer('user_mailer', 'send_join_beta', array('user' => $user)));
    }

    public function password_change($user) {
        $this->deliver(t('emails.password_change.subject'), $user->email, render_mailer('user_mailer', 'password_change', array('user' => $user)));
    }

    public function password_changed($user) {
        $this->deliver(t('emails.password_changed.subject'), $user->email, render_mailer('user_mailer', 'password_changed', array('user' => $user)));
    }

    public function ip_lock($user) {
        $this->deliver(t('emails.ip_lock.subject'), $user->email, render_mailer('user_mailer', 'ip_lock', array('user' => $user)));
    }
}