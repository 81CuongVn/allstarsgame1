<?php
require '_config.php';

$player_fidelities = Recordset::query('SELECT * FROM player_fidelities');
foreach ($player_fidelities->result_array() as $player_fidelity) {
    if (date('d') != 1) {
        if ($player_fidelity['reward'] == 1 && $player_fidelity['day'] != 30) {
            Recordset::update('player_fidelities', [
                'day'		 => $player_fidelity['day'] + 1,
                'reward'	 => 0,
                'reward_at'	 => NULL,
            ], [
                'player_id'	=> $player_fidelity['player_id']
            ]);

        }
    } else {
        Recordset::update('player_fidelities', [
            'day'		 => 1,
            'reward'	 => 0,
            'reward_at'	 => NULL,
            'created_at' => date('Y-m-d H:i:s')
        ], [
            'player_id'	=> $player_fidelity['player_id']
        ]);
    }
}