<?php
require '_config.php';

$playerFidelities = PlayerFidelity::all();
foreach ($playerFidelities as $playerFidelity) {
	if (!$playerFidelity->reward || $playerFidelity->day >= 30)
		$playerFidelity->day = 1;
	else
		++$playerFidelity->day;

	$playerFidelity->reward		= 0;
	$playerFidelity->reward_at	= NULL;
	$playerFidelity->save();
}
echo '[Fidelity] Cron executada com sucesso!';