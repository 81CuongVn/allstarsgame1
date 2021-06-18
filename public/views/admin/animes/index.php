<?=partial('shared/title', [
	'title'	=> 'Lista de Animes'
]);?>
<div class="text-right">
	<a href="<?=make_url('admin/animes/create');?>" class="btn btn-success waves-effect waves-light">
		Adicionar Novo
	</a>
</div><br />
<div class="row">
	<?php foreach ($animes as $anime) { ?>
		<div class="col-md-6 col-lg-4 col-xl-3">
			<div class="text-center card-box">
				<div class="pt-2 pb-2">
					<img src="<?=image_url('anime/' . $anime->id . '.jpg');?>" class="rounded-circle img-thumbnail avatar-xl" />

					<h4 class="mt-3">
						<a href="extras-profile.html" class="text-dark">
							<?=$anime->description()->name;?>
						</a>
					</h4>
					<div class="text-center mb-2">
						<?php if ($anime->playable) { ?>
							<span class="badge badge-info text-uppercase">Jogável</span>
						<?php } else { ?>
							<span class="badge badge-warning text-uppercase">Não Jogável</span>
						<?php } ?>
						<?php if ($anime->active) { ?>
							<span class="badge badge-success text-uppercase">Ativo</span>
						<?php } else { ?>
							<span class="badge badge-danger text-uppercase">Inativo</span>
						<?php } ?>
					</div>

					<button type="button" class="btn btn-primary btn-sm waves-effect waves-light">Detalhes</button>

					<div class="row mt-2">
						<div class="col-4">
							<div class="mt-3">
								<h4><?=highamount(sizeof($anime->characters()));?></h4>
								<p class="mb-0 text-muted text-truncate">Personagens</p>
							</div>
						</div>
						<div class="col-4">
							<div class="mt-3">
								<h4><?=highamount($anime->total_players());?></h4>
								<p class="mb-0 text-muted text-truncate">Jogadores</p>
							</div>
						</div>
						<div class="col-4">
							<div class="mt-3">
								<h4><?=highamount($anime->total_pets());?></h4>
								<p class="mb-0 text-muted text-truncate">Mascotes</p>
							</div>
						</div>
					</div> <!-- end row-->

				</div> <!-- end .padding -->
			</div> <!-- end card-box-->
		</div> <!-- end col -->
	<?php } ?>
</div>
<?=partial('shared/paginator', [
	'addClass'	=> 'justify-content-end',
	'current'	=> $page,
	'pages'		=> $pages
]);?>
