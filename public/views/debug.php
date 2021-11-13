<?php
	global $___memory, $___start, $___clear_cache_key;

	$___memory['final']	= memory_get_usage();
?>
<div class="mr-debug-window">
	<div class="title"></div>
	<div class="mr-container">
		<form method="get">
			<input type="hidden" name="__clear_the_damn_cache" value="<?=$___clear_cache_key;?>" />
			<input type="submit" class="btn btn-sm btn-primary" value="Limpar Cache" />
		</form>
		<h4>Script took <?=(microtime(true) - $___start);?> seconds</h4>
		<hr />

		<h4>SQL Status</h4>
		<table class="table table-condensed table-striped">
			<tr>
				<td class="col-lg-4">Queries:</td>
				<td>
					<?=highamount(Recordset::$count_queries);?>
					( Real: <?=highamount(Recordset::$count_queries - Recordset::$count_cache_hits - Recordset::$count_hard_cache_hits);?> )
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Cache:</td>
				<td>
					Hard: <?=highamount(Recordset::$count_hard_cache_hits);?> /
					Soft: <?=highamount(Recordset::$count_cache_hits);?>
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Without cache:</td>
				<td>
					<?=highamount(Recordset::$count_queries - Recordset::$count_cache_hits - Recordset::$count_hard_cache_hits);?>
					( Misses: <?highamount(Recordset::$count_cache_miss);?> )
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Time spent:</td>
				<td>
					<?php if (DB_LOGGING) { ?>
						<?php
						$sql_time	= 0;
						foreach (Recordset::$sqls as $sql) {
							if (array_key_exists('duration', $sql) && is_numeric($sql['duration'])) {
								$sql_time	+= $sql['duration'];
							}
						}

						echo $sql_time;
						?> seconds.<br />
						Repeated queries are not included
					<?php } else {?>
						DB_LOGGING not enableed!
					<?php } ?>
				</td>
			</tr>
		</table>

		<h3>Memory Status</h3>
		<table class="table table-condensed table-striped">
			<?php foreach ($___memory as $_ => $value) { ?>
				<tr>
					<td class="col-lg-4"><?=$_;?></td>
					<td><?=round($value / 1024);?>Kb</td>
				</tr>
			<?php } ?>
		</table>

		<?php if (DB_LOGGING) { ?>
			<h3>SQLs Without cache</h3>
			<table class="table table-condensed table-striped">
				<?php foreach (Recordset::$sqls as $_ => $sql) { ?>
					<?php if ($sql['cached']) continue; ?>
					<tr>
						<td class="mr-sql-trace" data-id="<?=$_;?>">
							<?=$sql['sql'];?><br /><br />
							<?=$sql['duration'];?> seconds.
						</td>
					</tr>
				<?php } ?>
			</table>
		<?php } ?>
	</div>

	<?php foreach (Recordset::$sqls as $_ => $sql) { ?>
		<?php if ($sql['cached']) continue; ?>
		<div id="mr-sql-trace-<?=$_;?>" class="mr-sql-trace-data">
			<table class="table table-condensed table-striped">
			<?php if (DB_LOGGING) { ?>
				<?php foreach ($sql['traces'] as $trace) { ?>
					<?php foreach ($trace as $detail) { ?>
						<tr><td>Line <?=$detail['line'];?> @ <?=$detail['file'];?></td></tr>
					<?php } ?>
				<?php } ?>
			<?php } else { ?>
				<tr><td>DB_LOGGING noot enabled</td></tr>
			<?php } ?>
			</table>
		</div>
	<?php } ?>
</div>
