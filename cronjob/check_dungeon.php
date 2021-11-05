<?php
require_once '_config.php';

$events	= Recordset::query("SELECT id, guild_id FROM guild_accepted_events WHERE finished_at IS NULL AND finishes_at <= NOW()");
foreach ($events->result() as $event) {
	Recordset::update('guild_accepted_events', [
		'finished_at'	=> now(true)
	], [
		'id'	=> $event->id
	]);

	// Remove os jogadores da dungeon
	$players	= Recordset::query("SELECT id FROM players WHERE guild_accepted_event_id = {$event->id}");
	foreach ($players->result() as $p) {
		Recordset::update('players', [
			'guild_accepted_event_id'	=> 0
		], [
			'id'	=> $p->id
		]);
	}

	// Atualiza a Guild
	Recordset::update('guilds', [
		'guild_accepted_event_id'	=> 0
	], [
		'id'	=> $event->guild_id
	]);
}
