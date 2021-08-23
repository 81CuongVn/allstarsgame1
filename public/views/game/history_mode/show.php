<?php echo partial('shared/title', array('title' => 'history_mode.index.title', 'place' => 'history_mode.index.title')) ?>
<!-- AASG - Modo Aventura -->
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-6665062829379662"
     data-ad-slot="8659839325"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script><br />
<?php foreach ($subgroups as $subgroup): ?>
	<?php echo partial('subgroup', ['player' => $player, 'subgroup' => $subgroup]) ?>
<?php endforeach ?>
