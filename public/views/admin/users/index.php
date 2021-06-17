<?=partial('shared/title', [
	'title'	=> 'Lista de Contas'
]);?>
<div class="row mb-2">
	<div class="col-sm-4">
		<button type="button" class="btn btn-success mb-3">
			<i class="mdi mdi-plus"></i>
			Criar Conta
		</button>
	</div>
	<div class="col-sm-8">
		<div class="text-sm-right">
			<div class="btn-group mb-3">
				<button type="button" data-filter="all" class="btn btn-primary">Todos</button>
			</div>
			<div class="btn-group mb-3 ml-1">
				<button type="button" data-filter="active" class="btn btn-light">Ativos</button>
				<button type="button" data-filter="inactive" class="btn btn-light">Inativos</button>
				<button type="button" data-filter="banned" class="btn btn-light">Banidos</button>
			</div>
		</div>
	</div><!-- end col-->
</div>
<!-- end row-->
<div class="row">
	<?php foreach ($users as $u) { ?>
		<div class="col-lg-4">
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

					<div class="mb-3">
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

					<button type="button" class="btn btn-primary btn-sm waves-effect waves-light">
						Detalhes
					</button>

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
