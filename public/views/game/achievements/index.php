<?=partial('shared/title', [
	'title' => 'menus.achievement',
	'place' => 'menus.achievement'
]);?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Conquistas -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="4812093655"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
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
<div id="make-list-achievement" data-url="<?=make_url('achievements#make_list');?>"></div>
