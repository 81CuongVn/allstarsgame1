<?=partial('shared/title', [
	'title'	=> 'Dashboard'
]);?>
<div class="row">
	<div class="col-md-6 col-xl-3">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-info border-info border">
						<i class="icon-users2 font-22 avatar-title text-info"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount(
							$couuntUsers['active'] +
							$couuntUsers['inactive'] +
							$couuntUsers['banned']
						);?></h3>
						<p class="text-muted mb-1 text-truncate">Contas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-xl-3">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-success border-success border">
						<i class="icon-users2 font-22 avatar-title text-success"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($couuntUsers['active']);?></h3>
						<p class="text-muted mb-1 text-truncate">Contas Ativas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-xl-3">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-warning border-warning border">
						<i class="icon-users2 font-22 avatar-title text-warning"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($couuntUsers['inactive']);?></h3>
						<p class="text-muted mb-1 text-truncate">Contas Inativas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6 col-xl-3">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
						<i class="icon-users2 font-22 avatar-title text-danger"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($couuntUsers['banned']);?></h3>
						<p class="text-muted mb-1 text-truncate">Contas Banidas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-4 col-sm-12">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-info border-info border">
						<i class="icon-users4 font-22 avatar-title text-info"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount(
							$countPlayers['active'] +
							$countPlayers['banned']
						);?></h3>
						<p class="text-muted mb-1 text-truncate">Personagens</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-12">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-success border-success border">
						<i class="icon-users4 font-22 avatar-title text-success"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($countPlayers['active']);?></h3>
						<p class="text-muted mb-1 text-truncate">Personagens Ativos</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4 col-sm-12">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
						<i class="icon-users4 font-22 avatar-title text-danger"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($countPlayers['banned']);?></h3>
						<p class="text-muted mb-1 text-truncate">Personagens Banidos</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-12">
		<div class="row">
			<div class="col-xl-6">
				<div class="card-box" dir="ltr">
					<h4 class="header-title">Gráfico de Crescimento</h4>
					<p class="sub-header">Últimos 6 meses</p>
					<div class="text-center">
						<p class="text-muted font-15 font-family-secondary mb-0">
							<span class="mx-2"><i class="mdi mdi-checkbox-blank-circle text-blue"></i> Contas</span>
							<span class="mx-2"><i class="mdi mdi-checkbox-blank-circle text-pink"></i> Personagens</span>
							<span class="mx-2"><i class="mdi mdi-checkbox-blank-circle text-success"></i> Organizações</span>
						</p>
					</div>
					<div id="graphic-upg" style="height: 350px;" class="morris-chart"></div>
				</div>
			</div>
			<div class="col-xl-6">
				<div class="card-box" dir="ltr">
					<h4 class="header-title">Gráfico de Batalhas</h4>
					<p class="sub-header">Últimos 7 dias</p>
					<div class="text-center">
						<p class="text-muted font-15 font-family-secondary mb-0">
							<span class="mx-2"><i class="mdi mdi-checkbox-blank-circle text-primary"></i> Batalhas PvP</span>
							<span class="mx-2"><i class="mdi mdi-checkbox-blank-circle text-danger"></i> Batalhas NPC</span>
						</p>
					</div>
					<div id="graphic-battles" style="height: 350px;" class="morris-chart"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xl-7">
		<div class="card-box">
			<h4 class="header-title">Últimos Cadastros</h4>
			<p class="sub-header">Últimas 4 contas criadas</p>
			<div class="table-responsive">
				<table class="table table-borderless table-hover table-centered m-0">
					<thead class="thead-light">
					<tr>
						<th class="text-center" style="width: 70px;">Data</th>
						<th>Nome</th>
						<th class="text-center" style="width: 50px;">Personagens</th>
						<th class="text-center" style="width: 70px;">Facebook</th>
						<th class="text-center" style="width: 70px;">Status</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach ($lastUsers as $u) {?>
							<tr>
								<td class="text-center">
									<span data-toggle="tooltip" title="<?=date('H:i:s', strtotime($u->created_at));?>">
										<?=date('d/m/Y', strtotime($u->created_at));?>
									</span>
								</td>
								<td>
									<h5 class="m-0 font-weight-normal">
										<a href="<?=make_url('admin/users/view/' . $u->id);?>">
											<?=$u->name;?>
										</a>
									</h5>
									<p class="mb-0 text-muted">
										<small><?=$u->email?></small>
									</p>
								</td>
								<td class="text-center">
									<span class="badge badge-primary">
										<?=$u->total_players();?>
									</span>
								</td>
								<td class="text-center">
									<?php if ($u->fb_id) { ?>
										<span class="badge badge-success text-uppercase">Sim</span>
									<?php } else { ?>
										<span class="badge badge-danger text-uppercase">Não</span>
									<?php } ?>
								</td>
								<td class="text-center">
									<?php if ($u->banned) { ?>
										<span class="badge badge-danger text-uppercase">Banido</span>
									<?php } else { ?>
										<?php if ($u->active) { ?>
											<span class="badge badge-success text-uppercase">Ativa</span>
										<?php } else { ?>
											<span class="badge badge-warning text-uppercase">Inativa</span>
										<?php } ?>
									<?php } ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-xl-5">
		<div class="card-box" dir="ltr">
			<h4 class="header-title mb-3">Venda de Estrelas</h4>
			<div id="graphic-sales" style="height: 333px;" class="morris-chart"></div>
		</div> <!-- end card-box-->
	</div>
</div>
<script type="text/javascript">
	(() => {
		// create line chart
		var $weekdays = [
			"DOMINGO", "SEGUNDA", "TERÇA",
			"QUARTA", "QUINTA", "SEXTA", "SÁBADO"
		];
		var $months	= [
			"Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
			"Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembbro"
		];

		Morris.Line({
			element: 'graphic-upg',
			data: [
				<?php foreach ($graphUPG as $data) { ?>
					{
						date:		'<?=$data['date'];?>',
						users:		<?=$data['users'];?>,
						players:	<?=$data['players'];?>,
						guilds:		<?=$data['guilds'];?>
					},
				<?php } ?>
			],
			xkey: 'date',
			ykeys: [ 'users', 'players', 'guilds' ],
			labels: [ 'Contas', 'Personagens', 'Organizações' ],
			fillOpacity: [ '0.1' ],
			pointFillColors: [ '#ffffff' ],
			pointStrokeColors: [ '#999999' ],
			behaveLikeLine: true,
			gridLineColor: '#eef0f2',
			hideHover: 'auto',
			lineWidth: '3px',
			pointSize: 0,
			preUnits: '',
			resize: true, // defaulted to true
			lineColors: [ '#4a81d4', '#f672a7', '#1abc9c' ],
			xLabelFormat: function(x) { // <--- x.getMonth() returns valid index
				var month = $months[x.getMonth()];
				return month.substring(0, 3) + '\n' + x.getFullYear();
			},
			dateFormat: function(x) {
				var month = $months[new Date(x).getMonth()] + '<br />' + new Date(x).getFullYear();
				return month;
			},
        });

		Morris.Line({
			element: 'graphic-battles',
			data: [
				<?php foreach ($graphBattles as $data) { ?>
					{
						date:		'<?=$data['date'];?>',
						pvps:		<?=$data['pvps'];?>,
						npcs:		<?=$data['npcs'];?>
					},
				<?php } ?>
			],
			xkey: 'date',
			ykeys: [ 'pvps', 'npcs' ],
			labels: [ 'Batalhas PvP', 'Batalhas NPC' ],
			fillOpacity: [ '0.1' ],
			pointFillColors: [ '#ffffff' ],
			pointStrokeColors: [ '#999999' ],
			behaveLikeLine: true,
			gridLineColor: '#eef0f2',
			hideHover: 'auto',
			lineWidth: '3px',
			pointSize: 0,
			preUnits: '',
			resize: true, // defaulted to true
			lineColors: [ '#4a81d4', '#f672a7' ],
			xLabels: 'day',
			xLabelFormat: function(x) { // <--- x.getMonth() returns valid index
				return $weekdays[x.getDay()].substring(0, 3);
			},
			dateFormat: function(x) {
				return $weekdays[new Date(x).getDay()];
			},
        });

		Morris.Donut({
            element: 'graphic-sales',
            data: [
				<?php foreach ($sales as $plan) { ?>
                	{
						label:	"<?=$plan['name'];?>",
						value:	<?=$plan['sales'];?>
					},
				<?php } ?>
            ],
            barSize: 0.2,
            resize: true, //defaulted to true
            // colors: [ '#4fc6e1','#6658dd', '#ebeff2' ]
        });
	})();
</script>
