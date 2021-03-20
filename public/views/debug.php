<?php
	global $___memory, $___start, $___clear_cache_key;

	$___memory['final']	= memory_get_usage();
?>
<div class="mr-debug-window">
	<div class="title"></div>
	<div class="mr-container">
		<form method="get">
			<input type="hidden" name="__clear_the_damn_cache" value="<?php echo $___clear_cache_key ?>" />
			<input type="submit" class="btn btn-sm btn-primary" value="Clear Cache" />
		</form>
		<h4>Script took <?php echo microtime(true) - $___start ?> seconds</h4>
		<hr />
		<h4>SQL Status</h4>
		<table class="table table-condensed table-striped">
			<tr>
				<td class="col-lg-4">Queries:</td>
				<td>
					<?php echo Recordset::$count_queries ?>
					( Real: <?php echo Recordset::$count_queries - Recordset::$count_cache_hits - Recordset::$count_hard_cache_hits ?> )
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Cache:</td>
				<td>
					Hard:
					<?php echo Recordset::$count_hard_cache_hits ?> /
					Soft:
					<?php echo Recordset::$count_cache_hits ?>
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Without cache:</td>
				<td>
					<?php echo Recordset::$count_queries - Recordset::$count_cache_hits - Recordset::$count_hard_cache_hits ?>
					( Misses: <?php echo Recordset::$count_cache_miss ?> )
				</td>
			</tr>
			<tr>
				<td class="col-lg-4">Time spent:</td>
				<td>
					<?php if (DB_LOGGING): ?>
						<?php
							$sql_time	= 0;
							foreach (Recordset::$sqls as $sql) {
								$sql_time	+= $sql['duration'];
							}

							echo $sql_time;
						?> seconds.<br />
						Repeated queries are not included<
					<?php else: ?>
						DB_LOGGING not enableed!
					<?php endif ?>
				</td>
			</tr>
		</table>
		<h3>Memory Status</h3>
		<table class="table table-condensed table-striped">
			<?php foreach ($___memory as $_ => $value): ?>
				<tr>
					<td class="col-lg-4"><?php echo $_ ?></td>
					<td><?php echo round($value / 1024) ?>Kb</td>
				</tr>
			<?php endforeach ?>			
		</table>
		<?php if (DB_LOGGING): ?>
		<h3>SQLs Without cache</h3>
		<table class="table table-condensed table-striped">
			<?php foreach (Recordset::$sqls as $_ => $sql): ?>
				<?php if($sql['cached']) continue; ?>
				<tr>
					<td class="mr-sql-trace" data-id="<?php echo $_ ?>">
						<?php echo $sql['sql'] ?><br /><br />
						<?php echo $sql['duration'] ?> seconds.
					</td>
				</tr>
			<?php endforeach ?>
		</table>
		<?php endif; ?>
	</div>
	<?php foreach (Recordset::$sqls as $_ => $sql): ?>
		<?php if($sql['cached']) continue; ?>
		<div id="mr-sql-trace-<?php echo $_ ?>" class="mr-sql-trace-data">
			<table class="table table-condensed table-striped">
			<?php if (DB_LOGGING): ?>
				<?php foreach ($sql['traces'] as $trace): ?>
					<?php foreach ($trace as $detail): ?>
						<tr>
							<td>
								Line <?php echo $detail['line'] ?> @ <?php echo $detail['file'] ?>
							</td>
						</tr>
					<?php endforeach ?>
				<?php endforeach ?>				
			<?php else: ?>
				<tr><td>DB_LOGGING noot enabled</td></tr>
			<?php endif ?>
			</table>
		</div>
	<?php endforeach ?>
</div>