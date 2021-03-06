<?php
class CharacterMailer extends Mailer {
    public function __construct()
    {
        global $mailConfig;

        $this->host		    = $mailConfig['host'];
        $this->port		    = $mailConfig['port'];
        $this->username	    = $mailConfig['username'];
        $this->password	    = $mailConfig['password'];
        $this->from		    = $mailConfig['from'];
        $this->from_name	= $mailConfig['from_name'];
    }

    public function character_deleted($user, $character) {
        $this->deliver(t('emails.character_deleted.subject'), $user->email, render_mailer('character_mailer', 'character_deleted', ['user' => $user, 'character' => $character]));
    }
}