<?=partial('shared/title', [
	'title'	=> 'Conta: #' . $u->id . ' - ' . $u->name
]);?>
<div class="row">
	<div class="col-lg-4 col-xl-4">
		<div class="text-center card-box">
			<div class="pt-2 pb-2">
				<img src="<?=getGravatar($u->email);?>" class="rounded-circle img-thumbnail avatar-xl" />

				<h4 class="mt-3">
					<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="text-dark">
						<?=$u->name;?>
					</a>
				</h4>

				<p class="text-muted">
					<?=$u->email;?>
				</p>

				<div class="mb-2">
					<?php if ($u->fb_id) { ?>
						<span class="badge badge-blue text-uppercase">Facebook</span>
					<?php } else { ?>
						<span class="badge badge-secondary text-uppercase">Normal</span>
					<?php } ?>

					<?php if ($u->banned) { ?>
						<span class="badge badge-danger text-uppercase">Banido</span>
					<?php } else { ?>
						<?php if ($u->active) { ?>
							<span class="badge badge-success text-uppercase">Ativo</span>
						<?php } else { ?>
							<span class="badge badge-warning text-uppercase">Inativo</span>
						<?php } ?>
					<?php } ?>
				</div>

				<button type="button" data-id="<?=$u->id?>" class="login-user btn btn-primary btn-sm waves-effect waves-light mt-1">
					Acessar
				</button>
				<?php if ($u->banned) { ?>
					<button type="button" data-id="<?=$u->id?>" class="toggle-ban btn btn-secondary btn-sm waves-effect waves-light mt-1">
						Desbanir
					</button>
				<?php } else { ?>
					<button type="button" data-id="<?=$u->id?>" class="toggle-ban btn btn-danger btn-sm waves-effect waves-light mt-1">
						Banir
					</button>
				<?php } ?>
				<?php if (!$u->active) { ?>
					<button type="button" data-id="<?=$u->id?>" class="active-user btn btn-success btn-sm waves-effect waves-light mt-1">
						Ativar
					</button>
				<?php } ?>

				<div class="row mt-2">
					<div class="col-6">
						<div class="mt-3">
							<h4><?=highamount($u->credits);?></h4>
							<p class="mb-0 text-muted text-truncate">Estrelas</p>
						</div>
					</div>

					<div class="col-6">
						<div class="mt-3">
							<h4><?=highamount($u->total_players());?></h4>
							<p class="mb-0 text-muted text-truncate">Personagens</p>
						</div>
					</div>
				</div><!-- end row-->
			</div><!-- end .padding -->
		</div><!-- end card-box-->
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded bg-soft-primary">
						<i class="fe-calendar font-24 avatar-title text-primary"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="text-dark mt-1">
							<?=date('d/m/Y', strtotime($u->created_at));?>
						</h3>
						<p class="text-muted mb-1 text-truncate text-uppercase">Data de cadastro</p>
					</div>
				</div>
			</div> <!-- end row-->
		</div>
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded bg-soft-info">
						<i class="fe-calendar font-24 avatar-title text-info"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="text-dark mt-1">
							<?=date('d/m/Y', strtotime($u->last_login_at));?>
						</h3>
						<p class="text-muted mb-1 text-truncate text-uppercase">Último login</p>
					</div>
				</div>
			</div> <!-- end row-->
		</div>
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded bg-soft-secondary">
						<i class="icon-display font-24 avatar-title text-secondary"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="text-dark mt-1">
							<?=long2ip($u->last_login_ip);?>
						</h3>
						<p class="text-muted mb-1 text-truncate text-uppercase">Último IP</p>
					</div>
				</div>
			</div> <!-- end row-->
		</div>
	</div> <!-- end col-->

	<div class="col-lg-8 col-xl-8">
		<div class="card-box mb-0">
			<ul class="nav nav-pills navtab-bg nav-justified mbssss-3">
				<li class="nav-item">
					<a href="#characters" data-toggle="tab" class="nav-link active">
						Personagens
					</a>
				</li>
				<li class="nav-item">
					<a href="#donates" data-toggle="tab" class="nav-link">
						Doações
					</a>
				</li>
				<li class="nav-item">
					<a href="#settings" data-toggle="tab" class="nav-link">
						Gerenciar
					</a>
				</li>
			</ul>
		</div>
		<div class="tab-content">
			<div class="tab-pane active" id="characters">
				<?php if (!sizeof($players)) { ?>
					<div class="alert alert-info" role="alert">
						<i class="mdi mdi-alert-circle-outline mr-2"></i> Este jogador ainda não criou personagens.
					</div>
				<?php } ?>
				<div class="row">
					<?php foreach ($players as $p) { ?>
						<?php
						$label		= 'Offline';
						$is_online	= '';
						if ($p->banned) {
							$label		= 'Banido';
							$is_online = 'background-color: #f1556c; border-color: #f1556c;';
						} else {
							if (is_player_online($p->id)) {
								$label		= 'Online';
								$is_online = 'background-color: #1abc9c; border-color: #1abc9c;';
							}
						}
						?>
						<div class="col-md-6 col-lg-4 col-xl-3">
							<div class="text-center card-box">
								<div class="pt-2 pb-2">
									<img data-toggle="tooltip" title="<?=$label;?>" src="<?=image_url($p->small_image(true));?>" class="rounded-circle img-thumbnail avatar-xl" style="<?=$is_online;?>" />

									<h4 class="mt-3">
										<a href="<?=make_url('admin/players/view/' . $p->id);?>" class="text-dark">
											<?=$p->name;?>
										</a>
									</h4>

									<p class="text-muted">
										<?=$p->character()->description()->name;?>
									</p>

									<div class="mb-3">
										<?php if ($p->banned) { ?>
											<span class="badge badge-danger text-uppercase">Banido</span>
										<?php } else { ?>
											<span class="badge badge-success text-uppercase">Ativo</span>
										<?php } ?>
									</div>

									<a href="<?=make_url('admin/players/view/' . $p->id);?>" class="btn btn-primary btn-sm waves-effect waves-light">
										Informações
									</a>
								</div><!-- end .padding -->
							</div><!-- end card-box-->
						</div><!-- end col -->
					<?php } ?>
				</div>
				<?=partial('shared/paginator', [
					'addClass'	=> 'justify-content-center',
					'current'	=> $page,
					'pages'		=> $pages
				]);?>
			</div>
			<div class="tab-pane" id="donates">
				<div class="card-box">
					<!-- <div class="table-responsive"> -->
						<table class="table data table-borderless table-hover dt-responsive nowrap mb-0" style="width: 100%;">
							<thead class="thead-light">
								<tr>
									<th class="text-center">Data</th>
									<th>Pacote</th>
									<th class="text-center">Double</th>
									<th class="text-center">Estrelas</th>
									<th class="text-center">Valor</th>
									<th class="text-center">Metódo</th>
									<th class="text-center">Status</th>
									<th class="text-center">Ação</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($donates as $donate) { ?>
									<?php $is_dbl = StarDouble::find_first("'{$donate->created_at}' BETWEEN data_init AND data_end"); ?>
									<tr>
										<td class="text-center">
											<span style="display: none;"><?=strtotime($donate->created_at);?></span>
											<span data-toggle="tooltip" title="<?=date('H:i:s', strtotime($donate->created_at));?>">
												<?=date('d/m/Y', strtotime($donate->created_at));?>
											</span>
										</td>
										<td><?=$donate->plan()->name;?></td>
										<td class="text-center">
											<?php if ($is_dbl) { ?>
												<span style="display: none;">1</span>
												<span class="badge badge-success">
													<i class="mdi mdi-check"></i>
												</span>
											<?php } else { ?>
												<span style="display: none;">0</span>
												<span class="badge badge-danger">
													<i class="mdi mdi-close"></i>
												</span>
											<?php } ?>
										</td>
										<td class="text-center">
											<?php if ($is_dbl) { ?>
												<span class="text-success"><?=($donate->plan()->credits * 2);?></span>
											<?php } else { ?>
												<?=$donate->plan()->credits;?>
											<?php } ?>
										</td>
										<td class="text-center">
											<?php if (in_array($donate->star_method, [ 'mercadopago', 'pagseguro', 'paypal_brl' ])) { ?>
												R$ <?=number_format($donate->plan()->price_brl, 2, ',', '.');?>
											<?php } elseif ($donate->star_method == 'paypal_eur') { ?>
												€ <?=number_format($donate->plan()->price_eur, 2, '.', ',');?>
											<?php } elseif ($donate->star_method == 'paypal_usd') { ?>
												U$ <?=number_format($donate->plan()->price_usd, 2, '.', ',');?>
											<?php } ?>
										</td>
										<td class="text-center"><?=t('donate.method.' . $donate->star_method);?></td>
										<td class="text-center">
											<?php if ($donate->status == 'aprovado') { ?>
												<span class="badge badge-success text-uppercase">Aprovado</span>
											<?php } else {?>
												<?php if ($donate->status == 'aguardando') { ?>
													<span class="badge badge-info text-uppercase">Aguardando</span>
												<?php } elseif ($donate->status == 'cancelado') { ?>
													<span class="badge badge-danger text-uppercase">Cancelado</span>
												<?php } ?>
											<?php } ?>
										</td>
										<td class="text-center">
											<?php if ($donate->status == 'aprovado') { ?>
												<button type="button" data-toggle="tooltip" title="Cancelar" data-id="<?=$donate->id;?>" class="btn delete-article btn-xs btn-danger waves-effect waves-light">
													<i class="mdi mdi-close"></i>
												</button>
											<?php } else {?>
												<?php if ($donate->status == 'aguardando') { ?>
													<button type="button" data-toggle="tooltip" title="Aprovar" data-id="<?=$donate->id;?>" class="btn delete-article btn-xs btn-success waves-effect waves-light">
														<i class="mdi mdi-check"></i>
													</button>
													<button type="button" data-toggle="tooltip" title="Cancelar" data-id="<?=$donate->id;?>" class="btn delete-article btn-xs btn-danger waves-effect waves-light">
														<i class="mdi mdi-close"></i>
													</button>
												<?php } else { ?>
													-
												<?php } ?>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					<!-- </div> -->
				</div>
			</div>
			<div class="tab-pane" id="settings">
				<div class="card-box">
					<form>
						<h5 class="mb-3 text-uppercase bg-light p-2">
							<i class="mdi mdi-account-circle mr-1"></i>
							Alterar Informações Pessoais
						</h5>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="name">Nome</label>
									<input id="name" type="text" value="<?=$u->name?>" name="name" class="form-control" require />
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="email">E-mail</label>
									<input id="email" type="email" value="<?=$u->email?>" name="email" class="form-control" require />
								</div>
							</div><!-- end col -->
						</div><!-- end row -->

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="country">País</label>
									<select id="country" name="country" class="form-control" require>
										<?php foreach ($countries as $country) { ?>
											<option value="<?=$country->id;?>" <?=($u->country_id == $country->id ? 'selected' : '');?>><?=$country->name;?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="gender">Sexo</label>
									<select id="gender" name="gender" class="form-control" require>
										<option value="1" <?=($u->gender == '1' ? 'selected' : '');?>>Masculino</option>
										<option value="2" <?=($u->gender == '2' ? 'selected' : '');?>>Feminino</option>
									</select>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<h5 class="mb-3 text-uppercase bg-light p-2">
							<i class="mdi mdi-lock mr-1"></i> ALterar Senha
						</h5>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="password">Senha</label>
									<input id="password" type="password" name="password" class="form-control" require>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="password_confirmation">Confirmar Senha</label>
									<input id="password_confirmation" type="password" name="password_confirmation" class="form-control" require>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="text-right">
							<button type="submit" class="btn btn-success waves-effect waves-light mt-2">
								<i class="mdi mdi-content-save"></i> Salvar Alterações
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div><!-- end col -->
</div><!-- end row-->
