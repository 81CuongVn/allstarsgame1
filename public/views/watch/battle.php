<?=partial('shared/title_battle', [
    'title' => 'watch.battle.title',
    'place' => 'watch.battle.title'
]);?>
<?=partial('shared/fight_panel', [
    'player'				=> $player,
    'enemy'					=> $enemy,
    'battle'				=> $battle,
    'target_url'			=> $target_url,
    'log'					=> $battle->get_log(),
    'player_wanted'			=> $player_wanted,
    'enemy_wanted'			=> $enemy_wanted,
    'is_watch'              => TRUE
]);?>