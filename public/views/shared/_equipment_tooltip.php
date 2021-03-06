<script type="text/javascript">
	$(document).ready(function () {
		_equipments[<?php echo $player_item->id ?>]	= {
		<?php foreach ($attributes as $attribute => $value): ?>
			<?php
				if (in_array($attribute, $ignores)) {
					continue;
				}
			?>
			<?php echo $attribute ?>: <?php echo is_numeric($value) ? $value : '"' . $value . '"' ?>,
		<?php endforeach ?>
			id:		<?php echo $player_item->id ?>,
			rarity:	'<?php echo $player_item->rarity ?>'
		};
	});
</script>