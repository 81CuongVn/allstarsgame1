<style type="text/css">
	.select2-container { z-index: 9999; }
</style>
<?=partial('shared/title', [
	'title'	=> 'Lista de Contas'
]);?>
<div class="row mb-2">
	<div class="col-sm-4">
		<?php if ($user->admin > 1) { ?>
			<a href="#add-user-modal" class="btn btn-success waves-effect waves-light mb-3" data-animation="push" data-plugin="custommodal" data-overlayColor="#38414a">
				<i class="mdi mdi-plus mr-1"></i>
				Criar Conta
			</a>
		<?php } else { ?>
			<button type="submit" class="btn btn-success waves-effect waves-light mb-3" disabled>
				<i class="mdi mdi-plus mr-1"></i>
				Criar Conta
			</button>
		<?php } ?>
	</div>
	<div class="col-sm-8">
		<div class="text-sm-right">
			<div class="btn-group mb-3">
				<button type="button" data-filter="all" class="filter-list btn btn-<?=($filter == 'all' ? 'primary' : 'light')?>">Todos</button>
			</div>
			<div class="btn-group mb-3 ml-1">
				<button type="button" data-filter="active" class="filter-list btn btn-<?=($filter == 'active' ? 'primary' : 'light')?>">Ativos</button>
				<button type="button" data-filter="vip" class="filter-list btn btn-<?=($filter == 'vip' ? 'primary' : 'light')?>">Vip</button>
				<button type="button" data-filter="online" class="filter-list btn btn-<?=($filter == 'online' ? 'primary' : 'light')?>">Online</button>
				<button type="button" data-filter="inactive" class="filter-list btn btn-<?=($filter == 'inactive' ? 'primary' : 'light')?>">Inativos</button>
			</div>
		</div>
	</div><!-- end col-->
</div>
<!-- end row-->
<?php if (!sizeof($users)) { ?>
	<div class="alert alert-info" role="alert">
		<i class="mdi mdi-alert-circle-outline mr-2"></i> Não encontramos nenhuma conta.
	</div>
<?php } ?>
<div class="row">
	<?php foreach ($users as $u) { ?>
		<div class="col-md-6 col-lg-4 col-xl-3">
			<div class="text-center card-box">
				<div class="pt-2 pb-2">
					<?php
					$label		= 'Offline';
					$is_online	= '';
					if ($u->hasBanishment()) {
						$label		= 'Banido';
						$is_online = 'background-color: #f1556c; border-color: #f1556c;';
					} else {
						if (is_user_online($u->id)) {
							$label		= 'Online';
							$is_online = 'background-color: #1abc9c; border-color: #1abc9c;';
						}
					}
					?>
					<img data-toggle="tooltip" title="<?=$label;?>" src="<?=getGravatar($u->email);?>" class="rounded-circle img-thumbnail avatar-xl" style="<?=$is_online;?>" />

					<h4 class="mt-3">
						<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="text-dark">
							<?=$u->name;?>
						</a>
					</h4>

					<p class="text-muted">
						<?=$u->email;?>
					</p>

					<div class="mb-1">
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
					<div class="mb-3" style="min-height: 22px;">
						<?php if (!$u->vip) { ?>
							<span class="badge badge-blue text-uppercase">Jogador Vip</span>
						<?php } ?>
						<?php if ($u->admin) { ?>
							<span class="badge badge-dark text-uppercase">Staff</span>
						<?php } ?>
					</div>

					<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="btn btn-primary btn-sm waves-effect waves-light">
						Detalhes
					</a>

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
		</div><!-- end col -->
	<?php } ?>
</div>
<?=partial('shared/paginator', [
	'addClass'	=> 'justify-content-center',
	'current'	=> $page,
	'pages'		=> $pages
]);?>
<?php if ($user->admin > 1) { ?>
	<div id="add-user-modal" class="modal-demo">
		<button type="button" class="close" onclick="Custombox.modal.close();">
			<span>&times;</span>
			<span class="sr-only">Fechar</span>
		</button>
		<h4 class="custom-modal-title">Criar nova conta</h4>
		<div class="custom-modal-text text-left">
			<div class="alert alert-danger showErrors" style="display: none;"></div>
			<form id="create-user" onsubmit="return false;">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="name">Nome Completo</label>
							<input id="name" type="text" name="name" class="form-control" require />
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="email">E-mail</label>
							<input id="email" type="email" name="email" class="form-control" require />
						</div>
					</div><!-- end col -->
				</div><!-- end row -->
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label for="country">País</label>
							<select id="country" name="country" data-toggle="select2" required style="width: 100%;">
								<?php foreach ($countries as $country) { ?>
									<option value="<?=$country->id;?>"><?=$country->name;?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label for="gender">Sexo</label>
							<select id="gender" name="gender" data-toggle="select2" required style="width: 100%;">
								<option value="1">Masculino</option>
								<option value="2">Feminino</option>
							</select>
						</div>
					</div> <!-- end col -->
				</div> <!-- end row -->

				<p class="text-warning text-center text-uppercase">
					<strong>A senha senha enviada para o email iinformado no cadastro.</strong>
				</p>

				<div class="text-right">
					<button type="submit" class="btn btn-success waves-effect waves-light">Salvar</button>
					<button type="button" class="btn btn-danger waves-effect waves-light m-l-10" onclick="Custombox.modal.close();">Cancelar</button>
				</div>
			</form>
		</div>
	</div>
<?php } ?>
<script type="text/javascript">
	(function() {
		<?php if ($user->admin > 1) { ?>
			var createUser	= $("#create-user");
			if (createUser.length) {
				createUser.on('submit', function(e) {
					e.preventDefault();
					$('.showErrors').hide();
					$('.showErrors').empty();

					$.ajax({
						url:		makeUrl('admin/users/create'),
						data:		createUser.serialize(),
						type:		'post',
						dataType:	'json',
						success:	function(result) {
							var $message	= result.success ? result.message : formatError(result.errors);
							if (result.success) {
								Custombox.modal.close();

								jAlert($message, result.success, () => {
									if (result.redirect) {
										window.location = makeUrl(result.redirect);
									}
								});
							} else {
								$('.showErrors').show()
								$('.showErrors').html($message);
							}

							lockScreen(false);
							blockForm(createUser, false);
						},
						error:		function() {
							$('.showErrors').show()
							$('.showErrors').html('Não foi possível processar sua ação! Tente mais tarde.');
						}
					});

					lockScreen(true);
					blockForm(createUser, true);
				});
			}
		<?php } ?>

		var $filter	= '<?=$filter;?>';
		$('.filter-list').on('click', function(e) {
			e.preventDefault();

			var new_filter = $(this).data('filter');
			if (new_filter == $filter) {
				return;
			}

			window.location.href = makeUrl('admin/users?filter=' + new_filter);
		});
	})();
</script>
