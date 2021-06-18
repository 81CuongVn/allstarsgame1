<?=partial('shared/title', [
	'title'	=> 'Lista de Contas'
]);?>
<div class="row mb-2">
	<div class="col-sm-4">
		<button type="button" class="btn btn-success mb-3">
			<i class="mdi mdi-plus"></i>
			Criar Personagem
		</button>
	</div>
	<div class="col-sm-8">
		<div class="text-sm-right">
			<div class="btn-group mb-3">
				<button type="button" data-filter="all" class="btn btn-primary">Todos</button>
			</div>
			<div class="btn-group mb-3 ml-1">
				<button type="button" data-filter="active" class="btn btn-light">Ativos</button>
				<button type="button" data-filter="banned" class="btn btn-light">Banidos</button>
			</div>
		</div>
	</div><!-- end col-->
</div>
<!-- end row-->
<div class="row">
	<?php foreach ($players as $p) { ?>
		<div class="col-lg-3">
			<div class="text-center card-box">
				<div class="pt-2 pb-2">
					<img src="<?=image_url($p->small_image(true));?>" class="rounded-circle img-thumbnail avatar-xl" />

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
