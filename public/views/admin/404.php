<div class="row justify-content-center">
	<div class="col-lg-6 col-xl-4 mb-4">
		<div class="error-text-box">
			<svg viewBox="0 0 600 200">
				<!-- Symbol-->
				<symbol id="s-text">
					<text text-anchor="middle" x="50%" y="50%" dy=".35em">404!</text>
				</symbol>
				<!-- Duplicate symbols-->
				<use class="text" xlink:href="#s-text"></use>
				<use class="text" xlink:href="#s-text"></use>
				<use class="text" xlink:href="#s-text"></use>
				<use class="text" xlink:href="#s-text"></use>
				<use class="text" xlink:href="#s-text"></use>
			</svg>
		</div>
		<div class="text-center">
			<h3 class="mt-0 mb-2">Não Encontrada!</h3>
			<p class="text-muted mb-3">
				Parece que vocês etá tentando acessar uma página da qual não existe!
				Caso acredite que isto seja um erro, entre em contato com a <b>administração</b>.
			</p>
			<a href="<?=make_url('admin/home');?>" class="btn btn-success waves-effect waves-light">Voltar</a>
		</div>
		<!-- end row -->

	</div> <!-- end col -->
</div>
<!-- end row -->
