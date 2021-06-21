<div class="new2">
	<?php echo partial('shared/title', [ 'title' => 'users.join.title', 'place' => 'users.join.title' ]) ?>
</div>
<div class="new2">
	<div class="conteudo">
		<form class="form" id="f-user-join" onsubmit="return false;">
			<input type="hidden"  name="partneer" value="<?php echo isset($_GET['partneer']) ? $_GET['partneer'] : ""?>" />
			<div class="form-group">
				<label class="control-label"><?php echo t('users.join.labels.name') ?></label>
				<input type="text" class="form-control input-sm" placeholder="<?php echo t('users.join.placeholders.name') ?>" name="name" />
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.email') ?></label>
					<input type="text" class="form-control input-sm" placeholder="<?php echo t('users.join.placeholders.email') ?>" name="email" />
				</div>
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.email_confirmation') ?></label>
					<input type="text" class="form-control input-sm" placeholder="<?php echo t('users.join.placeholders.email_confirmation') ?>" name="email_confirmation" />
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.password') ?></label>
					<input type="password" class="form-control input-sm" placeholder="<?php echo t('users.join.placeholders.password') ?>" name="password" />
				</div>
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.password_confirmation') ?></label>
					<input type="password" class="form-control input-sm" placeholder="<?php echo t('users.join.placeholders.password_confirmation') ?>" name="password_confirmation" />
				</div>
			</div>
			<div class="row">
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.country') ?></label>
					<select name="country_id" class="form-control input-sm select2">
						<?php foreach ($countries as $country): ?>
							<option value="<?php echo $country->id ?>"><?php echo $country->name ?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group col-md-6">
					<label class="control-label"><?php echo t('users.join.labels.gender') ?></label>
					<select name="gender" class="form-control input-sm">
						<option value="1"><?php echo t('genders.male') ?></option>
						<option value="2"><?php echo t('genders.female') ?></option>
					</select>
				</div>
			</div>
			<!-- <div class="form-group row">
				<label class="control-label col-md-offset-1 col-md-10"><?php echo t('users.join.labels.terms') ?></label>
				<div class="col-md-offset-1 col-md-10">
					<textarea class="form-control input-sm" rows="10" style="resize: none;" readonly><?php echo t('users.join.terms') ?></textarea>
				</div>
			</div> -->
			<hr />
			<div class="form-group" style="margin-top: 25px;">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="term1" value="1" />
						<?php echo t('users.join.terms.t1', array('link' => make_url('home#usege_terms'))) ?>
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="term2" value="1" />
						<?php echo t('users.join.terms.t2', array('link' => make_url('home#usege_terms'))) ?>
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="term3" value="1" />
						<?php echo t('users.join.terms.t3', array('link' => make_url('home#usege_terms'))) ?>
					</label>
				</div>
			</div>
			<div class="form-group">
				<div class="checkbox">
					<label>
						<input type="checkbox" name="term_all" value="1" />
						<?php echo t('users.join.terms.all') ?>
					</label>
				</div>
			</div>
			<hr />
			<div class="form-group text-center" style="margin-top: 25px;">
				<button type="submit" class="btn btn-sm btn-primary g-recaptcha" data-sitekey="<?=$recaptcha['site'];?>" data-callback="doRegister">
					<?=t('users.join.submit');?>
				</button>
			</div>
		</form>
	</div>
</div>
