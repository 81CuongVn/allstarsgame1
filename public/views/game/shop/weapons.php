<?php echo partial('shared/title', array('title' => 'shop.weapons.title', 'place' => 'shop.weapons.title')) ?>
<?php if (FW_ENV != 'dev') { ?>
	<!-- AASG - Shop -->
	<ins class="adsbygoogle"
		style="display:inline-block;width:728px;height:90px"
		data-ad-client="ca-pub-6665062829379662"
		data-ad-slot="5422661269"></ins>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
	</script><br />
<?php } ?>
<?php echo partial('shop/list', array('player' => $player, 'items' => $items, 'discount' => $discount)) ?>
