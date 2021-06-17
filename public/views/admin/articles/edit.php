<?=partial('shared/title', [
	'title'	=> 'Adicioar Noticia'
]);?>
<div class="card">
	<div class="card-body">
		<h4 class="header-title mb-3">Criar uma nova noticia</h4>
		<form id="edit-article" onsubmit="return false;">
			<div class="form-row">
				<div class="form-group col-md-9">
					<input type="text" value="<?=$article->title;?>" name="title" class="form-control" placeholder="Título da noticia" required />
				</div>
				<div class="form-group col-md-3">
					<select name="type" data-toggle="select2" required style="width: 100%;">
						<option value="news" <?=($article->type == 'news' ? 'selected' : '');?>>Novidade</option>
						<option value="promotions" <?=($article->type == 'promotions' ? 'selected' : '');?>>Promoções</option>
						<option value="events" <?=($article->type == 'events' ? 'selected' : '');?>>Eventos</option>
						<option value="maintenance" <?=($article->type == 'maintenance' ? 'selected' : '');?>>Manutenção</option>
					</select>
					<div class="clearfix"></div>
				</div>
			</div>
			<textarea name="description" id="summernote-editor" required><?=($article->description);?></textarea>

			<div class="text-right mt-3">
				<button type="submit" class="btn btn-success waves-effect waves-light">
					Salvar Edições
				</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">
	(() => {
		const editArticle	= $('#edit-article');
		editArticle.on('submit', () => {
			lockScreen(true);

			$.ajax({
				url:		makeUrl('admin/articles/edit/<?=$article->id;?>'),
				data:		editArticle.serialize(),
				type:		'post',
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
			})
		});

		$('[data-toggle="select2"]').select2();

		$('#summernote-editor').summernote({
			lang:			'pt-BR',
			placeholder:	'Escreva a noticia...',
			height:			300,	// set editor height
			minHeight:		300,	// set minimum height of editor
			maxHeight:		500,	// set maximum height of editor
			focus:			false	// set focus to editable area after initializing summernote
		});
	})();
</script>
