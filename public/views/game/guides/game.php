<?=partial('shared/title', [
	'title'	=> 'menus.game_guide',
	'place'	=> 'menus.game_guide'
]);?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Guias -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7729901030"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<div class="barra-secao barra-secao-<?=rand(1, 12);?>">
	<p>Categorias</p>
</div>
<ul class="nav nav-pills nav-justified" id="guide-category-list-tabs">
	<?php $first = true; ?>
	<?php foreach ($categories as $category) { ?>
		<li style="margin-bottom: 4px !important;" <?=($first ? 'class="active"' : '');?>>
			<a href="#guide-category-tab-<?=$category->id;?>"><?=$category->description()->name;?></a>
		</li>
		<?php $first = false; ?>
	<?php } ?>
</ul>
<br /><br />
<div class="barra-secao barra-secao-<?=rand(1, 12);?>">
	<p>Sub Categorias</p>
</div>
<div class="tab-content" id="guide-category-list-content">
	<?php $first = true; ?>
	<?php $first2 = true; ?>
	<?php $first3 = true; ?>
	<?php foreach ($categories as $category) { ?>
		<div class="tab-pane<?=($first ? ' active' : '');?>" id="guide-category-tab-<?=$category->id;?>">
			<?php $subgroups = GuideSubgroup::find("guide_category_id = " . $category->id, ['reorder'	=> 'sort asc']); ?>
				<ul class="nav nav-pills" id="guide-subcategory-list-tabs">
					<?php foreach ($subgroups as $subgroup) { ?>
						<li style="margin-bottom: 4px !important;" <?=($first3 ? 'class="active"' : '');?>>
							<a href="#guide-subcategory-tab-<?=$subgroup->id;?>"><?=$subgroup->description()->name;?></a>
						</li>
						<?php $first3 = false; ?>
					<?php } ?>
				</ul>
				<div class="tab-content" id="guide-subcategory-list-content">
					<?php foreach ($subgroups as $subgroup) { ?>
						<?php $guides = Guide::find_first("guide_subgroup_id = " . $subgroup->id);?>
						<div class="tab-pane<?php echo $first2 ? ' active' : '' ?>" id="guide-subcategory-tab-<?=$subgroup->id;?>">
							<?=nl2br($guides->description()->description);?>
						</div>
						<?php $first2 = false; ?>
					<?php } ?>
				</div>
		</div>
		<?php $first = false; ?>
	<?php } ?>
</div>
