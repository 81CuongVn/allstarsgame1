<?php
require '_config.php';

$user_stats = Recordset::query('SELECT * FROM user_stats');
foreach ($user_stats->result_array() as $user_stat) {
    $data_futura  = strtotime($user_stat['credits'] . "+30 days");
    $data_atual   = strtotime(date('Y-m-d H:i:s'));
    if ($data_atual >= $data_futura) {
        //Zerar o campo de credits
        Recordset::update('user_stats', [
            'credits'	=> NULL
        ], [
            'user_id'		=> $user_stat['user_id']

        ]);
    }
}