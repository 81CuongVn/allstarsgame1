<?php echo partial('shared/title', array('title' => 'menus.game_guide', 'place' => 'menus.game_guide')) ?>
<div class="barra-secao barra-secao-<?php echo rand(1,12) ?>"><p>&nbsp;&nbsp;Categorias</p></div>
<ul class="nav nav-pills nav-justified" id="guide-category-list-tabs">
	<?php $first = true; ?>
	<?php foreach ($categories as $category): ?>
		<li style="margin-bottom: 4px !important;" <?php echo $first ? 'class="active"' : '' ?>><a href="#guide-category-tab-<?php echo $category->id ?>"><?php echo $category->name ?></a></li>
		<?php $first = false; ?>
	<?php endforeach ?>
</ul>
<br /><br />
<div class="barra-secao barra-secao-<?php echo rand(1,12) ?>"><p>&nbsp;&nbsp;SubCategorias</p></div>
<div class="tab-content" id="guide-category-list-content">
	<?php $first = true; ?>
	<?php $first2 = true; ?>
	<?php $first3 = true; ?>
		<?php foreach ($categories as $category): ?>
			<div class="tab-pane<?php echo $first ? ' active' : '' ?>" id="guide-category-tab-<?php echo $category->id ?>">
				<?php $subgroups = GuideSubgroup::find("guide_category_id = ".$category->id." ORDER BY ordem ASC");?>
					<ul class="nav nav-pills" id="guide-subcategory-list-tabs">
						<?php foreach ($subgroups as $subgroup): ?>
							<li style="margin-bottom: 4px !important;" <?php echo $first3 ? 'class="active"' : '' ?>><a href="#guide-subcategory-tab-<?php echo $subgroup->id ?>"><?php echo $subgroup->name ?></a></li>
							<?php $first3 = false; ?>
						<?php endforeach ?>
					</ul>
					<div class="tab-content" id="guide-subcategory-list-content">
							<?php foreach ($subgroups as $subgroup): ?>
								<?php $guides = Guide::find_first("guide_subgroup_id = ".$subgroup->id);?>
								<div class="tab-pane<?php echo $first2 ? ' active' : '' ?>" id="guide-subcategory-tab-<?php echo $subgroup->id ?>">
									<?php echo  nl2br($guides->description)?>
								</div>
								<?php $first2 = false; ?>	
							<?php endforeach ?>		
					</div>	
			</div>
			<?php $first = false; ?>
		<?php endforeach ?>		
</div>	
