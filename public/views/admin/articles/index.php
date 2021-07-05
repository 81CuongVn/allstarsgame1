<?=partial('shared/title', [
	'title'	=> 'Lista de Noticías'
]);?>
<div class="row">
<div class="col-md-12 col-xl-6">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
						<i class="icon-magazine font-22 avatar-title text-primary"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($counters['articles']);?></h3>
						<p class="text-muted mb-1 text-truncate">Noticias Postadas</p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-12 col-xl-6">
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-4">
					<div class="avatar-lg rounded-circle bg-soft-secondary border-secondary border">
						<i class="icon-bubble-lines4 font-22 avatar-title text-secondary"></i>
					</div>
				</div>
				<div class="col-8">
					<div class="text-right">
						<h3 class="mt-1"><?=highamount($counters['comments']);?></h3>
						<p class="text-muted mb-1 text-truncate">Comentários</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="text-right">
	<a href="<?=make_url('admin/articles/create');?>" class="btn btn-success waves-effect waves-light">
		<span class="btn-label">
			<i class="mdi mdi-check-all"></i>
		</span>
		Adicionar Noticia
	</a>
</div><br />
<div class="card-box" dir="ltr">
	<h4 class="header-title mb-3">Lista de Noticias</h4>
	<div class="table-responsive">
		<table class="table table-borderless table-hover table-centered m-0">
			<thead class="thead-light">
			<tr>
				<th class="text-center">Data</th>
				<th>Titúlo</th>
				<th class="text-center">Tipo</th>
				<th class="text-center">Criada por</th>
				<th class="text-center">Comentários</th>
				<th class="text-center"></th>
			</tr>
			</thead>
			<tbody>
				<?php foreach ($articles as $article) { ?>
					<tr>
						<td class="text-center">
							<span data-toggle="tooltip" title="<?=date('H:i:s', strtotime($article->created_at));?>">
								<?=date('d/m/Y', strtotime($article->created_at));?>
							</span>
						</td>
						<td>
							<a href="<?=make_url('admin/articles/edit/' . $article->id);?>">
								<?=$article->title;?>
							</a>
						</td>
						<td class="text-center">
							<span class="badge badge-primary text-uppercase">
								<?=$article->type;?>
							</span>
						</td>
						<td class="text-center">
							<?=$article->user()->name;?>
						</td>
						<td class="text-center">
							<span class="badge badge-info">
								<?=highamount(sizeof($article->comments()));?>
							</span>
						</td>
						<td class="text-center">
							<a href="<?=make_url('admin/articles/edit/' . $article->id);?>" data-toggle="tooltip" title="Editar" class="btn btn-xs btn-warning waves-effect waves-light">
								<i class="mdi mdi-pencil"></i>
							</a>
							<button type="button" data-toggle="tooltip" title="Apagar" data-article="<?=$article->id;?>" class="btn delete-article btn-xs btn-danger waves-effect waves-light">
								<i class="mdi mdi-close"></i>
							</button>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>
<?=partial('shared/paginator', [
	'addClass'	=> 'justify-content-end',
	'current'	=> $page,
	'pages'		=> $pages
]);?>
<script type="text/javascript">
	(() => {
		const deleteArticle	= $('.delete-article');
		deleteArticle.on('click', function (e) {
			e.preventDefault();

			var _	= $(this);

			jConfirm((ee) => {
				lockScreen(true);

				$.ajax({
					url:		makeUrl('admin/articles/delete/' + _.data('article')),
					dataType:	'json',
					success:	(result) => {
						const $message	= result.success ? result.message : formatError(result.errors);

						jAlert($message, result.success, () => {
							if (result.redirect) {
								window.location = makeUrl(result.redirect);
							}
						});

						lockScreen(false);
					},
					error:		(e) => {
						jAlert('Não foi possível editar! Tente mais tarde.', false);
						lockScreen(false);
					}
				});
			});
		});
	})();
</script>
