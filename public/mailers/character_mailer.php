<?php
class CharacterMailer extends Mailer {
    public function character_deleted($user, $character) {
        $this->deliver(t('emails.character_deleted.subject'), $user->email, render_mailer('character_mailer', 'character_deleted', ['user' => $user, 'character' => $character]));
    }
}