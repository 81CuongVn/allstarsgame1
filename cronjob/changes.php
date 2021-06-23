<?php
require '_config.php';

Recordset::update('users', [
	'vip'		=> 0,
	'credits'	=> 0
]);

$donates = StarPurchase::find("status = 'aprovado'");
foreach ($donates as $donate) {
	$user		= $donate->user();
	$plan		= $donate->plan();
	$credits	= $donate->isDouble() ? ($plan->credits * 2) : $plan->credits;

	if (!$user->vip) {
		$user->vip	= 1;
	}

	$user->credits	+= $credits;
	$user->save();
}

echo "[CRON] Cron executada com sucesso!\n";
