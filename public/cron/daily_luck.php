<?php
require '_config.php';

Recordset::query('UPDATE players SET luck_used = 0');
Recordset::query('DELETE FROM player_star_items WHERE item_id = 431');

// Reseta as missoes diarias diriamente para terem uma vez gratis novamente
Recordset::query('UPDATE player_changes SET daily = 0 WHERE daily > 0');

echo '[Daily Luck] Cron executada com sucesso!';