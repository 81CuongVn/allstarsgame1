<?php
require '_config.php';

if((date('d') == 7) || (date('d') == 14) || (date('d') == 21) || (date('d') == 28)) {
    Recordset::query("UPDATE player_battle_stats set victory_pvp_weekly = 0, victory_npc_weekly = 0, looses_pvp_weekly = 0, looses_npc_weekly = 0, draws_pvp_weekly = 0, draws_npc_weekly = 0");
}elseif ((date('d') == 1)){
    Recordset::query("UPDATE player_battle_stats set victory_pvp_monthly = 0, victory_npc_monthly = 0, looses_pvp_monthly = 0, looses_npc_monthly = 0, draws_pvp_monthly = 0, draws_npc_monthly = 0");
}
Recordset::query("UPDATE player_battle_stats set victory_pvp = 0, victory_npc = 0, looses_pvp = 0, looses_npc = 0, draws_pvp = 0, draws_npc = 0");

echo "[Ranking Combats] Cron executada com sucesso!\n";