<?=partial('shared/title', [
	'title' => 'menus.achievement',
	'place' => 'menus.achievement'
]);?>
<ul class="nav nav-pills" id="time-quests-list-tabs">
	<?php
	$first = true;
	foreach ($categories as $category) {
	?>
		<li style="width: 140px;text-align:center; margin-bottom: 4px !important;" <?=($first ? 'class="active"' : '');?>>
			<a href="javascript:void(0)" data-id="<?=$category->id;?>"><?=$category->name;?></a>
		</li>
	<?php
		$first = false;
	}
	?>
</ul>
<div id="make-list-achievement"></div>