<?php
require('_config.php');

if ((date('d') == 6) || (date('d') == 13) || (date('d') == 20) || (date('d') == 27)) {
    // Pega a missão de combate ativa no momente - diaria
    $combat_quests_semanal = Recordset::query("SELECT * FROM player_combat_quests WHERE period='semanal' AND finished = 0")->result_array();
    if ($combat_quests_semanal) {
        // Trás as informações sobre a missão ativa
        $combat_quests 		 = Recordset::query("SELECT * FROM combat_quests WHERE id=".$combat_quests_semanal[0]['combat_quest_id'])->result_array();

        // Trás informações do jogador melhor colocado em relação a missão ativa
        $player_rank 		 = Recordset::query("SELECT * FROM player_battle_stats ORDER BY ".$combat_quests[0]['type']." DESC LIMIT 1")->result_array();

        // Faz a bonificação do jogador!
        Recordset::query("UPDATE players SET currency = currency + ".$combat_quests[0]['currency'].", exp = exp + ".$combat_quests[0]['exp']." WHERE id=".$player_rank[0]['player_id']);

        // Marca a missão como completa
        Recordset::query("UPDATE player_combat_quests SET player_id = ". $player_rank[0]['player_id'] . ", finished = 1 WHERE id=". $combat_quests_semanal[0]['id']);

        // Marca a missão na plauqer quest status
        Recordset::query("UPDATE player_quest_counters SET combat_total = combat_total +1 WHERE player_id=". $player_rank[0]['player_id']);
    }
} elseif ((date('d') == 1)) {
    // Pega a missão de combate ativa no momente - diaria
    $combat_quests_mensal = Recordset::query("SELECT * FROM player_combat_quests WHERE period='mensal' AND finished = 0")->result_array();
    if ($combat_quests_mensal) {
        // Trás as informações sobre a missão ativa
        $combat_quests 		 = Recordset::query("SELECT * FROM combat_quests WHERE id=".$combat_quests_mensal[0]['combat_quest_id'])->result_array();

        // Trás informações do jogador melhor colocado em relação a missão ativa
        $player_rank 		 = Recordset::query("SELECT * FROM player_battle_stats ORDER BY ".$combat_quests[0]['type']." DESC LIMIT 1")->result_array();

        // Faz a bonificação do jogador!
        Recordset::query("UPDATE players SET currency = currency + ".$combat_quests[0]['currency'].", exp = exp + ".$combat_quests[0]['exp']." WHERE id=".$player_rank[0]['player_id']);
        Recordset::query("UPDATE users SET credits = credits + ".$combat_quests[0]['credits']." WHERE id=".$player_rank[0]['user_id']);

        // Marca a missão como completa
        Recordset::query("UPDATE player_combat_quests SET player_id = ". $player_rank[0]['player_id'] . ", finished = 1 WHERE id=". $combat_quests_mensal[0]['id']);
        Recordset::query("UPDATE player_quest_counters SET combat_total = combat_total +1 WHERE player_id=". $player_rank[0]['player_id']);

    }
}

// Pega a missão de combate ativa no momente - diaria
$combat_quests_diario = Recordset::query("SELECT * FROM player_combat_quests WHERE period='diario' AND finished = 0")->result_array();
if ($combat_quests_diario) {
    // Trás as informações sobre a missão ativa
    $combat_quests 		 = Recordset::query("SELECT * FROM combat_quests WHERE id=".$combat_quests_diario[0]['combat_quest_id'])->result_array();

    // Trás informações do jogador melhor colocado em relação a missão ativa
    $player_rank 		 = Recordset::query("SELECT * FROM player_battle_stats ORDER BY ".$combat_quests[0]['type']." DESC LIMIT 1")->result_array();

    // Faz a bonificação do jogador!
    Recordset::query("UPDATE players SET currency = currency + ".$combat_quests[0]['currency'].", exp = exp + ".$combat_quests[0]['exp']." WHERE id=".$player_rank[0]['player_id']);

    // Marca a missão como completa
    Recordset::query("UPDATE player_combat_quests SET player_id = ". $player_rank[0]['player_id'] . ", finished = 1 WHERE id=". $combat_quests_diario[0]['id']);
    Recordset::query("UPDATE player_quest_counters SET combat_total = combat_total +1 WHERE player_id=". $player_rank[0]['player_id']);

}
