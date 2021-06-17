<?php
class HomeController extends Controller {
	function index() {
		// Contagem de contas
		$couuntUsers	= [
			'active'	=> Recordset::query("SELECT COUNT(id) AS total FROM users WHERE active = 1 AND banned = 0 AND removed = 0")->row()->total,
			'inactive'	=> Recordset::query("SELECT COUNT(id) AS total FROM users WHERE active = 0 AND banned = 0 AND removed = 0")->row()->total,
			'banned'	=> Recordset::query("SELECT COUNT(id) AS total FROM users WHERE banned = 1 AND removed = 0")->row()->total,
			'total'		=> Recordset::query("SELECT COUNT(id) AS total FROM users WHERE removed = 0")->row()->total
		];

		// Contagem de personagens
		$countPlayers	= [
			'active'	=> Recordset::query("SELECT COUNT(id) AS total FROM players WHERE banned = 0 AND removed = 0")->row()->total,
			'banned'	=> Recordset::query("SELECT COUNT(id) AS total FROM players WHERE banned = 1 AND removed = 0")->row()->total,
			'total'		=> Recordset::query("SELECT COUNT(id) AS total FROM players")->row()->total
		];

		// Últimas contas criadas
		$lastUsers		= User::all([
			'reorder'	=> 'created_at desc',
			'limit'		=> '4'
		]);

		// Últiimos 6 meses
		$months = [ date('Y-m') ];
		for ($i = 1; $i <= 5; ++$i) {
			$month		= date('Y-m', strtotime('-' . $i . ' months'));
			$months[]	= $month;
		}
		$months = array_reverse($months);

		// Gráfico de Crescimento Mensal
		$graphUPG	= [];
		foreach ($months as $month) {
			$start_date	= $month . '-01';
			$end_date	= lastDayOfMonth($start_date);

			$users		= Recordset::query("SELECT COUNT(id) AS total FROM users WHERE created_at BETWEEN '{$start_date}' AND '{$end_date}'")->row()->total;
			$players	= Recordset::query("SELECT COUNT(id) AS total FROM players WHERE created_at BETWEEN '{$start_date}' AND '{$end_date}'")->row()->total;
			$guilds		= Recordset::query("SELECT COUNT(id) AS total FROM guilds WHERE created_at BETWEEN '{$start_date}' AND '{$end_date}'")->row()->total;

			$graphUPG[] = [
				'date'		=> $month,
				'users'		=> $users,
				'players'	=> $players,
				'guilds'	=> $guilds
			];
		}

		// Últimos 7 dias
		$days = [ date('Y-m-d') ];
		for ($i = 1; $i <= 6; ++$i) {
			$day		= date('Y-m-d', strtotime('-' . $i . ' days'));
			$days[]	= $day;
		}
		$days = array_reverse($days);

		// Gráfico de Batalhas Diárias
		$graphBattles	= [];
		foreach ($days as $day) {
			$start_date	= $day . ' 00:00:00';
			$end_date	= $day . ' 23:59:59';

			$pvps		= Recordset::query("SELECT COUNT(id) AS total FROM battle_pvps WHERE created_at BETWEEN '{$start_date}' AND '{$end_date}'")->row()->total;
			$npcs		= Recordset::query("SELECT COUNT(id) AS total FROM battle_npcs WHERE created_at BETWEEN '{$start_date}' AND '{$end_date}'")->row()->total;

			$graphBattles[] = [
				'date'		=> $day,
				'pvps'		=> $pvps,
				'npcs'		=> $npcs
			];
		}

		// Venda de estrelas
		$sales	= [];
		$plans	= StarPlan::all();
		foreach ($plans as $plan) {
			$buys	= Recordset::query("SELECT COUNT(id) AS total FROM star_purchases WHERE status = 'aprovado' AND star_plan_id = {$plan->id}")->row()->total;
			if ($buys > 0) {
				$sales[$plan->id]	= [
					'name'	=> $plan->name,
					'sales'	=> $buys
				];
			}
		}

		$this->assign('couuntUsers',	$couuntUsers);
		$this->assign('countPlayers',	$countPlayers);
		$this->assign('lastUsers',		$lastUsers);
		$this->assign('graphUPG',		$graphUPG);
		$this->assign('graphBattles',	$graphBattles);
		$this->assign('sales',			$sales);
	}
}
