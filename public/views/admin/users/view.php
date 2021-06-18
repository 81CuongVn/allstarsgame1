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

				<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="btn btn-primary btn-sm waves-effect waves-light">
					Acessar
				</a>
				<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="btn btn-success btn-sm waves-effect waves-light">
					Ativar
				</a>
				<a href="<?=make_url('admin/users/view/' . $u->id);?>" class="btn btn-danger btn-sm waves-effect waves-light">
					Banir
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
		<div class="widget-rounded-circle card-box">
			<div class="row">
				<div class="col-6">
					<div class="avatar-lg rounded bg-soft-primary">
						<i class="fe-calendar font-24 avatar-title text-primary"></i>
					</div>
				</div>
				<div class="col-6">
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
				<div class="col-6">
					<div class="avatar-lg rounded bg-soft-info">
						<i class="fe-calendar font-24 avatar-title text-info"></i>
					</div>
				</div>
				<div class="col-6">
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
				<div class="col-6">
					<div class="avatar-lg rounded bg-soft-secondary">
						<i class="icon-display font-24 avatar-title text-secondary"></i>
					</div>
				</div>
				<div class="col-6">
					<div class="text-right">
						<h3 class="text-dark mt-1">
							<?=($u->last_login_ip);?>
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
						Informações
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
						<div class="col-lg-4">
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
					<div class="table-responsive">
						<table class="table table-borderless mb-0">
							<thead class="thead-light">
								<tr>
									<th>Data</th>
									<th>Pacote</th>
									<th>Metódo</th>
									<th>Status</th>
									<th>Ação</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>1</td>
									<td>App design and development</td>
									<td>01/01/2015</td>
									<td>10/15/2018</td>
									<td><span class="badge badge-info">Work in Progress</span></td>
									<td>Halette Boivin</td>
								</tr>
								<tr>
									<td>2</td>
									<td>Coffee detail page - Main Page</td>
									<td>21/07/2016</td>
									<td>12/05/2018</td>
									<td><span class="badge badge-success">Pending</span></td>
									<td>Durandana Jolicoeur</td>
								</tr>
								<tr>
									<td>3</td>
									<td>Poster illustation design</td>
									<td>18/03/2018</td>
									<td>28/09/2018</td>
									<td><span class="badge badge-pink">Done</span></td>
									<td>Lucas Sabourin</td>
								</tr>
								<tr>
									<td>4</td>
									<td>Drinking bottle graphics</td>
									<td>02/10/2017</td>
									<td>07/05/2018</td>
									<td><span class="badge badge-blue">Work in Progress</span></td>
									<td>Donatien Brunelle</td>
								</tr>
								<tr>
									<td>5</td>
									<td>Landing page design - Home</td>
									<td>17/01/2017</td>
									<td>25/05/2021</td>
									<td><span class="badge badge-warning">Coming soon</span></td>
									<td>Karel Auberjo</td>
								</tr>

							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="settings">
				<div class="card-box">
				<form>
						<h5 class="mb-4 text-uppercase"><i class="mdi mdi-account-circle mr-1"></i> Personal Info</h5>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="firstname">First Name</label>
									<input type="text" class="form-control" id="firstname" placeholder="Enter first name">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="lastname">Last Name</label>
									<input type="text" class="form-control" id="lastname" placeholder="Enter last name">
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="row">
							<div class="col-12">
								<div class="form-group">
									<label for="userbio">Bio</label>
									<textarea class="form-control" id="userbio" rows="4" placeholder="Write something..."></textarea>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="useremail">Email Address</label>
									<input type="email" class="form-control" id="useremail" placeholder="Enter email">
									<span class="form-text text-muted"><small>If you want to change email please <a href="javascript: void(0);">click</a> here.</small></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="userpassword">Password</label>
									<input type="password" class="form-control" id="userpassword" placeholder="Enter password">
									<span class="form-text text-muted"><small>If you want to change password please <a href="javascript: void(0);">click</a> here.</small></span>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-office-building mr-1"></i> Company Info</h5>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="companyname">Company Name</label>
									<input type="text" class="form-control" id="companyname" placeholder="Enter company name">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="cwebsite">Website</label>
									<input type="text" class="form-control" id="cwebsite" placeholder="Enter website url">
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-earth mr-1"></i> Social</h5>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-fb">Facebook</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-facebook-square"></i></span>
										</div>
										<input type="text" class="form-control" id="social-fb" placeholder="Url">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-tw">Twitter</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-twitter"></i></span>
										</div>
										<input type="text" class="form-control" id="social-tw" placeholder="Username">
									</div>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-insta">Instagram</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-instagram"></i></span>
										</div>
										<input type="text" class="form-control" id="social-insta" placeholder="Url">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-lin">Linkedin</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-linkedin"></i></span>
										</div>
										<input type="text" class="form-control" id="social-lin" placeholder="Url">
									</div>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-sky">Skype</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-skype"></i></span>
										</div>
										<input type="text" class="form-control" id="social-sky" placeholder="@username">
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="social-gh">Github</label>
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fab fa-github"></i></span>
										</div>
										<input type="text" class="form-control" id="social-gh" placeholder="Username">
									</div>
								</div>
							</div> <!-- end col -->
						</div> <!-- end row -->

						<div class="text-right">
							<button type="submit" class="btn btn-success waves-effect waves-light mt-2"><i class="mdi mdi-content-save"></i> Save</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div><!-- end col -->
</div><!-- end row-->
