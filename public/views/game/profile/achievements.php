<?=partial('profile/left', [
	'player'	=> $profile
]);?>
<?php if (!$profile) { ?>
	<?=partial('shared/title', ['title' => 'profile.unknow.title', 'place' => 'profile.unknow.title']);?>
	<?=partial('shared/info', [
		'id'		=> 2,
		'title'		=> 'profile.unknow.title',
		'message'	=> t('profile.unknow.description')
	]);?>
<?php } else { ?>
	<div class="titulo-secao">
		<p>Conquistas de <?=($profile ? $profile->name : '???');?></p>
		<span><a href="<?=make_url('/');?>">Página Principal</a> <b>&gt;&gt;</b> Conquistas de <?=($profile ? $profile->name : '???');?></span>
	</div>
	<ul class="nav nav-pills" id="achievements-list-tabs">
		<?php
		$first = true;
		foreach ($categories as $category) {
		?>
			<li style="width: 140px;text-align:center; margin-bottom: 4px !important;" <?=($first ? 'class="active"' : '');?>>
				<a href="javascript:void(0)" data-id="<?=$category->id;?>"><?=$category->description()->name;?></a>
			</li>
		<?php
			$first = false;
		}
		?>
	</ul>
	<div id="make-list-achievement" data-url="<?=make_url('profile#achievements_list', [ 'player' => $profile->id ]);?>"></div>
<?php } ?>
