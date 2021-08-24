<?php echo partial('shared/title', array('title' => 'characters.talents.title', 'place' => 'characters.talents.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Personagem -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="7609647387"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php if (!$player_tutorial->talents) { ?>
	<script type="text/javascript">
		$(function () {
			var tour = new Tour({
				backdrop: true,
				page: 21,
				steps: [{
					element: ".tutorial-2",
					title: "Escolha seus Talentos",
					content: "A cada dois leveis de conta você ganha o direito de escolher apenas um entre os três talentos disponíveis. Escolha atentamente!",
					placement: "top"
				}]
			});

			tour.restart();
			tour.init(true);
			tour.start(true);
		});
	</script>
<?php } ?>
<?php
	echo partial('shared/info', [
		'id'		=> 4,
		'title'		=> 'talents.how_to_title',
		'message'	=> t('talents.how_to_text')
	]);
?>
<br />
<div id="talents-container">
	<?php foreach ($list as $level => $items): ?>
		<div class="talents tutorial-<?php echo $level ?>">
			<div class="level <?php echo $user->level >= $level ? 'on' : '' ?>">
				<p><?php echo $level ?></p>
			</div>
			<?php foreach ($items as $item): ?>
				<div class="item <?php echo $player->has_item($item) ? 'on' : '' ?>" data-item="<?php echo $item->id ?>">
					<div class="image" >
						<img src="<?php echo image_url($item->image(true)) ?>"  class="technique-popover" data-source="#talent-content-<?php echo $item->id ?>" data-title="<?php echo $item->description()->name ?>" data-trigger="hover" data-placement="bottom" />
						<div class="technique-container" id="talent-content-<?php echo $item->id ?>">
							<?php echo $item->tooltip() ?>
						</div>
					</div>
					<div class="description">
						<p><?php echo $item->description()->name ?></p>
					</div>
				</div>
			<?php endforeach ?>
		</div>
	<?php endforeach ?>
</div>
