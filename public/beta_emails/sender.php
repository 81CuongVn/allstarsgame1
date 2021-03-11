<?php
set_time_limit(0);

date_default_timezone_set('America/Sao_Paulo');
define('RECORDSET_CACHE_OFF_FORCE', TRUE);

require '../includes/renderer.php';
require '../includes/mailer.php';
require '../includes/shared_store.php';

require '../config.php';
require '../includes/recordset.php';
require '../includes/db.php';

require '../includes/url_helper.php';

class BetaMailer extends Mailer {
    public function send_beta($user) {
        $this->deliver('O Beta começou!', $user['email'], render_file('email_template.php', ['user' => $user]));
    }
}

$users = Recordset::query('SELECT id, email, name FROM users WHERE active=1 AND beta_allowed=0 ORDER BY RAND() LIMIT 50');
foreach ($users->result_array() as $user) {
    echo "- BEGIN ";
    flush();

    BetaMailer::dispatch('send_beta', [$user]);
    Recordset::update('users', [
        'beta_allowed'	=> 1
    ], [
        'id'			=> $user['id']
    ]);

    echo " {$user['email']}\n";
    flush();

}